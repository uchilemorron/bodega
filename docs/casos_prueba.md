# Casos de prueba — Registro de ventas

Los resultados esperados se expresan como valores y estados persistidos. En los casos de rechazo, se comprueba que la venta no se confirma. Los casos R4 se ejecutan contra `bodega_test`.

| ID | regla | técnica | entrada exacta | resultado esperado | nivel |
|---|---|---|---|---|---|
| CP-01 | R1 — disponibilidad de stock | Valor límite: cantidad igual al stock | `hayStock(cantidad=10, stock=10)` | `true` (válida; en BD el stock final queda en 0) | Unitaria |
| CP-02 | R1 — disponibilidad de stock | Valor límite: uno por debajo del stock | `hayStock(cantidad=9, stock=10)` | `true` | Unitaria |
| CP-03 | R1 — disponibilidad de stock | Valor límite: uno por encima del stock | `hayStock(cantidad=11, stock=10)` | `false` (rechazo) | Unitaria |
| CP-04 | R1 — disponibilidad de stock | Valor límite: producto agotado | `hayStock(cantidad=1, stock=0)` | `false` (rechazo) | Unitaria |
| CP-05 | R2 — descuento por volumen | Valor límite: uno por debajo de S/ 100,00 | `items=[{producto_id:1, precio:99.99, cantidad:1}]` | `subtotal=99.99; descuento=0.00; total=99.99` | Unitaria |
| CP-06 | R2 — descuento por volumen | Valor límite: umbral exacto | `items=[{producto_id:1, precio:100.00, cantidad:1}]` | `subtotal=100.00; descuento=0.00; total=100.00` | Unitaria |
| CP-07 | R2 — descuento por volumen | Valor límite: uno por encima de S/ 100,00 | `items=[{producto_id:1, precio:100.01, cantidad:1}]` | `subtotal=100.01; descuento=5.00; total=95.01` | Unitaria |
| CP-08 | R3 — cantidad entera positiva | Valor límite: cero | `validarCantidad(0)` | `false` (rechazo) | Unitaria |
| CP-09 | R3 — cantidad entera positiva | Partición de equivalencia: entero negativo | `validarCantidad(-1)` | `false` (rechazo) | Unitaria |
| CP-10 | R3 — cantidad entera positiva | Partición de equivalencia: valor decimal | `normalizarItems([{producto_id:1, cantidad:"1.5"}])` | rechazo (`DomainException`) | Unitaria |
| CP-11 | R3 — cantidad entera positiva | Valor límite: mínimo entero válido | `validarCantidad(1)` | `true` | Unitaria |
| CP-12 | R3 — cantidad entera positiva | Valor límite: uno por encima del mínimo válido | `validarCantidad(2)` | `true` | Unitaria |
| CP-13 | R4 — actualización atómica del stock | Partición de equivalencia: transacción completada | `bodega_test; producto_id=1; stock inicial=10; cantidad=3` | `stock final=7; filas venta=1; filas detalle_venta=1` | Integración |
| CP-14 | R4 — actualización atómica del stock | Valor límite: cantidad menor que el stock | `bodega_test; producto_id=1; stock inicial=10; cantidad=9` | `stock final=1; filas venta=1; filas detalle_venta=1` | Integración |
| CP-15 | R4 — actualización atómica del stock | Valor límite: cantidad igual al stock | `bodega_test; producto_id=1; stock inicial=10; cantidad=10` | `stock final=0; filas venta=1; filas detalle_venta=1` | Integración |
| CP-16 | R4 — actualización atómica del stock | Valor límite: cantidad mayor que el stock | `bodega_test; producto_id=1; stock inicial=10; cantidad=11` | rechazo; `stock final=10; filas venta=0; filas detalle_venta=0` | Integración |
| CP-17 | R4 — actualización atómica del stock | Tabla de decisión: falla una escritura de la transacción | `bodega_test; producto_id=1; stock inicial=10; cantidad=3; inducir fallo de persistencia antes del commit` | rechazo; `stock final=10; filas venta=0; filas detalle_venta=0` | Integración |
| CP-18 | R3 — cantidad entera positiva | Valor límite: cero (vía formulario) | `venta.php: producto=Arroz, cantidad=0` | rechazo; mensaje de error; stock del Arroz sin cambio | E2E manual |
| CP-19 | R1 — disponibilidad de stock | Valor límite: stock + 1 (vía formulario) | `venta.php: producto=Atún (stock actual N), cantidad=N+1` | rechazo; mensaje de stock insuficiente; stock sin cambio | E2E manual |
| CP-20 | R2 — descuento por volumen | Valor por encima del umbral (vía formulario) | `venta.php: 2 líneas con subtotal > 100,00` | mensaje con descuento 5 %; stock reducido en index.php | E2E manual |