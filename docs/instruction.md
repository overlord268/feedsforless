# FeedsForLess — Product Import Template (v2)

Guide for `product-import-template-v2-feedsforless.xlsx`.

Generate the template:

```bash
python scripts/generate-product-import-template2.py
```

---

## Workbook structure

| Group | Sheets | Purpose |
|-------|--------|---------|
| **Masters** | 8 tabs | Shared catalog values. Fill **first**. |
| **Products** | `PRODUCTS` only | One row per product. All product-specific data on this row. |

---

## Fill order

```
Step 1 → Master sheets (all 8 tabs)
Step 2 → PRODUCTS (one row per product)
```

Every slug used in `PRODUCTS` must already exist in the matching master sheet (or in `PRODUCTS.slug` for related products).

---

## Separator standard: pipe `|`

All list values in `PRODUCTS` use **pipe `|`** as the only separator.

| Column type | Fields per item | Example |
|-------------|-----------------|---------|
| Slug lists | 1 (slug only) | `phosphates\|feed-additives` |
| packaging | 4 | `1\|25-kg-bag\|40\|12.50\|2\|big-bag-1000-kg\|20\|480.00` |
| volume_tiers | 7 | `1\|Tier 1\|1\|99\|percentage\|0\|\|1\|Tier 2\|99\|\|percentage\|5` |
| nutritional_analysis | 3 | `phosphorus-p\|18.5\|percent\|calcium-ca\|21\|percent` |
| specifications | 4 | `moisture\|aoac-930-15\|Max 12\|percent\|pH\|iso-6491\|5-7\|percent` |

**Multiple items** = concatenate with `|`, using a **fixed field count** per column (see above).

**Empty optional field** = leave blank between pipes. Example: unlimited max quantity → `100||5` (empty 4th field of that tier item).

---

## Master sheets

Each master sheet: row 1 = headers, row 2 = descriptions, row 3+ = data.

Every master record needs a unique **`slug`**.

### Master dependencies

| Sheet | Depends on another master? |
|-------|---------------------------|
| CATEGORIES | **Yes** — optional `parent_slug`. Parent row must exist first. |
| PACKAGING_TYPES | No |
| PARAMETERS | No |
| TEST_METHODS | No |
| MEASURE_UNITS | No |
| NUTRITIONAL_PARAMETERS | No |
| HANDLING_SPECS | No |
| TYPICAL_APPLICATIONS | No |

### CATEGORIES

| Column | Required | Description |
|--------|----------|-------------|
| label | Yes | Display name |
| slug | Yes | Unique slug |
| parent_slug | No | Parent slug. Empty = root |

### PACKAGING_TYPES

| Column | Required |
|--------|----------|
| name | Yes |
| slug | Yes |

### PARAMETERS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |
| type | No |

### TEST_METHODS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |

### MEASURE_UNITS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |
| notation | No |

### NUTRITIONAL_PARAMETERS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |
| notation | No |

### HANDLING_SPECS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |
| requirement | Yes |

### TYPICAL_APPLICATIONS

| Column | Required |
|--------|----------|
| label | Yes |
| slug | Yes |
| description | No |

---

## PRODUCTS sheet

One row = one product. No SKU column — use **`slug`**.

### General fields

| Column | Required | Description |
|--------|----------|-------------|
| slug | Yes | Unique product identifier |
| name | Yes | Product name |
| grade | No | Grade / concentration |
| base_price_ref | No | Reference price |
| description | No | HTML allowed |
| status | Yes | `draft`, `published`, or `archived` |
| stock_status | No | `in_stock`, `out_of_stock`, `backorder`, or `call` |
| availability | No | Customer-facing text |
| lead_time_days | No | Integer days |
| max_lead_time_days | No | Integer days |
| origin_address | No | Origin |
| shelf_life_template | No | E.g. `24 months` |
| market_trends_link | No | URL |
| tds_document_url | No | TDS PDF URL or path |
| sds_document_url | No | SDS PDF URL or path |
| coa_document_url | No | COA PDF URL or path |

