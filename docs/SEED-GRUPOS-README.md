# Issue #4: Precarga de Empleados por Grupo

## ¿Qué es?
Módulo para cargar 3 empleados de demostración según el grupo asignado (1, 2, 3 o 4).

Los datos ya están pre-configurados. Solo se necesita:
1. **Conoce el grupo**
2. **Cambiar una línea de código**
3. **Ejecutar un script PHP**

## Datos Disponibles

| Grupo | Empleado 1 | Empleado 2 | Empleado 3 |
|-------|-----------|-----------|-----------|
| **1** | Carlos Roberto Mendez (Conserje) | Miguel Angel Vasquez (Mantenimiento) | Ana Maria Gonzalez (Supervisor Limpieza) |
| **2** | Jesus David Hernandez (Operario) | Francisco Javier Ortiz (Electricista) | Patricia Elena Ramirez (Plomera) |
| **3** | Gabriela Beatriz Lopez (Asistente Admin) | Sandra Leticia Morales (Secretaria) | Maria Rosa Sanchez (Recepcionista) |
| **4** | Juan Carlos Rojas (Técnico IT) | Roberto Fonseca Martinez (Desarrollador) | Silvia Mariana Vargas (Analista Datos) |

## Cómo Usar

### Paso 1: Saber tu grupo
Esperas de la profesora el grupo asignado: 1, 2, 3 o 4

### Paso 2: Editar el archivo
Abre: `database/grupos-seed.php`

Encuentra esta línea (línea ~16):
```php
define('GROUP_ID', 1);  // Cambia a 1, 2, 3 o 4
```

Cambia el `1` por el grupo. Ej: si tu grupo es 2:
```php
define('GROUP_ID', 2);  // Grupo 2
```

### Paso 3: Ejecutar el script
En la terminal, desde la raíz del proyecto:

```bash
php database/grupos-seed.php
```

### Resultado esperado
```

📥 Cargando empleados...
✅ Cargado: Rubén Blades (4-590-678)
✅ Cargado: Alejandro Fernández (10-400-390)
✅ Cargado: Vicente Fernández (5-789-352)

📋 Creando planilla (junio 2015, segunda quincena)...
✅ Planilla ID: 2

💰 Cargando detalles de planilla e ingresos...
✅ Rubén Blades: Salario Neto B/. 3,160.80
✅ Alejandro Fernández: Salario Neto B/. 4,734.00
✅ Vicente Fernández: Salario Neto B/. 1,021.32

╔════════════════════════════════════════════════════════════════╗
║ ✅ COMPLETADO: Grupo 3 cargado con datos exactos del enunciado ║
╚════════════════════════════════════════════════════════════════╝

```

## ¿Qué Hace el Script?

1. ✅ Lee el `GROUP_ID`
2. ✅ Selecciona los 3 empleados del grupo
3. ✅ **Cifra** los datos sensibles (nombre, cédula) usando AES-256-GCM
4. ✅ Crea hashes SHA-256 para búsquedas rápidas
5. ✅ Los inserta en la tabla `colaboradores`
6. ✅ Evita duplicados (si ya existen, los salta)

## Datos que se Cargan

Para cada empleado se guarda:
- **Nombre completo** (cifrado AES-256-GCM)
- **Cédula** (cifrada AES-256-GCM)
- **Cargo**
- **Salario base mensual**
- **Estado civil** (soltero/casado/unido)
- **Año de ingreso** (para cálculos de antigüedad)

## Después de Ejecutar

Los empleados estarán listos en tu sistema. Puedes:
- 🔍 **Buscarlos** usando la funcionalidad de Issue #5 (busca por cédula)
- 📋 **Agregarlos** a una planilla
- 🧮 **Calcular** sus quincenas automáticamente

### Ejemplo: Buscar "Rubén Blades" (Grupo 3)
1. Ve a `/planilla`
2. En el campo "Cédula", ingresa: `4-590-678`
3. Click "Buscar"
4. Se cargarán sus datos automáticamente

## Si hay Error

| Error | Solución |
|-------|----------|
| `GROUP_ID debe ser 1, 2, 3 o 4` | Revisa que cambiaste correctamente a un número válido |
| `No hay empresas en la BD` | Necesitas crear una empresa primero en el sistema |
| `cédula ya existe` | El empleado ya fue cargado; salta duplicados |

## Cambiar de Grupo Después

Si tu grupo cambia o quieres probar otro:
1. Abre `database/grupos-seed.php`
2. Cambia `define('GROUP_ID', X)` al nuevo grupo
3. Ejecuta: `php database/grupos-seed.php` de nuevo
4. Los nuevos empleados se cargarán (los antiguos quedan en la BD)

---

**Creado para:** Issue #4 - Precarga de empleados según grupo asignado  
**Versión:** 1.0  
**Autor:** Sistema de Contabilidad - Grupo Modular
**FIRMA:** Mc
