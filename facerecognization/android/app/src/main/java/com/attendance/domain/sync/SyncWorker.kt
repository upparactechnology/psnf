package com.attendance.domain.sync

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.attendance.data.local.AppDatabase
import com.attendance.data.remote.ApiService
import com.attendance.data.remote.SyncRequest
import com.attendance.data.remote.OfflineLogPayload
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

class SyncWorker(
    context: Context,
    params: WorkerParameters
) : CoroutineWorker(context, params) {

    override suspend fun doWork(): Result {
        val database = AppDatabase.getDatabase(applicationContext)
        val unsyncedLogs = database.attendanceLogDao().getUnsyncedLogs()

        if (unsyncedLogs.isEmpty()) {
            return Result.success()
        }

        // Load configuration from local preferences
        val sharedPrefs = applicationContext.getSharedPreferences("attendance_prefs", Context.MODE_PRIVATE)
        val hostUrl = sharedPrefs.getString("host_url", null) ?: return Result.failure()
        val token = sharedPrefs.getString("access_token", null) ?: return Result.failure()

        val apiService = Retrofit.Builder()
            .baseUrl(hostUrl)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)

        val payload = SyncRequest(
            logs = unsyncedLogs.map {
                OfflineLogPayload(
                    employee_id = it.employeeId,
                    clock_time = it.clockTime,
                    clock_type = it.clockType,
                    status = it.status,
                    device_id = it.deviceId
                )
            }
        )

        return try {
            val response = apiService.syncLogs("Bearer $token", payload)
            if (response.isSuccessful) {
                // Update Room entries flag state
                unsyncedLogs.forEach {
                    database.attendanceLogDao().markSynced(it.id)
                }
                Result.success()
            } else {
                Result.retry()
            }
        } catch (e: Exception) {
            Result.retry()
        }
    }
}