### Master links (pipe-separated slugs)

| Column | Master sheet | Example |
|--------|--------------|---------|
| category_slugs | CATEGORIES | `phosphates\|feed-additives` |
| handling_spec_slugs | HANDLING_SPECS | `store-dry\|temperature` |
| application_slugs | TYPICAL_APPLICATIONS | `poultry-feed\|swine-feed` |
| related_product_slugs | PRODUCTS | `monocalcium-feed-phosphate\|other-slug` |

### Product-only columns (pipe-separated, fixed fields per item)

#### `packaging` — 4 fields per item

`presentation_index|packaging_type_slug|quantity_per_pallet|base_price_per_unit`

```
1|25-kg-bag|40|12.50|2|big-bag-1000-kg|20|480.00
```

#### `volume_tiers` — 7 fields per item

`presentation_index|tier_name|min_quantity|max_quantity|pricing_mode|discount_percentage|fixed_price`

| Field | Description |
|-------|-------------|
| presentation_index | Must match a `packaging` presentation index |
| tier_name | Label shown in admin / calculator |
| min_quantity | Minimum quantity (first tier is normalized to 1 on import) |
| max_quantity | Maximum quantity; **empty on last tier = unlimited (∞)** |
| pricing_mode | `percentage` (or `%`) for **All %** tiers; `fixed_price` (or `$`) for **All $** tiers |
| discount_percentage | Used when mode is `percentage` (0–100) |
| fixed_price | Used when mode is `fixed_price` (unit price in USD) |

**Rules (same as admin UI):**

- All tiers within one presentation must use the **same** `pricing_mode` (no mixing % and $).
- Tiers are **chained**: row 2+ `min_quantity` is derived from the previous tier’s `max_quantity` on save.
- The **last tier** always gets unlimited max on import.

**All % example** (presentation 1):

```
1|Small orders|1|49|percentage|0||1|Volume|49||percentage|12|
```

**All $ example** (presentation 2):

```
2|Tote single|1|3|fixed_price||1180|2|Tote bulk|3||fixed_price||1050
```

**Legacy 5-field format** (percentage only) is still accepted:

```
1|Tier 1|1|99|0|1|Tier 2|100||5
```

#### `nutritional_analysis` — 3 fields per item

`nutritional_parameter_slug|value|measure_unit_slug`

```
phosphorus-p|18.5|percent|calcium-ca|21|percent
```

#### `specifications` — 4 fields per item

`parameter_slug|test_method_slug|specification|measure_unit_slug`

```
moisture|aoac-930-15|Max 12|percent|pH|iso-6491|5-7|percent
```

---

## Example workbook (two products)

The template includes **two product rows** that demonstrate different import patterns.

### Product 1 — `premium-lysine-hcl` (new masters + two packagings with tiers)

Creates **new rows in every master sheet** and registers a product with:

- **Presentation 1** (`50-lb-bag-lysine`): **All %** — 0% discount up to 49 T, then 12% off from 49 T+
- **Presentation 2** (`tote-2000-lb`): **All $** — $1,180/T for 1–3 totes, then $1,050/T from 3+

| Column | Value |
|--------|-------|
| slug | `premium-lysine-hcl` |
| category_slugs | `amino-acids` |
| handling_spec_slugs | `keep-sealed` |
| application_slugs | `aqua-feed` |
| packaging | `1\|50-lb-bag-lysine\|48\|620.00\|2\|tote-2000-lb\|1\|850.00` |
| volume_tiers | `1\|Small orders\|1\|49\|percentage\|0\|\|1\|Volume\|49\|\|percentage\|12\|\|2\|Tote single\|1\|3\|fixed_price\|\|1180\|2\|Tote bulk\|3\|\|fixed_price\|\|1050` |
| nutritional_analysis | `lysine\|98.5\|percent` |
| specifications | `purity-lysine\|usp-891\|Min 98.5\|percent` |

### Product 2 — `lysine-premix-blend` (one packaging, base price only, existing slugs)

Reuses **slugs already in the workbook or database** — no new master rows required:

