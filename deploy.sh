#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# PSNF ERP - Server Deployment Script
# For: Hostinger VPS (Ubuntu/Debian)
# ═══════════════════════════════════════════════════════════════

set -e

# ─── Colors ───────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  PSNF ERP - Server Deployment Script${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"

# ─── Configuration ────────────────────────────────────────────
APP_DIR="/var/www/psnf"
BACKEND_DIR="$APP_DIR/backend"
PYTHON_VERSION="python3"
VENV_DIR="$BACKEND_DIR/venv"
DB_NAME="psnf_drm"
DB_USER="psnf_user"
DB_PASS=""  # Set this before running!

# ─── Step 1: System Packages ─────────────────────────────────
echo -e "\n${YELLOW}[1/8] Installing system packages...${NC}"
sudo apt update
sudo apt install -y \
    apache2 \
    libapache2-mod-php8.1 \
    php8.1 \
    php8.1-mysql \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-curl \
    php8.1-gd \
    php8.1-zip \
    php8.1-bcmath \
    mysql-server \
    python3 \
    python3-pip \
    python3-venv \
    python3-dev \
    build-essential \
    libgl1-mesa-glx \
    libglib2.0-0 \
    git \
    curl \
    unzip

echo -e "${GREEN}✓ System packages installed${NC}"

# ─── Step 2: Enable Apache Modules ───────────────────────────
echo -e "\n${YELLOW}[2/8] Configuring Apache...${NC}"
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod proxy
sudo a2enmod proxy_http

echo -e "${GREEN}✓ Apache modules enabled${NC}"

# ─── Step 3: MySQL Setup ─────────────────────────────────────
echo -e "\n${YELLOW}[3/8] Setting up MySQL database...${NC}"

if [ -z "$DB_PASS" ]; then
    echo -e "${RED}⚠ Set DB_PASS in this script before running!${NC}"
    echo "  Or run manually:"
    echo "  mysql -u root -p -e \"CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
    echo "  mysql -u root -p -e \"CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';\""
    echo "  mysql -u root -p -e \"GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';\""
else
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"
    echo -e "${GREEN}✓ Database created: $DB_NAME${NC}"
fi

# ─── Step 4: Deploy Application Files ────────────────────────
echo -e "\n${YELLOW}[4/8] Deploying application files...${NC}"

# Clone from GitHub (replace with your repo URL)
if [ ! -d "$APP_DIR" ]; then
    sudo git clone https://github.com/YOUR_USERNAME/psnf.git "$APP_DIR"
    echo -e "${GREEN}✓ Repository cloned${NC}"
else
    cd "$APP_DIR"
    sudo git pull origin main
    echo -e "${GREEN}✓ Repository updated${NC}"
fi

sudo chown -R www-data:www-data "$APP_DIR"

echo -e "${GREEN}✓ Application deployed to $APP_DIR${NC}"

# ─── Step 5: Configure Apache Virtual Host ────────────────────
echo -e "\n${YELLOW}[5/8] Creating Apache virtual host...${NC}"

sudo tee /etc/apache2/sites-available/psnf.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName YOUR_DOMAIN.com
    DocumentRoot $APP_DIR/public

    <Directory $APP_DIR/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/psnf_error.log
    CustomLog \${APACHE_LOG_DIR}/psnf_access.log combined
</VirtualHost>
EOF

sudo a2ensite psnf.conf
sudo systemctl reload apache2

echo -e "${GREEN}✓ Apache configured${NC}"

# ─── Step 6: Setup Python Backend ────────────────────────────
echo -e "\n${YELLOW}[6/8] Setting up Python backend...${NC}"

cd "$BACKEND_DIR"

# Create virtual environment
sudo $PYTHON_VERSION -m venv "$VENV_DIR"
source "$VENV_DIR/bin/activate"

# Install dependencies
pip install --upgrade pip
pip install -r requirements.txt
pip install gunicorn uvicorn[standard]

echo -e "${GREEN}✓ Python backend installed${NC}"

# ─── Step 7: Create .env for Production ─────────────────────
echo -e "\n${YELLOW}[7/8] Creating production .env...${NC}"

sudo tee "$BACKEND_DIR/.env" > /dev/null <<EOF
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASS
DB_NAME=$DB_NAME

# App
DEBUG=False
HOST=0.0.0.0
PORT=8000

# Security (CHANGE THESE!)
JWT_SECRET=$(openssl rand -hex 32)
SIMILARITY_THRESHOLD=0.55
COOLDOWN_MINUTES=10

# Model
MODEL_NAME=buffalo_l
MODEL_IDLE_TIMEOUT=600
NO_FACE_IDLE_TIMEOUT=300
EOF

sudo chown www-data:www-data "$BACKEND_DIR/.env"
sudo chmod 600 "$BACKEND_DIR/.env"

echo -e "${GREEN}✓ Production .env created${NC}"

# ─── Step 8: Create Systemd Service ──────────────────────────
echo -e "\n${YELLOW}[8/8] Creating systemd service for Python backend...${NC}"

sudo tee /etc/systemd/system/psnf-backend.service > /dev/null <<EOF
[Unit]
Description=PSNF Face Recognition Backend (FastAPI)
After=network.target mysql.service
Wants=mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=$BACKEND_DIR
Environment="PATH=$VENV_DIR/bin"
ExecStart=$VENV_DIR/bin/gunicorn -w 2 -k uvicorn.workers.UvicornWorker app:app --bind 127.0.0.1:8000 --timeout 120
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable psnf-backend
sudo systemctl start psnf-backend

echo -e "${GREEN}✓ Python backend service created and started${NC}"

# ─── Step 9: Set Permissions ─────────────────────────────────
echo -e "\n${YELLOW}[+] Setting directory permissions...${NC}"

sudo mkdir -p "$APP_DIR/storage/uploads"
sudo mkdir -p "$APP_DIR/storage/logs"
sudo mkdir -p "$APP_DIR/backend/uploads/faces"
sudo mkdir -p "$APP_DIR/backend/uploads/attendance"

sudo chown -R www-data:www-data "$APP_DIR/storage"
sudo chown -R www-data:www-data "$APP_DIR/backend/uploads"
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/backend/uploads"

echo -e "${GREEN}✓ Permissions set${NC}"

# ─── Done ────────────────────────────────────────────────────
echo -e "\n${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✓ Deployment Complete!${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "  ${YELLOW}Next Steps:${NC}"
echo -e "  1. Update YOUR_DOMAIN.com in /etc/apache2/sites-available/psnf.conf"
echo -e "  2. Update config/database.php with production DB credentials"
echo -e "  3. Update config/app.php: set debug => false, base_url"
echo -e "  4. Update config/auth.php: change jwt_secret"
echo -e "  5. Import database: mysql -u $DB_USER -p $DB_NAME < dump.sql"
echo -e "  6. Run migrations: visit https://YOUR_DOMAIN.com/migrate"
echo -e "  7. Restart backend: sudo systemctl restart psnf-backend"
echo ""
echo -e "  ${YELLOW}Service Commands:${NC}"
echo -e "  sudo systemctl status psnf-backend"
echo -e "  sudo systemctl restart psnf-backend"
echo -e "  sudo systemctl stop psnf-backend"
echo -e "  sudo journalctl -u psnf-backend -f"
echo ""
echo -e "  ${YELLOW}Backend API:${NC}"
echo -e "  http://YOUR_DOMAIN.com:8000/health"
echo ""
