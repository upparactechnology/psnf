package com.attendance.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey
import androidx.room.ForeignKey
import androidx.room.Index

@Entity(tableName = "employees")
data class EmployeeEntity(
    @PrimaryKey val id: Int,
    val employeeId: String,
    val firstName: String,
    val lastName: String,
    val email: String,
    val status: String
)

@Entity(
    tableName = "face_embeddings",
    foreignKeys = [
        ForeignKey(
            entity = EmployeeEntity::class,
            parentColumns = ["id"],
            childColumns = ["employeeId"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index(value = ["employeeId"])]
)
data class FaceEmbeddingEntity(
    @PrimaryKey(autoGenerate = true) val id: Int = 0,
    val employeeId: Int,
    val embeddingVector: String, // JSON array string
    val captureAngle: String
)

@Entity(tableName = "attendance_logs")
data class AttendanceLogEntity(
    @PrimaryKey(autoGenerate = true) val id: Int = 0,
    val employeeId: Int,
    val clockTime: String, // ISO timestamp
    val clockType: String, // CHECK_IN / CHECK_OUT
    val status: String,
    val deviceId: String,
    val isSynced: Boolean
)
