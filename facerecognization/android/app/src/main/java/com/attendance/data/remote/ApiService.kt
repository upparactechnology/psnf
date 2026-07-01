package com.attendance.data.remote

import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.Header
import retrofit2.Response

data class LoginRequest(val username: String, val password: String)
data class TokenResponse(val access_token: String, val refresh_token: String)

data class MatchRequest(
    val embedding: List<Double>,
    val timestamp: String,
    val device_id: String,
    val liveness_score: Double
)

data class MatchResponse(
    val matched: Boolean,
    val employee: MatchedEmployee,
    val attendance_log: LogDetails
)

data class MatchedEmployee(
    val id: Int,
    val employee_id: String,
    val first_name: String,
    val last_name: String
)

data class LogDetails(
    val id: Int,
    val clock_time: String,
    val clock_type: String,
    val status: String
)

data class SyncRequest(
    val logs: List<OfflineLogPayload>
)

data class OfflineLogPayload(
    val employee_id: Int,
    val clock_time: String,
    val clock_type: String,
    val status: String,
    val device_id: String
)

interface ApiService {
    @POST("api/v1/auth/login")
    suspend fun login(@Body request: LoginRequest): Response<TokenResponse>

    @POST("api/v1/recognition/match")
    suspend fun matchFace(
        @Header("Authorization") token: String,
        @Body request: MatchRequest
    ): Response<MatchResponse>

    @POST("api/v1/attendance/sync")
    suspend fun syncLogs(
        @Header("Authorization") token: String,
        @Body request: SyncRequest
    ): Response<Unit>
}
