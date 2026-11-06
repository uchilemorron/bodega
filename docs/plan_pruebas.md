# Plan de pruebas — Registro de ventas Bodega «Doña Rosa»

## 1. Objetivo y alcance

**Objetivo:** comprobar que el registro de ventas respeta las reglas de negocio de disponibilidad de stock (R1), descuento por volumen (R2), validación de cantidades (R3) y actualización atómica del inventario (R4), conforme a [requirements.md](../requirements.md) y [design.md](../design.md).

**Módulos incluidos en esta ejecución:**

- `src/ServicioVenta.php`: validaciones de cantidades y stock, subtotal, descuento y total.
- `src/RepositorioMySQL.php`: persistencia de venta y detalles, reducción de stock y reversión transaccional.
- `venta.php` y `guardar_venta.php`: formulario y flujo visible de confirmación o rechazo de la venta.

**Fuera del alcance de esta ejecución:** registro de productos, consulta independiente del inventario, autenticación, reportes y facturación electrónica. La aplicación no incluye autenticación, reportes ni facturación electrónica según los requisitos. No se agregan reglas de negocio distintas de R1–R4.

## 2. Cobertura por regla de negocio

| Regla | Nivel de prueba | Técnica | Datos de prueba | Criterio de aceptación |
|---|---|---|---|---|
| **R1 — Disponibilidad de stock** (requirements: 3.4–3.5 y 5.3) | Unitaria, integración y E2E manual | Valores límite y tabla de decisión: cantidad menor, igual y mayor que el stock. | Para un producto con stock 5, intentar vender 4, 5 y 6 unidades; incluir también un producto con stock 0. En E2E, seleccionar el producto y enviar cada cantidad. | Se acepta cantidad menor o igual al stock; al igualarlo, el stock queda en 0. Si se supera el stock, se rechaza la venta completa, se muestra un error de stock y no se modifica ningún producto de esa venta. |
| **R2 — Descuento por volumen** (requirements: 4.1–4.3) | Unitaria, integración y E2E manual | Valores límite alrededor de S/ 100,00. | Subtotal 99,99 → descuento 0,00, total 99,99; subtotal 100,00 → descuento 0,00, total 100,00; subtotal 100,01 → descuento 5,00, total 95,01; subtotal 200,00 → descuento 10,00, total 190,00. | Con subtotal ≤ S/ 100,00 el descuento es 0,00; con subtotal > S/ 100,00 se aplica 5 %. El total es subtotal menos descuento, con dos decimales. |
| **R3 — Cantidad entera positiva** (requirements: 3.2–3.3) | Unitaria, integración y E2E manual | Partición de equivalencia y valores límite: enteros positivos (válido), cero, negativos y decimales (inválidos). | Cantidad 1 → válida; cantidad 0 → rechazo; cantidad -1 → rechazo; cantidad 1,5 → rechazo. Enviar también valores inválidos manipulando el POST, dado que un campo HTML numérico no sustituye la validación del servidor. | Solo se acepta un entero mayor que cero. Las cantidades 0, -1 y 1,5 rechazan la venta y muestran que la cantidad debe ser un entero mayor que cero. |
| **R4 — Actualización atómica del stock** (requirements: 3.6 y 5.1–5.2) | Integración y E2E manual | Tabla de decisión: todas las escrituras tienen éxito / falla una escritura de la transacción. | Venta con más de un producto disponible; además, inducir un fallo de persistencia durante una transacción de prueba en `bodega_test` y consultar después ventas, detalles y stock. | En éxito, se guardan la venta y sus detalles y se descuenta cada cantidad en una transacción. Si falla la transacción, no queda confirmada la venta y se revierten todas las reducciones de stock; se informa que falló la actualización del stock. |

## 3. Niveles que se ejecutarán hoy

1. **Pruebas unitarias con PHPUnit sobre `ServicioVenta`:** probar R1, R2 y R3 con casos representativos y límites. No requieren conexión a MySQL.
2. **Pruebas de integración de `RepositorioMySQL` con la base `bodega_test`:** verificar persistencia de ventas/detalles, reducción de stock, caso de stock exacto y reversión transaccional. Usar datos aislados de la base de pruebas; no ejecutar limpieza ni fixtures contra la base `bodega`.
3. **Prueba E2E manual sobre `venta.php`:** completar y enviar ventas válidas e inválidas en el navegador; observar mensajes, importes y stock resultante en el inventario.

## 4. Criterio de salida

La versión queda lista para desplegar cuando se cumplen **todas** estas condiciones medibles:

- **100 %** de las pruebas unitarias (`--testsuite unitarias`) en verde, con **≥ 10 pruebas**.
- **100 %** de las pruebas de integración (`--testsuite integracion`) en verde sobre `bodega_test`, con la base `bodega` sin modificar.
- **3 de 3** escenarios E2E manuales marcados como "pasa": venta válida con descuento, stock insuficiente y cantidad 0.
- **0 defectos críticos abiertos** (un defecto es crítico si incumple R1, R2, R3 o R4).
- **0 hallazgos OWASP confirmados** sin corregir.
