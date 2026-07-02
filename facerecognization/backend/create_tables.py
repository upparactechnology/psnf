import asyncio
import logging
from app.core.database import engine
from app.models.base import Base
import app.models  # Crucial: Import all models to register them with Base.metadata
from app.models.auth import User
from app.models.shift import Shift
from app.core.security import get_password_hash
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("db_init")

async def init_db():
    logger.info("Auto-creating database if not exists...")
    # 0. Automatically create database using raw pymysql connection
    import pymysql
    try:
        conn = pymysql.connect(host="127.0.0.1", user="root", password="", port=3306)
        with conn.cursor() as cursor:
            cursor.execute("CREATE DATABASE IF NOT EXISTS psnf_drm")
        conn.close()
        logger.info("Database 'psnf_drm' checked/created successfully.")
    except Exception as e:
        logger.warning(f"Auto-create database skipped: {e}")

    logger.info("Initializing database tables...")
    async with engine.begin() as conn:
        # Create all tables
        await conn.run_sync(Base.metadata.create_all)
    logger.info("Tables created successfully.")

    # Create active session to insert seed data
    async with AsyncSession(engine) as session:

        # Check if default shift exists
        shift_result = await session.execute(select(Shift).where(Shift.name == "Default Shift"))
        default_shift = shift_result.scalar_one_or_none()

        if not default_shift:
            logger.info("Seeding default shift profile...")
            import datetime
            new_shift = Shift(
                name="Default Shift",
                start_time=datetime.time(9, 0),
                end_time=datetime.time(18, 0),
                grace_period_minutes=15,
                half_day_minutes=240
            )
            session.add(new_shift)
        else:
            logger.info("Default shift profile already exists.")

        await session.commit()
    logger.info("Database seeding completed.")

if __name__ == "__main__":
    asyncio.run(init_db())
