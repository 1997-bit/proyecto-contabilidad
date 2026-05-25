CREATE DATABASE IF NOT EXISTS planillas_prospera
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE planillas_prospera;


CREATE TABLE usuarios (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  nombre          VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado',
  email           VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado',
  email_hash      CHAR(64)         NOT NULL COMMENT 'SHA-256 hex — login lookup',
  password_hash   VARCHAR(255)     NOT NULL COMMENT 'Argon2id',
  rol             ENUM('admin','contador','visor') NOT NULL DEFAULT 'visor',
  login_attempts  TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos consecutivos',
  locked_until    DATETIME         NULL     COMMENT 'NULL = cuenta activa; fecha = bloqueada hasta',
  last_login      DATETIME         NULL     COMMENT 'Ultimo login exitoso',
  activo          TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '1=activo 0=desactivado',
  created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_email_hash (email_hash),
  INDEX      idx_activo    (activo)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios con acceso al sistema';

CREATE TABLE colaboradores (
  id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  nombre_completo  VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado',
  nombre_hash      CHAR(64)         NULL     COMMENT 'SHA-256 hex para busqueda',
  cedula           VARBINARY(255)   NOT NULL COMMENT 'AES-256-GCM cifrado',
  cedula_hash      CHAR(64)         NULL     COMMENT 'SHA-256 hex para busqueda',
  estado_civil     ENUM('soltero','casado','unido') NOT NULL,
  cargo            VARCHAR(100)     NOT NULL,
  salario_base     DECIMAL(10,2)    NOT NULL COMMENT 'Salario mensual bruto',
  tipo_salario     ENUM('fijo','comisiones','dietas','prima_produccion') NOT NULL,
  anio_inicio      YEAR             NOT NULL COMMENT 'Para calculo de antiguedad',
  activo           TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '1=activo 0=inactivo',
  created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_anio_inicio   CHECK (anio_inicio >= 2000 AND anio_inicio <= YEAR(CURDATE()),

  PRIMARY KEY (id),
  UNIQUE KEY uq_cedula_hash  (cedula_hash),
  INDEX      idx_nombre_hash (nombre_hash),
  INDEX      idx_activo      (activo),
  INDEX      idx_created_at  (created_at)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Datos personales y laborales de cada colaborador';

CREATE TABLE planillas (
  id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  periodo          ENUM('1ra_quincena','2da_quincena') NOT NULL,
  mes              TINYINT UNSIGNED NOT NULL COMMENT '1-12',
  anio             YEAR             NOT NULL,
  fecha_creacion   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado           ENUM('borrador','aprobada','pagada','anulada') NOT NULL DEFAULT 'borrador',
  motivo_anulacion VARCHAR(500)     NULL COMMENT 'Obligatorio cuando estado = anulada',
  anulada_por      INT UNSIGNED     NULL COMMENT 'FK a usuarios.id',
  fecha_anulacion  DATETIME         NULL,
  created_by       INT UNSIGNED     NULL COMMENT 'FK a usuarios.id',
  updated_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT chk_mes CHECK (mes BETWEEN 1 AND 12),

  PRIMARY KEY (id),
  UNIQUE KEY uq_periodo_mes_anio (periodo, mes, anio),
  INDEX      idx_estado          (estado),
  INDEX      idx_created_by      (created_by),

  CONSTRAINT fk_planilla_creada_por
    FOREIGN KEY (created_by)  REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_planilla_anulada_por
    FOREIGN KEY (anulada_por) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Encabezado de cada periodo — ciclo: borrador > aprobada > pagada | anulada';

CREATE TABLE detalle_planilla (
  id                    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_planilla           INT UNSIGNED  NOT NULL,
  id_colaborador        INT UNSIGNED  NOT NULL,

  salario_base_quincena DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'salario_base / 2',
  horas_extras          DECIMAL(5,2)  NOT NULL DEFAULT 0.00 COMMENT 'Cantidad de horas extra',
  monto_horas_extras    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  bonificacion          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  comision              DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '2% ventas — solo comisiones',
  dietas                DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto bruto recibido',
  prima_produccion      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto bruto',
  salario_bruto         DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total ingresos gravables',

  desc_seguro_social    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '9.75% del bruto',
  desc_seguro_educativo DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '1.25% del bruto',
  desc_isr              DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Retencion ISR quincenal',
  otros_descuentos      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Muebleria, adelantos, ahorros',
  total_descuentos      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  salario_neto          DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'bruto - total_descuentos',

  created_by            INT UNSIGNED  NULL COMMENT 'FK a usuarios.id',
  created_at            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT chk_horas_extras     CHECK (horas_extras     >= 0),
  CONSTRAINT chk_salario_bruto    CHECK (salario_bruto    >= 0),
  CONSTRAINT chk_salario_neto     CHECK (salario_neto     >= 0),
  CONSTRAINT chk_otros_descuentos CHECK (otros_descuentos >= 0),

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
  COMMENT='Linea de planilla por colaborador — campos calculados desnormalizados para auditoria historica';

CREATE TABLE audit_log (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tabla         VARCHAR(50)     NOT NULL COMMENT 'Tabla afectada',
  registro_id   INT UNSIGNED    NOT NULL COMMENT 'PK del registro afectado',
  accion        ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','LOGIN_FAIL','LOCK') NOT NULL,
  usuario_id    INT UNSIGNED    NULL COMMENT 'NULL en acciones pre-autenticacion',
  ip_address    VARCHAR(45)     NULL COMMENT 'IPv4 o IPv6',
  datos_antes   JSON            NULL COMMENT 'Estado anterior (UPDATE/DELETE)',
  datos_despues JSON            NULL COMMENT 'Estado nuevo (INSERT/UPDATE)',
  descripcion   VARCHAR(500)    NULL COMMENT 'Detalle libre — ej: motivo de anulacion',
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
  COMMENT='Registro inmutable de todas las acciones — ISO 27001 A.12.4';
