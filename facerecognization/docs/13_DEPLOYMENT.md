# Deployment and Infrastructure Guide

This document details the environment configuration, Docker Compose architecture, Nginx proxy setup, SSL generation, and security hardening for production deployment on an Ubuntu VPS.

---

## 1. Hosting Hardware Recommendations (Hostinger VPS)

For a company with 20–50 employees, scaling up to hundreds:
* **Recommended Tier**: KVM 2 VPS Plan (or equivalent).
* **Specifications**:
  * **OS**: Ubuntu 22.04 LTS 64-bit
  * **vCPU**: 2 Cores
  * **RAM**: 4 GB
  * **Storage**: 50 GB NVMe Disk
  * **Bandwidth**: Unmetered (100Mbps port interface)

---

## 2. Docker & Compose Architecture

```
                       DOCKER COMPOSE ROUTING
                                │
                    Nginx Container (Port 80/443)
                                │
                     ┌──────────┴──────────┐
                     │                     │
            React Static Files      FastAPI Application
                                    (Internal Port 8000)
                                           │
                                     MySQL Database
                                     (Internal Port 3306)
```

The system runs in containerized environments managed by Docker Compose.

### 2.1 File: `docker-compose.prod.yml`
```yaml
version: '3.8'

services:
  database:
    image: mysql:8.0
    container_name: attendance_mysql_prod
    restart: always
    environment:
      MYSQL_DATABASE: attendance_db
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - app_network

  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: attendance_backend_prod
    restart: always
    environment:
      - DATABASE_URL=mysql+pymysql://root:${DB_ROOT_PASSWORD}@database/attendance_db
      - SECRET_KEY=${API_SECRET_KEY}
    depends_on:
      - database
    networks:
      - app_network

  nginx:
    image: nginx:latest
    container_name: attendance_nginx_prod
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - /etc/letsencrypt:/etc/letsencrypt
    depends_on:
      - backend
    networks:
      - app_network

volumes:
  mysql_data:

networks:
  app_network:
    driver: bridge
```

---

## 3. Nginx Configuration With SSL

### 3.1 File: `docker/nginx/conf.d/default.conf`
```nginx
server {
    listen 80;
    server_name attendance.company.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name attendance.company.com;

    ssl_certificate /etc/letsencrypt/live/attendance.company.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/attendance.company.com/privkey.pem;
    ssl_protocols TLSv1.3;

    # Static Web Client Hosting
    location / {
        root /var/www/dashboard;
        index index.html;
        try_files $uri $uri/ /index.html;
    }

    # API Routing
    location /api/v1/ {
        proxy_pass http://backend:8000/api/v1/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## 4. Firewall Settings (UFW)

To secure the host system, configure the Uncomplicated Firewall (UFW) to block unneeded public ports:

```bash
# Block all incoming by default
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH administration
sudo ufw allow 22/tcp

# Allow Web traffic
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

For testing procedures and verification matrices, refer to [14_TESTING.md](14_TESTING.md).
