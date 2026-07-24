<?php

class UsuarioModel
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Conexion::conectar();
  }

  public function buscarPorEmailHash(string $email): array|false
  {
    $emailHash = CifradoService::hash($email);

    $stmt = $this->db->prepare(
      "SELECT id, nombre, email, password_hash, rol,
      login_attempts, locked_until, activo
      FROM usuarios
      WHERE email_hash = ? AND activo = 1
      LIMIT 1"
        );
        $stmt->execute([$emailHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        // Descifrar PII para uso en sesión
        $cifrado = new CifradoService();
        $row['nombre_plain'] = $cifrado->descifrar($row['nombre']);

        return $row;
    }

    public function incrementarIntentos(int $id): void
    {
        $this->db->prepare(
            "UPDATE usuarios SET login_attempts = login_attempts + 1 WHERE id = ?"
        )->execute([$id]);
    }

    public function bloquearCuenta(int $id, int $minutos = 15): void
    {
        $this->db->prepare(
            "UPDATE usuarios
             SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
             WHERE id = ?"
        )->execute([$minutos, $id]);
    }

    public function resetearIntentos(int $id): void
    {
        $this->db->prepare(
            "UPDATE usuarios
             SET login_attempts = 0, locked_until = NULL, last_login = NOW()
             WHERE id = ?"
        )->execute([$id]);
    }

    public function existeEmailHash(string $emailHash): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM usuarios WHERE email_hash = ? LIMIT 1"
        );
        $stmt->execute([$emailHash]);
        return (bool) $stmt->fetch();
    }

    public function insertar(array $datos): void
    {
        $this->db->prepare(
            "INSERT INTO usuarios (nombre, email, email_hash, password_hash, rol)
            VALUES (:nombre, :email, :email_hash, :password_hash, :rol)"
        )->execute($datos);
    }

    public function listarTodos(): array
    {
        return $this->db->query(
            "SELECT id, nombre, email, rol, activo, last_login
             FROM usuarios
             ORDER BY id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT id, rol, activo FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarRol(int $id, string $rol): void
    {
        $this->db->prepare("UPDATE usuarios SET rol = ? WHERE id = ?")->execute([$rol, $id]);
    }

    public function actualizarActivo(int $id, bool $activo): void
    {
        $this->db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?")->execute([$activo ? 1 : 0, $id]);
    }

}
