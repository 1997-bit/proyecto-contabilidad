## Requisitos

Tener Composer instalado. Verificar con:

```powershell
composer --version
```

---

## Instalación

La primera vez que se clona el repo, instalar las dependencias:

```powershell
composer install
```

Esto recrea la carpeta `vendor/` con PHPUnit y todo lo necesario.

---

## Correr los tests

Todos los tests de una vez:

```powershell
vendor/bin/phpunit
```

Solo un archivo:

```powershell
vendor/bin/phpunit tests/CifradoServiceTest.php
```

Solo un método específico:

```powershell
vendor/bin/phpunit --filter test_round_trip
```

Salida esperada cuando todo pasa:

```
PHPUnit 13.1.11 by Sebastian Bergmann and contributors.

....                                                    4 / 4 (100%)

Time: 00:00.026, Memory: 16.00 MB
OK (4 tests, 4 assertions)
```

---

## Estructura de la carpeta tests/

```
tests/
├── bootstrap.php            ← se carga antes de todos los tests
└── CifradoServiceTest.php   ← tests de CifradoService
```

`bootstrap.php` define las constantes y carga las clases que los tests necesitan. No se toca salvo que se agregue una clase nueva al proyecto.

---

## Cómo añadir un test nuevo

### 1. Crear el archivo

Dentro de `tests/` crear un archivo con el nombre `NombreClaseTest.php`. Por ejemplo, para testear `PlanillaService`:

```
tests/PlanillaServiceTest.php
```

### 2. Estructura básica

```php
<?php
use PHPUnit\Framework\TestCase;

class PlanillaServiceTest extends TestCase
{
    private PlanillaService $servicio;

    // setUp() corre antes de CADA test
    protected function setUp(): void
    {
        require_once BASE_PATH . '/services/PlanillaService.php';
        $this->servicio = new PlanillaService();
    }

    public function test_salario_quincena(): void
    {
        // salario mensual 900 → quincena = 450
        $this->assertSame(450.00, $this->servicio->calcularQuincena(900.00));
    }
}
```

Tres reglas obligatorias:

- El archivo termina en `Test.php`
- La clase extiende `TestCase`
- Cada método de prueba empieza con `test_`

PHPUnit detecta todo automáticamente — no hay que registrar nada.

### 3. Registrar la clase en bootstrap.php

Si la clase que se va a testear no está cargada todavía, agregarla en `tests/bootstrap.php`:

```php
require_once BASE_PATH . '/services/PlanillaService.php';
```

### 4. Correr y verificar

```powershell
vendor/bin/phpunit
```

---

## Aserciones más usadas

| Aserción                                   | Qué verifica                      |
| ------------------------------------------ | --------------------------------- |
| `assertSame($esperado, $actual)`           | Igualdad estricta (tipo y valor)  |
| `assertNotSame($a, $b)`                    | Que dos valores sean distintos    |
| `assertTrue($condicion)`                   | Que algo sea `true`               |
| `assertFalse($condicion)`                  | Que algo sea `false`              |
| `expectException(RuntimeException::class)` | Que el código lance una excepción |

---

## Tests actuales

| Archivo                  | Clase testeada   | Qué cubre                                                                          |
| ------------------------ | ---------------- | ---------------------------------------------------------------------------------- |
| `CifradoServiceTest.php` | `CifradoService` | Cifrado/descifrado round-trip, IVs aleatorios, hash determinístico, clave inválida |

---

## Convención de nombres

Los métodos de test deben describir exactamente qué se está probando:

```php
// claro
public function test_cedula_con_formato_invalido_falla(): void

// vago
public function test_cedula(): void
```

---

## Lo que no se sube al repo

```
vendor/
.phpunit.result.cache
```

Ya están en el `.gitignore`. `vendor/` se recrea con `composer install`. `.phpunit.result.cache` es un archivo temporal de PHPUnit.
