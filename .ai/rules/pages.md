---
paths:
  - 'resources/js/pages/**'
---

# Pages

## TanStack Table v9: usar useTable, no la API v8
El proyecto usa @tanstack/vue-table 9.x. Casi toda la documentación pública en línea es de v8 y no aplica.

- v9: `useTable({ features, columns, data })`. v8 (`useVueTable`, `getCoreRowModel()`, `getSortedRowModel()` como opciones) NO existe.
- Cada capacidad se registra explícitamente en `tableFeatures({...})` junto con su row model: `rowSortingFeature` + `createSortedRowModel()`, `rowPaginationFeature` + `createPaginatedRowModel()`, etc. Si falta el feature, su estado y sus métodos (`setSorting`, `nextPage`, …) simplemente no se instalan.
- El filtro global requiere además `columnFilteringFeature` + `createFilteredRowModel()`.
- `data` se pasa como ref/computed, nunca `data.value`; el adapter observa la referencia.
- Leer estado con `table.atoms.<slice>.get()` solo es reactivo dentro de template, `computed` o `watch`.
- Referencia canónica offline: `node_modules/@tanstack/vue-table/skills/` y `node_modules/@tanstack/table-core/skills/` (SKILL.md por tema). Consultar ahí antes que la web.

## useHttp rechaza la promesa aunque uses onHttpException
`useHttp` de Inertia v3 llama al callback (`onHttpException`, `onNetworkError`) y **después vuelve a lanzar el error**. Solo el 422 se absorbe (va a `onError`).

Si no encadenas un `.catch()`, cualquier 4xx/5xx deja una promesa sin manejar en consola:

```ts
peticion.get(url, { onSuccess, onHttpException }).catch(() => {});
```

Además `respuesta.data` en `onHttpException` llega como **string** sin parsear, a diferencia del primer argumento de `onSuccess`, que sí viene como objeto. Hay que hacer `JSON.parse` dentro de un try/catch para leer el cuerpo del error.

Fuente: `node_modules/@inertiajs/vue3/dist/index.js`, función `submit`.