- **One packaging** with `base_price_per_unit` only — **leave `volume_tiers` empty**
- **`related_product_slugs`** points to products already registered (`premium-lysine-hcl` from row above, plus e.g. `urea-feed-grade` if it exists in your DB)

| Column | Value |
|--------|-------|
| slug | `lysine-premix-blend` |
| category_slugs | `phosphates` |
| handling_spec_slugs | `store-dry` |
| application_slugs | `poultry-feed` |
| related_product_slugs | `premium-lysine-hcl\|urea-feed-grade` |
| packaging | `1\|25-kg-bag\|40\|85.00` |
| volume_tiers | *(empty)* |
| nutritional_analysis | `phosphorus-p\|18.5\|percent` |
| specifications | `moisture\|aoac-930-15\|Max 12\|percent` |

> **Import order:** Product 1 must appear **before** Product 2 if you link to `premium-lysine-hcl` in the same file. Slugs like `urea-feed-grade` must already exist in the database.

---

## Full example row (legacy single product)

| Column | Value |
|--------|-------|
| slug | `dicalcium-feed-phosphate` |
| name | `Dicalcium Feed Phosphate` |
| status | `published` |
| category_slugs | `phosphates` |
| handling_spec_slugs | `store-dry` |
| application_slugs | `poultry-feed\|swine-feed` |
| related_product_slugs | `monocalcium-feed-phosphate` |
| packaging | `1\|25-kg-bag\|40\|12.50\|2\|big-bag-1000-kg\|20\|480.00` |
| volume_tiers | `1\|Tier 1\|1\|99\|percentage\|0\|\|1\|Tier 2\|99\|\|percentage\|5` |
| nutritional_analysis | `phosphorus-p\|18.5\|percent\|calcium-ca\|21\|percent` |
| specifications | `moisture\|aoac-930-15\|Max 12\|percent` |

---

## How data is shared

```
PACKAGING_TYPES sheet:  25-kg-bag  (defined once)
PRODUCTS row A packaging: 1|25-kg-bag|40|12.50
PRODUCTS row B packaging: 1|25-kg-bag|50|11.00
```

Masters = one definition, many products reference by slug.  
Product columns = values unique to each product row.

---

## Recommended workflow

1. Fill all **master sheets** (root categories before children).
2. Add one **PRODUCTS** row per product.
3. Set master link columns using slugs from step 1.
4. Fill product-only columns (`packaging`, `specifications`, …).
5. Validate every referenced slug exists in a master sheet.

---

## Common mistakes

| Mistake | Fix |
|---------|-----|
| Using commas in list columns | Use `\|` only |
| Slug not in master sheet | Add master row first |
| Child category before parent | Add parent in CATEGORIES first |
| Wrong field count when parsing | packaging=4, volume_tiers=7 (legacy 5), nutritional_analysis=3, specifications=4 fields per item |
| Mixed pricing modes in one presentation | Use either all `percentage` or all `fixed_price` tiers per presentation |
| Tier index mismatch | `presentation_index` in `volume_tiers` must match `packaging` |

---

## Import via admin UI

1. Go to **Admin → Products → Import** (`/admin/products/import`).
2. Download the template or use your filled workbook.
3. Run a **dry run** first to validate without saving.
4. Uncheck dry run and import again to persist data.

API endpoints (admin auth required):

- `GET /api/v1/admin/products/import/template` — download template
- `POST /api/v1/admin/products/import` — multipart field `file`, optional `dry_run` (boolean)

Existing products are matched by **slug** and updated. New products get an auto-generated **SKU** from the slug.

---

| Sheet | Fill order |
|-------|------------|
| CATEGORIES | 1 |
| PACKAGING_TYPES | 1 |
| PARAMETERS | 1 |
| TEST_METHODS | 1 |
| MEASURE_UNITS | 1 |
| NUTRITIONAL_PARAMETERS | 1 |
| HANDLING_SPECS | 1 |
| TYPICAL_APPLICATIONS | 1 |
| PRODUCTS | 2 |
