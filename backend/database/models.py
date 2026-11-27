from sqlalchemy import Column, Integer, String, Enum, DateTime, Float, Text, Date, BigInteger, ForeignKey
from sqlalchemy.orm import relationship
from database.connection import Base
from utils.timeutil import now_ist_naive

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
    created_at      = Column(DateTime, default=now_ist_naive)
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
    created_at  = Column(DateTime, default=now_ist_naive)

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
    tenant_id       = Column(Integer, nullable=True)
    school_id       = Column(Integer, nullable=True)
    branch_id       = Column(Integer, nullable=True)
    attendance_date = Column(Date, nullable=True)
    check_in        = Column(DateTime, nullable=True)
    confidence      = Column(Float, nullable=True, default=0.0)
    image_path      = Column(String(255), nullable=True)
    ip_address      = Column(String(45), nullable=True)
    user_agent      = Column(Text, nullable=True)
    created_at      = Column(DateTime, default=now_ist_naive)

    user = relationship("ERPUser", back_populates="attendances", foreign_keys=[user_id])


class FaceScanLog(Base):
    """
    One row per kiosk recognition attempt (success, cooldown or failure) — powers the
    admin "Face Scan Logs" page. Scores are stored here and NEVER sent to the kiosk.
    Failed-scan snapshots live in uploads/failed/ and are deleted after
    settings.FAILED_SCAN_IMAGE_DAYS; rows after settings.SCAN_LOG_RETENTION_DAYS.
    Same schema as database/migrations/20261128_CreateFaceScanLogs.php.
    """
    __tablename__ = "face_scan_logs"
    __table_args__ = {"extend_existing": True}

    id              = Column(BigInteger, primary_key=True, autoincrement=True)
    created_at      = Column(DateTime, default=now_ist_naive, index=True)
    tenant_id       = Column(Integer, nullable=True, index=True)
    outcome         = Column(String(20), nullable=False, index=True)   # success | cooldown | not_recognized | blurry | dark | overexposed | no_face | multiple_faces | liveness | error
    message         = Column(String(255), nullable=True)
    matched_user_id = Column(Integer, nullable=True, index=True)      # set on success / cooldown
    best_user_id    = Column(Integer, nullable=True)                  # closest registered person (even below threshold)
    score           = Column(Float, nullable=True)                    # best similarity score
    second_score    = Column(Float, nullable=True)                    # best score of any other person
    threshold       = Column(Float, nullable=True)
    blur_score      = Column(Float, nullable=True)                    # face-region sharpness
    blur_threshold  = Column(Float, nullable=True)
    brightness      = Column(Float, nullable=True)                    # whole frame
    face_brightness = Column(Float, nullable=True)
    face_contrast   = Column(Float, nullable=True)
    face_width      = Column(Integer, nullable=True)                  # px in the received image
    det_score       = Column(Float, nullable=True)
    yaw             = Column(Float, nullable=True)
    pitch           = Column(Float, nullable=True)
    img_width       = Column(Integer, nullable=True)
    img_height      = Column(Integer, nullable=True)
    image_path      = Column(String(255), nullable=True)
    session_id      = Column(String(40), nullable=True, index=True)   # groups the retries of one kiosk scan
    attempt         = Column(Integer, nullable=True)
    client_meta     = Column(Text, nullable=True)                     # JSON from the kiosk (camera, crop, device)
    processing_ms   = Column(Integer, nullable=True)
    ip_address      = Column(String(45), nullable=True)
    user_agent      = Column(String(255), nullable=True)
