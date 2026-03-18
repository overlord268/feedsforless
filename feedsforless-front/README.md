# FeedsForLess Frontend (Vue 3 + Vite)

## Levantar el proyecto

### Local (desarrollo)
Sin configurar nada: el front usa el API en `http://localhost:8000` automáticamente.

```bash
npm install
npm run dev
```

Abre http://localhost:5173 (o http://127.0.0.1:5173). Asegúrate de tener el API Laravel corriendo en el puerto 8000.

### Producción (Railway)
El build detecta el host y usa el API de producción. Opcional: define `VITE_API_URL` en Railway si usas otro dominio.

```bash
npm run build
```

---

Vue 3 `<script setup>` SFCs — [docs](https://v3.vuejs.org/api/sfc-script-setup.html#sfc-script-setup).
