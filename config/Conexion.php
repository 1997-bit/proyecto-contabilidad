class Conexion
{
    private static ?PDO $instance = null;

    public static function conectar(): PDO
    {
        if (self::$instance !== null) return self::$instance;

        self::$instance = self::crearPDO();
        return self::$instance;
    }

    public static function setPDO(PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    private static function crearPDO(): PDO
    {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        return new PDO(
            "mysql:host=$host;dbname=$db;charset=$charset",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}