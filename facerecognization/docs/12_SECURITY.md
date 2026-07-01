# Security, Privacy and Compliance Architecture

This document details the security layers, encryption protocols, face embedding protections, and audit trailing implemented in the system.

---

## 1. Network & Transport Layer Security

All external network communication between client apps and the backend services is encrypted:

```
┌───────────────────────────────────────────────────────────────┐
│                     TRANSPORT ENCRYPTION                      │
├───────────────────────┬──────────────────────┬────────────────┤
│       HTTPS Only      │       TLS 1.3        │ Nginx Routing  │
│                       │                      │                │
│ • Block HTTP traffic  │ • Secure ciphers     │ • Rate limiting│
│ • Let's Encrypt SSL   │ • Forward secrecy    │ • Secure headers│
└───────────────────────┴──────────────────────┴────────────────┘
```

* **HTTPS Enforcement**: Direct HTTP requests are redirected to HTTPS at the Nginx gateway.
* **TLS 1.3 Standard**: The Nginx configuration restricts TLS protocols to version 1.3, disabling older, vulnerable TLS and SSL configurations.
* **API Rate Limiting**: Nginx limits request rates on matching endpoints (`POST /api/v1/recognition/match`) to prevent brute-force attacks.

---

## 2. Authentication & Credential Storage

* **Admin Password Protection**: Admin passwords are encrypted using the **bcrypt** hashing algorithm. Raw password strings are never stored in the database.
* **JSON Web Tokens (JWT)**: Users authenticate using stateless JWT access tokens containing the user's role and ID, signed with a 256-bit secret key using the HMAC-SHA256 algorithm.
* **Access Expiration**:
  * Access tokens expire after 1 hour.
  * Refresh tokens expire after 14 days and are stored in HttpOnly cookies to mitigate XSS (Cross-Site Scripting) risks.

---

## 3. Face Embedding Protection & Privacy

To protect user privacy and comply with biometric data regulations:
1. **No Image Storage**: The system does not save raw photographs or video frames of employees on the server. Images are processed in-memory and immediately discarded after embedding extraction.
2. **One-Way Vector Representation**: Face embeddings are stored as a flat array of 512 floating-point values. These vectors cannot be reconstructed back into a recognizable human face image.
3. **Data Isolation**: Face embedding tables are isolated from demographic employee tables, linked only by an internal foreign key.

---

## 4. Immutable Audit Logs

The system records all administrative actions in the `audit_logs` database table to track changes:
* **Log Fields**: Includes Timestamp, Admin User ID, IP Address, Action Performed (e.g., `DELETE_EMPLOYEE`), and a JSON details payload describing the changed fields.
* **Permissions**: Access to the audit log table is read-only for system administrators; records cannot be modified or deleted.

For production deployment instructions, refer to [13_DEPLOYMENT.md](13_DEPLOYMENT.md).
