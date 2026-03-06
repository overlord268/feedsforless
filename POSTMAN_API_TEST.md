# Probar API en Postman

## 1. Login

**Método:** `POST`  
**URL:** `http://localhost:8000/api/v1/auth/login`

**Headers:**
| Key          | Value            |
|-------------|------------------|
| Content-Type | application/json |
| Accept      | application/json |

**Body (raw – JSON):**
```json
{
  "email": "tu_email@ejemplo.com",
  "password": "tu_password"
}
```

**Ejemplo con usuario de prueba (si tienes uno en la BD):**
```json
{
  "email": "admin@feedsforless.com",
  "password": "password"
}
```

**Respuesta esperada (200):**
```json
{
  "message": "Login successful",
  "user": { "id": 1, "email": "...", ... },
  "token": "1|xxxxxxxxxxxx..."
}
```

---

## 2. cURL (para pegar en terminal)

```bash
curl -X POST "http://localhost:8000/api/v1/auth/login" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\": \"admin@feedsforless.com\", \"password\": \"password\"}"
```

En **PowerShell**:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/v1/auth/login" -Method POST -ContentType "application/json" -Body '{"email":"admin@feedsforless.com","password":"password"}'
```

En **Git Bash / Linux / Mac**:
```bash
curl -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@feedsforless.com","password":"password"}'
```

---

## 3. Si sigue dando 404

1. **Comprueba que el servidor esté en el puerto 8000:**
   ```bash
   cd feedsforless-api
   php artisan serve
   ```
   Debe salir: `Server running on [http://127.0.0.1:8000]`

2. **Prueba esta URL en el navegador (GET):**
   ```
   http://localhost:8000/api/v1/auth/login
   ```
   Deberías ver **405 Method Not Allowed** (la ruta existe pero GET no está permitido), no 404.

3. **Si ves 404**, lista las rutas:
   ```bash
   cd feedsforless-api
   php artisan route:list
   ```
   Busca una línea que contenga `auth/login` y comprueba la URI exacta.

4. **Usuario para probar:** Si no tienes usuario, créalo por registro o por tinker:
   ```bash
   php artisan tinker
   ```
   Luego (ajusta email/password):
   ```php
   $u = \App\Domains\B2B\Models\User::create(['name'=>'Admin','email'=>'admin@test.com','password'=>bcrypt('password')]);
   ```
