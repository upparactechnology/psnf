from sqlalchemy import Column, Integer, String, Enum, DateTime, ForeignKey, Float, Text, Date
from sqlalchemy.orm import relationship
from datetime import datetime
from database.connection import Base

class Employee(Base):
    __tablename__ = "employees"

    id = Column(Integer, primary_class=True, primary_key=True, index=True, autoincrement=True)
    employee_code = Column(String(50), unique=True, nullable=False, index=True)
    name = Column(String(150), nullable=False)
    department = Column(String(100), nullable=True)
    designation = Column(String(100), nullable=True)
    status = Column(Enum("active", "inactive", name="employee_status"), default="active", index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    embeddings = relationship("FaceEmbedding", back_populates="employee", cascade="all, delete-orphan")
    attendances = relationship("Attendance", back_populates="employee", cascade="all, delete-orphan")

class FaceEmbedding(Base):
    __tablename__ = "face_embeddings"

    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    employee_id = Column(Integer, ForeignKey("employees.id", ondelete="CASCADE"), nullable=False, index=True)
    embedding = Column(Text, nullable=False)  # JSON serialized list of 512 float numbers
    image_path = Column(String(255), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    employee = relationship("Employee", back_populates="embeddings")

class Attendance(Base):
    __tablename__ = "attendance"

    id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    employee_id = Column(Integer, ForeignKey("employees.id", ondelete="CASCADE"), nullable=False, index=True)
    attendance_date = Column(Date, nullable=False, index=True)
    check_in = Column(DateTime, nullable=False, index=True)
    confidence = Column(Float, nullable=False)
    image_path = Column(String(255), nullable=True)
    ip_address = Column(String(45), nullable=True)
    user_agent = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    employee = relationship("Employee", back_populates="attendances")
