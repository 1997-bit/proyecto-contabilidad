# UNIVERSIDAD TECNOLÓGICA DE PANAMÁ

## EXAMEN SEMESTRAL

Usted debe elaborar un software donde se visualicen pantallas de selección de reportes.

```Mermaid
flowchart TD
    M@{ shape: docs, label: "2" }
    N@{ shape: docs, label: "2" }
    Ñ@{ shape: docs, label: "Expediente" }
    KA@{ shape: manual-file, label: "P"}
    KB@{ shape: manual-file, label: "P"}
    QS@{ shape: win-pane, label: "Informe de Planilla(C)" }
    XE@{ shape: win-pane, label: "Reporte de los colaboradores" }
    QE@{ shape: win-pane, label: "Reporte de para el pago de la Caja de seguro Social" }
    QR@{ shape: win-pane, label: "Reporte de para los colaboradores" }


    A([Inicio]) --> B[[Datos del Personal]]
    B --> C[Captura]
    C --> D[/Salario, estado civil, exct... - A/]
    D --> KB
    KB --> Ñ
    Ñ --> XE
    Ñ --> H((  ))

    C --> G[Captura]
    G --> H

    H --> I[/Cálculo de la planilla - B/]
    I --> QS
    I --> QR
    I --> QE

    QR --> KA
    QE --> KA

    KA --> M
    KA --> N

    M --> O([Fin])
    N --> O
```

### A

Datos del personal deben ser capturas; nombre completo, salario base, cédula, estado civil, cargo, año de inicio de labores
P- reporte grupal por colaboradores E\_ Reporte individual de los colaboradores

### B

Al capturar la planilla se debe buscar los datos de los colaboradores en su almacenamiento. Salario base, otros ingresos, salario total, seguro social, seguro educativo, impuesto sobre la renta, otros ingresos, total de descuentos y salario neto a pagar.

### C

Se debe visualizar el reporte de planillas

**NOTA: Todos reportes tiene que tener opción para imprimir y enviar por correo.**

## DATOS A DESARROLLAR LAS PLANILLAS:

Instructivo: La empresa a desarrollar para los cuatro grupos es igual; la variación corresponde a las formas de pago, las cuales se especifica por grupo

**NOTA, ESTA INFORMACIÓN APLICA PARA TODOS LOS GRUPOS.**

La empresa la PROSPERA, S.A. de la fábrica de Cemento, ubicada en Juan Díaz, calle 200, tiene un horario de 8 am a 5 pm los días de semana y 5 horas los días sábado (laborando 45 horas semanales), y le corresponde un riesgo profesional del 0.56%
Planilla de la Segunda quincena de junio de 2015.
Para este mes todos los colaboradores tienen una Bonificación del 10% de su salario

### INFORMACIÓN ESPECÍFICA POR GRUPO.

#### Grupo #1

Rubén Palacios; (asistente de gerencia) casado, cedula 4-590-678; tiene un salario de $900 mensuales; para ésta quincena laboró 2 horas extras seguidas el día sábado 20 y 27, y el miércoles 24; 3 horas extra más. Tiene un descuento de mueblería de $200 por mes.
Alejandro Mirones; (supervisora de planta) soltero con cédula 10-400-390; tiene un salario de $680 por mes para esta quincena laboró 3 horas extra el día 23 y 4 horas el día 30. Tiene un adelanto de $60 y un descuento de mueblería de 120 por mes.
Jairo Fernández; (aseador) soltero, cédula 5-789-352; tiene un salario base mensual, para ésta quincena laboró 3 horas extra el lunes 22 y 2 horas el día 25, tiene un descuento de ahorro en la empresa de $50 por mes.

#### Grupo #2 - Pago por comisiones corresponde al 2$ de las ventas)

Cesar García, (vendedor de calle) casado, cedula 4-590-678; tiene un salario de $650 mensuales; más comisiones, para ésta quincena, tiene una venta de $35,000 .Tiene un descuento de mueblería de $250 por mes
Amanda Iglesias; (vendedor de agencia) soltera con cédula 10-400-390; tiene un salario base (salario mínimo), más comisión sobre las ventas de $60,000, Tiene un adelanto de $50 y un descuento de mueblería de 220 por mes.
Vladimir Cáceres (vendedor supervisor) soltero, cédula 5-789-352; tiene un salario de $800, sus ventas para este período son de $55,000, tiene un descuento de ahorro en la empresa de $50 por mes.

#### Grupo #3 - Salarios más dietas

Rubén Blades; (asistente de gerencia) casado, cedula 4-590-678; tiene un salario de $1,200 mensuales; para ésta quincena participo de un congreso en Provincias centrales, sólo se le pago dietas por $3,000. Tiene un descuento de mueblería de $300 por mes.
Alejandro Fernández; (supervisora de planta) soltero con cédula 10-400-390; tiene un salario de $1,000 por mes participo en una capacitación en Costa Rica; se le pago dieta por $5,000 Tiene un descuento de mueblería de 500 por mes.
Vicente Fernández; (analista de recursos humanos) soltero, cédula 5-789-352; tiene un salario de $980 mensual, para ésta quincena recibió dietas por reuniones de por $700, tiene un descuento de ahorro en la empresa de $250 por mes.

#### Grupo #4 - Salario más prima de producción

José González, (Reparador de calle) casado, cedula 4-590-678; tiene un salario de $690 mensuales; tiene una prima de producción $600, para ésta quincena, tiene una venta de $35,000 .Tiene un descuento de mueblería de $250 por mes
Julio Iglesias; (Supervisor de Planta) soltero con cédula 10-400-390; tiene un salario $ 800, tiene una prima de productiva del 2% sobre la producción de 150,000, Tiene un adelanto de $250 y un descuento de mueblería de 320 por mes.
Mariano Ramos; (Analista supervisor) soltero, cédula 5-789-352; tiene un salario de $900, su prima de producción para este período es del 2% de $55,000, tiene un descuento de ahorro en la empresa de $200 por mes.
