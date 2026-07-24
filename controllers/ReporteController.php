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
        if ((int) $p['id'] === $planillaId) {
          $planilla = $p;
          break;
        }
      }
      $rawFilas = $planillaModel->listarDetallePlanilla($planillaId);
      $cifrado = new CifradoService();
      foreach ($rawFilas as $f) {
        $f['cedula'] = $cifrado->descifrarConFallback($f['cedula']);
        $f['nombre_completo'] = $cifrado->descifrarConFallback($f['nombre_completo']);
        $filas[] = $f;
      }
    }

    require BASE_PATH . '/views/reporte/personal.php';
  }

  public function expediente(): void
  {
    $planillaModel = new PlanillaModel($this->db);
    $colaboradorModel = new ColaboradorModel($this->db);
    $cifrado = new CifradoService();

    $empresas = $planillaModel->listarEmpresas();
    $empresaId = SessionHelper::empresaIdActiva($empresas);

    $rawColaboradores = $empresaId > 0 ? $planillaModel->listarColaboradoresActivos($empresaId) : [];
    $colaboradores = [];
    foreach ($rawColaboradores as $c) {
      $c['nombre_completo'] = $cifrado->descifrarConFallback($c['nombre_completo']);
      $c['cedula'] = $cifrado->descifrarConFallback($c['cedula']);
      $colaboradores[] = $c;
    }

    $colaboradorId = (int) ($_GET['id'] ?? 0);
    $colaborador = null;
    $detalles = [];

    if ($colaboradorId > 0) {
      $colab = $colaboradorModel->buscarPorId($colaboradorId);

      // IDOR: verificar que el colaborador pertenece a la empresa activa
      if ($colab && (int) $colab['empresa_id'] === $empresaId) {
        $colab['nombre_completo'] = $cifrado->descifrarConFallback($colab['nombre_completo']);
        $colab['cedula'] = $cifrado->descifrarConFallback($colab['cedula']);
        $colaborador = $colab;
        $detalles = $planillaModel->listarDetallePorColaborador($colaboradorId);
      }
    }

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
        $f['cedula'] = $cifrado->descifrarConFallback($f['cedula']);
        $f['nombre_completo'] = $cifrado->descifrarConFallback($f['nombre_completo']);
        $f['aporte_patronal_css'] = round($f['salario_bruto'] * Config::CSS_PATRONO, 2);
        $f['aporte_patronal_edu'] = round($f['salario_bruto'] * Config::SEG_EDUCATIVO_PAT, 2);
        $filas[] = $f;
      }
    }

    require BASE_PATH . '/views/reporte/css.php';
  }
}
