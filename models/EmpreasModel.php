<?php declare(strict_types=1);

class EmpresaModel
{
  public function __construct(private PDO $db) {}

    public function listarActivas(): array
    {
      return $this->db
                  ->query("SELECT * FROM empresas WHERE activo = 1 ORDER BY nombre ASC")
                  ->fetchAll();
    }

  public function buscarPorId(int $id): array|false
  {
    $stmt = $this->db->prepare(
      "SELECT * FROM empresas WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
  }

  public function insertar(array $d): void
  {
    $stmt = $this->db->prepare("
            INSERT INTO empresas
                (nombre, ruc, region, horas_semanales, semanas_mes, clase_riesgo, grado_riesgo)
            VALUES
                (:nombre, :ruc, :region, :horas_semanales, :semanas_mes, :clase_riesgo, :grado_riesgo)
        ");
        $stmt->execute($d);
    }

    public function actualizar(int $id, array $d): void
    {
        $d[':id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE empresas SET
                nombre          = :nombre,
                ruc             = :ruc,
                region          = :region,
                horas_semanales = :horas_semanales,
                semanas_mes     = :semanas_mes,
                clase_riesgo    = :clase_riesgo,
                grado_riesgo    = :grado_riesgo
            WHERE id = :id
        ");
        $stmt->execute($d);
    }
}
