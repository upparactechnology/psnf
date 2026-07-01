# Error Code Catalog and Resolution Guide

This document lists standard error codes returned by the backend API, detailing their root causes, response payloads, client handling rules, and troubleshooting steps.

---

## 1. HTTP Error Code Reference

### 1.1 Error: `400 Bad Request`
* **Meaning**: The request parameters are invalid or missing required fields.
* **Root Cause**: The client sent invalid JSON or omitted a required field (e.g., missing the `device_id` field in a match request).
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Malformed request payload.",
    "data": null,
    "errors": [
      {
        "field": "device_id",
        "detail": "Field is required."
      }
    ],
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_bad001"
  }
  ```
* **Android Recovery**: Display an overlay alerting the user to configure the kiosk settings.
* **Dashboard Recovery**: Show validation errors near the affected form inputs.

---

### 1.2 Error: `401 Unauthorized`
* **Meaning**: Missing or invalid authentication token.
* **Root Cause**: The JWT token in the `Authorization` header is invalid, expired, or missing.
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Authentication failed. Token has expired.",
    "data": null,
    "errors": null,
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_auth01"
  }
  ```
* **Android Recovery**: Redirect the user to the administrator login screen and clear the saved access token.
* **Dashboard Recovery**: Redirect to the `/login` route.

---

### 1.3 Error: `404 Not Found`
* **Meaning**: The requested resource does not exist.
* **Root Cause**: Requesting an employee profile with an invalid ID, or matching a face embedding that does not meet similarity thresholds.
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Resource not found.",
    "data": null,
    "errors": [
      {
        "field": "id",
        "detail": "Employee with ID 999 does not exist."
      }
    ],
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_notfound01"
  }
  ```
* **Android Recovery**: Show a warning overlay: "User profile not found. Please contact an administrator."
* **Dashboard Recovery**: Display a page-level `404 Not Found` graphic.

---

### 1.4 Error: `422 Unprocessable Entity`
* **Meaning**: Request structure is valid, but the values fail semantic validation rules.
* **Root Cause**: The `embedding_vector` size does not contain exactly 512 dimensions.
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Validation error.",
    "data": null,
    "errors": [
      {
        "field": "embedding",
        "detail": "Vector must contain exactly 512 elements."
      }
    ],
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_val01"
  }
  ```
* **Android Recovery**: Discard the invalid vector and prompt the user to scan their face again.
* **Dashboard Recovery**: Show validation warnings near the input fields.

---

### 1.5 Error: `429 Too Many Requests`
* **Meaning**: The client has exceeded rate limits.
* **Root Cause**: Rapidly scanning faces or making too many API requests in a short period.
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Rate limit exceeded. Try again later.",
    "data": null,
    "errors": null,
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_rate01"
  }
  ```
* **Android Recovery**: Temporarily lock the recognition stream for 10 seconds and display a countdown overlay.
* **Dashboard Recovery**: Show an alert banner: "Slow down. You are making too many requests."

---

### 1.6 Error: `500 Internal Server Error`
* **Meaning**: An unexpected server-side error occurred.
* **Root Cause**: Database connections timed out, or unhandled exceptions occurred in the face recognition engine.
* **Response Payload**:
  ```json
  {
    "success": false,
    "message": "Internal system error. Please contact support.",
    "data": null,
    "errors": null,
    "timestamp": "2026-06-25T18:15:00Z",
    "request_id": "req_err500"
  }
  ```
* **Android Recovery**: Cache logs locally and switch the application to offline mode.
* **Dashboard Recovery**: Show a system error screen containing the `request_id` for tracking.

For details on supported kiosk tablet hardware and VPS specifications, refer to [24_SUPPORTED_DEVICES.md](24_SUPPORTED_DEVICES.md).
