<?php declare(strict_types=1);

class ColaboradorModel
{
    public function __construct(private PDO $db) {}

    public function insertar(array $d): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO colaboradores
                (empresa_id, nombre_completo, nombre_hash, cedula, cedula_hash,
                estado_civil, cargo, salario_base, anio_inicio)
            VALUES
                (:empresa_id, :nombre_completo, :nombre_hash, :cedula, :cedula_hash,
                :estado_civil, :cargo, :salario_base, :anio_inicio)
        ");
        $stmt->execute($d);
    }

    public function listarTodos(): array
    {
        return $this->db->query("
            SELECT c.*, e.nombre AS empresa_nombre,
                   e.horas_semanales, e.semanas_mes,
                   e.clase_riesgo, e.grado_riesgo
            FROM colaboradores c
            JOIN empresas e ON e.id = c.empresa_id
            WHERE c.activo = 1
            ORDER BY c.id DESC
        ")->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT c.*, e.nombre AS empresa_nombre,
                   e.horas_semanales, e.semanas_mes,
                   e.clase_riesgo, e.grado_riesgo
            FROM colaboradores c
            JOIN empresas e ON e.id = c.empresa_id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function listarPorEmpresa(int $empresaId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, e.nombre AS empresa_nombre,
                   e.horas_semanales, e.semanas_mes,
                   e.clase_riesgo, e.grado_riesgo
            FROM colaboradores c
            JOIN empresas e ON e.id = c.empresa_id
            WHERE c.empresa_id = :empresa_id AND c.activo = 1
            ORDER BY c.id DESC
        ");
        $stmt->execute([':empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function actualizar(int $id, array $d): void
    {
        // CREADO: Método para mostrar formulario de nuevo colaborador
        $sql = "UPDATE colaboradores SET
                    nombre_completo = :nombre_completo,
                    nombre_hash = :nombre_hash,
                    cedula = :cedula,
                    cedula_hash = :cedula_hash,
                    estado_civil = :estado_civil,
                    cargo = :cargo,
                    salario_base = :salario_base,
                    anio_inicio = :anio_inicio
                WHERE id = :id";
        $d[':id'] = $id;
        $this->db->prepare($sql)->execute($d);
    }

    public function eliminar(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE colaboradores SET activo = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
