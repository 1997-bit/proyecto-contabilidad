--  planillas_prospera - schema v2
--
--  Cambios vs v1:
--    · detalle_planilla: columnas fijas por tipo de ingreso
--      eliminadas -> reemplazadas por totales + detalle_ingresos
--    · nueva tabla detalle_ingresos (ingresos genéricos por tipo)
--    · colaboradores: elimina tipo_salario / grupo (modelo viejo
  --      de grupos excluyentes); ingresos definidos por período
--    · detalle_planilla: agrega otros_ingresos_sin_descuento,
--      valor_hora, pct_descuentos, alerta_desc_excede
--    · planillas: agrega empresa_id (una planilla por empresa)
--    · colaboradores: agrega empresa_id
--
--  Normativa aplicada:
--    · Art. 91 Ley CSS / Decreto Gabinete 68-1970:
--        comisiones 100% gravable
--        dietas: exento hasta 25% salario mensual
--        prima producción: exento hasta 50% salario mensual
--    · Art. 161 Código de Trabajo: alerta si descuentos > tope
--    · ISR: DGI Panamá, tarifa progresiva Art. 10, Ley 8/2010
--    · Salario mínimo: B/.654.55 (Decreto 001-2024 Región 1)
--    · Seguridad: AES-256-GCM en PII, Argon2id en passwords,
--      SHA-256 hashes para lookup sin exponer datos cifrados
--    · Auditoría: ISO 27001 A.12.4 (audit_log)

CREATE DATABASE IF NOT EXISTS planillas_prospera
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE planillas_prospera;

