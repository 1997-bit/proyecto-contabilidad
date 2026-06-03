<?php declare(strict_types=1);

class PlanillaService
{
  public function __construct(
    private readonly ISRService $isrService
  ) {}

  private function redondear(float $valor, int $decimales = 2): float
  {
    return round($valor, $decimales, PHP_ROUND_HALF_UP);
  }

  /**
   * Calcula una línea completa de planilla quincenal.
   *
   * @param array $colaborador  Fila de BD: salario_base, estado_civil,
   *                            horas_semanales, semanas_mes
   * @param array $extras       Inputs variables del período:
   *                            otros_descuentos,
   *                            ingresos: array de ['tipo' => string, 'monto' => float]
   *
   * Tipos de ingreso soportados:
   *   'bonificacion' -> gravable 100%, CSS completo. Art.140 CT.
   *   'comision' -> gravable 100%, CSS completo.
   *   'horas_extra' -> gravable 100%, CSS completo. Monto directo + horas como referencia.
   *   'dietas' -> exento hasta 25% salario mensual, excedente gravable + CSS.
   *   'prima' -> exento hasta 50% salario mensual, excedente gravable + CSS.
   */
  public function calcularQuincena(array $colaborador, array $extras = []): array
  {
    $salMensual = (float) $colaborador['salario_base'];
    $horasSem = (float) ($colaborador['horas_semanales'] ?? 48);
    $semanasMes = (float) ($colaborador['semanas_mes']     ?? 4.3333);
    $estadoCivil = $colaborador['estado_civil'] ?? 'soltero';

    //  Bases
    $quincena = $this->redondear($salMensual / 2, 2);
    $valorHora = $this->redondear($salMensual / ($horasSem * $semanasMes), 4);


    //  Procesar todos los ingresos del período
    $ingresos = $this->procesarIngresos(
      $extras['ingresos'] ?? [],
      $salMensual,
      $valorHora
    );

    //  Salario bruto = quincena + ingresos gravables
    $bruto = $this->redondear($quincena + $ingresos['total_gravable'], 2);

    //  Base CSS = bruto + excedente CSS de ingresos con exención parcial
    $baseCSS = $this->redondear($bruto + $ingresos['excedente_css'], 2);

    //  Deducciones empleado
    $css = $this->redondear($baseCSS * Config::CSS_EMPLEADO,  2);
    $segEdu = $this->redondear($baseCSS * Config::SEG_EDUCATIVO, 2);
    $isr = $this->isrService->calcularQuincena($bruto, $estadoCivil);

    $otrosDesc = $this->redondear((float) ($extras['otros_descuentos'] ?? 0), 2);
    $otrosDesc = min($otrosDesc, $bruto * Config::MAX_OTROS_DESC_PCT);
    $totalDesc = $this->redondear($css + $segEdu + $isr + $otrosDesc, 2);

    //  Neto = (bruto - descuentos) + ingresos exentos (no entraron al bruto)
    $neto = $this->redondear(($bruto - $totalDesc) + $ingresos['total_sin_descuento'], 2);

    //  Validación disponibilidad Art. 161
    $pctDesc = $bruto > 0 ? ($otrosDesc / $bruto) : 0;
    $alertaDesc = $pctDesc > Config::MAX_OTROS_DESC_PCT;

    return [
      // Ingresos
      'salario_base_quincena' => $quincena,
      'valor_hora' => $valorHora,
      'otros_ingresos' => $ingresos['total_gravable'],
      'otros_ingresos_sin_descuento' => $ingresos['total_sin_descuento'],
      'detalle_ingresos' => $ingresos['detalle'],
      'salario_bruto' => $bruto,

      // Deducciones empleado
      'desc_seguro_social' => $css,
      'desc_seguro_educativo' => $segEdu,
      'desc_isr' => $isr,
      'otros_descuentos' => $otrosDesc,
      'total_descuentos' => $totalDesc,
      'salario_neto' => $neto,

      // Alerta
      'alerta_desc_excede' => $alertaDesc,
      'pct_descuentos' => $this->redondear($pctDesc * 100, 2),
    ];
  }

  /**
   * Procesa el array de ingresos del período y los clasifica.
   *
   * @return array{
   *   total_gravable: float,
   *   total_sin_descuento: float,
   *   excedente_css: float,
   *   detalle: array
   * }
   */
  private function procesarIngresos(array $ingresos, float $salMensual, float $valorHora): array
  {
    $totalGravable = 0.0;
    $totalSinDescuento = 0.0;
    $excedenteCSS = 0.0;
    $detalle = [];

    foreach ($ingresos as $ingreso) {
      $tipo = $ingreso['tipo'] ?? '';
      $monto = (float) ($ingreso['monto'] ?? 0);

      $resultado = match($tipo) {
        'horas_extra', 'comision', 'bonificacion' => $this->calcularComision($monto),
        'dietas' => $this->calcularConExencion($monto, $salMensual * Config::DIETAS_EXENCION),
        'prima' => $this->calcularConExencion($monto, $salMensual * Config::PRIMA_EXENCION),
        default => ['gravable' => $monto, 'sin_descuento' => 0.0, 'excedente_css' => 0.0],
      };

      $totalGravable += $resultado['gravable'];
      $totalSinDescuento += $resultado['sin_descuento'];
      $excedenteCSS += $resultado['excedente_css'];

      $detalle[] = [
        'tipo' => $tipo,
        'monto' => $monto,
        'gravable' => $this->redondear($resultado['gravable'],      2),
        'sin_descuento' => $this->redondear($resultado['sin_descuento'], 2),
        'horas' => $ingreso['horas'] ?? null,  // se guarda en BD, no afecta calculo
      ];   
    }

    return [
      'total_gravable' => $this->redondear($totalGravable,     2),
      'total_sin_descuento' => $this->redondear($totalSinDescuento, 2),
      'excedente_css' => $this->redondear($excedenteCSS,      2),
      'detalle' => $detalle,
    ];
  }

  /** Horas extra: monto calculado desde horas * valorHora * recargo */
  private function calcularHorasExtra(array $ingreso, float $valorHora): array
  {
    $monto = (float) ($ingreso['monto'] ?? 0);
    return ['gravable' => $monto, 'sin_descuento' => 0.0, 'excedente_css' => 0.0];
  }

  /** Comisión: gravable 100% */
  private function calcularComision(float $monto): array
  {
    return ['gravable' => $monto, 'sin_descuento' => 0.0, 'excedente_css' => 0.0];
  }

  /**
   * Ingreso con exención parcial (dietas, prima):
   * - Hasta umbral -> exento, va a sin_descuento
   * - Excedente -> gravable + CSS
   */
  private function calcularConExencion(float $monto, float $umbral): array
  {
    $umbral = $this->redondear($umbral, 2);
    $excedente = $this->redondear(max(0.0, $monto - $umbral), 2);
    $exento = $this->redondear(min($monto, $umbral), 2);
    return [
      'gravable' => $excedente,
      'sin_descuento' => $exento,
      'excedente_css' => $excedente,
    ];
  }
}
