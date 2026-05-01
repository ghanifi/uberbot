# Airlinel Transfer API Documentation

## Overview

The Airlinel Transfer API provides REST endpoints for integrated airport transfer services. The API enables third-party applications and partners to:

- Search for available vehicles with real-time pricing
- Create new booking reservations
- Retrieve reservation details and status
- Support multiple countries (UK and Turkey) and currencies (GBP, EUR, TRY, USD)

**Base URL:** `/wp-json/airlinel/v1`

**API Version:** 1.0.0

**Latest Update:** 2026-04-25

## Table of Contents

1. [Authentication](#authentication)
2. [Request/Response Format](#requestresponse-format)
3. [Endpoints](#endpoints)
   - [POST /search](#post-search)
   - [POST /reservation/create](#post-reservationcreate)
   - [GET /reservation/{id}](#get-reservationid)
4. [Error Handling](#error-handling)
5. [Code Examples](#code-examples)
6. [Use Cases](#use-cases)
7. [Rate Limiting](#rate-limiting)
8. [Troubleshooting](#troubleshooting)
9. [Support](#support)

## Authentication

All API endpoints require API key authentication via the `x-api-key` header.

### Obtaining an API Key

1. Log in to the WordPress admin panel
2. Navigate to **Airlinel Settings** > **API Settings**
3. Generate or copy your API key
4. Store the key securely (treat it like a password)

### Including API Key in Requests

Add the API key to every request header:

```
x-api-key: your_api_key_here
```

**Important Security Notes:**

- Never commit API keys to version control systems
- Rotate API keys periodically
- Use different keys for development and production environments
- If a key is compromised, regenerate it immediately from the settings panel

## Request/Response Format

All requests and responses use JSON format with `Content-Type: application/json`.

### Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid parameters or validation failed |
| 401 | Unauthorized - Invalid or missing API key |
| 404 | Not Found - Resource does not exist |
| 503 | Service Unavailable - API temporarily unavailable |

### Standard Response Format

**Success Response:**
```json
{
  "success": true,
  "data": {
    // endpoint-specific data
  }
}
```

**Error Response:**
```json
{
  "code": "error_code",
  "message": "Human-readable error description",
  "data": {
    "status": 400
  }
}
```

## Endpoints

### POST /search

Search for available vehicles with pricing for a specific route.

**Endpoint:** `POST /wp-json/airlinel/v1/search`

**Description:** Query the pricing engine to get available vehicles and calculate pricing based on distance, location zones, passenger count, and currency.

#### Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| pickup | string | Yes | - | Pickup location (address or postcode) |
| dropoff | string | Yes | - | Dropoff location (address or postcode) |
| date | string (YYYY-MM-DD) | Yes | - | Transfer date |
| passengers | integer | No | 1 | Number of passengers (1-8) |
| currency | string | No | GBP | Currency code: GBP, EUR, TRY, or USD |
| country | string | No | UK | Country code: UK or TR |

#### Request Example

**cURL:**
```bash
curl -X POST https://yourdomain.com/wp-json/airlinel/v1/search \
  -H "Content-Type: application/json" \
  -H "x-api-key: your_api_key_here" \
  -d '{
    "pickup": "London Heathrow Terminal 3",
    "dropoff": "Central London, W1A 1AA",
    "date": "2026-05-15",
    "passengers": 2,
    "currency": "GBP",
    "country": "UK"
  }'
```

**JavaScript (Fetch):**
```javascript
const searchVehicles = async () => {
  const response = await fetch('/wp-json/airlinel/v1/search', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'x-api-key': 'your_api_key_here'
    },
    body: JSON.stringify({
      pickup: 'London Heathrow Terminal 3',
      dropoff: 'Central London, W1A 1AA',
      date: '2026-05-15',
      passengers: 2,
      currency: 'GBP',
      country: 'UK'
    })
  });
  
  const data = await response.json();
  if (data.success) {
    console.log('Search Results:', data);
  } else {
    console.error('Error:', data.message);
  }
};
```

**Python (requests):**
```python
import requests
import json

api_key = 'your_api_key_here'
base_url = 'https://yourdomain.com/wp-json/airlinel/v1'

headers = {
    'Content-Type': 'application/json',
    'x-api-key': api_key
}

payload = {
    'pickup': 'London Heathrow Terminal 3',
    'dropoff': 'Central London, W1A 1AA',
    'date': '2026-05-15',
    'passengers': 2,
    'currency': 'GBP',
    'country': 'UK'
}

response = requests.post(
    f'{base_url}/search',
    headers=headers,
    json=payload
)

if response.status_code == 200:
    data = response.json()
    print('Distance:', data['distance_km'], 'km')
    print('Total Price:', data['total_display'], data['currency'])
else:
    print('Error:', response.json())
```

#### Response Example (Success)

```json
{
  "success": true,
  "distance_km": 25.5,
  "base_price_gbp": 35.00,
  "passengers": 2,
  "multiplier": 1.5,
  "total_gbp": 52.50,
  "currency": "EUR",
  "rate": 1.18,
  "total_display": 61.95
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Indicates successful search |
| distance_km | float | Distance between locations in kilometers |
| base_price_gbp | float | Base price in GBP before multiplier |
| passengers | integer | Number of passengers |
| multiplier | float | Price multiplier based on passenger count |
| total_gbp | float | Total price in GBP (base × multiplier) |
| currency | string | Requested currency code |
| rate | float | Exchange rate applied |
| total_display | float | Final price in requested currency |

#### Response Example (Error)

```json
{
  "code": "missing_params",
  "message": "Missing required parameters",
  "data": {
    "status": 400
  }
}
```

#### Possible Errors

| Error Code | Status | Description | Solution |
|-----------|--------|-------------|----------|
| missing_params | 400 | Required parameters missing (pickup, dropoff, date) | Verify all required fields are provided |
| pricing_engine_not_available | 503 | Pricing engine not initialized | Try again later or contact support |
| rest_forbidden | 401 | Invalid or missing API key | Check API key is correct and included in header |

---

### POST /reservation/create

Create a new booking reservation.

**Endpoint:** `POST /wp-json/airlinel/v1/reservation/create`

**Description:** Create a new reservation with customer details and pricing. Validates all required fields before creating the reservation.

#### Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| customer_name | string | Yes | - | Full name of customer |
| email | string | Yes | - | Valid email address |
| phone | string | Yes | - | Contact phone number |
| pickup_location | string | Yes | - | Pickup location |
| dropoff_location | string | Yes | - | Dropoff location |
| transfer_date | string (YYYY-MM-DD) | Yes | - | Transfer date (must be future) |
| total_price | float | Yes | - | Total price for reservation |
| passengers | integer | No | 1 | Number of passengers (1-8) |
| currency | string | No | GBP | Currency code: GBP, EUR, TRY, USD |
| country | string | No | UK | Country code: UK or TR |
| agency_code | string | No | - | Agency code for commission tracking |
| commission_type | string | No | included | Commission type: included or additional |

#### Request Example

**cURL:**
```bash
curl -X POST https://yourdomain.com/wp-json/airlinel/v1/reservation/create \
  -H "Content-Type: application/json" \
  -H "x-api-key: your_api_key_here" \
  -d '{
    "customer_name": "John Smith",
    "email": "john.smith@example.com",
    "phone": "+44 20 7946 0958",
    "pickup_location": "London Heathrow Terminal 3",
    "dropoff_location": "Central London, W1A 1AA",
    "transfer_date": "2026-05-15",
    "passengers": 2,
    "currency": "GBP",
    "country": "UK",
    "total_price": 52.50,
    "agency_code": "PARTNER001"
  }'
```

**JavaScript (Fetch):**
```javascript
const createReservation = async () => {
  const response = await fetch('/wp-json/airlinel/v1/reservation/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'x-api-key': 'your_api_key_here'
    },
    body: JSON.stringify({
      customer_name: 'John Smith',
      email: 'john.smith@example.com',
      phone: '+44 20 7946 0958',
      pickup_location: 'London Heathrow Terminal 3',
      dropoff_location: 'Central London, W1A 1AA',
      transfer_date: '2026-05-15',
      passengers: 2,
      currency: 'GBP',
      country: 'UK',
      total_price: 52.50,
      agency_code: 'PARTNER001'
    })
  });
  
  const data = await response.json();
  if (data.success) {
    console.log('Reservation created with ID:', data.reservation_id);
  } else {
    console.error('Error:', data.message);
  }
};
```

**PHP:**
```php
<?php
$api_key = 'your_api_key_here';
$base_url = 'https://yourdomain.com/wp-json/airlinel/v1';

$payload = [
    'customer_name' => 'John Smith',
    'email' => 'john.smith@example.com',
    'phone' => '+44 20 7946 0958',
    'pickup_location' => 'London Heathrow Terminal 3',
    'dropoff_location' => 'Central London, W1A 1AA',
    'transfer_date' => '2026-05-15',
    'passengers' => 2,
    'currency' => 'GBP',
    'country' => 'UK',
    'total_price' => 52.50,
    'agency_code' => 'PARTNER001'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/reservation/create');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $api_key
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($http_code === 201 && $data['success']) {
    echo 'Reservation ID: ' . $data['reservation_id'];
} else {
    echo 'Error: ' . $data['message'];
}
?>
```

#### Response Example (Success)

```json
{
  "success": true,
  "reservation_id": 456
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Indicates successful creation |
| reservation_id | integer | Unique reservation ID (WordPress post ID) |

#### Response Example (Error)

```json
{
  "code": "validation",
  "message": "Invalid email address provided"
}
```

#### Possible Errors

| Error Code | Status | Description | Solution |
|-----------|--------|-------------|----------|
| validation | 400 | Field validation failed | Check all fields match required format |
| missing | 400 | Required field missing | Verify all required fields provided |
| rest_forbidden | 401 | Invalid or missing API key | Check API key |

**Validation Rules:**

- **customer_name:** Non-empty string
- **email:** Valid email format (RFC 5322)
- **phone:** Non-empty string
- **transfer_date:** YYYY-MM-DD format, must be future date
- **passengers:** Integer 1-8
- **total_price:** Positive number
- **currency:** One of GBP, EUR, TRY, USD
- **country:** One of UK, TR

---

### GET /reservation/{id}

Retrieve reservation details by ID.

**Endpoint:** `GET /wp-json/airlinel/v1/reservation/{id}`

**Description:** Get complete details of a specific reservation including customer information, transfer details, and pricing.

#### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Reservation ID (WordPress post ID) |

#### Request Example

**cURL:**
```bash
curl -X GET https://yourdomain.com/wp-json/airlinel/v1/reservation/456 \
  -H "x-api-key: your_api_key_here"
```

**JavaScript (Fetch):**
```javascript
const getReservation = async (reservationId) => {
  const response = await fetch(`/wp-json/airlinel/v1/reservation/${reservationId}`, {
    method: 'GET',
    headers: {
      'x-api-key': 'your_api_key_here'
    }
  });
  
  const data = await response.json();
  if (response.ok) {
    console.log('Reservation:', data);
  } else {
    console.error('Error:', data.message);
  }
};
```

**Python (requests):**
```python
import requests

api_key = 'your_api_key_here'
base_url = 'https://yourdomain.com/wp-json/airlinel/v1'
reservation_id = 456

headers = {
    'x-api-key': api_key
}

response = requests.get(
    f'{base_url}/reservation/{reservation_id}',
    headers=headers
)

if response.status_code == 200:
    data = response.json()
    print('Customer:', data['customer_name'])
    print('Date:', data['date'])
    print('Total:', data['total_price'], data['currency'])
else:
    print('Error:', response.json())
```

#### Response Example (Success)

```json
{
  "id": 456,
  "status": "pending",
  "customer_name": "John Smith",
  "email": "john.smith@example.com",
  "phone": "+44 20 7946 0958",
  "pickup": "London Heathrow Terminal 3",
  "dropoff": "Central London, W1A 1AA",
  "date": "2026-05-15",
  "passengers": 2,
  "total_price": 52.50,
  "currency": "GBP",
  "country": "UK"
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Reservation ID |
| status | string | Current status (pending, processing, completed, cancelled) |
| customer_name | string | Customer full name |
| email | string | Customer email |
| phone | string | Customer phone |
| pickup | string | Pickup location |
| dropoff | string | Dropoff location |
| date | string | Transfer date (YYYY-MM-DD) |
| passengers | integer | Number of passengers |
| total_price | float | Total reservation price |
| currency | string | Price currency code |
| country | string | Country code |

#### Response Example (Error - Not Found)

```json
{
  "code": "not_found",
  "message": "Reservation not found"
}
```

#### Possible Errors

| Error Code | Status | Description | Solution |
|-----------|--------|-------------|----------|
| not_found | 404 | Reservation ID does not exist | Verify correct reservation ID |
| rest_forbidden | 401 | Invalid or missing API key | Check API key |

---

## Error Handling

All error responses follow a standard format and include relevant HTTP status codes.

### Error Response Structure

```json
{
  "code": "error_code_identifier",
  "message": "Human-readable error message",
  "data": {
    "status": 400
  }
}
```

### Common Error Codes

| Code | Status | Meaning |
|------|--------|---------|
| missing_params | 400 | Required parameters are missing |
| validation | 400 | Field validation failed |
| not_found | 404 | Resource does not exist |
| rest_forbidden | 401 | API key is invalid or missing |
| pricing_engine_not_available | 503 | Pricing service temporarily unavailable |

### Best Practices for Error Handling

1. **Always check HTTP status code** - Not all errors return HTTP 400
2. **Read the error message** - Provides specific details about what failed
3. **Implement retry logic** - For 5xx errors, use exponential backoff
4. **Log errors** - Store error codes and messages for debugging
5. **Provide user feedback** - Show meaningful messages to end users

---

## Code Examples

### Complete Booking Flow

This example shows a complete workflow from search to reservation:

**JavaScript:**
```javascript
const apiKey = 'your_api_key_here';
const baseUrl = '/wp-json/airlinel/v1';

async function completeBooking() {
  try {
    // Step 1: Search for available vehicles
    console.log('Searching for vehicles...');
    const searchResponse = await fetch(`${baseUrl}/search`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey
      },
      body: JSON.stringify({
        pickup: 'London Heathrow Terminal 3',
        dropoff: 'Central London, W1A 1AA',
        date: '2026-05-15',
        passengers: 2,
        currency: 'GBP'
      })
    });
    
    if (!searchResponse.ok) {
      throw new Error('Search failed');
    }
    
    const searchData = await searchResponse.json();
    console.log('Search Results:', searchData);
    
    // Step 2: Create reservation with search results
    console.log('Creating reservation...');
    const reservationResponse = await fetch(`${baseUrl}/reservation/create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey
      },
      body: JSON.stringify({
        customer_name: 'John Smith',
        email: 'john.smith@example.com',
        phone: '+44 20 7946 0958',
        pickup_location: 'London Heathrow Terminal 3',
        dropoff_location: 'Central London, W1A 1AA',
        transfer_date: '2026-05-15',
        passengers: 2,
        currency: 'GBP',
        total_price: searchData.total_display
      })
    });
    
    if (!reservationResponse.ok) {
      throw new Error('Reservation creation failed');
    }
    
    const reservationData = await reservationResponse.json();
    console.log('Reservation created:', reservationData.reservation_id);
    
    // Step 3: Retrieve reservation details
    console.log('Retrieving reservation details...');
    const detailsResponse = await fetch(
      `${baseUrl}/reservation/${reservationData.reservation_id}`,
      {
        headers: {
          'x-api-key': apiKey
        }
      }
    );
    
    if (!detailsResponse.ok) {
      throw new Error('Failed to retrieve reservation');
    }
    
    const details = await detailsResponse.json();
    console.log('Complete Reservation:', details);
    
    return details;
  } catch (error) {
    console.error('Booking error:', error);
    throw error;
  }
}

// Execute the booking
completeBooking();
```

### Handling Different Currencies

```javascript
async function searchInMultipleCurrencies(pickup, dropoff, date) {
  const currencies = ['GBP', 'EUR', 'USD', 'TRY'];
  const results = {};
  
  for (const currency of currencies) {
    const response = await fetch('/wp-json/airlinel/v1/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': 'your_api_key_here'
      },
      body: JSON.stringify({
        pickup,
        dropoff,
        date,
        currency
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      results[currency] = data.total_display;
    }
  }
  
  return results;
}
```

### Multi-Passenger Pricing Comparison

```javascript
async function comparePricingByPassengers(pickup, dropoff, date) {
  const results = {};
  
  for (let passengers = 1; passengers <= 8; passengers++) {
    const response = await fetch('/wp-json/airlinel/v1/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': 'your_api_key_here'
      },
      body: JSON.stringify({
        pickup,
        dropoff,
        date,
        passengers
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      results[passengers] = {
        total: data.total_display,
        perPerson: data.total_display / passengers
      };
    }
  }
  
  return results;
}
```

---

## Use Cases

### Use Case 1: Integration with OTA (Online Travel Agency)

Travel agencies can integrate Airlinel to offer ground transportation alongside hotel and flight bookings.

**Workflow:**
1. Customer provides travel dates and airport
2. OTA calls `/search` with airport location and destination
3. Display pricing options to customer
4. On booking confirmation, call `/reservation/create`
5. Store reservation ID for confirmation email

### Use Case 2: B2B Partner Agency

Partner agencies manage multiple customer bookings through a portal.

**Workflow:**
1. Admin sets up API key in Airlinel settings
2. Partner system searches for prices with `agency_code` parameter
3. Creates reservations with agency commission tracking
4. Pulls reservation details for reporting

### Use Case 3: Mobile App Booking

A mobile application provides real-time transfer booking.

**Workflow:**
1. App detects user location and booking date
2. Calls `/search` to get pricing in user's local currency
3. User confirms booking
4. App calls `/reservation/create` with customer details
5. Returns confirmation with reservation ID

### Use Case 4: White-Label Solution

Another company rebrands Airlinel API as their own service.

**Workflow:**
1. Set up dedicated API key
2. All API calls go through white-label domain
3. Use agency codes to track revenue
4. Implement own UI around Airlinel endpoints

---

## Rate Limiting

Currently, there is no enforced rate limiting on the API. However, best practices recommend:

- **Per-minute limits:** Maximum 60 requests per minute per API key
- **Burst allowance:** Allow 10 requests per second for brief spikes
- **Queue management:** Implement client-side request queuing for reliability

### Recommended Rate Limit Headers

The API may implement the following headers in future versions:

| Header | Description |
|--------|-------------|
| X-RateLimit-Limit | Maximum requests allowed |
| X-RateLimit-Remaining | Requests remaining in window |
| X-RateLimit-Reset | Time when limit resets (Unix timestamp) |

**Backoff Strategy:**

```javascript
async function callWithBackoff(fn, maxRetries = 3) {
  for (let attempt = 0; attempt < maxRetries; attempt++) {
    try {
      return await fn();
    } catch (error) {
      if (error.status === 429 && attempt < maxRetries - 1) {
        const delay = Math.pow(2, attempt) * 1000; // Exponential backoff
        await new Promise(resolve => setTimeout(resolve, delay));
        continue;
      }
      throw error;
    }
  }
}
```

---

## Troubleshooting

### Issue: "Invalid API Key" (401 Unauthorized)

**Causes:**
- API key is incorrect or expired
- API key not included in header
- API key header name is wrong (should be `x-api-key`)

**Solution:**
1. Verify API key in WordPress admin > Airlinel Settings
2. Check header name is exactly `x-api-key` (case-insensitive in HTTP)
3. Ensure key is passed in every request
4. Regenerate key if suspected compromise

### Issue: "Missing Parameters" (400 Bad Request)

**Causes:**
- Required fields not provided
- JSON formatting is invalid
- Field names don't match API specification

**Solution:**
1. Verify all required fields are present (see endpoint documentation)
2. Validate JSON syntax (use online JSON validators)
3. Check field names match exactly (case-sensitive)
4. Ensure Content-Type header is `application/json`

### Issue: "Pricing Engine Not Available" (503 Service Unavailable)

**Causes:**
- Pricing engine class not properly initialized
- Database connection issue
- Missing dependencies

**Solution:**
1. Check WordPress error log for details
2. Verify all plugin files are present
3. Reload the page to retry
4. Contact support if issue persists

### Issue: "Reservation Not Found" (404)

**Causes:**
- Incorrect reservation ID
- Reservation deleted from system
- ID refers to wrong post type

**Solution:**
1. Verify reservation ID from creation response
2. Check reservation exists in WordPress admin
3. Ensure ID is integer, not string
4. Confirm ID matches correct post type (post_type: 'reservations')

### Issue: Slow Response Times

**Causes:**
- Network latency
- Google Maps API throttling
- Database performance
- Too many concurrent requests

**Solution:**
1. Implement client-side caching for search results
2. Check Google Maps API quota not exceeded
3. Use async/await to avoid blocking operations
4. Implement request queuing to limit concurrency

### Issue: "Date Cannot Be in the Past"

**Causes:**
- Transfer date is before current date
- Server time differs from client time
- Incorrect date format

**Solution:**
1. Use only future dates (today or later)
2. Format as YYYY-MM-DD
3. Check server time is correct
4. Account for timezone differences

---

## Support

For additional support and resources:

- **Documentation:** https://airlinel.com/api-docs
- **Status Page:** https://status.airlinel.com
- **Email Support:** support@airlinel.com
- **Chat Support:** Available in WordPress admin panel

### Reporting Issues

When reporting issues, please include:

1. API endpoint being called
2. Full request (without API key)
3. Full response (including error messages)
4. Steps to reproduce
5. Server environment details (WordPress version, PHP version)

### API Changelog

**Version 1.0.0 (2026-04-25)**
- Initial release
- Three core endpoints: search, create reservation, get reservation
- Support for UK and Turkey regions
- Multi-currency support (GBP, EUR, TRY, USD)
- API key authentication
- Complete error handling

**Future Versions:**
- Rate limiting headers
- Batch search endpoint
- Reservation modification/cancellation
- Webhook notifications
- Analytics dashboard
