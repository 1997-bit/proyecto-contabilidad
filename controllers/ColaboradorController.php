<?php

class ColaboradorController
{
    private PDO $db;
    private CifradoService $cifrado;
    private ColaboradorModel $model;

    public function __construct()
    {
        $this->db = Conexion::conectar();
        $this->cifrado = new CifradoService();
        $this->model   = new ColaboradorModel($this->db);
    }

    public function index(): void
    {
        $colaboradores = $this->listar();
        $errores = [];
        $valores = [];
        require BASE_PATH . '/views/colaboradores/index.php';
    }

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /colaborador');
            exit;
        }

        $valores = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'cedula' => trim($_POST['cedula'] ?? ''),
            'estado_civil' => $_POST['estado_civil'] ?? '',
            'cargo' => trim($_POST['cargo'] ?? ''),
            'salario_base' => $_POST['salario_base'] ?? '',
            'tipo_salario' => $_POST['tipo_salario'] ?? '',
            'anio_inicio' => $_POST['anio_inicio'] ?? '',
        ];

        $errores = $this->validar($valores);

        if (!empty($errores)) {
            $colaboradores = $this->listar();
            require BASE_PATH . '/views/colaboradores/index.php';
            return;
        }

        $this->model->insertar([
            ':nombre_completo' => $this->cifrado->cifrar($valores['nombre_completo']),
            ':nombre_hash' => CifradoService::hash($valores['nombre_completo']),
            ':cedula' => $this->cifrado->cifrar($valores['cedula']),
            ':cedula_hash' => CifradoService::hash($valores['cedula']),
            ':estado_civil' => $valores['estado_civil'],
            ':cargo' => $valores['cargo'],
            ':salario_base'  => (float) $valores['salario_base'],
            ':tipo_salario' => $valores['tipo_salario'],
            ':anio_inicio' => (int) $valores['anio_inicio'],
        ]);

        header('Location: /colaborador?ok=1');
        exit;
    }

    private function validar(array $v): array
    {
        $e = [];

        if ($v['nombre_completo'] === '')
            $e['nombre_completo'] = 'El nombre es obligatorio.';

        if (!preg_match('/^\d{1,2}-\d{3,4}-\d{3,4}$/', $v['cedula']))
            $e['cedula'] = 'Formato inválido. Ej: 8-123-4567';

        if (!in_array($v['estado_civil'], ['soltero', 'casado', 'unido'], true))
            $e['estado_civil'] = 'Seleccione un estado civil.';

        if ($v['cargo'] === '')
            $e['cargo'] = 'El cargo es obligatorio.';

        $salario = (float) $v['salario_base'];
        if ($salario <= 0)
            $e['salario_base'] = 'El salario debe ser mayor a 0.';
        elseif ($salario < Config::SALARIO_MINIMO_MES)
            $e['salario_base_aviso'] = "Aviso: $salario está bajo el mínimo legal ($" . Config::SALARIO_MINIMO_MES . "). Se guardará igual.";

        if (!in_array($v['tipo_salario'], ['fijo', 'comisiones', 'dietas', 'prima_produccion'], true))
            $e['tipo_salario'] = 'Seleccione un tipo de salario.';

        $anio = (int) $v['anio_inicio'];
        if ($anio < 1900 || $anio > (int) date('Y'))
            $e['anio_inicio'] = 'Año inválido.';

        return $e;
    }

    private function listar(): array
    {
        $filas = $this->model->listarTodos();

        foreach ($filas as &$fila) {
            try {
                $fila['nombre_completo'] = $this->cifrado->descifrar($fila['nombre_completo']);
                $fila['cedula']          = $this->cifrado->descifrar($fila['cedula']);
            } catch (RuntimeException) {
                $fila['nombre_completo'] = '[error]';
                $fila['cedula']          = '[error]';
            }
        }

        return $filas;
    }
}