# Registro de prompts

| N.° | Prompt | Herramienta | Decisión del ingeniero |
|-----|--------|-------------|------------------------|
| P1  | Redacción de D0.md | IA (autorizado por la docente) | Revisado: las reglas R1–R4 se copiaron literalmente de la guía |
| P2  | Requisitos (requirements.md) | Kiro (Spec) | Aceptado: 5 historias, criterios EARS. Corrección manual: se agregó la cita (R4) en 5.1 y (R1) en 5.3 |
| P3  | Diseño (design.md) | Kiro (Spec) | Aceptado: 3 capas, 3 tablas, diagrama Mermaid R1–R4. Kiro agregó RepositorioInterface para probar sin BD; se mantiene |
| P4  | Script SQL (sql/bodega.sql) | Copilot Agent | Aceptado: 3 tablas con FK, 6 productos, Leche con stock 0 (para probar R1). Se reimportó con utf8mb4 por las tildes |
| P5  | Código de la app | Copilot Agent | Probado: lista 6 productos, venta #1 de 2 Arroz (S/ 9,00 sin descuento), stock 35→33. El 0.05 está en ServicioVenta.php |
| P6  | — | — | No existe en la guía (la numeración salta de P5 a P7) |
| P7  | Refactorización para testeabilidad | Copilot Agent | Verificado: sin echo/PDO/$_POST. Corrección manual: 0.05 y 100.00 movidos a constantes TASA_DESCUENTO y UMBRAL_DESCUENTO |
| T1  | Plan de pruebas (docs/plan_pruebas.md) | Copilot Agent | Aceptado: 4 filas R1–R4, R4 en integración. Corrección manual: criterio de salida medible (100 % verde, 0 defectos críticos), valores exactos en R2 y R3, enlaces a la raíz |
| T2  | Casos de prueba (docs/casos_prueba.md) | Copilot Agent | Aceptado: 8 casos obligatorios con valores exactos. Corrección manual: R1 y R3 reclasificados como Unitaria (hayStock/validarCantidad no requieren BD; la IA los había puesto en integración/E2E, dejando solo 5 unitarios); se añadieron CP-18 a CP-20 E2E |
| T3  | Suite unitaria (tests/ServicioVentaTest.php) | Copilot Agent | Aceptado: 12 tests, esperados literales, __DIR__ correcto. 12/12 en verde |
| T4  | Auditoría de la suite | Copilot Agent + revisión manual | Corrección manual: testAceptaCantidadDos (caso cómodo) reemplazado por testCalculaSubtotalConVariasLineasYCantidades (28.50); renombrado testCalculaImportes... a testNoAplicaDescuentoAlSubtotalDe9999 |
| T5  | Integración (tests/integracion/RegistrarVentaTest.php + sql/bodega_test.sql) | Copilot Agent | Verificado: 2 tests en verde sobre bodega_test (stock 10→7, 1 venta, 1 detalle; venta sin stock lanza excepción sin insertar). Base bodega sin cambios (capturas antes/después). Suite completa 14/14 |
| T6  | Guion E2E (docs/e2e_manual.md) | Copilot Agent + ejecución manual en pareja | 3/3 escenarios pasan: E1 descuento 6,70 (venta #4); E2 rechazo por stock; E3 cantidad 0 bloqueada. Se distinguió un error de ingreso de dato (venta #3) de un defecto de código |
| T7  | Análisis de fallo (docs/analisis_fallo.md) | Copilot Agent | Defecto plantado (>= en vez de >). Se eligió la hipótesis "defecto en el código"; no se modificó el test. Código restaurado, 14/14 en verde |