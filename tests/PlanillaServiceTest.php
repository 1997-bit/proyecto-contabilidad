<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../services/IsrService.php';
require_once __DIR__ . '/../services/PlanillaService.php';

class PlanillaServiceTest extends TestCase
{
  private PlanillaService $svc;

  protected function setUp(): void
  {
    $this->svc = new PlanillaService(new ISRService());
  }

  private function calcular(float $salario, array $ingresos = [], float $otrosDesc = 0, string $civil = 'soltero'): array
  {
    return $this->svc->calcularQuincena(
      ['salario_base' => $salario, 'estado_civil' => $civil, 'horas_semanales' => 48, 'semanas_mes' => 4.3333],
      ['ingresos' => $ingresos, 'otros_descuentos' => $otrosDesc]
    );
  }

  // Invariantes globales

  public function test_neto_nunca_negativo(): void
  {
    $r = $this->calcular(Config::SALARIO_MINIMO_MES, [], 9999.00);
    $this->assertGreaterThanOrEqual(0.0, $r['salario_neto']);
  }

  public function test_bruto_es_base_mas_gravables(): void
  {
    $r = $this->calcular(1000.00, [
      ['tipo' => 'comision', 'monto' => 200.00],
      ['tipo' => 'bonificacion', 'monto' => 100.00],
    ]);
    $this->assertEquals(
      $r['salario_base_quincena'] + $r['otros_ingresos'],
      $r['salario_bruto']
    );
  }

  public function test_neto_es_bruto_menos_descuentos_mas_exentos(): void
  {
    $r = $this->calcular(1000.00, [['tipo' => 'dietas', 'monto' => 150.00]]);
    $esperado = $r['salario_bruto'] - $r['total_descuentos'] + $r['otros_ingresos_sin_descuento'];
    $this->assertEquals($esperado, $r['salario_neto']);
  }

  public function test_total_descuentos_es_suma_de_partes(): void
  {
    $r = $this->calcular(1000.00, [], 50.00);
    $suma = $r['desc_seguro_social'] + $r['desc_seguro_educativo'] + $r['desc_isr'] + $r['otros_descuentos'];
    $this->assertEquals($r['total_descuentos'], $suma);
  }

  public function test_exentos_no_afectan_css_ni_isr(): void
  {
    $base = $this->calcular(1000.00);
    // dieta completamente dentro del umbral -> exenta, no cambia bruto ni CSS
    $conDietaExenta = $this->calcular(1000.00, [['tipo' => 'dietas', 'monto' => 50.00]]);
    $this->assertEquals($base['desc_seguro_social'], $conDietaExenta['desc_seguro_social']);
    $this->assertEquals($base['desc_isr'], $conDietaExenta['desc_isr']);
    $this->assertEquals($base['salario_bruto'], $conDietaExenta['salario_bruto']);
  }

  public function test_neto_mayor_que_bruto_cuando_hay_exentos(): void
  {
    $r = $this->calcular(1000.00, [['tipo' => 'dietas', 'monto' => 200.00]]);
    // exentos se suman al neto sin pasar por deducciones
    $this->assertGreaterThan($r['salario_bruto'] - $r['total_descuentos'], $r['salario_neto']);
  }

  // Quincena base

  public function test_base_quincena_es_mitad_del_mensual(): void
  {
    $r = $this->calcular(1200.00);
    $this->assertEquals(600.00, $r['salario_base_quincena']);
  }

  public function test_salario_minimo_genera_resultado_coherente(): void
  {
    $r = $this->calcular(Config::SALARIO_MINIMO_MES);
    $this->assertGreaterThan(0.0, $r['salario_neto']);
    $this->assertGreaterThan(0.0, $r['salario_bruto']);
    $this->assertGreaterThanOrEqual($r['salario_neto'], $r['salario_bruto']);
  }

  // CSS y Seg. Educativo

  public function test_css_se_calcula_sobre_bruto(): void
  {
    $r = $this->calcular(1000.00);
    $csEsperado = round($r['salario_bruto'] * Config::CSS_EMPLEADO, 2);
    $this->assertEquals($csEsperado, $r['desc_seguro_social']);
  }

  public function test_seg_educativo_se_calcula_sobre_bruto(): void
  {
    $r = $this->calcular(1000.00);
    $seEsperado = round($r['salario_bruto'] * Config::SEG_EDUCATIVO, 2);
    $this->assertEquals($seEsperado, $r['desc_seguro_educativo']);
  }

  public function test_ingreso_gravable_incrementa_css(): void
  {
    $base = $this->calcular(1000.00);
    $conComision = $this->calcular(1000.00, [['tipo' => 'comision', 'monto' => 500.00]]);
    $this->assertGreaterThan($base['desc_seguro_social'], $conComision['desc_seguro_social']);
  }

  // ISR

  public function test_isr_cero_bajo_tramo1(): void
  {
    // rentaAnual = brutoQuincena * 2 * 13 debe quedar <= ISR_TRAMO1_TOPE
    $brutoQuincenaNecesario = Config::ISR_TRAMO1_TOPE / (2 * 13);
    $salario = $brutoQuincenaNecesario * 2 * 0.95; // margen para quedar bajo
    $r = $this->calcular($salario);
    $this->assertEquals(0.00, $r['desc_isr']);
  }

