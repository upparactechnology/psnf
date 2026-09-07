import os
from pydantic_settings import BaseSettings
from dotenv import load_dotenv

load_dotenv()

class Settings(BaseSettings):
    # App Settings
    APP_NAME: str = "Face Recognition Attendance Service"
    DEBUG: bool = os.getenv("DEBUG", "False").lower() in ("true", "1", "t")
    PORT: int = int(os.getenv("PORT", "8000"))
    HOST: str = os.getenv("HOST", "0.0.0.0")

    # MySQL Database Config
    DB_HOST: str = os.getenv("DB_HOST", "127.0.0.1")
    DB_PORT: int = int(os.getenv("DB_PORT", "3306"))
    DB_USER: str = os.getenv("DB_USER", "root")
    DB_PASSWORD: str = os.getenv("DB_PASSWORD", "")
    DB_NAME: str = os.getenv("DB_NAME", "psnf_drm")

    @property
    def DATABASE_URL(self) -> str:
        # Load database credentials directly from PHP config/database.php if it exists
        import re
        import urllib.parse
        config_path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "config", "database.php")
        
        db_host = self.DB_HOST
        db_port = self.DB_PORT
        db_user = self.DB_USER
        db_pass = self.DB_PASSWORD
        db_name = self.DB_NAME
        
        if os.path.exists(config_path):
            try:
                with open(config_path, "r", encoding="utf-8") as f:
                    content = f.read()
                
                # Extract string values
                matches = re.findall(r"['\"](\w+)['\"]\s*=>\s*['\"](.*?)['\"]", content)
                for key, val in matches:
                    if key == "host": db_host = val
                    elif key == "username": db_user = val
                    elif key == "password": db_pass = val
                    elif key == "dbname": db_name = val
                
                # Extract numeric values
                num_matches = re.findall(r"['\"](\w+)['\"]\s*=>\s*(\d+)", content)
                for key, val in num_matches:
                    if key == "port": db_port = int(val)
            except Exception as e:
                pass
                
        # URL-encode the username and password to handle special characters (e.g. '@' or ':') safely in SQLAlchemy URLs
        db_user_encoded = urllib.parse.quote_plus(db_user)
        db_pass_encoded = urllib.parse.quote_plus(db_pass)
        
        return f"mysql+pymysql://{db_user_encoded}:{db_pass_encoded}@{db_host}:{db_port}/{db_name}?charset=utf8mb4"

    # AI Recognition & Verification Settings
    SIMILARITY_THRESHOLD: float = float(os.getenv("SIMILARITY_THRESHOLD", "0.55"))  # Standard Sweet Spot (0.50 - 0.60)
    COOLDOWN_MINUTES: int = int(os.getenv("COOLDOWN_MINUTES", "10"))
    MODEL_NAME: str = os.getenv("MODEL_NAME", "buffalo_l")
    DETECTION_SIZE: int = int(os.getenv("DETECTION_SIZE", "640"))
    MIN_FACE_SIZE: int = int(os.getenv("MIN_FACE_SIZE", "60"))
    MODEL_IDLE_TIMEOUT: int = int(os.getenv("MODEL_IDLE_TIMEOUT", "600"))  # seconds before model auto-unloads
    NO_FACE_IDLE_TIMEOUT: int = int(os.getenv("NO_FACE_IDLE_TIMEOUT", "300"))  # seconds with no face detected before model unloads

    # Image Quality Thresholds
    BLUR_THRESHOLD: float = float(os.getenv("BLUR_THRESHOLD", "40.0"))  # Laplacian variance threshold
    MIN_BRIGHTNESS: float = float(os.getenv("MIN_BRIGHTNESS", "30.0"))
    MAX_BRIGHTNESS: float = float(os.getenv("MAX_BRIGHTNESS", "230.0"))

    # Storage Paths
    BASE_DIR: str = os.path.dirname(os.path.abspath(__file__))
    UPLOAD_FACES_DIR: str = os.path.join(BASE_DIR, "uploads", "faces")
    UPLOAD_ATTENDANCE_DIR: str = os.path.join(BASE_DIR, "uploads", "attendance")

    # Security & JWT
    JWT_SECRET: str = os.getenv("JWT_SECRET", "super-secret-jwt-key-psnf-2026")
    JWT_ALGORITHM: str = "HS256"
    RATE_LIMIT_REQUESTS: int = 60
    RATE_LIMIT_WINDOW_SECONDS: int = 60

settings = Settings()

# Ensure upload directories exist
os.makedirs(settings.UPLOAD_FACES_DIR, exist_ok=True)
os.makedirs(settings.UPLOAD_ATTENDANCE_DIR, exist_ok=True)
