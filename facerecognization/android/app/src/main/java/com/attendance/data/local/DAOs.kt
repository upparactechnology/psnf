package com.attendance.data.local

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query

@Dao
interface EmployeeDao {
    @Query("SELECT * FROM employees WHERE status = 'ACTIVE'")
    suspend fun getActiveEmployees(): List<EmployeeEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertEmployees(employees: List<EmployeeEntity>)

    @Query("DELETE FROM employees")
    suspend fun clearAll()
}

@Dao
interface FaceEmbeddingDao {
    @Query("SELECT * FROM face_embeddings")
    suspend fun getAllEmbeddings(): List<FaceEmbeddingEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertEmbeddings(embeddings: List<FaceEmbeddingEntity>)

    @Query("DELETE FROM face_embeddings")
    suspend fun clearAll()
}

@Dao
interface AttendanceLogDao {
    @Query("SELECT * FROM attendance_logs WHERE isSynced = 0")
    suspend fun getUnsyncedLogs(): List<AttendanceLogEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertLog(log: AttendanceLogEntity)

    @Query("UPDATE attendance_logs SET isSynced = 1 WHERE id = :id")
    suspend fun markSynced(id: Int)
}
