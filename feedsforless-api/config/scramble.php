<?php

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     */
    'api_path' => 'api/v1',

    'api_domain' => null,

    'export_path' => 'storage/api-docs/openapi.json',

    'info' => [
        'version' => env('API_VERSION', '1.0.0'),

        'description' => <<<'MD'
# FeedsForLess API (v1)

Interactive reference for all `/api/v1` endpoints. Machine-readable spec: [`/docs/api.json`](/docs/api.json).

## Base URL

- Development: `{APP_URL}/api/v1`
- Production: `https://api.feedsforless.com/api/v1`

## Authentication

| Scheme | Header | Used for |
|--------|--------|----------|
| **Sanctum** | `Authorization: Bearer {token}` | Customer and admin user sessions |
| **Agent token** | `Authorization: Bearer {token}` | AI agent routes (`/masters`, `/ai/products`) |
| **None** | — | Public catalog, RFQ, guest quotes, newsletter |

Admin routes require Sanctum **and** the `admin` role.

Agent tokens are managed in the admin panel and documented in [AI Agent API workflow](https://github.com/feedsforless/docs/ai-agent-api.md).

## Conventions

- Send `Accept: application/json` on all requests.
- Send `Content-Type: application/json` on JSON bodies.
- Paginated lists return `data`, `links`, and `meta` at the top level (when using API Resources).

## Common errors

| Code | Meaning |
|------|---------|
| 400 | Business logic error (`{ "message": "..." }`) |
| 401 | Missing or invalid token |
| 403 | Forbidden (wrong user or missing admin role) |
| 404 | Resource not found |
| 422 | Validation failed (`{ "message", "errors" }`) |
| 500 | Server error |

## Quote request statuses

`pending` → `quoted` → `accepted` | `rejected` | `expired` | `cancelled`
MD,
    ],

    'ui' => [
        'title' => 'FeedsForLess API',
        'theme' => 'light',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    'servers' => null,

    'enum_cases_description_strategy' => 'description',
    'enum_cases_names_strategy' => false,
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