  public function test_isr_positivo_sobre_tramo1(): void
  {
    // salario que proyecta renta anual claramente sobre tramo 1
    $salario = (Config::ISR_TRAMO1_TOPE / (2 * 13)) * 2 * 1.3;
    $r = $this->calcular($salario);
    $this->assertGreaterThan(0.00, $r['desc_isr']);
  }

  public function test_casado_paga_menos_o_igual_isr_que_soltero(): void
  {
    $soltero = $this->calcular(5000.00, [], 0, 'soltero');
    $casado = $this->calcular(5000.00, [], 0, 'casado');
    $this->assertLessThanOrEqual($soltero['desc_isr'], $casado['desc_isr']);
  }

  public function test_unido_paga_igual_isr_que_casado(): void
  {
    $casado = $this->calcular(5000.00, [], 0, 'casado');
    $unido = $this->calcular(5000.00, [], 0, 'unido');
    $this->assertEquals($casado['desc_isr'], $unido['desc_isr']);
  }

  public function test_isr_no_negativo(): void
  {
    $r = $this->calcular(Config::SALARIO_MINIMO_MES, [], 0, 'casado');
    $this->assertGreaterThanOrEqual(0.00, $r['desc_isr']);
  }

  // Art. 161
  public function test_pct_descuentos_refleja_relacion_otros_desc_sobre_bruto(): void
  {
    $r = $this->calcular(1000.00, [], 100.00);
    $pctEsperado = round(($r['otros_descuentos'] / $r['salario_bruto']) * 100, 2);
    $this->assertEquals($pctEsperado, $r['pct_descuentos']);
  }

  // Bonificacion

  public function test_bonificacion_entra_al_bruto_completa(): void
  {
    $base = $this->calcular(1000.00);
    $conBono = $this->calcular(1000.00, [['tipo' => 'bonificacion', 'monto' => 300.00]]);
    $this->assertEquals($base['salario_bruto'] + 300.00, $conBono['salario_bruto']);
  }

  public function test_bonificacion_no_genera_exentos(): void
  {
    $r = $this->calcular(1000.00, [['tipo' => 'bonificacion', 'monto' => 300.00]]);
    $this->assertEquals(0.00, $r['otros_ingresos_sin_descuento']);
  }

  public function test_bonificacion_incrementa_css_e_isr(): void
  {
    $base = $this->calcular(2000.00);
    $conBono = $this->calcular(2000.00, [['tipo' => 'bonificacion', 'monto' => 500.00]]);
    $this->assertGreaterThan($base['desc_seguro_social'], $conBono['desc_seguro_social']);
    $this->assertGreaterThanOrEqual($base['desc_isr'], $conBono['desc_isr']);
  }

  // Dietas

  public function test_dietas_completamente_exentas_bajo_umbral(): void
  {
    $umbral = 1000.00 * Config::DIETAS_EXENCION;
    $r = $this->calcular(1000.00, [['tipo' => 'dietas', 'monto' => $umbral - 1]]);
    $this->assertEquals(0.00, $r['otros_ingresos']);
    $this->assertEquals($umbral - 1, $r['otros_ingresos_sin_descuento']);
  }

  public function test_dietas_excedente_sobre_umbral_es_gravable(): void
  {
    $umbral = 1000.00 * Config::DIETAS_EXENCION;
    $monto = $umbral + 100.00;
    $r = $this->calcular(1000.00, [['tipo' => 'dietas', 'monto' => $monto]]);
    $this->assertEquals(100.00, $r['otros_ingresos']);
    $this->assertEquals($umbral, $r['otros_ingresos_sin_descuento']);
  }

  // Prima

  public function test_prima_completamente_exenta_bajo_umbral(): void
  {
    $umbral = 1000.00 * Config::PRIMA_EXENCION;
    $r = $this->calcular(1000.00, [['tipo' => 'prima', 'monto' => $umbral - 1]]);
    $this->assertEquals(0.00, $r['otros_ingresos']);
    $this->assertEquals($umbral - 1, $r['otros_ingresos_sin_descuento']);
  }

  public function test_prima_excedente_sobre_umbral_es_gravable(): void
  {
    $umbral = 1000.00 * Config::PRIMA_EXENCION;
    $monto = $umbral + 200.00;
    $r = $this->calcular(1000.00, [['tipo' => 'prima', 'monto' => $monto]]);
    $this->assertEquals(200.00, $r['otros_ingresos']);
    $this->assertEquals($umbral, $r['otros_ingresos_sin_descuento']);
  }

  // Horas extra

  private function esperadoHorasExtra(float $horas, float $valorHora, float $recargo): float
  {
    return round($horas * $valorHora * $recargo, 2);
  }

