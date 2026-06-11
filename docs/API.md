# Location Master API Documentation

High-performance REST API for Indian location data (States, Cities, Areas, Pincodes) with Redis caching.

---

## Base URL

```
http://localhost/Soft_devindia_pincode_apis/api/location.php
```

Replace with your production domain when deployed.

---

## General Information

| Item | Value |
|------|-------|
| Protocol | HTTP/HTTPS |
| Method | `GET` only |
| Format | JSON (`UTF-8`) |
| CORS | Enabled (global access) |
| Authentication | Optional JWT (disabled by default) |
| Cache | Redis (24 hours / 86400 seconds) |

---

## Request Headers

| Header | Required | Description |
|--------|----------|-------------|
| `Accept` | No | `application/json` recommended |
| `Accept-Encoding` | No | Send `gzip` for compressed JSON responses |
| `Authorization` | No | `Bearer <token>` when JWT is enabled |

---

## Response Format

### Success (HTTP 200)

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": []
}
```

### Error

```json
{
  "status": false,
  "message": "Error Message"
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `400` | Invalid or missing parameters |
| `401` | Unauthorized (JWT enabled and token invalid) |
| `404` | Record not found |
| `405` | Method not allowed (only GET supported) |
| `500` | Internal server error |

---

## Endpoints

### 1. Get All States

Returns a list of all states.

**Request**

```
GET /api/location.php?action=states
```

**Parameters**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `action` | string | Yes | Must be `states` |

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": [
    {
      "state_id": "1",
      "state_name": "Uttar Pradesh"
    }
  ]
}
```

**Redis Cache Key:** `LOC:STATE_LIST`

---

### 2. Get Cities by State ID

Returns all cities for a given state.

**Request**

```
GET /api/location.php?action=cities&state_id=1
```

**Parameters**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `action` | string | Yes | Must be `cities` |
| `state_id` | integer | Yes | Valid state ID (positive integer) |

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": [
    {
      "city_id": "10",
      "city_name": "Varanasi"
    }
  ]
}
```

**Redis Cache Key:** `LOC:CITY_{state_id}`  
Example: `LOC:CITY_1`

---

### 3. Get Areas by City ID

Returns all areas (with pincodes) for a given city.

**Request**

```
GET /api/location.php?action=areas&city_id=10
```

**Parameters**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `action` | string | Yes | Must be `areas` |
| `city_id` | integer | Yes | Valid city ID (positive integer) |

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 2,
  "data": [
    {
      "area_id": "100",
      "area_name": "Sigra",
      "pincode": "221005"
    },
    {
      "area_id": "101",
      "area_name": "Lanka",
      "pincode": "221005"
    }
  ]
}
```

**Redis Cache Key:** `LOC:AREA_{city_id}`  
Example: `LOC:AREA_10`

---

### 4. Get Complete Location by Pincode

Returns full location details for a 6-digit Indian pincode.

**Request**

```
GET /api/location.php?action=pincode&pin=221005
```

**Parameters**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `action` | string | Yes | Must be `pincode` |
| `pin` | string | Yes | 6-digit pincode (e.g. `221005`) |

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 2,
  "data": [
    {
      "state_id": "1",
      "state_name": "Uttar Pradesh",
      "city_id": "10",
      "city_name": "Varanasi",
      "area_id": "100",
      "area_name": "Sigra",
      "pincode": "221005"
    }
  ]
}
```

**Error (404) — Pincode not found**

```json
{
  "status": false,
  "message": "No location found for the given pincode."
}
```

**Redis Cache Key:** `LOC:PIN_{pincode}`  
Example: `LOC:PIN_221005`

---

### 5. Search by ID

Lookup a single record by **ID** (not by name). Provide **exactly one** of the ID parameters below.

#### 5a. Search State by ID

**Request**

```
GET /api/location.php?action=search&state_id=1
```

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": [
    {
      "state_id": "1",
      "state_name": "Uttar Pradesh"
    }
  ]
}
```

**Redis Cache Key:** `LOC:SEARCH_STATE_{state_id}`

---

#### 5b. Search City by ID

**Request**

```
GET /api/location.php?action=search&city_id=10
```

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": [
    {
      "city_id": "10",
      "city_name": "Varanasi",
      "state_id": "1",
      "state_name": "Uttar Pradesh"
    }
  ]
}
```

**Redis Cache Key:** `LOC:SEARCH_CITY_{city_id}`

---

#### 5c. Search Area by ID

**Request**

```
GET /api/location.php?action=search&area_id=100
```

**Example Response**

