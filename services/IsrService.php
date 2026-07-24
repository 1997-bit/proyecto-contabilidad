<?php declare(strict_types=1);

class ISRService
{
  private function redondear(float $valor, int $decimales = 2): float
  {
    return round($valor, $decimales, PHP_ROUND_HALF_UP);
  }

  /**
   * Calcula ISR quincenal a partir de ingreso bruto quincenal.
   *
   * Metodología:
   * - Convierte ingreso quincenal a renta anual proyectada:
   *   rentaAnual = brutoQuincena * 2 * 13
   *   (24 quincenas + el decimo)
   *
   * - Aplica tarifa progresiva ISR:
   *   Tramo 1: <= ISR_TRAMO1_TOPE -> 0%
   *   Tramo 2: excedente sobre tramo 1 -> ISR_TRAMO2_TASA
   *   Tramo 3: excedente sobre tramo 2 -> ISR_TRAMO3_TASA + base fija
   *
   * - Conversión a quincena:
   *   ISR anual / 24
   *
   * - Deducción por estado civil:
   *   casado/unido -> aplica deducción proporcional (dividida entre 24 periodos)
   *   soltero -> sin deducción
   *
   * Normativa base:
   * - Tabla de retención ISR de la Dirección General de Ingresos (DGI) de Panamá
   * - Artículo 10 (tarifa progresiva)
   * - Ajustes por Ley 8 del 15 de marzo de 2010 (deducción cónyuge)
   *
   * Nota técnica:
   * - 13 en el cálculo = incorporación de décimo tercer mes en proyección anual
   * - Se retorna valor redondeado a 2 decimales
   *
   * @param float  $brutoQuincena Ingreso bruto por quincena
   * @param string $estadoCivil   'casado', 'unido', u otro
   * @return float ISR quincenal neto
   */
  public function calcularQuincena(float $brutoQuincena, string $estadoCivil): float
  {
    $rentaAnual = $brutoQuincena * 2 * 13;

    if ($rentaAnual <= Config::ISR_TRAMO1_TOPE) {
      return 0.0;
    }

    if ($rentaAnual <= Config::ISR_TRAMO2_TOPE) {
      $isrAnual = ($rentaAnual - Config::ISR_TRAMO1_TOPE) * Config::ISR_TRAMO2_TASA;
    } else {
      $isrAnual = Config::ISR_TRAMO3_BASE + ($rentaAnual - Config::ISR_TRAMO2_TOPE) * Config::ISR_TRAMO3_TASA;
    }

    $deduccion = in_array($estadoCivil, ['casado', 'unido'])
      ? (Config::ISR_DEDUCCION_E * Config::ISR_TRAMO2_TASA) / 24
      : 0.0;

    return $this->redondear(max(0.0, ($isrAnual / 24) - $deduccion), 2);
  }
}