  public function test_horas_extra_diurna_usa_recargo_125(): void
  {
    $base = $this->calcular(1000.00);
    $r = $this->calcular(1000.00, [['tipo' => 'horas_extra_diurna', 'horas' => 8]]);
    $esperado = $this->esperadoHorasExtra(8, $base['valor_hora'], Config::RECARGO_DIURNO);
    $this->assertEquals($base['salario_bruto'] + $esperado, $r['salario_bruto']);
  }

  public function test_horas_extra_nocturna_usa_recargo_150(): void
  {
    $base = $this->calcular(1000.00);
    $r = $this->calcular(1000.00, [['tipo' => 'horas_extra_nocturna', 'horas' => 8]]);
    $esperado = $this->esperadoHorasExtra(8, $base['valor_hora'], Config::RECARGO_NOCTURNO);
    $this->assertEquals($base['salario_bruto'] + $esperado, $r['salario_bruto']);
  }

  public function test_horas_extra_dominical_usa_recargo_175(): void
  {
    $base = $this->calcular(1000.00);
    $r = $this->calcular(1000.00, [['tipo' => 'horas_extra_dominical', 'horas' => 8]]);
    $esperado = $this->esperadoHorasExtra(8, $base['valor_hora'], Config::RECARGO_DOMINICAL);
    $this->assertEquals($base['salario_bruto'] + $esperado, $r['salario_bruto']);
  }

  public function test_horas_extra_nocturna_paga_mas_que_diurna_a_igual_horas(): void
  {
    $diurna = $this->calcular(1000.00, [['tipo' => 'horas_extra_diurna', 'horas' => 10]]);
    $nocturna = $this->calcular(1000.00, [['tipo' => 'horas_extra_nocturna', 'horas' => 10]]);
    $this->assertGreaterThan($diurna['otros_ingresos'], $nocturna['otros_ingresos']);
  }

  public function test_horas_extra_dominical_paga_mas_que_nocturna_a_igual_horas(): void
  {
    $nocturna = $this->calcular(1000.00, [['tipo' => 'horas_extra_nocturna', 'horas' => 10]]);
    $dominical = $this->calcular(1000.00, [['tipo' => 'horas_extra_dominical', 'horas' => 10]]);
    $this->assertGreaterThan($nocturna['otros_ingresos'], $dominical['otros_ingresos']);
  }

  public function test_horas_extra_cero_horas_no_afecta_bruto(): void
  {
    $base = $this->calcular(1000.00);
    $r = $this->calcular(1000.00, [['tipo' => 'horas_extra_diurna', 'horas' => 0]]);
    $this->assertEquals($base['salario_bruto'], $r['salario_bruto']);
  }

  public function test_horas_extra_no_genera_exentos(): void
  {
    $r = $this->calcular(1000.00, [['tipo' => 'horas_extra_diurna', 'horas' => 5]]);
    $this->assertEquals(0.00, $r['otros_ingresos_sin_descuento']);
  }

  // Combinaciones multiples

  public static function providerCombinacionesIngresos(): array
  {
    return [
      'comision + dietas bajo umbral' => [
        1000.00,
        [
          ['tipo' => 'comision', 'monto' => 100.00],
          ['tipo' => 'dietas', 'monto' => 50.00],
        ],
        100.00, // gravable
        50.00,  // exento
      ],
      'bonificacion + prima bajo umbral' => [
        1000.00,
        [
          ['tipo' => 'bonificacion', 'monto' => 200.00],
          ['tipo' => 'prima', 'monto' => 300.00],
        ],
        200.00,
        300.00,
      ],
      'dietas excede + prima bajo umbral' => [
        1000.00,
        [
          ['tipo' => 'dietas', 'monto' => 400.00], // umbral=250, excedente=150
          ['tipo' => 'prima', 'monto' => 200.00],  // umbral=500, todo exento
        ],
        150.00,
        450.00,
      ],
      'todos los tipos' => [
        1000.00,
        [
          ['tipo' => 'bonificacion', 'monto' => 100.00],
          ['tipo' => 'comision', 'monto' => 100.00],
          ['tipo' => 'horas_extra_diurna', 'horas' => 4], // 4h * valorHora(4.8077) * 1.25 = 24.04
          ['tipo' => 'dietas', 'monto' => 50.00],
          ['tipo' => 'prima', 'monto' => 100.00],
        ],
        224.04,
        150.00,
      ],
    ];
  }

  #[DataProvider('providerCombinacionesIngresos')]
  public function test_combinacion_ingresos_separa_gravable_y_exento(
    float $salario,
    array $ingresos,
    float $gravableEsperado,
    float $exentoEsperado
  ): void {
    $r = $this->calcular($salario, $ingresos);
    $this->assertEquals($gravableEsperado, $r['otros_ingresos']);
    $this->assertEquals($exentoEsperado, $r['otros_ingresos_sin_descuento']);
  }

  #[DataProvider('providerCombinacionesIngresos')]
  public function test_invariante_bruto_en_combinaciones(
    float $salario,
    array $ingresos,
    float $gravableEsperado,
    float $exentoEsperado
  ): void {
    $r = $this->calcular($salario, $ingresos);
    $this->assertEquals(
      $r['salario_base_quincena'] + $r['otros_ingresos'],
      $r['salario_bruto']
    );
  }
}
