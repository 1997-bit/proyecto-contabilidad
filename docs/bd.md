# Base de Datos

---

## Modalidades de pago

| Modalidad          | Descripción                                    |
| ------------------ | ---------------------------------------------- |
| `fijo`             | Salario mensual fijo con posibles horas extras |
| `comisiones`       | Salario base + 2% sobre ventas del período     |
| `dietas`           | Salario base + viáticos por representación     |
| `prima_produccion` | Salario base + prima según producción          |

---

## Tablas

### `usuarios`

| Campo            | Tipo                 | Notas                                      |
| ---------------- | -------------------- | ------------------------------------------ |
| `id`             | `INT UNSIGNED` PK AI |                                            |
| `nombre`         | `VARBINARY(255)`     | AES-256-GCM cifrado                        |
| `email`          | `VARBINARY(255)`     | AES-256-GCM cifrado                        |
| `email_hash`     | `CHAR(64)`           | SHA-256 hex - lookup en login              |
| `password_hash`  | `VARCHAR(255)`       | Argon2id                                   |
| `rol`            | `ENUM`               | `'admin','contador','visor'`               |
| `login_attempts` | `TINYINT UNSIGNED`   | Intentos fallidos consecutivos · default 0 |
| `locked_until`   | `DATETIME`           | NULL = activa; fecha = bloqueada hasta     |
| `last_login`     | `DATETIME`           | Último login exitoso                       |
| `activo`         | `TINYINT(1)`         | 1=activo 0=desactivado · default 1         |
| `created_at`     | `DATETIME`           | Default `CURRENT_TIMESTAMP`                |
| `updated_at`     | `DATETIME`           | Auto-actualiza en cada UPDATE              |

**Índices:** `PRIMARY (id)` · `UNIQUE uq_email_hash (email_hash)` · `INDEX idx_activo (activo)`

**Roles:**

| Rol        | Ver | Crear/Editar | Aprobar/Pagar | Gestión usuarios |
| ---------- | --- | ------------ | ------------- | ---------------- |
| `visor`    | ✓   | x            | x             | x                |
| `contador` | ✓   | ✓            | x             | x                |
| `admin`    | ✓   | ✓            | ✓             | ✓                |

---

### `colaboradores`

| Campo             | Tipo                 | Notas                                             |
| ----------------- | -------------------- | ------------------------------------------------- |
| `id`              | `INT UNSIGNED` PK AI |                                                   |
| `nombre_completo` | `VARBINARY(255)`     | AES-256-GCM cifrado                               |
| `nombre_hash`     | `CHAR(64)`           | SHA-256 hex - búsqueda                            |
| `cedula`          | `VARBINARY(255)`     | AES-256-GCM cifrado                               |
| `cedula_hash`     | `CHAR(64)`           | SHA-256 hex - búsqueda                            |
| `estado_civil`    | `ENUM`               | `'soltero','casado','unido'`                      |
| `cargo`           | `VARCHAR(100)`       |                                                   |
| `salario_base`    | `DECIMAL(10,2)`      | Mínimo legal: $654.55 (Decreto 001-2024)          |
| `tipo_salario`    | `ENUM`               | `'fijo','comisiones','dietas','prima_produccion'` |
| `anio_inicio`     | `YEAR`               | Para cálculo de antigüedad                        |
| `activo`          | `TINYINT(1)`         | 1=activo 0=inactivo · default 1                   |
| `created_at`      | `DATETIME`           |                                                   |
| `updated_at`      | `DATETIME`           |                                                   |

**Índices:** `PRIMARY` · `UNIQUE uq_cedula_hash` · `INDEX idx_nombre_hash` · `INDEX idx_activo` · `INDEX idx_created_at`

**CHECK constraints**

- `chk_salario_base` ->`salario_base >= 654.55`
- `chk_anio_inicio` ->`anio_inicio BETWEEN 1900 AND 2026` _(actualizar el techo cada año)_

---

### `planillas`

