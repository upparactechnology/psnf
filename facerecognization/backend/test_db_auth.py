import asyncio
import pymysql
import bcrypt

def verify_password(plain_password: str, hashed_password: str) -> bool:
    try:
        # Convert PHP $2y$ variant prefix to standard $2b$ for python bcrypt compatibility
        py_hash = hashed_password.replace("$2y$", "$2b$") if hashed_password else ""
        return bcrypt.checkpw(plain_password.encode('utf-8'), py_hash.encode('utf-8'))
    except Exception as e:
        print(f"Bcrypt verification error: {e}")
        return False

def test_connection():
    print("="*60)
    print("          DIAGNOSTIC DATABASE AUTHENTICATION CHECK")
    print("="*60)

    # 1. Connect to MySQL using PyMySQL
    conn = None
    try:
        conn = pymysql.connect(
            host="127.0.0.1",
            user="root",
            password="",
            port=3306
        )
        print("✅ 1. MySQL Server Connection: SUCCESSFUL")
    except Exception as e:
        print(f"❌ 1. MySQL Server Connection: FAILED\n   Error: {e}")
        return

    # 2. Query user from psnf_drm.users
    try:
        with conn.cursor() as cursor:
            cursor.execute("USE psnf_drm")
            print("✅ 2. Select Database 'psnf_drm': SUCCESSFUL")
            
            cursor.execute("SELECT id, password, name FROM users WHERE email = 'admin@psnf.edu' AND deleted_at IS NULL")
            row = cursor.fetchone()
            
            if row:
                user_id, pwd_hash, name = row
                print(f"✅ 3. Find user 'admin@psnf.edu': FOUND (ID: {user_id}, Name: {name})")
                print(f"   Hash: {pwd_hash}")
                
                # 3. Test verification
                test_pw = "admin123"
                if verify_password(test_pw, pwd_hash):
                    print(f"✅ 4. Password verification for '{test_pw}': SUCCESSFUL!")
                else:
                    print(f"❌ 4. Password verification for '{test_pw}': FAILED (Hash did not match)")
            else:
                print("❌ 3. Find user 'admin@psnf.edu': NOT FOUND in table 'users'")
    except Exception as e:
        print(f"❌ Database Query Process: FAILED\n   Error: {e}")
    finally:
        if conn:
            conn.close()
    print("="*60)

if __name__ == "__main__":
    test_connection()
