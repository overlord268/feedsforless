# FeedsForLess API — Full Documentation

> **Interactive API reference (OpenAPI / Swagger):** [`/docs/api`](/docs/api)  
> **Machine-readable spec:** [`/docs/api.json`](/docs/api.json)
>
> **Base URL:** `http://localhost/api/v1` (development) | `https://api.feedsforless.com/api/v1` (production)
>
> **Version:** v1  
> **Response Format:** `application/json` (except file/YAML endpoints documented in Swagger)  
> **Authentication:** Laravel Sanctum — Bearer Token (see Swagger security schemes)

Endpoint paths, request bodies, and response schemas are maintained automatically in **Swagger UI**. This document keeps conventions, error codes, and reference tables that are not duplicated in the OpenAPI spec.

---

## Table of Contents

- [General Conventions](#general-conventions)
- [Authentication — Auth](#authentication--auth)
- [Catalog](#catalog)
- [RFQ List](#rfq-list)
- [Addresses](#addresses)
- [Quote Requests](#quote-requests)
- [Admin — Quotes](#admin--quotes)
- [Admin — Products](#admin--products)
- [Admin — Categories](#admin--categories)
- [Admin — Packaging Types](#admin--packaging-types)
- [Admin — Product Packaging](#admin--product-packaging)
- [Admin — Volume Pricing Tiers](#admin--volume-pricing-tiers)
- [Admin — Product Specifications](#admin--product-specifications)
- [Admin — Product Technical](#admin--product-technical)
- [Admin — Nutritional Analysis](#admin--nutritional-analysis)
- [Admin — Related Products](#admin--related-products)
- [Admin — Measure Units](#admin--measure-units)
- [Admin — Parameters](#admin--parameters)
- [Admin — Test Methods](#admin--test-methods)
- [Admin — Handling Specs](#admin--handling-specs)
- [Admin — Typical Applications](#admin--typical-applications)
- [Admin — Users](#admin--users)
- [Admin — Companies](#admin--companies)
- [Admin — Dashboard](#admin--dashboard)
- [AI Agent API](../docs/ai-agent-api.md) — agent tokens, YAML masters, product upsert
- [Global Error Codes](#global-error-codes)
- [Quote Request Status Reference](#quote-request-status-reference)

---

## General Conventions

### Required Headers for All Requests

| Header | Value | When |
|--------|-------|------|
| `Content-Type` | `application/json` | Any request with a body (POST, PUT, PATCH) |
| `Accept` | `application/json` | Always (ensures Laravel returns JSON on errors) |
| `Authorization` | `Bearer {token}` | All protected routes (`auth:sanctum`) |

### Pagination

Paginated list endpoints return the following structure:

```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/v1/endpoint?page=1",
    "last": "http://localhost/api/v1/endpoint?page=5",
    "prev": null,
    "next": "http://localhost/api/v1/endpoint?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

## Authentication — Auth

### `POST /v1/auth/register`

Registers a new B2B customer along with their associated company.

**Auth required:** ❌ No

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "company_name": "Acme Corp",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@acme.com",
  "password": "secret1234",
  "password_confirmation": "secret1234",
  "phone": "+1 305 555 0100",
  "job_title": "Purchasing Manager",
  "tax_classification": "LLC",
  "tax_registration_number": "12-3456789"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `company_name` | string | ✅ | max:255 |
| `first_name` | string | ✅ | max:100 |
| `last_name` | string | ✅ | max:100 |
| `email` | string | ✅ | valid email, unique in `users` |
| `password` | string | ✅ | min:8, must be confirmed |
| `password_confirmation` | string | ✅ | must match `password` |
| `phone` | string | ❌ | max:50 |
| `job_title` | string | ❌ | max:100 |
| `tax_classification` | string | ❌ | max:100 |
| `tax_registration_number` | string | ❌ | max:100 |

**Success Response — `201 Created`:**
```json
{
  "message": "Customer registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@acme.com"
  },
  "token": "1|abc123tokenvalue..."
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `422 Unprocessable Entity` | Validation failed (required field missing, duplicate email, passwords don't match) |

**Error 422 example:**
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### `POST /v1/auth/login`

Authenticates an existing user and returns an access token.

**Auth required:** ❌ No

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "email": "john.doe@acme.com",
  "password": "secret1234"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `email` | string | ✅ | valid email format |
| `password` | string | ✅ | — |

**Success Response — `200 OK`:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@acme.com",
    "roles": ["customer"]
  },
  "token": "2|xyz789tokenvalue..."
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `422 Unprocessable Entity` | Invalid credentials |

**Error 422 example:**
```json
{
  "message": "These credentials do not match our records.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

---

### `POST /v1/auth/logout`

Revokes the current access token of the authenticated user.

**Auth required:** ✅ Yes (Bearer Token)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Request Body:** None

**Success Response — `200 OK`:**
```json
{
  "message": "Successfully logged out"
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `401 Unauthorized` | Invalid or missing token |

---

### `GET /v1/auth/me`

Returns the authenticated user's profile including their roles.

**Auth required:** ✅ Yes (Bearer Token)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Params:** None
**Request Body:** None

**Success Response — `200 OK`:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@acme.com",
    "roles": ["customer"]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `401 Unauthorized` | Token not sent or expired |

---

## Catalog

### `GET /v1/catalog/categories`

Returns all categories in a tree structure (parent + children).

**Auth required:** ❌ No

**Headers:**
```
Accept: application/json
```

**Query Params:** None

**Success Response — `200 OK`:**
```json
{
  "data": [
    {
      "id": 1,
      "label": "Animal Feed",
      "parent_id": null,
      "children": [
        {
          "id": 3,
          "label": "Poultry Feed",
          "parent_id": 1,
          "children": []
        }
      ]
    }
  ]
}
```

---

### `GET /v1/catalog/products`

Lists all published products (paginated, 15 per page).

**Auth required:** ❌ No

**Headers:**
```
Accept: application/json
```

**Query Params:**

| Param | Type | Description |
|-------|------|-------------|
| `page` | integer | Page number (default: 1) |

**Success Response — `200 OK`:** (paginated)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Corn Gluten Meal",
      "slug": "corn-gluten-meal",
      "status": "published",
      "categories": [...],
      "packaging": [...]
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### `GET /v1/catalog/products/{product}`

Returns the full detail of a single published product.

**Auth required:** ❌ No

**Headers:**
```
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `product` | integer | Product ID |

**Success Response — `200 OK`:**
```json
{
  "data": {
    "id": 1,
    "name": "Corn Gluten Meal",
    "slug": "corn-gluten-meal",
    "description": "...",
    "status": "published",
    "categories": [...],
    "packaging": [...]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `404 Not Found` | Product does not exist |

---

## RFQ List

> The RFQ list supports both authenticated users and anonymous sessions via `session_id`.

### `POST /v1/rfq-list/items`

Adds a product to the RFQ list for a user or session.

**Auth required:** ❌ No (optional)

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}   (optional — if user is logged in)
```

**Request Body:**
```json
{
  "product_id": 1,
  "packaging_type_id": 2,
  "quantity": 500,
  "session_id": "sess_abc123xyz"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | ✅ | exists in `products` |
| `packaging_type_id` | integer | ✅ | exists in `packaging_types` |
| `quantity` | integer | ✅ | min:1 |
| `session_id` | string | ❌ | max:255 — required if user is NOT authenticated |

**Success Response — `200 OK`:**
```json
{
  "message": "Product added to RFQ list successfully",
  "data": {
    "id": 1,
    "status": "active",
    "items": [
      {
        "id": 1,
        "product": {...},
        "packaging_type": {...},
        "quantity": 500
      }
    ]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `422 Unprocessable Entity` | Validation failed |

---

### `GET /v1/rfq-list`

Retrieves the active RFQ list for the authenticated user or the given session.

**Auth required:** ❌ No (optional)

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}   (optional)
```

**Query Params:**

| Param | Type | Description |
|-------|------|-------------|
| `session_id` | string | Required if user is NOT authenticated |

**Success Response — `200 OK`:**
```json
{
  "data": {
    "id": 1,
    "status": "active",
    "items": [...]
  }
}
```

> If no active list exists, `data` will be `null`.

---

### `DELETE /v1/rfq-list/items/{itemId}`

Removes a specific item from the RFQ list.

**Auth required:** ❌ No (ownership is not validated currently)

**Headers:**
```
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `itemId` | integer | ID of the item to remove |

**Success Response — `200 OK`:**
```json
{
  "message": "Item removed successfully"
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `404 Not Found` | Item does not exist |

---

## Addresses

> All address routes require authentication.

### `GET /v1/addresses`

Lists all addresses belonging to the authenticated user.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response — `200 OK`:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "address_line_1": "123 Main St",
      "city": "Miami",
      "state": "FL",
      "zip_code": "33101",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /v1/addresses`

Creates a new address for the authenticated user.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "address_line_1": "123 Main St",
  "city": "Miami",
  "state": "FL",
  "zip_code": "33101"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `address_line_1` | string | ✅ | max:255 |
| `city` | string | ✅ | max:255 |
| `state` | string | ✅ | max:255 |
| `zip_code` | string | ✅ | max:20 |

**Success Response — `201 Created`:**
```json
{
  "message": "Address created successfully",
  "data": {
    "id": 2,
    "user_id": 1,
    "address_line_1": "123 Main St",
    "city": "Miami",
    "state": "FL",
    "zip_code": "33101",
    "created_at": "2024-01-15T10:00:00.000000Z",
    "updated_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `422 Unprocessable Entity` | Validation failed |
| `401 Unauthorized` | Token not sent |

---

### `GET /v1/addresses/{address}`

Returns a specific address belonging to the authenticated user.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `address` | integer | Address ID |

**Success Response — `200 OK`:**
```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "address_line_1": "123 Main St",
    "city": "Miami",
    "state": "FL",
    "zip_code": "33101"
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `403 Forbidden` | Address belongs to another user |
| `404 Not Found` | Address does not exist |

---

### `PUT /v1/addresses/{address}`

Updates one or more fields of an address (partial updates supported).

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `address` | integer | Address ID |

**Request Body** (all fields optional, validated only when present):
```json
{
  "address_line_1": "456 Ocean Dr",
  "city": "Miami Beach",
  "state": "FL",
  "zip_code": "33139"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `address_line_1` | string | ❌ | if present: max:255 |
| `city` | string | ❌ | if present: max:255 |
| `state` | string | ❌ | if present: max:255 |
| `zip_code` | string | ❌ | if present: max:20 |

**Success Response — `200 OK`:**
```json
{
  "message": "Address updated successfully",
  "data": {
    "id": 1,
    "address_line_1": "456 Ocean Dr",
    "city": "Miami Beach",
    "state": "FL",
    "zip_code": "33139"
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `403 Forbidden` | Address belongs to another user |
| `422 Unprocessable Entity` | Validation failed |
| `404 Not Found` | Address does not exist |

---

### `DELETE /v1/addresses/{address}`

Deletes an address belonging to the authenticated user.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `address` | integer | Address ID |

**Success Response — `200 OK`:**
```json
{
  "message": "Address deleted successfully"
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `403 Forbidden` | Address belongs to another user |
| `404 Not Found` | Address does not exist |

---

## Quote Requests

### `GET /v1/quote-requests`

Lists all quote requests submitted by the authenticated user (paginated, 15 per page).

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Params:**

| Param | Type | Description |
|-------|------|-------------|
| `page` | integer | Page number |

**Success Response — `200 OK`:** (paginated)
```json
{
  "data": [
    {
      "id": 1,
      "status": "pending",
      "delivery_zip": "33101",
      "requires_liftgate": false,
      "requires_appointment": false,
      "total_estimated_cost": null,
      "created_at": "2024-01-15T10:00:00.000000Z",
      "address": {
        "id": 1,
        "address_line_1": "123 Main St",
        "city": "Miami",
        "state": "FL",
        "zip_code": "33101"
      },
      "items": [
        {
          "id": 1,
          "qty": 500,
          "estimated_product_cost": null,
          "estimated_freight_cost": null,
          "line_total_cost": null,
          "product": {...},
          "packaging_type": {...}
        }
      ]
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### `POST /v1/quote-requests`

Submits a new quote request from the user's active RFQ list.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "rfq_list_id": 1,
  "delivery_zip": "33101",
  "requires_liftgate": false,
  "requires_appointment": true,
  "target_address_id": 2
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `rfq_list_id` | integer | ✅ | exists in `rfq_lists` |
| `delivery_zip` | string | ✅ | max:20 |
| `requires_liftgate` | boolean | ❌ | — |
| `requires_appointment` | boolean | ❌ | — |
| `target_address_id` | integer | ❌ | exists in `addresses` if provided |

**Success Response — `201 Created`:**
```json
{
  "message": "Quote request submitted successfully",
  "data": {
    "id": 1,
    "status": "pending",
    "delivery_zip": "33101",
    "requires_liftgate": false,
    "requires_appointment": true,
    "total_estimated_cost": null,
    "items": [...],
    "address": {...}
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `400 Bad Request` | Business logic error (e.g., empty list, already submitted) |
| `422 Unprocessable Entity` | Validation failed |
| `401 Unauthorized` | Token not sent |

**Error 400 example:**
```json
{
  "message": "The RFQ list is empty or has already been submitted."
}
```

---

### `POST /v1/quote-requests/{quoteRequest}/accept`

The customer accepts a quote that is in `quoted` status.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `quoteRequest` | integer | Quote request ID |

**Request Body:** None

**Success Response — `200 OK`:**
```json
{
  "message": "Quote accepted successfully",
  "data": {
    "id": 1,
    "status": "accepted",
    "items": [...]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `403 Forbidden` | Quote belongs to another user |
| `400 Bad Request` | Quote is not in `quoted` status |
| `404 Not Found` | Quote does not exist |

**Error 400 example:**
```json
{
  "message": "Only quoted requests can be accepted"
}
```

---

### `POST /v1/quote-requests/{quoteRequest}/reject`

The customer rejects a quote that is in `quoted` status.

**Auth required:** ✅ Yes

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `quoteRequest` | integer | Quote request ID |

**Request Body:** None

**Success Response — `200 OK`:**
```json
{
  "message": "Quote rejected successfully",
  "data": {
    "id": 1,
    "status": "rejected",
    "items": [...]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `403 Forbidden` | Quote belongs to another user |
| `400 Bad Request` | Quote is not in `quoted` status |
| `404 Not Found` | Quote does not exist |

---

## Admin — Quotes

> All `/v1/admin/*` routes require authentication **and** the `admin` role.

### `GET /v1/admin/quote-requests`

Lists all quote requests in the system (paginated, 15 per page).

**Auth required:** ✅ Admin

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Query Params:**

| Param | Type | Description |
|-------|------|-------------|
| `page` | integer | Page number |

**Success Response — `200 OK`:** (paginated — same structure as `/v1/quote-requests`)

---

### `GET /v1/admin/quote-requests/{quoteRequest}`

Returns the full detail of a specific quote request.

**Auth required:** ✅ Admin

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `quoteRequest` | integer | Quote request ID |

**Success Response — `200 OK`:**
```json
{
  "data": {
    "id": 1,
    "status": "pending",
    "delivery_zip": "33101",
    "total_estimated_cost": null,
    "items": [
      {
        "id": 1,
        "qty": 500,
        "estimated_product_cost": null,
        "estimated_freight_cost": null,
        "line_total_cost": null,
        "product": {...},
        "packaging_type": {...}
      }
    ]
  }
}
```

---

### `PUT /v1/admin/quote-requests/{quoteRequest}/prices`

Admin assigns prices to each item in a quote. Sets quote status to `quoted`.

**Auth required:** ✅ Admin

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
```

**Route Params:**

| Param | Type | Description |
|-------|------|-------------|
| `quoteRequest` | integer | Quote request ID |

**Request Body:**
```json
{
  "items": [
    {
      "id": 1,
      "estimated_product_cost": 12.50,
      "estimated_freight_cost": 3.75
    },
    {
      "id": 2,
      "estimated_product_cost": 8.00,
      "estimated_freight_cost": 2.00
    }
  ]
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `items` | array | ✅ | array of item objects |
| `items[].id` | integer | ✅ | `QuoteRequestItem` ID |
| `items[].estimated_product_cost` | numeric | ✅ | product cost per unit |
| `items[].estimated_freight_cost` | numeric | ✅ | freight cost per unit |

> **Note:** `line_total_cost = (estimated_freight_cost + estimated_product_cost) × qty`
> `total_estimated_cost` = sum of all `line_total_cost` values

**Success Response — `200 OK`:**
```json
{
  "message": "Quote prices updated successfully",
  "data": {
    "id": 1,
    "status": "quoted",
    "total_estimated_cost": 8125.00,
    "items": [...]
  }
}
```

**Errors:**

| Code | Cause |
|------|-------|
| `422 Unprocessable Entity` | Validation failed |
| `404 Not Found` | Quote or item not found |
| `403 Forbidden` | User is not an admin |

---

## Admin — Products

> Base path: `/v1/admin/products`
> **Auth required:** ✅ Admin on all routes

### Endpoint Summary (apiResource)

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/products` | List all products (paginated) |
| `POST` | `/v1/admin/products` | Create a new product |
| `GET` | `/v1/admin/products/{product}` | Get product detail |
| `PUT/PATCH` | `/v1/admin/products/{product}` | Update product |
| `DELETE` | `/v1/admin/products/{product}` | Delete product |
| `POST` | `/v1/admin/products/{product}/categories` | Sync product categories |
| `POST` | `/v1/admin/products/{product}/handling-specs` | Sync handling specifications |
| `POST` | `/v1/admin/products/{product}/applications` | Sync typical applications |

**Body for `POST /v1/admin/products`:**
```json
{
  "name": "Corn Gluten Meal 60%",
  "slug": "corn-gluten-meal-60",
  "description": "High protein animal feed ingredient...",
  "status": "published"
}
```

**Body for `POST /v1/admin/products/{product}/categories`:**
```json
{
  "category_ids": [1, 3, 5]
}
```

---

## Admin — Categories

> Base path: `/v1/admin/categories` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/categories` | List categories |
| `POST` | `/v1/admin/categories` | Create category |
| `GET` | `/v1/admin/categories/{category}` | Get category |
| `PUT/PATCH` | `/v1/admin/categories/{category}` | Update category |
| `DELETE` | `/v1/admin/categories/{category}` | Delete category |

---

## Admin — Packaging Types

> Base path: `/v1/admin/packaging-types` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/packaging-types` | List packaging types |
| `POST` | `/v1/admin/packaging-types` | Create packaging type |
| `GET` | `/v1/admin/packaging-types/{packagingType}` | Get packaging type |
| `PUT/PATCH` | `/v1/admin/packaging-types/{packagingType}` | Update |
| `DELETE` | `/v1/admin/packaging-types/{packagingType}` | Delete |

---

## Admin — Product Packaging

> Base path: `/v1/admin/products/{product}/packaging`
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/products/{product}/packaging` | List packaging options for a product |
| `POST` | `/v1/admin/products/{product}/packaging` | Create packaging option |
| `GET` | `/v1/admin/products/{product}/packaging/{packaging}` | Get packaging detail |
| `PUT/PATCH` | `/v1/admin/products/{product}/packaging/{packaging}` | Update |
| `DELETE` | `/v1/admin/products/{product}/packaging/{packaging}` | Delete |

---

## Admin — Volume Pricing Tiers

> Base path: `/v1/admin/packaging/{packaging}/tiers`
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/packaging/{packaging}/tiers` | List pricing tiers |
| `POST` | `/v1/admin/packaging/{packaging}/tiers` | Create tier |
| `GET` | `/v1/admin/packaging/{packaging}/tiers/{tier}` | Get tier detail |
| `PUT/PATCH` | `/v1/admin/packaging/{packaging}/tiers/{tier}` | Update |
| `DELETE` | `/v1/admin/packaging/{packaging}/tiers/{tier}` | Delete |

---

## Admin — Product Specifications

> Base path: `/v1/admin/products/{product}/specifications` — only `index`, `store`, `destroy`
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/products/{product}/specifications` | List specifications |
| `POST` | `/v1/admin/products/{product}/specifications` | Add specification |
| `DELETE` | `/v1/admin/products/{product}/specifications/{specification}` | Remove specification |

---

## Admin — Product Technical

### `POST /v1/admin/products/{product}/handling-specs`

Syncs the handling specifications associated with a product.

**Auth required:** ✅ Admin

**Body:**
```json
{
  "handling_spec_ids": [1, 2, 3]
}
```

### `POST /v1/admin/products/{product}/applications`

Syncs the typical applications associated with a product.

**Auth required:** ✅ Admin

**Body:**
```json
{
  "application_ids": [1, 4]
}
```

---

## Admin — Nutritional Analysis

> Base path: `/v1/admin/products/{product}/nutritional-analysis` — only `index`, `store`, `destroy`
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/products/{product}/nutritional-analysis` | List nutritional analysis entries |
| `POST` | `/v1/admin/products/{product}/nutritional-analysis` | Add entry |
| `DELETE` | `/v1/admin/products/{product}/nutritional-analysis/{nutritionalAnalysis}` | Remove entry |

---

## Admin — Related Products

> Base path: `/v1/admin/products/{product}/related-products` — only `index`, `store`, `destroy`
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/products/{product}/related-products` | List related products |
| `POST` | `/v1/admin/products/{product}/related-products` | Link a related product |
| `DELETE` | `/v1/admin/products/{product}/related-products/{relatedProduct}` | Unlink |

---

## Admin — Measure Units

> Base path: `/v1/admin/measure-units` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/measure-units` | List measure units |
| `POST` | `/v1/admin/measure-units` | Create |
| `GET` | `/v1/admin/measure-units/{measureUnit}` | Get |
| `PUT/PATCH` | `/v1/admin/measure-units/{measureUnit}` | Update |
| `DELETE` | `/v1/admin/measure-units/{measureUnit}` | Delete |

---

## Admin — Parameters

> Base path: `/v1/admin/parameters` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/parameters` | List parameters |
| `POST` | `/v1/admin/parameters` | Create |
| `GET` | `/v1/admin/parameters/{parameter}` | Get |
| `PUT/PATCH` | `/v1/admin/parameters/{parameter}` | Update |
| `DELETE` | `/v1/admin/parameters/{parameter}` | Delete |

---

## Admin — Test Methods

> Base path: `/v1/admin/test-methods` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/test-methods` | List test methods |
| `POST` | `/v1/admin/test-methods` | Create |
| `GET` | `/v1/admin/test-methods/{testMethod}` | Get |
| `PUT/PATCH` | `/v1/admin/test-methods/{testMethod}` | Update |
| `DELETE` | `/v1/admin/test-methods/{testMethod}` | Delete |

---

## Admin — Handling Specs

> Base path: `/v1/admin/handling-specs` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/handling-specs` | List handling specifications |
| `POST` | `/v1/admin/handling-specs` | Create |
| `GET` | `/v1/admin/handling-specs/{handlingSpec}` | Get |
| `PUT/PATCH` | `/v1/admin/handling-specs/{handlingSpec}` | Update |
| `DELETE` | `/v1/admin/handling-specs/{handlingSpec}` | Delete |

---

## Admin — Typical Applications

> Base path: `/v1/admin/typical-applications` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/typical-applications` | List typical applications |
| `POST` | `/v1/admin/typical-applications` | Create |
| `GET` | `/v1/admin/typical-applications/{typicalApplication}` | Get |
| `PUT/PATCH` | `/v1/admin/typical-applications/{typicalApplication}` | Update |
| `DELETE` | `/v1/admin/typical-applications/{typicalApplication}` | Delete |

---

## Admin — Users

> Base path: `/v1/admin/users` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/users` | List all users |
| `POST` | `/v1/admin/users` | Create user |
| `GET` | `/v1/admin/users/{user}` | Get user detail |
| `PUT/PATCH` | `/v1/admin/users/{user}` | Update user |
| `DELETE` | `/v1/admin/users/{user}` | Delete user |

---

## Admin — Companies

> Base path: `/v1/admin/companies` — full apiResource (CRUD)
> **Auth required:** ✅ Admin on all routes

| Method | Route | Description |
|--------|-------|-------------|
| `GET` | `/v1/admin/companies` | List all companies |
| `POST` | `/v1/admin/companies` | Create company |
| `GET` | `/v1/admin/companies/{company}` | Get company detail |
| `PUT/PATCH` | `/v1/admin/companies/{company}` | Update company |
| `DELETE` | `/v1/admin/companies/{company}` | Delete company |

---

## Admin — Dashboard

### `GET /v1/admin/dashboard/stats`

Returns high-level statistics for the admin dashboard.

**Auth required:** ❌ No (currently a public route)

**Headers:**
```
Accept: application/json
```

**Success Response — `200 OK`:**
```json
{
  "data": {
    "total_users": 150,
    "total_products": 45,
    "total_quote_requests": 320,
    "pending_quotes": 12
  }
}
```

---

## Global Error Codes

| HTTP Code | Meaning | Common Cause |
|-----------|---------|--------------|
| `400 Bad Request` | Business logic error | Invalid action (quote already accepted, empty list, etc.) |
| `401 Unauthorized` | Not authenticated | Token not sent or expired |
| `403 Forbidden` | Not authorized | Accessing another user's resource or missing admin role |
| `404 Not Found` | Resource not found | Invalid ID — Model Binding fails |
| `422 Unprocessable Entity` | Validation failed | Required field missing, wrong format, uniqueness violated |
| `500 Internal Server Error` | Server error | Unhandled exception on the server |

### Validation error structure (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Specific error message for this field."
    ]
  }
}
```

### Generic error structure (400, 403, 404)

```json
{
  "message": "Description of the error."
}
```

---

## Quote Request Status Reference

| Status | Description |
|--------|-------------|
| `pending` | Newly submitted, awaiting admin review |
| `quoted` | Admin has set prices — customer can accept or reject |
| `accepted` | Customer accepted the quote |
| `rejected` | Customer rejected the quote |
| `expired` | Quote expired without a customer response |
| `cancelled` | Cancelled by the admin |

---


