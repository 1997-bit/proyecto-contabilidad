<?php
class CifradoService
{
  private string $clave;
  private const ALGORITMO = 'aes-256-gcm';
  private const IV_LEN    = 12;
  private const TAG_LEN   = 16;

  public function __construct()
  {
    $hex = $_ENV['DB_ENCRYPTION_KEY'] ?? '';

    if (strlen($hex) !== 64) {
      throw new RuntimeException('DB_ENCRYPTION_KEY debe ser exactamente 64 caracteres hexadecimales (32 bytes).');
    }

    $this->clave = hex2bin($hex);
  }

  /**
   * Cifra un string y devuelve bytes binarios: [IV:12][Tag:16][Ciphertext:N]
   */
  public function cifrar(string $texto): string
  {
    $iv  = random_bytes(self::IV_LEN);
    $tag = '';

    $ciphertext = openssl_encrypt(
      $texto,
      self::ALGORITMO,
      $this->clave,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      '',
      self::TAG_LEN
    );

    if ($ciphertext === false) {
      throw new RuntimeException('Error al cifrar el dato.');
    }

    return $iv . $tag . $ciphertext;
  }

  /**
   * Descifra bytes binarios [IV:12][Tag:16][Ciphertext:N] y devuelve el texto original.
   */
  public function descifrar(string $binario): string
  {
    if (strlen($binario) < self::IV_LEN + self::TAG_LEN + 1) {
      throw new RuntimeException('Dato cifrado inválido o corrupto.');
    }

    $iv = substr($binario, 0, self::IV_LEN);
    $tag = substr($binario, self::IV_LEN, self::TAG_LEN);
    $ciphertext = substr($binario, self::IV_LEN + self::TAG_LEN);

    $texto = openssl_decrypt(
      $ciphertext,
      self::ALGORITMO,
      $this->clave,
      OPENSSL_RAW_DATA,
      $iv,
      $tag
    );

    if ($texto === false) {
      throw new RuntimeException('Fallo al descifrar: datos corruptos o clave incorrecta.');
    }

    return $texto;
  }

  /**
   * Hash SHA-256 determinístico para búsquedas (no reversible).
   */
  public static function hash(string $valor): string
  {
    return hash('sha256', mb_strtolower(trim($valor)));
  }
}
