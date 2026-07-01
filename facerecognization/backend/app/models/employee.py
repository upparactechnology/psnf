import datetime
from sqlalchemy import Integer, String, Enum, DateTime, ForeignKey, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship
from app.models.base import Base

class Department(Base):
    __tablename__ = "departments"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False)
    code: Mapped[str] = mapped_column(String(10), unique=True, nullable=False, index=True)

    employees: Mapped[list["Employee"]] = relationship("Employee", back_populates="department")

class Employee(Base):
    __tablename__ = "employees"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    employee_id: Mapped[str] = mapped_column(String(50), unique=True, nullable=False, index=True)
    first_name: Mapped[str] = mapped_column(String(100), nullable=False)
    last_name: Mapped[str] = mapped_column(String(100), nullable=False)
    email: Mapped[str] = mapped_column(String(255), unique=True, nullable=False, index=True)
    phone: Mapped[str] = mapped_column(String(20), nullable=True)
    department_id: Mapped[int] = mapped_column(Integer, ForeignKey("departments.id", ondelete="SET NULL"), nullable=True)
    status: Mapped[str] = mapped_column(Enum("ACTIVE", "SUSPENDED", "TERMINATED", name="employee_status_types"), nullable=False, default="ACTIVE", index=True)
    created_at: Mapped[datetime.datetime] = mapped_column(DateTime, default=datetime.datetime.utcnow, nullable=False)

    department: Mapped["Department"] = relationship("Department", back_populates="employees")
    face_embeddings: Mapped[list["FaceEmbedding"]] = relationship("FaceEmbedding", back_populates="employee", cascade="all, delete-orphan")
    attendance_logs: Mapped[list["AttendanceLog"]] = relationship("AttendanceLog", back_populates="employee", cascade="all, delete-orphan")
    shifts: Mapped[list["EmployeeShift"]] = relationship("EmployeeShift", back_populates="employee", cascade="all, delete-orphan")

class FaceEmbedding(Base):
    __tablename__ = "face_embeddings"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    employee_id: Mapped[int] = mapped_column(Integer, ForeignKey("employees.id", ondelete="CASCADE"), nullable=False)
    embedding_vector: Mapped[str] = mapped_column(Text, nullable=False)  # JSON-serialized list of 512 floats
    capture_angle: Mapped[str] = mapped_column(String(30), nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(DateTime, default=datetime.datetime.utcnow, nullable=False)

    employee: Mapped["Employee"] = relationship("Employee", back_populates="face_embeddings")
