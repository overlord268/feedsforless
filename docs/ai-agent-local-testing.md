# Pruebas locales — API agente IA (Epic 1 y 2)

Guía de comandos para Windows (Laragon).  
Frontend admin: `http://localhost:5173` · API: `http://localhost:8000/api/v1`

---

## 0. Requisitos

- Token generado en **System → AI Agent Tokens** (`http://localhost:5173/admin/agent-tokens`)
- API en marcha (Laragon o `php artisan serve`)
- Usar **PowerShell** (`PS C:\...>`) para `Invoke-WebRequest` / `Invoke-RestMethod`
- En **CMD** usar la sección de `curl` al final

---

## 1. Arrancar el API (terminal 1)

```powershell
cd c:\laragon\www\feedsforless\feedsforless-api
php artisan serve
```

Debe quedar en `http://127.0.0.1:8000`. Deja esta ventana abierta.

Comprobar en navegador (sin token → 401 es correcto):

```text
http://localhost:8000/api/v1/masters
```

---

## 2. Variables (terminal 2 — PowerShell)

Ejecutar al inicio de cada sesión de pruebas:

```powershell
$token = "PEGA_AQUI_TU_TOKEN_COMPLETO"
$base  = "http://localhost:8000/api/v1"
```

---

## 3. Epic 1 — GET /masters (catálogo YAML)

### Ver respuesta en pantalla

```powershell
$response = Invoke-WebRequest -Uri "$base/masters" -Headers @{ Authorization = "Bearer $token" } -UseBasicParsing
$response.StatusCode
$response.Content
```

### Guardar YAML en el Escritorio

```powershell
$response = Invoke-WebRequest -Uri "$base/masters?include_products=1" -Headers @{ Authorization = "Bearer $token" } -UseBasicParsing
$response.Content | Out-File "$env:USERPROFILE\Desktop\masters.yaml" -Encoding utf8
```

**Importante:** usar `$response.Content`, no guardar `$response` directo (archivo vacío).

### Verificar tamaño del archivo

```powershell
(Get-Item "$env:USERPROFILE\Desktop\masters.yaml").Length
```

Debe ser > 0 (normalmente miles de bytes).

---

## 4. Epic 2 — POST /ai/products (crear producto)

### Prueba mínima (JSON inline)

Sustituir `slug-categoria-real` por un slug de `categories` en `masters.yaml`:

```powershell
$body = @{
  slug = "prueba-api-001"
  name = "Producto prueba API"
  status = "draft"
  stock_status = "in_stock"
  category_slugs = @("slug-categoria-real")
  handling_spec_slugs = @()
  application_slugs = @()
  related_product_slugs = @()
  packaging = @()
  nutritional_analysis = @()
  specifications = @()
} | ConvertTo-Json -Depth 10

Invoke-RestMethod -Method POST -Uri "$base/ai/products" -Headers @{ Authorization = "Bearer $token"; "Content-Type" = "application/json" } -Body $body
```

### Desde JSON de Claude (archivo)

1. Guardar JSON en `C:\Users\TU_USUARIO\Desktop\producto.json`
2. Ejecutar **una sola línea**:

```powershell
$json = Get-Content "$env:USERPROFILE\Desktop\producto.json" -Raw; Invoke-RestMethod -Method POST -Uri "$base/ai/products" -Headers @{ Authorization = "Bearer $token"; "Content-Type" = "application/json" } -Body $json
```

### Multilínea (PowerShell)

```powershell
$json = Get-Content "$env:USERPROFILE\Desktop\producto.json" -Raw

Invoke-RestMethod -Method POST -Uri "$base/ai/products" `
  -Headers @{ Authorization = "Bearer $token"; "Content-Type" = "application/json" } `
  -Body $json
```

### Respuestas esperadas

| Código | Significado |
|--------|-------------|
| **201** | `"action": "created"` — producto nuevo |
| **200** | `"action": "updated"` — mismo `slug`, actualizado |
| **401** | Token inválido o revocado |
| **422** | Slugs incorrectos — revisar `references` en el error |

Verificar en admin: `http://localhost:5173/admin/products`

---

## 5. Prueba de token revocado

Revocar en admin, luego:

```powershell
Invoke-WebRequest -Uri "$base/masters" -Headers @{ Authorization = "Bearer $token" } -UseBasicParsing
```

Debe dar **401**.

---

## 6. Admin — tokens (API con Sanctum, opcional)

Login admin normal; usar token Sanctum del navegador (no el agent token):

```powershell
# Listar tokens agente (respuesta JSON, sin secreto)
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/admin/agent-api-tokens" -Headers @{ Authorization = "Bearer SANCTUM_TOKEN_ADMIN" }
```

Gestión habitual: panel `http://localhost:5173/admin/agent-tokens`

---

## 7. Limpiar todos los tokens agente (BD)

```powershell
cd c:\laragon\www\feedsforless\feedsforless-api
php artisan tinker --execute="App\Models\AgentApiToken::query()->delete();"
```

Luego generar token nuevo en el admin.

---

## 8. Alternativa CMD (curl)

### GET masters

```cmd
set TOKEN=PEGA_TU_TOKEN
curl -s -H "Authorization: Bearer %TOKEN%" http://localhost:8000/api/v1/masters
```

### Guardar masters.yaml

```cmd
curl -s -H "Authorization: Bearer %TOKEN%" "http://localhost:8000/api/v1/masters?include_products=1" -o "%USERPROFILE%\Desktop\masters.yaml"
```

### POST producto

```cmd
curl -X POST http://localhost:8000/api/v1/ai/products -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/json" -d @%USERPROFILE%\Desktop\producto.json
```

---

## 9. Flujo Claude (manual)

1. `GET /masters` → `masters.yaml`
2. Claude: pegar YAML + ficha/PDF → pedir **JSON** (no YAML) para `POST /ai/products`
3. Guardar JSON → comando sección 4
4. Revisar en admin products

Prompt útil para Claude:

```text
Usa SOLO slugs del YAML adjunto. Responde ÚNICAMENTE JSON válido para POST /api/v1/ai/products con:
slug, name, status, stock_status, category_slugs, handling_spec_slugs, application_slugs,
related_product_slugs, packaging, nutritional_analysis, specifications.
status debe ser "draft" salvo que indique lo contrario.
```

---

## 10. Errores frecuentes

| Error | Causa | Solución |
|-------|--------|----------|
| `$token` no reconocido | Estás en **CMD** | Usar PowerShell o `set TOKEN=` + curl |
| `-Body` no reconocido | Líneas sueltas, no un comando | Copiar `Invoke-RestMethod` completo |
| `masters.yaml` vacío | Guardaste `$response` sin `.Content` | Usar `$response.Content \| Out-File ...` |
| 401 | Token revocado o mal copiado | Token nuevo en admin |
| 422 references | Slug no existe en masters | Corregir JSON con slugs del YAML |

---

## Referencias

- [ai-agent-api.md](./ai-agent-api.md) — documentación API
- Panel tokens: `/admin/agent-tokens`
- Endpoints agente: `GET /masters`, `POST /ai/products`
