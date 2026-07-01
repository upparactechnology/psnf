import os
from pydantic_settings import BaseSettings
from pydantic import Field

class Settings(BaseSettings):
    DATABASE_URL: str = Field(
        default="mysql+pymysql://root:rootpassword@database:3306/attendance_db",
        validation_alias="DATABASE_URL"
    )
    SECRET_KEY: str = Field(
        default="37a1f592a34fcbe9b0b46ad88133544c9b91fb8be338d38101a2f1abef6a12d1",
        validation_alias="SECRET_KEY"
    )
    ACCESS_TOKEN_EXPIRE_MINUTES: int = Field(default=60, validation_alias="ACCESS_TOKEN_EXPIRE_MINUTES")
    REFRESH_TOKEN_EXPIRE_DAYS: int = Field(default=14, validation_alias="REFRESH_TOKEN_EXPIRE_DAYS")
    ENV_NAME: str = Field(default="development", validation_alias="ENV_NAME")
    LOG_LEVEL: str = Field(default="INFO", validation_alias="LOG_LEVEL")

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"
        extra = "ignore"

settings = Settings()