```json
{
  "status": true,
  "message": "Success",
  "count": 1,
  "data": [
    {
      "state_id": "1",
      "state_name": "Uttar Pradesh",
      "city_id": "10",
      "city_name": "Varanasi",
      "area_id": "100",
      "area_name": "Sigra",
      "pincode": "221005"
    }
  ]
}
```

**Redis Cache Key:** `LOC:SEARCH_AREA_{area_id}`

---

**Search Parameters**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `action` | string | Yes | Must be `search` |
| `state_id` | integer | One required | Search by state ID |
| `city_id` | integer | One required | Search by city ID |
| `area_id` | integer | One required | Search by area ID |

> Only **one** ID parameter is allowed per request.

**Search Validation Errors (400)**

| Message | Cause |
|---------|-------|
| `Provide exactly one ID: state_id, city_id, or area_id.` | No ID provided |
| `Provide only one ID parameter at a time.` | Multiple IDs sent |
| `Invalid state_id.` / `Invalid city_id.` / `Invalid area_id.` | Non-numeric or zero/negative ID |

**Not Found (404)**

```json
{
  "status": false,
  "message": "No record found for the given ID."
}
```

---

## Database Schema

```
states
├── state_id (PK)
└── state_name

cities
├── city_id (PK)
├── state_id (FK → states)
└── city_name

areas
├── area_id (PK)
├── city_id (FK → cities)
├── area_name
└── pincode
```

---

## Redis Cache Strategy

1. Check Redis for the cache key.
2. If found → return cached JSON immediately.
3. If miss → query MySQL → store in Redis (TTL: 86400s) → return response.

| Endpoint | Cache Key Pattern |
|----------|-------------------|
| States list | `LOC:STATE_LIST` |
| Cities by state | `LOC:CITY_{state_id}` |
| Areas by city | `LOC:AREA_{city_id}` |
| Pincode lookup | `LOC:PIN_{pincode}` |
| Search by state ID | `LOC:SEARCH_STATE_{state_id}` |
| Search by city ID | `LOC:SEARCH_CITY_{city_id}` |
| Search by area ID | `LOC:SEARCH_AREA_{area_id}` |

---

## Configuration

Edit `config/env.php` or set environment variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_NAME` | `location_master` | Database name |
| `DB_USER` | `root` | MySQL username |
| `DB_PASS` | `` | MySQL password |
| `REDIS_HOST` | `127.0.0.1` | Redis host |
| `REDIS_PORT` | `6379` | Redis port |
| `REDIS_PASSWORD` | `` | Redis auth password |
| `JWT_ENABLED` | `false` | Enable JWT auth |
| `JWT_SECRET` | `` | JWT signing secret |

---

## Integration Examples

### JavaScript (Fetch)

```javascript
const baseUrl = 'http://localhost/Soft_devindia_pincode_apis/api/location.php';

async function getCities(stateId) {
  const response = await fetch(`${baseUrl}?action=cities&state_id=${stateId}`);
  return response.json();
}

async function searchByAreaId(areaId) {
  const response = await fetch(`${baseUrl}?action=search&area_id=${areaId}`);
  return response.json();
}
```

### PHP (cURL)

```php
$url = 'http://localhost/Soft_devindia_pincode_apis/api/location.php?action=search&city_id=10';
$response = file_get_contents($url);
$data = json_decode($response, true);
```

### cURL (Command Line)

```bash
curl "http://localhost/Soft_devindia_pincode_apis/api/location.php?action=states"

curl "http://localhost/Soft_devindia_pincode_apis/api/location.php?action=search&state_id=1"

curl "http://localhost/Soft_devindia_pincode_apis/api/location.php?action=pincode&pin=221005"
```

---

## CRM / Mobile App Usage Flow

Typical cascading dropdown flow:

1. `action=states` → populate State dropdown
2. `action=cities&state_id={id}` → populate City dropdown
3. `action=areas&city_id={id}` → populate Area dropdown
4. `action=pincode&pin={pincode}` → auto-fill full address
5. `action=search&area_id={id}` → fetch single area details by ID

---

## Error Logging

API errors are logged to:

```
/logs/api_errors.log
```

Log entries are JSON lines with timestamp, message, and context.

---

## Security Notes

- All database queries use **prepared statements** (SQL injection safe).
- Input IDs are validated as positive integers.
- Pincodes must be exactly 6 digits.
- Generic error messages are returned to clients; detailed errors are logged server-side.
- Direct access to `/config`, `/helpers`, and `/logs` is blocked via `.htaccess`.

---

## Version

- **API Version:** 1.1
- **PHP:** 8.0+
- **MySQL:** 5.7+ / 8.0+
- **Redis:** 6.0+ with authentication support
