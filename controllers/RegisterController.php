<?php

class RegisterController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }
        require BASE_PATH . '/views/register/register.php';
    }

    private function guardar(): void
    {
        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol      = trim($_POST['rol']      ?? 'visor');

        // Validación básica
        $rolesValidos = ['admin', 'contador', 'visor'];
        if (empty($nombre) || empty($email) || empty($password) || !in_array($rol, $rolesValidos)) {
            $error = 'Todos los campos son requeridos y el rol debe ser válido.';
            require BASE_PATH . '/views/register/register.php';
            return;
        }

        $cifrado   = new CifradoService();
        $emailHash = CifradoService::hash($email);

        // Verificar que el email no exista ya
        $model = new UsuarioModel();
        if ($model->existeEmailHash($emailHash)) {
            $error = 'Ya existe un usuario con ese email.';
            require BASE_PATH . '/views/register/register.php';
            return;
        }

        $model->insertar([
            'nombre'        => $cifrado->cifrar($nombre),
            'email'         => $cifrado->cifrar($email),
            'email_hash'    => $emailHash,
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            'rol'           => $rol,
        ]);

        $exito = 'Usuario creado. <a href="' . BASE_URL . '/login">Ir al login</a>';
        require BASE_PATH . '/views/register/register.php';
    }
}