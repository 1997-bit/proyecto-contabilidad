<?php
use PHPUnit\Framework\TestCase;

class CifradoServiceTest extends TestCase
{
    private CifradoService $c;

    protected function setUp(): void
    {
        $this->c = new CifradoService();
    }

    public function test_round_trip(): void
    {
        $texto = 'Rubén Blades';
        $this->assertSame($texto, $this->c->descifrar($this->c->cifrar($texto)));
    }

    public function test_ivs_aleatorios(): void
    {
        $this->assertNotSame($this->c->cifrar('test'), $this->c->cifrar('test'));
    }

    public function test_hash_deterministico(): void
    {
        $this->assertSame(
            CifradoService::hash('8-123'),
            CifradoService::hash('8-123')
        );
    }

    public function test_clave_invalida_lanza_excepcion(): void
    {
        $this->expectException(RuntimeException::class);
        $_ENV['DB_ENCRYPTION_KEY'] = 'corta';
        new CifradoService();
    }
}