# SmartNeti Mobile API Documentation

Customer-facing REST API for Flutter mobile application.

## Base URL

```
http://your-server-ip/api
```

## Authentication

All endpoints except `/api/login`, `/api/register`, and `/api/announcement` require authentication using a Bearer token.

### Token Format

```
c.{user_id}.{time}.{hash}
```

### Authentication Header

```
Authorization: Bearer c.5.1234567890.a1b2c3d4e5f6...
```

### Token Expiration

Tokens are valid for 30 days from generation.

## Endpoints

### POST /api/login

Customer login endpoint.

**Request Body:**
```json
{
  "username": "customer_username",
  "password": "customer_password"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "c.5.1234567890.a1b2c3d4e5f6...",
    "user": {
      "id": 5,
      "username": "customer_username",
      "fullname": "Customer.Name",
      "email": "customer@example.com",
      "phone": "+265123456789",
      "status": "Active",
      "account_type": "Personal"
    }
  },
  "message": "Login successful"
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid username or password"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Account is Banned"
}
```

---

### POST /api/register

Customer registration endpoint. Creates a new customer account.

**Request Body:**
```json
{
  "username": "new_customer",
  "password": "secure_password",
  "cpassword": "secure_password",
  "fullname": "John Doe",
  "email": "john@example.com",
  "address": "123 Main Street",
  "phone_number": "+265123456789",
  "otp_code": "123456"
}
```

**Field Descriptions:**
- `username` (required): Username (3-35 characters)
- `password` (required): Password (3-35 characters)
- `cpassword` (required): Password confirmation (must match password)
- `fullname` (required if enabled): Full name (3-36 characters)
- `email` (required if enabled): Valid email address
- `address` (optional): Physical address
- `phone_number` (required if OTP enabled): Phone number for OTP verification
- `otp_code` (required if OTP enabled): 6-digit OTP code sent to phone

**Photo Upload:**
If photo registration is enabled, send photo as multipart/form-data with field name `photo`.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 10,
      "username": "new_customer",
      "fullname": "John Doe",
      "email": "john@example.com",
      "phone": "+265123456789",
      "status": "Active"
    }
  },
  "message": "Registration successful"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Username should be between 3 to 35 characters"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Registration is disabled"
}
```

**Error Response (409):**
```json
{
  "success": false,
  "message": "Account already exists"
}
```

---

### GET /api/announcement

Get customer announcement message configured by admin.

**Authentication:** Not required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "announcement": "Customer Announcement!!<br>Tomorrow holiday<br><br><br>This Announcement is for Customer Dashboard"
  },
  "message": "Announcement retrieved successfully"
}
```

**Empty Response (200):**
```json
{
  "success": true,
  "data": {
    "announcement": ""
  },
  "message": "No announcement configured"
}
```

---

### GET /api/profile

Get customer profile information including active subscriptions.

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "username": "customer_username",
    "fullname": "Customer.Name",
    "email": "customer@example.com",
    "phone": "+265123456789",
    "address": "123 Main Street",
    "city": "Lilongwe",
    "status": "Active",
    "account_type": "Personal",
    "balance": 5000.00,
    "service_type": "Hotspot",
    "auto_renewal": true,
    "created_at": "2026-01-15 10:30:00",
    "last_login": "2026-07-28 14:30:00",
    "active_subscriptions": [
      {
        "id": 123,
        "plan_name": "Daily Hotspot",
        "plan_type": "Hotspot",
        "price": "500.00",
        "recharged_on": "2026-07-28",
        "expiration": "2026-07-29",
        "status": "on",
        "router": "main_router"
      }
    ]
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Unauthorized or invalid token"
}
```

---

### GET /api/packages

Get available internet packages for the customer's account type.

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Daily Hotspot",
      "type": "Hotspot",
      "price": 500.00,
      "price_old": null,
      "validity": 1,
      "validity_unit": "Days",
      "limit_type": "Time_Limit",
      "time_limit": 24,
      "time_unit": "Hrs",
      "data_limit": null,
      "data_unit": null,
      "shared_users": null,
      "bandwidth": {
        "name": "5Mbps",
        "rate_down": 5,
        "rate_down_unit": "Mbps",
        "rate_up": 2,
        "rate_up_unit": "Mbps"
      },
      "is_radius": false,
      "account_type": "Personal"
    },
    {
      "id": 2,
      "name": "Weekly Hotspot",
      "type": "Hotspot",
      "price": 2500.00,
      "price_old": 3000.00,
      "validity": 7,
      "validity_unit": "Days",
      "limit_type": "Data_Limit",
      "time_limit": null,
      "time_unit": null,
      "data_limit": 10,
      "data_unit": "GB",
      "shared_users": 1,
      "bandwidth": {
        "name": "10Mbps",
        "rate_down": 10,
        "rate_down_unit": "Mbps",
        "rate_up": 5,
        "rate_up_unit": "Mbps"
      },
      "is_radius": false,
      "account_type": "Personal"
    }
  ]
}
```

