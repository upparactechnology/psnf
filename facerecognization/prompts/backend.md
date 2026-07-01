# AI Prompt: FastAPI Backend Development

Use this prompt to instruct an AI assistant to implement the FastAPI backend API:

```markdown
## Objective
Build the async REST API backend for the AI Face Recognition Attendance System using Python 3.12, FastAPI, and SQLAlchemy 2.0.

## Requirements
1. **Asynchronous Framework**: Leverage FastAPI's `async/await` syntax for all endpoint handlers, CRUD queries, and database sessions.
2. **Pydantic Validation**: Implement strict validation schemas for all inputs and outputs (e.g., login request, employee creation, attendance logs).
3. **Authentication Layer**:
   - Secure routes using OAuth2 Bearer Tokens.
   - Implement JWT access and refresh token creation and verification.
   - Use `passlib[bcrypt]` to hash and verify administrative user passwords.
4. **CRUD Modules**:
   - **Authentication**: `/auth/login`, `/auth/refresh`.
   - **Employees**: `/employees` (GET with pagination/filtering, POST, GET by ID, PUT, DELETE).
   - **Attendance Logs**: `/attendance` (GET with date filters, POST for manual entry).
   - **Health**: `/health` (returns status check for DB connectivity).
5. **Rate Limiting & CORS**: Configure CORS middleware to restrict access to trusted origins.
6. **Error Responses**: Implement global exception handlers returning consistent error structures:
   ```json
   {
     "error": "Not Found",
     "detail": "Employee with ID 12 was not found in the database."
   }
   ```

## Target Folder Structure
```
backend/
├── app/
│   ├── api/
│   │   ├── v1/
│   │   │   ├── auth.py                 # Authentication routes
│   │   │   ├── employees.py            # Employee management routes
│   │   │   ├── attendance.py           # Attendance routing
│   │   │   └── health.py               # Health verification
│   │   └── router.py                   # Master APIRouter configuration
│   ├── schemas/
│   │   ├── auth.py                     # Auth Pydantic definitions
│   │   ├── employee.py                 # Employee metadata schemas
│   │   └── attendance.py               # Attendance event schemas
│   ├── crud/
│   │   ├── base.py                     # Generic CRUD parent class
│   │   ├── employee.py                 # DB queries for Employees
│   │   └── attendance.py               # DB queries for Attendance
│   └── main.py                         # FastAPI App initialization
```

## Coding Standards
- Maintain strict type annotations across functions.
- Run database queries using asynchronous session transactions (`async with session.begin():`).
- Implement logging configurations using Python's standard `logging` library.

## Expected Deliverables
1. FastAPI app setup (`main.py`).
2. API routing scripts inside `app/api/v1/`.
3. Pydantic schema validation files inside `app/schemas/`.
4. DB query implementation files inside `app/crud/`.

## Acceptance Criteria
- Starting the server with `uvicorn app.main:app` runs without errors.
- Navigating to `/docs` opens the Swagger interactive documentation, showing all configured endpoints, correct schemas, and auth locks.
```
