# Análisis de fallo (T7)

## Defecto plantado

En `src/ServicioVenta.php`, método `calcularDescuento`, se cambió temporalmente la condición `>` por `>=`:

    if ($subtotal >= self::UMBRAL_DESCUENTO) { // defecto plantado

## Traza de PHPUnit

    There was 1 failure:
    1) ServicioVentaTest::testNoAplicaDescuentoAlSubtotalExactoDeCien
    Failed asserting that 5.0 matches expected 0.0.
    C:\laragon\www\bodega\tests\ServicioVentaTest.php:44
    FAILURES!
    Tests: 12, Assertions: 17, Failures: 1.

## Análisis generado por la IA (Copilot Agent, prompt T7)

**1. ¿Qué esperaba y qué obtuvo?**
El test esperaba un descuento de 0,0 para un subtotal exacto de S/ 100,00, pero obtuvo 5,0.

**2. Hipótesis, ordenadas por probabilidad**
1. Defecto en el código — muy probable. La condición `>=` aplica el descuento también al subtotal exacto de 100,00. Los requisitos indican que el descuento se aplica solo cuando el subtotal supera S/ 100,00.
2. Defecto en el test — poco probable. Sería posible si el comportamiento esperado fuera aplicar el descuento desde S/ 100,00, pero eso contradice los requisitos.
3. Defecto en el dato — poco probable. El subtotal real usado por el test podría ser mayor que 100,00 por sus entradas, aunque el nombre del test sugiere que usa exactamente 100,00.

**3. Cómo verificar cada hipótesis en un minuto**
1. Ejecutar `ServicioVenta::calcularDescuento(100.00)`: devuelve `5.0`, lo que confirma que el código aplica el umbral de forma inclusiva. Contrastar con R2 en `requirements.md`: solo corresponde descuento si el subtotal es mayor que 100,00.
2. Revisar el caso fallido en `ServicioVentaTest.php`: para `100.00` espera `0.00`, lo que coincide con R2. El test no es el defecto.
3. Calcular `ServicioVenta::calcularSubtotal([['cantidad' => 1, 'precio' => 100.00]])`: da exactamente `100.00`. El dato no explica el fallo.

**4. Cambio mínimo**
Cambiar `if ($subtotal >= self::UMBRAL_DESCUENTO)` por `if ($subtotal > self::UMBRAL_DESCUENTO)`.

## Decisión del ingeniero

Hipótesis correcta: **defecto en el código**. La regla R2 dice: «Si el subtotal de la venta es mayor a S/ 100,00 se aplica 5 % de descuento sobre el subtotal. Si es exactamente S/ 100,00 no hay descuento». Por eso la comparación debe ser estricta (`>`), no `>=`. El test está bien: su valor esperado (0.0) coincide literalmente con R2. **No se modifica el test; se corrige el código.**

## Resultado tras la corrección

Se restauró `>` en `calcularDescuento` y se ejecutó la suite completa: `OK (14 tests, 25 assertions)`.