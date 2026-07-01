# REST API Standards and Response Envelope

This document defines the communication protocols, payload formatting envelopes, error schemas, pagination standards, and query parameters for all REST API endpoints.

---

## 1. Unified Response Envelope

All API endpoints must return a standardized JSON envelope to simplify error handling and response parsing across client applications:

```json
{
  "success": true,
  "message": "Employee record retrieved successfully.",
  "data": {},
  "errors": null,
  "timestamp": "2026-06-25T18:15:00Z",
  "request_id": "req_84ef82312b9c"
}
```

### Envelope Fields
* **`success`** (Boolean): Indicates whether the request was processed successfully.
* **`message`** (String): A human-readable description of the result.
* **`data`** (Object/Array/Null): The response payload.
* **`errors`** (Array/Null): Detailed validation error descriptions.
* **`timestamp`** (ISO 8601 String): Server processing timestamp.
* **`request_id`** (String): Unique request identifier for tracking in application logs.

---

## 2. Pagination, Sorting, and Filtering

Endpoints that return lists of records (e.g., `/employees`, `/attendance`) must support the following query parameters:

### 2.1 Pagination Parameters
* `page`: Target page number (default: `1`).
* `limit`: Page size (default: `20`, maximum: `100`).

### 2.2 Sorting Parameter
* `sort`: Formatted as `field:direction` (e.g., `created_at:desc`, `first_name:asc`).

### 2.3 Filter Query Standard
Use explicit query parameters for filtering records:
* `department_id=2`
* `status=ACTIVE`

### 2.4 Paginated Response Example
```json
{
  "success": true,
  "message": "Employees list retrieved successfully.",
  "data": {
    "items": [
      { "id": 1, "name": "John Doe" }
    ],
    "pagination": {
      "total_items": 125,
      "page_size": 20,
      "current_page": 1,
      "total_pages": 7
    }
  },
  "errors": null,
  "timestamp": "2026-06-25T18:15:00Z",
  "request_id": "req_1a2b3c4d5e6f"
}
```

---

## 3. Rate Limiting and Performance Controls

To prevent abuse and denial-of-service attacks, endpoints are rate-limited:
* **Standard Endpoints**: Limit of 100 requests per minute per IP address.
* **Recognition Matching Endpoint (`/recognition/match`)**: Limit of 10 requests per minute per kiosk device ID.
* **Rate Limit Headers**: Responses include rate-limiting headers:
  ```http
  X-RateLimit-Limit: 100
  X-RateLimit-Remaining: 99
  X-RateLimit-Reset: 1719339360
  ```

---

## 4. File and Image Upload Specifications

For operations that involve file uploads (e.g., uploading company logos or employee profile photos):
* **Request Format**: Use `multipart/form-data` encoding.
* **Size Limits**: Company Logo size is limited to 2 MB; profile pictures are limited to 5 MB.
* **MIME Verification**: Validate file extensions and MIME headers to restrict uploads to `image/jpeg` and `image/png`.

For details on standard HTTP error responses and recovery guides, refer to [21_ERROR_CODES.md](21_ERROR_CODES.md).
