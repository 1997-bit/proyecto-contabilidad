<?php declare(strict_types=1);

class PlanillaModel
{
    public function __construct(private PDO $db) {}

    // Empresas 

    public function listarEmpresas(): array
    {
        return $this->db
            ->query("SELECT id, nombre FROM empresas WHERE activo = 1 ORDER BY nombre ASC")
            ->fetchAll();
    }

    public function buscarEmpresa(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM empresas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Colaboradores 

    public function buscarColaboradorPorCedulaHash(string $hash): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM colaboradores WHERE cedula_hash = :h LIMIT 1"
        );
        $stmt->execute([':h' => $hash]);
        return $stmt->fetch();
    }

    public function insertarColaborador(array $d): int
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
        return (int) $this->db->lastInsertId();
    }

    // Planilla encabezado

    public function buscarPlanilla(int $empresaId, string $periodo, int $mes, int $anio): array|false
    {
        $stmt = $this->db->prepare("
            SELECT * FROM planillas
            WHERE empresa_id = :eid AND periodo = :periodo
              AND mes = :mes AND anio = :anio
            LIMIT 1
        ");
        $stmt->execute([':eid' => $empresaId, ':periodo' => $periodo,
                        ':mes' => $mes,       ':anio'    => $anio]);
        return $stmt->fetch();
    }

    public function crearPlanilla(int $empresaId, string $periodo, int $mes, int $anio, ?int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO planillas (empresa_id, periodo, mes, anio, estado, created_by)
            VALUES (:eid, :periodo, :mes, :anio, 'borrador', :created_by)
        ");
        $stmt->execute([':eid' => $empresaId, ':periodo' => $periodo,
                        ':mes' => $mes,       ':anio'    => $anio,
                        ':created_by' => $createdBy]);
        return (int) $this->db->lastInsertId();
    }

    // Detalle

    public function existeDetalle(int $planillaId, int $colaboradorId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM detalle_planilla
            WHERE id_planilla = :p AND id_colaborador = :c LIMIT 1
        ");
        $stmt->execute([':p' => $planillaId, ':c' => $colaboradorId]);
        return (bool) $stmt->fetch();
    }

    public function insertarDetalle(int $planillaId, int $colaboradorId, array $calc, ?int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO detalle_planilla (
                id_planilla, id_colaborador,
                salario_base_quincena, valor_hora,
                otros_ingresos, otros_ingresos_sin_descuento, salario_bruto,
                desc_seguro_social, desc_seguro_educativo, desc_isr,
                otros_descuentos, total_descuentos, salario_neto,
                pct_descuentos, alerta_desc_excede, created_by
            ) VALUES (
                :id_planilla, :id_colaborador,
                :salario_base_quincena, :valor_hora,
                :otros_ingresos, :otros_ingresos_sin_descuento, :salario_bruto,
                :desc_seguro_social, :desc_seguro_educativo, :desc_isr,
                :otros_descuentos, :total_descuentos, :salario_neto,
                :pct_descuentos, :alerta_desc_excede, :created_by
            )
        ");
        $stmt->execute([
            ':id_planilla' => $planillaId,
            ':id_colaborador' => $colaboradorId,
            ':salario_base_quincena' => $calc['salario_base_quincena'],
            ':valor_hora' => $calc['valor_hora'],
            ':otros_ingresos' => $calc['otros_ingresos'],
            ':otros_ingresos_sin_descuento' => $calc['otros_ingresos_sin_descuento'],
            ':salario_bruto' => $calc['salario_bruto'],
            ':desc_seguro_social' => $calc['desc_seguro_social'],
            ':desc_seguro_educativo' => $calc['desc_seguro_educativo'],
            ':desc_isr' => $calc['desc_isr'],
            ':otros_descuentos' => $calc['otros_descuentos'],
            ':total_descuentos' => $calc['total_descuentos'],
            ':salario_neto' => $calc['salario_neto'],
            ':pct_descuentos' => $calc['pct_descuentos'],
            ':alerta_desc_excede' => $calc['alerta_desc_excede'] ? 1 : 0,
            ':created_by' => $createdBy,
        ]);

        $detalleId = (int) $this->db->lastInsertId();

        foreach ($calc['detalle_ingresos'] ?? [] as $ing) {
            $s = $this->db->prepare("
                INSERT INTO detalle_ingresos (id_detalle, tipo, monto, gravable, sin_descuento, horas)
                VALUES (:id_detalle, :tipo, :monto, :gravable, :sin_descuento, :horas)
            ");
            $s->execute([
                ':id_detalle' => $detalleId,
                ':tipo' => $ing['tipo'],
                ':monto' => $ing['monto'],
                ':gravable' => $ing['gravable'],
                ':sin_descuento' => $ing['sin_descuento'],
                ':horas' => $ing['horas'] ?? null,
            ]);
        }

        return $detalleId;
    }
}
