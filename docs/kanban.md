# Kanban: Proyecto Contabilidad

```mermaid
---
config:
  kanban:
    ticketBaseUrl: 'https://github.com/1997-bit/proyecto-contabilidad/issues/#TICKET#'
---
kanban
  Todo
    id1[Setup]@{ ticket: 1, assigned: '1997-bit', priority: 'Very High' }
    id2[Login y sesión básica en PHP]@{ ticket: 2, assigned: '1997-bit', priority: 'Very High' }
    id3[Captura de datos del personal]@{ ticket: 3, priority: 'Very High' }
    id4[Precarga de empleados del grupo asignado]@{ ticket: 4, priority: 'Low' }
    id5[Captura de planilla]@{ ticket: 5, priority: 'Very High' }
    id6[Motor de cálculo de planilla]@{ ticket: 6, priority: 'Very High' }
    id7[Deducciones del empleado]@{ ticket: 7, priority: 'Very High' }
    id8[Cargas patronales]@{ ticket: 8, priority: 'Very High' }
    id9[Pantalla de selección de reportes]@{ ticket: 9, priority: 'Very High' }
    id10[Reporte P: grupal de colaboradores]@{ ticket: 10 }
    id11[Reporte E: individual, expediente]@{ ticket: 11, priority: 'Very High' }
    id12[Reporte C: Caja de Seguro Social]@{ ticket: 12, priority: 'Very High' }
    id13[Imprimir y enviar por correo en todos los reportes]@{ ticket: 13, priority: 'Very High' }
    id14[Verificación final de números]@{ ticket: 14 }
```

---

## Resumen por Épica

| Épica           | Issues                 |
| --------------- | ---------------------- |
| EP-1: Setup     | #1, #2                 |
| EP-2: Empleados | #3, #4                 |
| EP-3: Planilla  | #5, #6, #7, #8         |
| EP-4: Reportes  | #9, #10, #11, #12, #13 |
