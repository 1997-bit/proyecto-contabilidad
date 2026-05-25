<?php
class ColaboradorModel
{
    public function __construct(private PDO $db) {}

    public function insertar(array $d): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO colaboradores
                (nombre_completo, nombre_hash, cedula, cedula_hash,
                 estado_civil, cargo, salario_base, tipo_salario, anio_inicio)
            VALUES
                (:nombre_completo, :nombre_hash, :cedula, :cedula_hash,
                 :estado_civil, :cargo, :salario_base, :tipo_salario, :anio_inicio)
        ");
        $stmt->execute($d);
    }

    public function listarTodos(): array
    {
        return $this->db
            ->query("SELECT * FROM colaboradores ORDER BY id DESC")
            ->fetchAll();
    }
}