# Google Maps Business Data Collection System - API Documentation

## Overview

The REST API allows the **Google Maps Business Collector** Chrome Extension and authorized clients to authenticate, stream batch business data, manage collection sessions, query statistics, and report parsing exceptions.

**Base API URL:** `http://127.0.0.1:8000/api/v1`

---

## Authentication

All protected endpoints require a Sanctum Personal Access Token supplied in the HTTP Authorization header:

```http
Authorization: Bearer <YOUR_PERSONAL_ACCESS_TOKEN>
```

---

## API Endpoints Summary

| Method | Endpoint | Auth Required | Description |
| :--- | :--- | :---: | :--- |
| `POST` | `/api/v1/auth/login` | No | Authenticate user & issue API token |
| `POST` | `/api/v1/auth/logout` | Yes | Revoke current API token |
| `GET` | `/api/v1/me` | Yes | Get authenticated user details |
| `POST` | `/api/v1/collection-sessions` | Yes | Create a new collection session |
| `PATCH` | `/api/v1/collection-sessions/{id}` | Yes | Update session status & metrics |
| `POST` | `/api/v1/businesses/bulk` | Yes (Rate 60/min) | Upload batch business records |
| `GET` | `/api/v1/businesses` | Yes | Query & filter collected businesses |
| `GET` | `/api/v1/businesses/{id}` | Yes | Fetch single business details |
| `DELETE` | `/api/v1/businesses/{id}` | Yes | Delete a business record |
| `POST` | `/api/v1/businesses/check-duplicates` | Yes | Check place IDs against database |
| `GET` | `/api/v1/stats` | Yes | Query aggregated user collection metrics |
| `POST` | `/api/v1/errors` | Yes | Report Chrome Extension errors |

---

## Endpoints Specification & Examples

### 1. Authentication Login
- **Endpoint:** `POST /api/v1/auth/login`
- **Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "chrome-extension"
}
```
- **Response (200 OK):**
```json
{
  "success": true,
  "token": "1|abcdef1234567890...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}
```

---

### 2. Bulk Upload Businesses
- **Endpoint:** `POST /api/v1/businesses/bulk`
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Rate Limit:** 60 requests / minute per user
- **Request Body:**
```json
{
  "session_id": 1,
  "businesses": [
    {
      "name": "Apex Dental Clinic",
      "phone": "+91 98765 43210",
      "email": "contact@apexdental.com",
      "address": "101 High Street, CG Road",
      "city": "Ahmedabad",
      "state": "Gujarat",
      "country": "India",
      "postal_code": "380009",
      "website": "https://apexdental.com",
      "category": "Dental clinic",
      "rating": 4.8,
      "review_count": 145,
      "place_id": "ChIJN1t_tDeEXjkR123456",
      "maps_url": "https://www.google.com/maps/place/Apex+Dental",
      "latitude": 23.0225,
      "longitude": 72.5714
    }
  ]
}
```
- **Response (200 OK):**
```json
{
  "success": true,
  "summary": {
    "received": 1,
    "inserted": 1,
    "updated": 0,
    "duplicates": 0,
    "failed": 0
  }
}
```

---

### 3. Collection Sessions Management
- **Create Session:** `POST /api/v1/collection-sessions`
  - Body:
  ```json
  {
    "session_uuid": "e8a938c4-1234-4567-89ab-cdef12345678",
    "search_query": "dentists in Ahmedabad",
    "google_maps_url": "https://www.google.com/maps/search/dentists+in+Ahmedabad"
  }
  ```
- **Update Session:** `PATCH /api/v1/collection-sessions/{id}`
  - Body:
  ```json
  {
    "status": "completed",
    "businesses_detected": 25,
    "businesses_uploaded": 20,
    "duplicates_count": 3,
    "errors_count": 2
  }
  ```

---

### 4. Check Duplicates
- **Endpoint:** `POST /api/v1/businesses/check-duplicates`
- **Request Body:**
```json
{
  "place_ids": [
    "ChIJN1t_tDeEXjkR123456",
    "ChIJX87654321000000000"
  ]
}
```
- **Response (200 OK):**
```json
{
  "success": true,
  "existing_place_ids": [
    "ChIJN1t_tDeEXjkR123456"
  ]
}
```

---

### 5. Collection Statistics
- **Endpoint:** `GET /api/v1/stats`
- **Response (200 OK):**
```json
{
  "success": true,
  "stats": {
    "total_businesses": 12450,
    "collected_today": 347,
    "collected_this_week": 1920,
    "collected_this_month": 8540,
    "total_sessions": 42,
    "total_errors": 5,
    "total_duplicates_detected": 312
  }
}
```

---

### 6. Remote Error Logging
- **Endpoint:** `POST /api/v1/errors`
- **Request Body:**
```json
{
  "collection_session_id": 1,
  "error_type": "PARSER_ELEMENT_MISSING",
  "message": "Could not find business title element",
  "payload": {
    "url": "https://www.google.com/maps/search/restaurants"
  }
}
```

---

## HTTP Error Responses

- **401 Unauthorized:** Missing or invalid API Token
- **422 Unprocessable Entity:** Validation failure
- **429 Too Many Requests:** Rate limit exceeded (60 req/min)
- **500 Internal Server Error:** Server-side failure (Extension retains queue locally)
