<?php
define('BASE_URL', 'http://localhost/proyecto-contabilidad/public');
class Config
{
  public static function cargarEnv(string $path): void
  {
    if (!file_exists($path)) {
      throw new RuntimeException(".env no encontrado en: $path");
    }

    $lineas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
      $linea = trim($linea);
      if ($linea === '' || str_starts_with($linea, '#')) continue;

      [$clave, $valor] = explode('=', $linea, 2);
      $_ENV[trim($clave)] = trim($valor);
    }
  }

  public static function get(string $clave, mixed $default = null): mixed
  {
    return $_ENV[$clave] ?? $default;
  }

  public const SALARIO_MINIMO_MES  = 655.15;
  public const CSS_EMPLEADO = 0.0975;
  public const SEG_EDUCATIVO = 0.0125;
  public const COMISION_VENTAS = 0.02;
  public const DIETAS_EXENCION = 0.25;
  public const PRIMA_EXENCION = 0.50;
  public const HORAS_SEMANALES = 45;
  public const SEMANAS_MES = 4.333;
  public const SALARIO_MINIMO_HORA = 3.36;
  public const RECARGO_DIURNO = 1.25;
  public const RECARGO_NOCTURNO = 1.50;
  public const RECARGO_DOMINICAL = 1.75;
  public const MAX_OTROS_DESC_PCT = 0.35;
  public const MIN_NETO_PCT = 0.50;
  // CSS patrono — períodos vigentes
  public const CSS_PATRONO = 0.1325; // 2025-2026
  public const SEG_EDUCATIVO_PAT = 0.015;

  // ISR tramos anuales
  public const ISR_TRAMO1_TOPE = 11000.00;
  public const ISR_TRAMO2_TOPE = 50000.00;
  public const ISR_TRAMO2_TASA = 0.15;
  public const ISR_TRAMO3_BASE = 5850.00;
  public const ISR_TRAMO3_TASA = 0.25;
  public const ISR_DEDUCCION_E = 800.00;  // casado/unido

  // Prestaciones
  public const DECIMO_PCT = 0.083333; // 1/12
  public const VACACIONES_PCT = 0.090909; // 1/11
  public const INDEMNIZACION_SEM = 3.4;
}
