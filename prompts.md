# Registro de prompts

| N.° | Prompt | Herramienta | Decisión del ingeniero |
|-----|--------|-------------|------------------------|
| P1  | Redacción de D0.md | IA (autorizado por la docente) | Revisado: las reglas R1–R4 se copiaron literalmente de la guía |
| P2  | Requisitos (requirements.md) | Kiro (Spec) | Aceptado: 5 historias, criterios EARS. Corrección manual: se agregó la cita (R4) en 5.1 y (R1) en 5.3 |
| P3  | Diseño (design.md) | Kiro (Spec) | Aceptado: 3 capas, 3 tablas, diagrama Mermaid R1–R4. Kiro agregó RepositorioInterface para probar sin BD; se mantiene |
| P4  | Script SQL (sql/bodega.sql) | Copilot Agent | Aceptado: 3 tablas con FK, 6 productos, Leche con stock 0 (para probar R1). Se reimportó con utf8mb4 por las tildes |
| P5  | Código de la app | Copilot Agent | Probado: lista 6 productos, venta #1 de 2 Arroz (S/ 9,00 sin descuento), stock 35→33. El 0.05 está en ServicioVenta.php |