| Campo              | Tipo                 | Notas                                                           |
| ------------------ | -------------------- | --------------------------------------------------------------- |
| `id`               | `INT UNSIGNED` PK AI |                                                                 |
| `periodo`          | `ENUM`               | `'1ra_quincena','2da_quincena'`                                 |
| `mes`              | `TINYINT UNSIGNED`   | 1–12                                                            |
| `anio`             | `YEAR`               |                                                                 |
| `fecha_creacion`   | `DATETIME`           | Default `CURRENT_TIMESTAMP`                                     |
| `estado`           | `ENUM`               | `'borrador','aprobada','pagada','anulada'` **[nuevo: anulada]** |
| `motivo_anulacion` | `VARCHAR(500)`       | Obligatorio si estado = anulada                                 |
| `anulada_por`      | `INT UNSIGNED` FK    | ->`usuarios.id`                                                 |
| `fecha_anulacion`  | `DATETIME`           |                                                                 |
| `created_by`       | `INT UNSIGNED` FK    | ->`usuarios.id`                                                 |
| `updated_at`       | `DATETIME`           |                                                                 |

**Índices:** `PRIMARY` · `UNIQUE uq_periodo_mes_anio (periodo, mes, anio)` · `INDEX idx_estado` · `INDEX idx_created_by`

**CHECK:** `chk_mes` ->`mes BETWEEN 1 AND 12`

**Ciclo de vida:**

```
borrador --> aprobada --> pagada
    |              |
    +--------------+--> anulada  (solo admin; requiere motivo_anulacion)
```

---

### `detalle_planilla`

#### Ingresos

| Campo                   | Tipo                 | Notas                                 |
| ----------------------- | -------------------- | ------------------------------------- |
| `id`                    | `INT UNSIGNED` PK AI |                                       |
| `id_planilla`           | `INT UNSIGNED` FK    | ->`planillas.id`                      |
| `id_colaborador`        | `INT UNSIGNED` FK    | ->`colaboradores.id`                  |
| `salario_base_quincena` | `DECIMAL(10,2)`      | `salario_base / 2`                    |
| `horas_extras`          | `DECIMAL(5,2)`       | Cantidad de horas                     |
| `monto_horas_extras`    | `DECIMAL(10,2)`      |                                       |
| `bonificacion`          | `DECIMAL(10,2)`      | 10% salario mensual en junio          |
| `comision`              | `DECIMAL(10,2)`      | 2% ventas, solo `comisiones`          |
| `dietas`                | `DECIMAL(10,2)`      | Monto bruto, solo `dietas`            |
| `prima_produccion`      | `DECIMAL(10,2)`      | Monto bruto, solo `prima_produccion`  |
| `salario_bruto`         | `DECIMAL(10,2)`      | Total ingresos gravables              |

#### Deducciones

| Campo                   | Tipo            | Notas                              |
| ----------------------- | --------------- | ---------------------------------- |
| `desc_seguro_social`    | `DECIMAL(10,2)` | 9.75% del bruto                    |
| `desc_seguro_educativo` | `DECIMAL(10,2)` | 1.25% del bruto                    |
| `desc_isr`              | `DECIMAL(10,2)` | Retención ISR quincenal            |
| `otros_descuentos`      | `DECIMAL(10,2)` | Mueblería, adelantos, ahorros      |
| `total_descuentos`      | `DECIMAL(10,2)` |                                    |
| `salario_neto`          | `DECIMAL(10,2)` | `salario_bruto - total_descuentos` |

#### Auditoría

| Campo        | Tipo              | Notas           |
| ------------ | ----------------- | --------------- |
| `created_by` | `INT UNSIGNED` FK | ->`usuarios.id` |
| `created_at` | `DATETIME`        |                 |

**Índices:** `PRIMARY` · `UNIQUE uq_planilla_colaborador (id_planilla, id_colaborador)` · `INDEX idx_colaborador` · `INDEX idx_created_by`

**CHECK:** `chk_horas_extras` · `chk_salario_bruto` · `chk_salario_neto` · `chk_otros_descuentos` ->todos `>= 0`

> Los campos calculados (`salario_bruto`, `total_descuentos`, `salario_neto`) se persisten desnormalizados para preservar la historia de cálculos pasados. La validación de consistencia ocurre en la capa de servicio.

---

### `audit_log` _(tabla nueva)_

Registra todas las acciones del sistema para cumplimiento ISO 27001 A.12.4.

