from datetime import datetime
from zoneinfo import ZoneInfo

IST = ZoneInfo("Asia/Kolkata")


def now_ist() -> datetime:
    """Current time in IST (timezone-aware) — same clock the services use."""
    return datetime.now(IST)


def now_ist_naive() -> datetime:
    """Current IST wall-clock time without tzinfo (for MySQL DATETIME defaults)."""
    return datetime.now(IST).replace(tzinfo=None)
