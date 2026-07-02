import pymysql
import json

def check_db_data():
    conn = pymysql.connect(
        host="127.0.0.1",
        user="root",
        password="",
        port=3306,
        database="psnf_drm"
    )
    try:
        with conn.cursor() as cursor:
            # 1. Check Employees
            cursor.execute("SELECT id, employee_id, first_name, last_name, email FROM employees")
            employees = cursor.fetchall()
            print("=== EMPLOYEES ===")
            for emp in employees:
                print(f"ID: {emp[0]} | EmpID: {emp[1]} | Name: {emp[2]} {emp[3]} | Email: {emp[4]}")
            
            print("\n=== FACE EMBEDDINGS ===")
            cursor.execute("SELECT id, employee_id, capture_angle, CHAR_LENGTH(embedding_vector) as len FROM face_embeddings")
            embeddings = cursor.fetchall()
            for emb in embeddings:
                print(f"ID: {emb[0]} | EmpID (FK): {emb[1]} | Angle: {emb[2]} | Vector Char Len: {emb[3]}")
                
                # Fetch first few elements of vector
                cursor.execute(f"SELECT embedding_vector FROM face_embeddings WHERE id = {emb[0]}")
                vec_str = cursor.fetchone()[0]
                try:
                    vec = json.loads(vec_str)
                    print(f"  Vector sample (first 5 values): {vec[:5]}... (Total length: {len(vec)})")
                except Exception as e:
                    print(f"  Error parsing vector: {e}")
                    
    except Exception as e:
        print(f"Error checking data: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    check_db_data()
