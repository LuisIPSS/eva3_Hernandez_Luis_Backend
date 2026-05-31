## Documentación Swagger

Accede a:

```
http://localhost/api/documentation
```

## Endpoints de la API

Base URL: `/api`

### Salud del servicio


| Método | Ruta      | Descripción                    |
| ------ | --------- | ------------------------------ |
| GET    | `/health` | Estado del servicio            |
| GET    | `/up`     | Health check nativo de Laravel |


### Personas (talentos)


| Método | Ruta                     | Descripción                                  |
| ------ | ------------------------ | -------------------------------------------- |
| GET    | `/personas`              | Listar talentos activos (CV ciego, paginado) |
| POST   | `/personas`              | Registrar nuevo talento                      |
| GET    | `/personas/{id}`         | Obtener talento por UUID                     |
| PUT    | `/personas/{id}`         | Actualizar talento                           |
| DELETE | `/personas/{id}`         | Desactivar talento (soft delete)             |
| PATCH  | `/personas/{id}/validar` | Validar perfil (administración)              |


### Empresas


| Método | Ruta                     | Descripción                        |
| ------ | ------------------------ | ---------------------------------- |
| GET    | `/empresas`              | Listar empresas activas (paginado) |
| POST   | `/empresas`              | Registrar nueva empresa            |
| GET    | `/empresas/{id}`         | Obtener empresa por UUID           |
| PUT    | `/empresas/{id}`         | Actualizar empresa                 |
| DELETE | `/empresas/{id}`         | Desactivar empresa (soft delete)   |
| PATCH  | `/empresas/{id}/validar` | Validar perfil (administración)    |


### Administración


| Método | Ruta                           | Descripción                    |
| ------ | ------------------------------ | ------------------------------ |
| GET    | `/admin/contactos`             | Listar solicitudes de contacto |
| POST   | `/admin/contactos`             | Crear solicitud de contacto    |
| PATCH  | `/admin/contactos/{id}/estado` | Actualizar estado del proceso  |
| GET    | `/admin/estadisticas`          | Estadísticas generales         |


---

## Formato de respuestas

### Respuesta exitosa

```json
{
  "success": true,
  "data": { }
}
```

### Respuesta de error

```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {
    "campo": ["Mensaje de validación"]
  }
}
```

### Códigos HTTP habituales


| Código | Significado                        |
| ------ | ---------------------------------- |
| 200    | Operación exitosa                  |
| 201    | Recurso creado                     |
| 404    | Recurso no encontrado              |
| 409    | Conflicto (ej. contacto duplicado) |
| 422    | Error de validación                |


---
# Mejoras de rendimiento

## Paginación

Se creo la funcion `perPage()`para mejorar el rendimiento, en casos de que exitan 5000 personas, devuelve 50 personas y no los 5000, lo cual la respuesta es mas rapida.

Los listados de **personas** y **empresas** devuelven resultados paginados.


| Parámetro  | Descripción          | Default | Máximo |
| ---------- | -------------------- | ------- | ------ |
| `page`     | Número de página     | 1       | —      |
| `per_page` | Registros por página | 15      | 50     |


**Ejemplo:**

```
GET /api/personas?validado=true&page=2&per_page=20
```

**Estructura de respuesta paginada:**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [ ],
    "per_page": 15,
    "total": 45,
    "last_page": 3,
    "next_page_url": "http://localhost/api/personas?page=2",
    "prev_page_url": null
  }
}
```

## Índices de base de datos

Se agregaron índices en las columnas más usadas en filtros y consultas, para que MySQL encuentre registros más rápido sin recorrer toda la tabla.

### Índices en `personas`

| Índice | Columnas | Optimiza |
| ------ | -------- | -------- |
| `personas_activo_validado_index` | `activo`, `validado` | `GET /personas?validado=true` |
| `personas_activo_nivel_educacional_index` | `activo`, `nivel_educacional` | `GET /personas?nivel_educacional=...` |
| `personas_validado_index` | `validado` | Conteos en `/admin/estadisticas` |

### Índices en `empresas`

| Índice | Columnas | Optimiza |
| ------ | -------- | -------- |
| `empresas_activo_tipo_empresa_index` | `activo`, `tipo_empresa` | `GET /empresas?tipo_empresa=est` |
| `empresas_activo_validado_index` | `activo`, `validado` | Listados filtrados por validación |
| `empresas_validado_index` | `validado` | Conteos en `/admin/estadisticas` |

### Índices en `contactos_solicitados`

| Índice | Columnas | Optimiza |
| ------ | -------- | -------- |
| `contactos_estado_index` | `estado` | Filtro y conteos por estado |
| `contactos_estado_created_at_index` | `estado`, `created_at` | Listado ordenado por fecha |
| `contactos_empresa_persona_estado_index` | `empresa_id`, `persona_id`, `estado` | Verificación de contacto duplicado |

## Select en CV ciego

En `GET /personas` la consulta usa `selectCvCiego()` para traer de la base de datos **solo las columnas del CV ciego**, sin cargar datos privados como `email`, `telefono` o `comprobante_residencia`.

**Modelo:** `Persona::CV_CIEGO_COLUMNS` y scope `selectCvCiego()`

**Columnas consultadas:**

`id`, `codigo_talento`, `resumen`, `nivel_educacional`, `titulo_carrera`, `anio_egreso`, `anios_experiencia`, `areas_experiencia`, `competencias`, `rango_renta`, `tipo_jornada`, `modalidad`, `cursos`, `idiomas`, `portafolio_url`, `persona_discapacidad`

**Beneficios:**

- Menos datos leídos desde MySQL
- Menor uso de memoria en PHP
- Los datos sensibles no entran al listado público, ni siquiera en memoria

La respuesta JSON al cliente **no cambia**; solo se optimiza la consulta interna.

### Integrante: Luis Hernandez