| Campo           | Tipo                    | Notas                                                             |
| --------------- | ----------------------- | ----------------------------------------------------------------- |
| `id`            | `BIGINT UNSIGNED` PK AI |                                                                   |
| `tabla`         | `VARCHAR(50)`           | Tabla afectada                                                    |
| `registro_id`   | `INT UNSIGNED`          | PK del registro afectado                                          |
| `accion`        | `ENUM`                  | `'INSERT','UPDATE','DELETE','LOGIN','LOGOUT','LOGIN_FAIL','LOCK'` |
| `usuario_id`    | `INT UNSIGNED` FK       | ->`usuarios.id` · NULL en acciones pre-autenticación              |
| `ip_address`    | `VARCHAR(45)`           | IPv4 o IPv6                                                       |
| `datos_antes`   | `JSON`                  | Estado anterior (UPDATE/DELETE)                                   |
| `datos_despues` | `JSON`                  | Estado nuevo (INSERT/UPDATE)                                      |
| `descripcion`   | `VARCHAR(500)`          | Detalle libre, ej. motivo de anulación                            |
| `created_at`    | `DATETIME`              | Default `CURRENT_TIMESTAMP`                                       |

**Índices:** `PRIMARY` · `INDEX idx_tabla_registro (tabla, registro_id)` · `INDEX idx_usuario_id` · `INDEX idx_accion` · `INDEX idx_created_at`

---

```Mermaid

erDiagram

    usuarios {
        INT_UNSIGNED id PK
        VARBINARY_255 nombre "AES-256-GCM"
        VARBINARY_255 email "AES-256-GCM"
        CHAR_64 email_hash "SHA-256 lookup"
        VARCHAR_255 password_hash "Argon2id"
        ENUM rol "admin|contador|visor"
        TINYINT_UNSIGNED login_attempts "nuevo"
        DATETIME locked_until "nuevo"
        DATETIME last_login "nuevo"
        TINYINT activo "nuevo"
        DATETIME created_at "nuevo"
        DATETIME updated_at "nuevo"
    }

    colaboradores {
        INT_UNSIGNED id PK
        VARBINARY_255 nombre_completo "AES-256-GCM"
        CHAR_64 nombre_hash "SHA-256 busqueda"
        VARBINARY_255 cedula "AES-256-GCM"
        CHAR_64 cedula_hash "SHA-256 busqueda"
        ENUM estado_civil "soltero|casado|unido"
        VARCHAR_100 cargo
        DECIMAL_10_2 salario_base "min 654.55"
        ENUM tipo_salario "fijo|comisiones|dietas|prima_produccion"
        YEAR anio_inicio
        TINYINT activo
        DATETIME created_at "nuevo"
        DATETIME updated_at "nuevo"
    }

    planillas {
        INT_UNSIGNED id PK
        ENUM periodo "1ra_quincena|2da_quincena"
        TINYINT mes "1-12"
        YEAR anio
        DATETIME fecha_creacion
        ENUM estado "borrador|aprobada|pagada|anulada"
        VARCHAR_500 motivo_anulacion "nuevo"
        INT_UNSIGNED anulada_por FK "nuevo"
        DATETIME fecha_anulacion "nuevo"
        INT_UNSIGNED created_by FK "nuevo"
        DATETIME updated_at "nuevo"
    }

    detalle_planilla {
        INT_UNSIGNED id PK
        INT_UNSIGNED id_planilla FK
        INT_UNSIGNED id_colaborador FK
        DECIMAL_10_2 salario_base_quincena
        DECIMAL_5_2 horas_extras
        DECIMAL_10_2 monto_horas_extras
        DECIMAL_10_2 bonificacion
        DECIMAL_10_2 comision
        DECIMAL_10_2 dietas
        DECIMAL_10_2 prima_produccion
        DECIMAL_10_2 salario_bruto
        DECIMAL_10_2 desc_seguro_social
        DECIMAL_10_2 desc_seguro_educativo
        DECIMAL_10_2 desc_isr
        DECIMAL_10_2 otros_descuentos
        DECIMAL_10_2 total_descuentos
        DECIMAL_10_2 salario_neto
        INT_UNSIGNED created_by FK "nuevo"
        DATETIME created_at "nuevo"
    }

    audit_log {
        BIGINT_UNSIGNED id PK
        VARCHAR_50 tabla
        INT_UNSIGNED registro_id
        ENUM accion "INSERT|UPDATE|DELETE|LOGIN|LOGOUT|LOGIN_FAIL|LOCK"
        INT_UNSIGNED usuario_id FK
        VARCHAR_45 ip_address
        JSON datos_antes
        JSON datos_despues
        VARCHAR_500 descripcion
        DATETIME created_at
    }


    colaboradores ||--o{ detalle_planilla : "posee"
    planillas ||--o{ detalle_planilla : "contiene"
    usuarios ||--o{ detalle_planilla : "created_by"
    usuarios ||--o{ planillas : "created_by"
    usuarios ||--o{ planillas : "anulada_por"
    usuarios ||--o{ audit_log : "registra"
```
