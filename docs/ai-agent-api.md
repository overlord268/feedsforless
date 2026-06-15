# AI Agent API

Machine-to-machine endpoints for external AI agents (e.g. Claude). Uses **dedicated agent API tokens**, separate from Sanctum user sessions.

**Base URL:** `/api/v1`

**OpenAPI spec:** `{APP_URL}/docs/api.json` — fetch this for complete endpoint schemas, parameters, and payloads.  
**Swagger UI:** `{APP_URL}/docs/api` — browse all API endpoints including agent routes under **AI Agent API**.

---

## Admin: generate a token

1. Log in to the admin panel as an admin user.
2. Open **System → AI Agent Tokens** (`/admin/agent-tokens`).
3. Click **Generate token**, enter a name (e.g. `Claude Production`).
4. Copy the `plain_token` from the response immediately — it is shown **once** and cannot be retrieved later.
5. Store the token securely in the agent environment (secrets manager, not in git).

### Admin API (Sanctum + admin role)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/admin/agent-api-tokens` | List tokens (prefix only, never full secret) |
| POST | `/api/v1/admin/agent-api-tokens` | Create token — body: `{ "name": "..." }` — returns `plain_token` once |
| DELETE | `/api/v1/admin/agent-api-tokens/{id}` | Revoke token |
| POST | `/api/v1/admin/agent-api-tokens/{id}/rotate` | Revoke and issue new token — returns `plain_token` once |

---

## Agent authentication

Send the token on every agent request:

```
Authorization: Bearer <secret>
```

Revoked or invalid tokens receive `401 Unauthenticated.`

Agent routes are rate-limited to **60 requests per minute**.

---

## GET /masters

Returns all catalog master records and field constraints as **YAML** (`text/yaml`).

Use this before creating products so the agent only references valid slugs.

```bash
curl -s -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/v1/masters"
```

Optional: include a slim product index for related-product slugs:

```bash
curl -s -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost/api/v1/masters?include_products=1"
```

### YAML sections

- `meta` — version and generation timestamp
- `constraints` — allowed `status`, `stock_status`, required nested fields, slug pattern
- `categories`, `packaging_types`, `parameters`, `test_methods`, `measure_units`
- `nutritional_parameters`, `handling_specs`, `typical_applications`
- `products` (only when `include_products=1`)

---

## POST /ai/products

Create or update a product by **slug** (upsert). All master references use **slugs**, not integer IDs.

**Prerequisite:** Every slug in the payload must exist in `GET /masters` (or be the product’s own slug for self-reference in related products).

```bash
curl -s -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "example-product",
    "name": "Example Product",
    "status": "draft",
    "stock_status": "in_stock",
    "category_slugs": ["your-category-slug"],
    "packaging": [],
    "nutritional_analysis": [],
    "specifications": []
  }' \
  "http://localhost/api/v1/ai/products"
```

### Response

- `201` + `"action": "created"` — new product
- `200` + `"action": "updated"` — existing product (matched by `slug`)
- `422` — validation errors (including `references` array for unknown slugs)

### Payload reference

| Field | Required | Notes |
|-------|----------|-------|
| `slug` | Yes | Unique product slug |
| `name` | Yes | Display name |
| `status` | Yes | `draft`, `published`, or `archived` |
| `category_slugs` | Yes | Min 1; must exist in masters |
| `stock_status` | No | Default `in_stock` |
| `handling_spec_slugs` | No | Array of slugs |
| `application_slugs` | No | Typical application slugs |
| `related_product_slugs` | No | Other product slugs (must exist) |
| `packaging` | No | See plan example with `presentation_index`, `packaging_type_slug`, tiers |
| `nutritional_analysis` | No | `parameter_slug`, `value`, `measure_unit_slug` |
| `specifications` | No | `parameter_slug`, `test_method_slug`, `specification`, `measure_unit_slug` |
| `lead_time_days` / `max_lead_time_days` | No | Converted to dates from today |

SKU is auto-generated from slug on create (same rules as Excel import).

---

## Recommended agent workflow

1. `GET /masters` — load constraints and valid slugs into context.
2. Extract product data from source documents.
3. `POST /ai/products` — submit structured JSON using only slugs from step 1.
4. On `422`, fix slug references and retry.
