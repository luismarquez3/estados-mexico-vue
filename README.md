# PRUEBA TÉCNICA | ESTADOS INEGI

El siguiente proyecto consume el servicio del INEGI para consultar los estados de la República Mexicana y llenar una base de datos con ellos. En la vista principal se listan los estados en una tabla y, al dar clic en uno, se abre un modal con sus municipios consultados en vivo a la API del INEGI (no se guardan en base de datos, solo se cachean).

Ambas tablas cuentan con búsqueda, ordenamiento y paginación del lado del cliente, y la población total se muestra con separadores de miles (`798447` → `798,447`).

## STACK USADO

**Backend**

- PHP 8.3
- Laravel 13
- MySQL
- Inertia 3 (adaptador de Laravel)
- Pest 4 · Pint · Larastan (PHPStan)

**Frontend**

- Vue 3.5 con TypeScript
- Inertia 3 (`@inertiajs/vue3`)
- Tailwind CSS 4
- Vite 8
- TanStack Table 9 (`@tanstack/vue-table`) para las tablas
- Laravel Wayfinder para las rutas tipadas

## REQUISITOS

- PHP >= 8.3 y Composer
- MySQL
- Node `^20.19.0 || >=22.12.0` (lo exige Vite 8) y npm

## INSTALACIÓN

La forma corta: copia el `.env`, genera la llave, migra, instala dependencias de npm y compila los assets en un solo comando.

```sh
composer setup
```

Después de eso hay que configurar las credenciales de base de datos en el `.env` y volver a migrar si la primera migración falló por falta de credenciales.

### Paso a paso

Si prefieres hacerlo manualmente:

```sh
composer i
```

```sh
npm i
```

## CONFIGURACIÓN

Copiando variables de entorno

```sh
cp .env.example .env
```

Generando llave de aplicación

```sh
php artisan key:generate
```

Configurando base de datos y credenciales

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=estados_inegi_vue
DB_USERNAME=root
DB_PASSWORD=TUCONTRASEÑA
```

### URL de la API del INEGI

Estas variables son **opcionales**: `config/inegi.php` ya trae las URL oficiales como valor por defecto, y `.env.example` no las incluye. Solo hay que declararlas si se quiere apuntar a otro origen.

```env
INEGI_API_ESTADOS=
INEGI_API_MUNICIPIOS=
```

> Si se declaran, **no pueden quedar vacías**: `env()` devuelve una cadena vacía y esa cadena gana sobre el valor por defecto de `config/inegi.php`. O se ponen con valor, o se omiten por completo.

Opcionalmente se puede ajustar cuántos segundos se cachea el catálogo de municipios de cada estado (por defecto 86400, es decir 24 horas)

```env
INEGI_CACHE_TTL_MUNICIPIOS=86400
```

El caché usa el driver por defecto del proyecto, `CACHE_STORE=database`, así que la tabla `cache` se crea con la migración del siguiente paso.

## MIGRACIÓN DE DATOS

Primero haremos una migración para crear la tabla en base de datos

```sh
php artisan migrate
```

Sincronizando y llenando datos de la API con la base de datos

```sh
php artisan estados:sync
```

El comando guarda clave, nombre y población total de cada estado. Se puede volver a ejecutar cuantas veces se quiera: los estados existentes se actualizan y los nuevos se crean.

Los municipios **no** se sincronizan: se consultan en vivo al abrir el modal y se cachean por clave de entidad.

## MODO DESARROLLO

```sh
composer dev
```

Levanta en paralelo el servidor de Laravel, la cola, los logs (Pail) y Vite.

Visitar en el navegador

http://localhost:8000

> Si un cambio del frontend no se refleja, es porque Vite no está corriendo. Usa `composer dev`, `npm run dev` o compila con `npm run build`.


## ESTRUCTURA DEL FRONTEND

```
resources/js/
├── app.ts            Punto de entrada de Inertia
├── pages/            Una página por Inertia::render()
│   └── Index.vue     Listado de estados + modal de municipios
├── components/
│   ├── DataTable.vue Tabla genérica: búsqueda, orden y paginación
│   └── Modal.vue     <dialog> nativo
├── lib/
│   ├── tabla.ts      Features de TanStack Table y formato numérico
│   └── utils.ts      Helper cn() para clases de Tailwind
├── types/            Tipos compartidos (Estado, Municipio, Auth)
├── actions/          ⚙️ Generado por Wayfinder
├── routes/           ⚙️ Generado por Wayfinder
└── wayfinder/        ⚙️ Generado por Wayfinder
```

Las tres últimas carpetas están en `.gitignore` y las regenera Wayfinder en cada `npm run dev` / `npm run build`, o a mano con `php artisan wayfinder:generate`. No hay que editarlas.

## RUTAS

| Método | URI | Nombre | Respuesta |
| --- | --- | --- | --- |
| GET | `/` | `estados.index` | Página Inertia con el listado de estados |
| GET | `/estados/{estado:cve_ent}/municipios` | `estados.municipios` | JSON con los municipios del estado |

El endpoint de municipios responde `502` con `{"mensaje": "..."}` si la consulta al INEGI falla.
