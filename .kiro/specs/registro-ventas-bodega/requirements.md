# Requirements Document

## Introduction

El presente documento describe los requisitos funcionales del sistema de registro de ventas de la bodega «Doña Rosa», ubicada en San Vicente de Cañete. La aplicación permite a quien atiende la bodega registrar productos, consultar el stock, registrar ventas, aplicar descuentos por volumen y mantener el control del inventario actualizado en cada venta. El sistema se ejecuta en una sola computadora del local, sobre PHP 8 + MySQL, sin framework y sin librerías externas salvo PHPUnit. Esta versión no incluye autenticación de usuarios, reportes ni facturación electrónica.

## Glossary

- **Sistema_Bodega**: Aplicación de registro de ventas de la bodega «Doña Rosa».
- **Producto**: Mercancía registrada en el inventario, con nombre, precio unitario y stock.
- **Stock**: Cantidad disponible de un producto en el inventario.
- **Venta**: Transacción que registra uno o más productos con sus cantidades.
- **Subtotal**: Suma del precio unitario multiplicado por la cantidad de cada producto en la venta.
- **Descuento**: Reducción del 5 % aplicada sobre el subtotal cuando este supera S/ 100,00.
- **Total**: Importe final de la venta, calculado como el subtotal menos el descuento aplicado.

## Requirements

### Requirement 1: Registrar productos

**User Story:** Como atendiente de la bodega, quiero registrar productos para mantener un inventario de las mercancías disponibles.

#### Acceptance Criteria

1. THE Sistema_Bodega SHALL permitir registrar un Producto con nombre, precio unitario y stock inicial, donde el nombre no sea vacío, el precio unitario sea mayor que cero y el stock inicial sea un entero mayor o igual a cero.
2. WHEN se registra un Producto con datos válidos, THE Sistema_Bodega SHALL almacenar el nombre, el precio unitario y el stock en la base de datos y confirmar que el Producto está disponible en el inventario.
3. IF se intenta registrar un Producto sin nombre, con precio unitario menor o igual a cero o con más de dos decimales, o con stock inicial que no sea un entero mayor o igual a cero, THEN THE Sistema_Bodega SHALL rechazar el registro, indicar el campo inválido, mostrar un mensaje de error y no almacenar el Producto en la base de datos.

### Requirement 2: Consultar stock

**User Story:** Como atendiente de la bodega, quiero consultar el stock disponible de cada producto para saber qué hay para vender.

#### Acceptance Criteria

1. WHEN se solicita el listado de productos, THE Sistema_Bodega SHALL mostrar el nombre, el precio unitario y el stock actual de cada producto, incluyendo aquellos cuyo stock es cero.
2. THE Sistema_Bodega SHALL mostrar el stock actual de cada Producto en unidades enteras.
3. IF no hay Productos registrados en el inventario, THEN THE Sistema_Bodega SHALL mostrar un mensaje indicando que no hay productos registrados.

### Requirement 3: Registrar ventas

**User Story:** Como atendiente de la bodega, quiero registrar las ventas con sus cantidades para llevar el control de lo vendido.

#### Acceptance Criteria

1. THE Sistema_Bodega SHALL permitir registrar una Venta con uno o más Productos y sus cantidades, y almacenar la Venta con los Productos y sus cantidades ingresadas.
2. WHEN se ingresa la cantidad de un producto, THE Sistema_Bodega SHALL validar que la cantidad sea un entero mayor que cero (R3).
3. IF la cantidad es cero, negativa o decimal, THEN THE Sistema_Bodega SHALL rechazar la Venta y mostrar un mensaje de error indicando que la cantidad debe ser un entero mayor que cero (R3).
4. IF la cantidad vendida de cualquier Producto supera el Stock disponible de ese Producto, THEN THE Sistema_Bodega SHALL rechazar la Venta completa y mostrar un mensaje de error indicando que la cantidad excede el Stock disponible (R1).
5. IF la cantidad vendida iguala el Stock disponible del Producto, THEN THE Sistema_Bodega SHALL aceptar la Venta y dejar el Stock del Producto en cero (R1).
6. WHEN se confirma una Venta cuyas cantidades son enteros mayores que cero y no superan el Stock disponible del Producto correspondiente, THE Sistema_Bodega SHALL reducir el Stock de cada Producto en la cantidad vendida, en una misma transacción (R4).

### Requirement 4: Aplicar descuento por volumen

**User Story:** Como atendiente de la bodega, quiero que se aplique automáticamente un descuento por volumen para evitar errores en el cálculo.

#### Acceptance Criteria

1. IF el subtotal de la Venta es mayor a S/ 100,00, THEN THE Sistema_Bodega SHALL aplicar un descuento del 5 % sobre el subtotal (R2).
2. IF el subtotal de la Venta es menor o igual a S/ 100,00, THEN THE Sistema_Bodega SHALL calcular el total igual al subtotal sin aplicar descuento (R2).
3. THE Sistema_Bodega SHALL calcular el total de la Venta como el subtotal menos el descuento aplicado, expresado con dos decimales.

### Requirement 5: Confirmar venta y descontar stock

**User Story:** Como atendiente de la bodega, quiero que al confirmar la venta el stock se descuente automáticamente para mantener el control del inventario.

#### Acceptance Criteria

1. WHEN se confirma la venta, THE Sistema_Bodega SHALL reducir el stock de cada Producto en la cantidad vendida dentro de la misma transacción, incluyendo el caso en que la cantidad vendida sea igual al stock disponible del Producto, en cuyo caso el stock queda en 0 (R4).
2. IF la transacción de actualización de stock falla, THEN THE Sistema_Bodega SHALL revertir las reducciones de stock aplicadas a todos los Productos de la Venta, no confirmar la Venta y mostrar un mensaje de error que indique que la actualización de stock falló.
3. IF al confirmar la Venta la cantidad vendida de un Producto excede el stock disponible de ese Producto, THEN THE Sistema_Bodega SHALL rechazar la confirmación de la Venta, no reducir el stock de ningún Producto y mostrar un mensaje de error que indique stock insuficiente (R1).
