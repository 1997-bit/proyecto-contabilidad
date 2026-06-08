<?php declare(strict_types=1);

class ReporteController
{
  private PDO $db;

  public function __construct()
  {
    SessionHelper::requerir();
    $this->db = Conexion::conectar();
  }

  public function index(): void
  {
    require BASE_PATH . '/views/reporte/index.php';
  }

  public function personal(): void
  {
    $filas = [];
    require BASE_PATH . '/views/reporte/personal.php';
  }

  public function expediente(): void
  {
    $colaboradorId = (int) ($_GET['id'] ?? 0);
    $colaborador = null;
    $detalles = [];
    require BASE_PATH . '/views/reporte/expediente.php';
  }

  public function css(): void
  {
    $planillaId = (int) ($_GET['planilla_id'] ?? 0);
    $empresas = (new PlanillaModel($this->db))->listarEmpresas();
    $empresaId = (int) ($_GET['empresa_id'] ?? 0);
    $mes = (int) ($_GET['mes'] ?? 0);
    $anio = (int) ($_GET['anio'] ?? (int) date('Y'));
    $periodo = $_GET['periodo'] ?? '';

    $planillaModel = new PlanillaModel($this->db);
    $planillas = $planillaModel->listarPlanillas($empresaId, $mes, $anio, $periodo);

    $filas = [];
    $planilla = null;

    if ($planillaId > 0) {
      foreach ($planillas as $p) {
        if ((int)$p['id'] === $planillaId) {
          $planilla = $p;
          break;
        }
      }
      $rawFilas = $planillaModel->listarDetalleCss($planillaId);
      $cifrado = new CifradoService();
      foreach ($rawFilas as $f) {
        try {
          $f['cedula'] = $cifrado->descifrar($f['cedula']);
          $f['nombre_completo'] = $cifrado->descifrar($f['nombre_completo']);
        } catch (RuntimeException) {
          $f['cedula'] = '[error]';
          $f['nombre_completo'] = '[error]';
        }
        $f['aporte_patronal_css'] = round($f['salario_bruto'] * Config::CSS_PATRONO, 2);
        $f['aporte_patronal_edu'] = round($f['salario_bruto'] * Config::SEG_EDUCATIVO_PAT, 2);
        $filas[] = $f;
      }
    }

    require BASE_PATH . '/views/reporte/css.php';
  }
}