---

### GET /api/balance

Get customer balance information and recent balance transactions.

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "balance": 5000.00,
    "currency": "MWK",
    "status": "Active",
    "auto_renewal": true,
    "recent_transactions": [
      {
        "id": 456,
        "plan_name": "Top Up",
        "amount": 1000.00,
        "payment_method": "PayChangu",
        "payment_channel": "Mobile Money",
        "created_date": "2026-07-28 12:00:00",
        "status": 2
      }
    ]
  }
}
```

---

### GET /api/payments

Get customer payment history with pagination.

**Authentication:** Required

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20, max: 50)

**Example Request:**
```
GET /api/payments?page=1&limit=10
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "payments": [
      {
        "id": 789,
        "invoice": "INV-789",
        "plan_name": "Daily Hotspot",
        "plan_id": 1,
        "price": 500.00,
        "gateway": "PayChangu",
        "payment_method": "PayChangu",
        "payment_channel": "Mobile Money",
        "created_date": "2026-07-28 10:00:00",
        "paid_date": "2026-07-28 10:05:00",
        "expired_date": "2026-07-29 10:00:00",
        "status": "paid",
        "router": "main_router"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 45,
      "total_pages": 5
    }
  }
}
```

**Payment Status Codes:**
- `unpaid`: Payment not yet completed
- `paid`: Payment successfully completed
- `failed`: Payment failed
- `canceled`: Payment canceled

---

### GET /api/support

Get customer support and contact information.

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "company_name": "SmartNeti",
    "support_email": "support@smartneti.com",
    "support_phone": "+265123456789",
    "support_whatsapp": "+265123456789",
    "business_hours": "24/7",
    "website": "https://smartneti.com",
    "social_media": {
      "facebook": "https://facebook.com/smartneti",
      "twitter": "https://twitter.com/smartneti",
      "instagram": "https://instagram.com/smartneti"
    },
    "help_center_url": "https://help.smartneti.com",
    "faq_url": "https://faq.smartneti.com"
  }
}
```

---

## Error Responses

All endpoints return consistent error responses:

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Username and password are required"
}
```

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Unauthorized or invalid token"
}
```

**403 Forbidden:**
```json
{
  "success": false,
  "message": "Account is Banned"
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Endpoint not found"
}
```

**405 Method Not Allowed:**
```json
{
  "success": false,
  "message": "Method not allowed"
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Security Notes

- All customer data is isolated - users can only access their own information
- Tokens are validated on every request
- SQL injection protection via ORM
- Input sanitization for all user inputs
- CORS enabled for mobile app access
- No PHP errors/warnings leaked to API responses

---

## Flutter Integration Example

```dart
// Login Example
Future<void> login(String username, String password) async {
  final response = await http.post(
    Uri.parse('http://your-server-ip/api/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'username': username,
      'password': password,
    }),
  );

  final data = jsonDecode(response.body);
  if (data['success']) {
    String token = data['data']['token'];
    // Store token for subsequent requests
    await storage.write(key: 'auth_token', value: token);
  }
}

// Authenticated Request Example
Future<void> getProfile() async {
  final token = await storage.read(key: 'auth_token');
  final response = await http.get(
    Uri.parse('http://your-server-ip/api/profile'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
  );

  final data = jsonDecode(response.body);
  if (data['success']) {
    // Process profile data
  }
}
```

---

## Configuration

Support information can be configured in the SmartNeti admin panel under Settings. The API reads these values from the configuration database.

---

## Rate Limiting

Currently not implemented. Consider adding rate limiting for production deployments.

---

## Version

API Version: 1.0.0