-- ------------------------------------------------------------
--  usuarios
--
--  Roles:
--    visor    -> solo lectura
--    contador -> crear/editar planillas
--    admin    -> aprobar, pagar, anular, gestión de usuarios
--
--  Seguridad:
--    · nombre/email cifrados AES-256-GCM (solo se descifran en capa de app)
--    · email_hash SHA-256 para lookup en login sin descifrar
--    · password_hash Argon2id
--    · login_attempts + locked_until para bloqueo por fuerza bruta
-- ------------------------------------------------------------
CREATE TABLE usuarios (
  id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  nombre         VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado - descifrar en app',
  email          VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado - descifrar en app',
  email_hash     CHAR(64)         NOT NULL COMMENT 'SHA-256 hex - único uso: lookup en login',
  password_hash  VARCHAR(255)     NOT NULL COMMENT 'Argon2id',
  rol            ENUM('admin','contador','visor') NOT NULL DEFAULT 'visor'
  COMMENT 'visor=solo lectura · contador=crear/editar · admin=aprobar/pagar/anular/usuarios',
  login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0    COMMENT 'Intentos fallidos consecutivos; reset a 0 en login exitoso',
  locked_until   DATETIME         NULL                  COMMENT 'NULL = cuenta activa; fecha futura = bloqueada hasta esa fecha',
  last_login     DATETIME         NULL                  COMMENT 'Último login exitoso (UTC)',
  activo         TINYINT(1)       NOT NULL DEFAULT 1    COMMENT '1 = activo · 0 = desactivado (soft delete)',
  created_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_email_hash (email_hash),
  INDEX      idx_activo    (activo)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios con acceso al sistema. PII cifrada en reposo.';

-- ------------------------------------------------------------
--  empresas
--
--  Una fila por empresa cliente. Los parámetros de nómina
--  (jornada, región, riesgo CSS) se heredan a los colaboradores
--  y se usan en los cálculos de PlanillaService.
-- ------------------------------------------------------------
CREATE TABLE empresas (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nombre          VARCHAR(150)  NOT NULL,
  ruc             VARCHAR(50)   NULL                   COMMENT 'RUC Panamá - NULL si no aplica',
  region          TINYINT(1)    NOT NULL DEFAULT 1     COMMENT '1 = Región 1 · 2 = Región 2 (Decreto 001-2024)',
  horas_semanales DECIMAL(5,2)  NOT NULL DEFAULT 48.00 COMMENT 'Jornada semanal pactada - default 48h (Código de Trabajo)',
  semanas_mes     DECIMAL(6,4)  NOT NULL DEFAULT 4.3333 COMMENT 'Factor semanas/mes para cálculo de valor-hora',
  clase_riesgo    TINYINT(1)    NOT NULL DEFAULT 1     COMMENT 'Clase CSS riesgo profesional I-V (pendiente implementar)',
  grado_riesgo    TINYINT       NOT NULL DEFAULT 8     COMMENT 'Grado dentro de la clase 6-100 (pendiente implementar)',
  activo          TINYINT(1)    NOT NULL DEFAULT 1     COMMENT '1 = activa · 0 = inactiva',
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_activo (activo)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Una fila por empresa cliente. Parámetros de nómina heredados a colaboradores.';

-- ------------------------------------------------------------
--  colaboradores
--
--  · nombre_completo y cedula cifrados AES-256-GCM
--  · *_hash para búsqueda sin descifrar
--  · tipo_salario eliminado (v1): en v2 los ingresos son
--    dinámicos por período -> tabla detalle_ingresos
--  · salario_base = salario mensual bruto (base para quincena,
  --    valor-hora, umbrales de exención CSS)
--  · anio_inicio para cálculo de antigüedad (décimo, vacaciones)
-- ------------------------------------------------------------
CREATE TABLE colaboradores (
  id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  empresa_id      INT UNSIGNED   NOT NULL                COMMENT 'Empresa a la que pertenece',
  nombre_completo VARBINARY(255) NOT NULL                COMMENT 'AES-256-GCM cifrado',
  nombre_hash     CHAR(64)       NULL                    COMMENT 'SHA-256 hex - búsqueda por nombre',
  cedula          VARBINARY(255) NOT NULL                COMMENT 'AES-256-GCM cifrado',
  cedula_hash     CHAR(64)       NULL                    COMMENT 'SHA-256 hex - búsqueda + unicidad',
  estado_civil    ENUM('soltero','casado','unido') NOT NULL DEFAULT 'soltero'
  COMMENT 'Afecta deducción ISR (Ley 8/2010)',
  cargo           VARCHAR(100)   NOT NULL,
  salario_base    DECIMAL(10,2)  NOT NULL                COMMENT 'Salario mensual bruto',
  anio_inicio     YEAR           NOT NULL                COMMENT 'Año de ingreso - base para cálculo de antigüedad',
  activo          TINYINT(1)     NOT NULL DEFAULT 1      COMMENT '1 = activo · 0 = inactivo (soft delete)',
  created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_anio_inicio  CHECK (anio_inicio BETWEEN 1900 AND 2100),

  PRIMARY KEY (id),
  UNIQUE KEY uq_cedula_hash  (cedula_hash),
  INDEX      idx_empresa_id  (empresa_id),
  INDEX      idx_nombre_hash (nombre_hash),
  INDEX      idx_activo      (activo),
  INDEX      idx_created_at  (created_at),

  CONSTRAINT fk_colaborador_empresa
  FOREIGN KEY (empresa_id) REFERENCES empresas(id)
  ON DELETE RESTRICT ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Datos personales y laborales. PII cifrada en reposo. Sin tipo_salario (v2: ingresos dinámicos por período).';

-- ------------------------------------------------------------
--  planillas  (encabezado de período)
--
--  Una planilla = una empresa + quincena + mes + año.
--  Ciclo de vida: borrador -> aprobada -> pagada
--                     └──────────────────┘→ anulada (solo admin)
--
--  anulada requiere motivo_anulacion + anulada_por.
-- ------------------------------------------------------------
CREATE TABLE planillas (
  id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  empresa_id       INT UNSIGNED     NOT NULL                COMMENT 'Empresa dueña de esta planilla',
  periodo          ENUM('1ra_quincena','2da_quincena') NOT NULL,
  mes              TINYINT UNSIGNED NOT NULL                COMMENT '1-12',
  anio             YEAR             NOT NULL,
  estado           ENUM('borrador','aprobada','pagada','anulada') NOT NULL DEFAULT 'borrador'
  COMMENT 'Ciclo: borrador→aprobada→pagada | anulada (solo admin)',
  motivo_anulacion VARCHAR(500)     NULL                    COMMENT 'Obligatorio cuando estado = anulada',
  anulada_por      INT UNSIGNED     NULL                    COMMENT 'FK usuarios.id - quién anuló',
  fecha_anulacion  DATETIME         NULL,
  created_by       INT UNSIGNED     NULL                    COMMENT 'FK usuarios.id - quién creó',
  created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_mes CHECK (mes BETWEEN 1 AND 12),

  PRIMARY KEY (id),
  UNIQUE KEY uq_empresa_periodo_mes_anio (empresa_id, periodo, mes, anio)
  COMMENT 'Una planilla por empresa por quincena',
  INDEX      idx_estado      (estado),
  INDEX      idx_empresa_id  (empresa_id),
  INDEX      idx_created_by  (created_by),

  CONSTRAINT fk_planilla_empresa
  FOREIGN KEY (empresa_id)  REFERENCES empresas(id)  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_planilla_creada_por
  FOREIGN KEY (created_by)  REFERENCES usuarios(id)  ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_planilla_anulada_por
  FOREIGN KEY (anulada_por) REFERENCES usuarios(id)  ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Encabezado de período de nómina. Una por empresa por quincena.';

-- ------------------------------------------------------------
--  detalle_planilla  (una fila por colaborador por período)
--
--  Todos los montos se persisten desnormalizados para preservar
--  la historia de cálculos aunque cambien tasas o salarios.
--  La validación de consistencia ocurre en PlanillaService.
--
--  Ingresos:
--    · salario_base_quincena = salario_base / 2
--    · otros_ingresos        = ingresos gravables del período
--                              (desglose en detalle_ingresos)
--    · otros_ingresos_sin_descuento = ingresos exentos CSS
--                              (no entran al bruto; suman al neto)
--    · salario_bruto         = salario_base_quincena + otros_ingresos
--
--  Deducciones (sobre base CSS = bruto + excedente_css):
--    · desc_seguro_social    = 9.75%  (Config::CSS_EMPLEADO)
--    · desc_seguro_educativo = 1.25%  (Config::SEG_EDUCATIVO)
--    · desc_isr              = retención quincenal DGI
--    · otros_descuentos      = mueblería, adelantos, ahorros
--
--  Neto = (bruto - total_descuentos) + otros_ingresos_sin_descuento
--
--  Alerta Art. 161 Código de Trabajo:
--    · pct_descuentos > MAX_OTROS_DESC_PCT -> alerta_desc_excede = 1
-- ------------------------------------------------------------
CREATE TABLE detalle_planilla (
  id                           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_planilla                  INT UNSIGNED  NOT NULL,
  id_colaborador               INT UNSIGNED  NOT NULL,

  -- snapshot de referencia
  salario_base_quincena        DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'salario_base / 2 al momento del cálculo',
  valor_hora                   DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'salario_base / (horas_sem * semanas_mes)',

  -- totales de ingresos (desglose en detalle_ingresos)
  otros_ingresos               DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'Suma gravable de todos los ingresos del período',
  otros_ingresos_sin_descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'Suma exenta (dietas/prima bajo umbral) - suma al neto, no al bruto',
  salario_bruto                DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'salario_base_quincena + otros_ingresos',

  -- deducciones empleado
  desc_seguro_social           DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT '9.75% sobre base CSS (Art. 91 Ley CSS)',
  desc_seguro_educativo        DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT '1.25% sobre base CSS',
  desc_isr                     DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'Retención ISR quincenal - DGI Panamá tarifa progresiva',
  otros_descuentos             DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'Mueblería, adelantos, ahorros, etc.',
  total_descuentos             DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT 'css + seg_edu + isr + otros_descuentos',
  salario_neto                 DECIMAL(10,2) NOT NULL DEFAULT 0.00   COMMENT '(bruto - total_descuentos) + otros_ingresos_sin_descuento',

  -- alerta Art. 161 Código de Trabajo
  pct_descuentos               DECIMAL(5,2)  NOT NULL DEFAULT 0.00   COMMENT '% de total_descuentos sobre salario_bruto',
  alerta_desc_excede           TINYINT(1)    NOT NULL DEFAULT 0      COMMENT '1 si pct_descuentos supera el tope legal (Art. 161)',

  created_by                   INT UNSIGNED  NULL                    COMMENT 'FK usuarios.id',
  created_at                   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT chk_salario_bruto    CHECK (salario_bruto                >= 0),
  CONSTRAINT chk_salario_neto     CHECK (salario_neto                 >= 0),
  CONSTRAINT chk_otros_descuentos CHECK (otros_descuentos             >= 0),
  CONSTRAINT chk_otros_ingresos   CHECK (otros_ingresos               >= 0),
  CONSTRAINT chk_sin_descuento    CHECK (otros_ingresos_sin_descuento >= 0),

  PRIMARY KEY (id),
  UNIQUE KEY uq_planilla_colaborador (id_planilla, id_colaborador),
  INDEX      idx_colaborador         (id_colaborador),
  INDEX      idx_created_by          (created_by),

  CONSTRAINT fk_detalle_planilla
  FOREIGN KEY (id_planilla)    REFERENCES planillas(id)     ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_colaborador
  FOREIGN KEY (id_colaborador) REFERENCES colaboradores(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_created_by
  FOREIGN KEY (created_by)     REFERENCES usuarios(id)      ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Línea de planilla por colaborador. Montos desnormalizados para auditoría histórica.';

-- ------------------------------------------------------------
--  detalle_ingresos  (desglose de ingresos por tipo)
--
--  Una fila por tipo de ingreso por línea de planilla.
--  Un colaborador puede tener múltiples tipos en el mismo período.
--
--  Tipos implementados en PlanillaService:
--    horas_extra  -> gravable 100%, sin exención CSS
--                   campo 'horas' requerido
--    comision     -> gravable 100% (Art. 91 numeral 1)
--    dietas       -> exento hasta 25% salario mensual;
--                   excedente gravable (Art. 91 numeral 4)
--    prima        -> exento hasta 50% salario mensual;
--                   excedente gravable (Art. 91 numeral 5)
--
--  gravable      = parte que entró en salario_bruto
--  sin_descuento = parte exenta que sumó directo al neto
--
--  ON DELETE CASCADE: si se borra detalle_planilla se borra
--  el desglose asociado (son el mismo registro lógico).
-- ------------------------------------------------------------
CREATE TABLE detalle_ingresos (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_detalle    INT UNSIGNED  NOT NULL  COMMENT 'FK detalle_planilla.id',
  tipo          VARCHAR(50)   NOT NULL  COMMENT 'horas_extra | comision | dietas | prima | ...',
  monto         DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto total recibido por el colaborador',
  gravable      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Parte que entra al bruto y a la base CSS',
  sin_descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Parte exenta CSS - suma al neto sin pasar por deducciones',
  horas         DECIMAL(5,2)  NULL                  COMMENT 'Solo para tipo = horas_extra; NULL en el resto',

  CONSTRAINT chk_ing_monto    CHECK (monto         >= 0),
  CONSTRAINT chk_ing_gravable CHECK (gravable       >= 0),
  CONSTRAINT chk_ing_sin_desc CHECK (sin_descuento  >= 0),

  PRIMARY KEY (id),
  INDEX idx_detalle (id_detalle),
  INDEX idx_tipo    (tipo),

  CONSTRAINT fk_ing_detalle
  FOREIGN KEY (id_detalle) REFERENCES detalle_planilla(id)
  ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Desglose de ingresos por tipo por línea de planilla. Art. 91 Ley CSS.';

-- ------------------------------------------------------------
--  audit_log
--
--  Registro inmutable de todas las acciones del sistema.
--  Cumplimiento ISO 27001 A.12.4.
--
--  · usuario_id NULL en acciones pre-autenticación (LOGIN_FAIL)
--  · No tiene UPDATE ni DELETE - append-only por diseño
-- ------------------------------------------------------------
CREATE TABLE audit_log (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tabla         VARCHAR(50)     NOT NULL  COMMENT 'Tabla afectada por la acción',
  registro_id   INT UNSIGNED    NOT NULL  COMMENT 'PK del registro afectado',
  accion        ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','LOGIN_FAIL','LOCK') NOT NULL,
  usuario_id    INT UNSIGNED    NULL      COMMENT 'FK usuarios.id - NULL en acciones pre-autenticación',
  descripcion   VARCHAR(500)    NULL      COMMENT 'Detalle libre - ej: motivo de anulación, contexto',
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_tabla_registro (tabla, registro_id),
  INDEX idx_usuario_id     (usuario_id),
  INDEX idx_accion         (accion),
  INDEX idx_created_at     (created_at),

  CONSTRAINT fk_audit_usuario
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci
COMMENT='Registro inmutable append-only. ISO 27001 A.12.4. No borrar ni actualizar filas.';
