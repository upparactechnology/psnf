from sqlalchemy.ext.asyncio import create_async_engine, async_sessionmaker, AsyncSession
from app.core.config import settings

# For SQLAlchemy async, we use the mysql+aiomysql or mysql+pymysql async driver.
# Let's ensure the URL is converted to an async driver if needed.
# Since we are using SQLAlchemy async, we use mysql+aiomysql or mysql+pymysql (using run_sync if pymysql, but aiomysql is standard async).
# Let's check if the database URL uses pymysql. Pymysql is blocking, so we replace it with aiomysql or use the async driver.
# Let's assume mysql+aiomysql is the async protocol driver.
async_db_url = settings.DATABASE_URL
if "pymysql" in async_db_url:
    async_db_url = async_db_url.replace("pymysql", "aiomysql")
elif "mysql://" in async_db_url:
    async_db_url = async_db_url.replace("mysql://", "mysql+aiomysql://")

engine = create_async_engine(
    async_db_url,
    pool_pre_ping=True,
    pool_size=10,
    max_overflow=20
)

SessionLocal = async_sessionmaker(
    bind=engine,
    class_=AsyncSession,
    expire_on_commit=False
)

async def get_db():
    async with SessionLocal() as session:
        try:
            yield session
        finally:
            await session.close()
