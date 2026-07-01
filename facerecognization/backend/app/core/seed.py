import asyncio
import datetime
from sqlalchemy.future import select
from app.core.database import SessionLocal, engine
from app.core.security import get_password_hash
from app.models.auth import User
from app.models.employee import Department
from app.models.shift import Shift

async def seed_data():
    async with SessionLocal() as db:
        # 1. Seed Departments
        dept_check = await db.execute(select(Department).filter(Department.code == "ENG"))
        dept = dept_check.scalars().first()
        if not dept:
            dept = Department(name="Engineering", code="ENG")
            db.add(dept)
            await db.commit()
            print("Department 'ENG' seeded.")
        else:
            print("Department 'ENG' already exists.")

        # 2. Seed Admin User
        admin_check = await db.execute(select(User).filter(User.username == "admin_main"))
        admin = admin_check.scalars().first()
        if not admin:
            hashed_pw = get_password_hash("SecurePassword123")
            admin = User(
                username="admin_main",
                password_hash=hashed_pw,
                role="SUPER_ADMIN"
            )
            db.add(admin)
            await db.commit()
            print("Super Admin user 'admin_main' seeded with password 'SecurePassword123'.")
        else:
            print("Admin user 'admin_main' already exists.")

        # 3. Seed Default Shift
        shift_check = await db.execute(select(Shift).filter(Shift.name == "Morning Shift"))
        shift = shift_check.scalars().first()
        if not shift:
            shift = Shift(
                name="Morning Shift",
                start_time=datetime.time(9, 0),
                end_time=datetime.time(18, 0),
                grace_period_minutes=15,
                half_day_minutes=240
            )
            db.add(shift)
            await db.commit()
            print("Morning Shift seeded (09:00 - 18:00).")
        else:
            print("Morning Shift already exists.")

if __name__ == "__main__":
    asyncio.run(seed_data())
