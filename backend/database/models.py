from sqlalchemy import Column, Integer, String, Enum, DateTime, Float, Text, Date, BigInteger, ForeignKey
from sqlalchemy.orm import relationship
from datetime import datetime
from database.connection import Base


class ERPUser(Base):
    """
    Maps to the ERP's main `users` table (staff/teachers from /staff/users).
    This is the PRIMARY identity source — no separate employees table needed.
    """
    __tablename__ = "users"
    __table_args__ = {"extend_existing": True}

    id              = Column(Integer, primary_key=True, index=True, autoincrement=True)
    uuid            = Column(String(36), unique=True, nullable=True)
    tenant_id       = Column(Integer, nullable=True)
    school_id       = Column(Integer, nullable=True)
    branch_id       = Column(Integer, nullable=True)
    name            = Column(String(191), nullable=False)
    email           = Column(String(191), nullable=False, unique=True, index=True)
    phone           = Column(String(30), nullable=True)
    designation     = Column(String(100), nullable=True)
    employee_id     = Column(String(50), nullable=True, index=True)  # employee code / ID
    avatar          = Column(String(255), nullable=True)
    is_active       = Column(Integer, default=1)
    created_at      = Column(DateTime, default=datetime.utcnow)
    updated_at      = Column(DateTime, nullable=True)
    deleted_at      = Column(DateTime, nullable=True)

    face_embeddings = relationship("FaceEmbedding", back_populates="user", cascade="all, delete-orphan",
                                   foreign_keys="FaceEmbedding.user_id")
    attendances     = relationship("Attendance", back_populates="user", cascade="all, delete-orphan",
                                   foreign_keys="Attendance.user_id")


class FaceEmbedding(Base):
    """
    Stores InsightFace 512-dim L2-normalized embeddings.
    Linked to `users` table via user_id.
    """
    __tablename__ = "face_embeddings"
    __table_args__ = {"extend_existing": True}

    id          = Column(Integer, primary_key=True, index=True, autoincrement=True)
    user_id     = Column(Integer, ForeignKey("users.id"), nullable=True, index=True)    # FK → users.id (ERP)
    employee_id = Column(Integer, nullable=True, index=True)    # legacy column (kept for compat)
    embedding   = Column(Text, nullable=False)                  # JSON list of 512 floats
    image_path  = Column(String(255), nullable=True)
    created_at  = Column(DateTime, default=datetime.utcnow)

    user = relationship("ERPUser", back_populates="face_embeddings", foreign_keys=[user_id])


class Attendance(Base):
    """
    Face attendance records — linked to `users` table via user_id.
    """
    __tablename__ = "attendance"
    __table_args__ = {"extend_existing": True}

    id              = Column(Integer, primary_key=True, index=True, autoincrement=True)
    user_id         = Column(Integer, ForeignKey("users.id"), nullable=True, index=True)    # FK → users.id
    employee_id     = Column(Integer, nullable=True, index=True)    # legacy (kept)
    attendance_date = Column(Date, nullable=True)
    check_in        = Column(DateTime, nullable=True)
    confidence      = Column(Float, nullable=True, default=0.0)
    image_path      = Column(String(255), nullable=True)
    ip_address      = Column(String(45), nullable=True)
    user_agent      = Column(Text, nullable=True)
    created_at      = Column(DateTime, default=datetime.utcnow)

    user = relationship("ERPUser", back_populates="attendances", foreign_keys=[user_id])
