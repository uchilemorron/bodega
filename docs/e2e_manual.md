# Guion E2E manual — Registrar venta

**URL inicial:** `http://localhost/bodega/`  
**Precondición:** MySQL y Laragon iniciados. Confirmar al comenzar que el inventario muestra estos stocks: Arroz extra Costeño 28, Aceite vegetal Primor 18, Leche evaporada Gloria 0, Azúcar rubia 27, Atún en agua Florida 12 y Gaseosa Inca Kola 20.  
**Uso:** ejecutar los escenarios en orden y marcar «pasa» o «falla» en cada paso después de observarlo.

**Nota de ejecución:** antes de ejecutar el guion se hicieron ventas exploratorias (venta #3), por lo que el stock inicial real de Atún (2) y Gaseosa (10) difería de la precondición. Se verificó la regla y la variación de stock, no el número absoluto.

| paso | acción exacta en pantalla | dato a ingresar | resultado esperado visible | resultado obtenido (pasa/falla) |
|---|---|---|---|---|
| E1-1 | En `index.php`, confirmar los stocks iniciales y hacer clic en **Registrar venta**. | Ninguno | Se abre la pantalla **Registrar venta**. | Pasa |
| E1-2 | En **Cliente**, reemplazar el valor por defecto. En la primera fila, abrir **Producto**, elegir **Arroz extra Costeño 1 kg** y completar **Cantidad**. | Cliente: `Cliente E2E`; Arroz extra Costeño: `10` | Quedan visibles el cliente indicado, Arroz extra Costeño y cantidad 10. | Pasa |
| E1-3 | Hacer clic en **Agregar otro producto**. En la nueva fila, elegir **Gaseosa Inca Kola 1.5 L** y completar **Cantidad**. | Gaseosa Inca Kola: `10` | Se ven dos líneas: 10 Arroz extra Costeño y 10 Gaseosa Inca Kola. | Pasa |
| E1-4 | Hacer clic en **Confirmar venta**. | Ninguno | Se muestra venta registrada con subtotal **S/ 134,00**, descuento **S/ 6,70** y total **S/ 127,30**. | Pasa (venta #4): subtotal 134,00, descuento 6,70, total 127,30 |
| E1-5 | Hacer clic en **Ver inventario** y revisar las filas de Arroz extra Costeño y Gaseosa Inca Kola. | Ninguno | En `index.php`: Arroz extra Costeño **18 unidades** y Gaseosa Inca Kola **10 unidades**. | Pasa: Arroz 28 → 18; Gaseosa bajó 10 unidades (10 → 0, por la venta exploratoria previa) |
| E2-1 | En `index.php`, hacer clic en **Registrar venta**. | Ninguno | Se abre la pantalla **Registrar venta**. | Pasa |
| E2-2 | En **Cliente**, ingresar un nombre; en **Producto**, elegir **Atún en agua Florida 170 g**; en **Cantidad**, ingresar 13. | Cliente: `Cliente E2E`; Atún: `13` (stock inicial 12) | Quedan visibles Atún y cantidad 13. | Pasa (stock real del Atún: 2) |
| E2-3 | Hacer clic en **Confirmar venta**. | Ninguno | La venta se rechaza y aparece un mensaje de error indicando que la cantidad excede el stock disponible del producto **Atún en agua Florida 170 g**. | Pasa: mensaje de stock insuficiente |
| E2-4 | Regresar a `index.php` con **Ver inventario** si aparece, o abrir `http://localhost/bodega/`; revisar la fila de Atún. | Ninguno | Atún en agua Florida permanece en **12 unidades**. | Pasa: el Atún permanece sin cambio (2 unidades) |
| E3-1 | En `index.php`, hacer clic en **Registrar venta**. | Ninguno | Se abre la pantalla **Registrar venta**. | Pasa |
| E3-2 | En **Cliente**, ingresar un nombre; en **Producto**, elegir **Azúcar rubia 1 kg**; en **Cantidad**, ingresar 0 e intentar confirmar la venta. | Cliente: `Cliente E2E`; Azúcar rubia: `0` | La venta se rechaza: se muestra un error de cantidad o el navegador impide enviar el formulario por el mínimo 1. | Pasa: el navegador bloquea el envío («El valor debe ser mayor de o igual a 1»); el servidor también lo rechaza (unitaria CP-08) |
| E3-3 | Regresar a `index.php` con **Ver inventario** si aparece, o abrir `http://localhost/bodega/`; revisar la fila de Azúcar. | Ninguno | Azúcar rubia permanece en **27 unidades**. | Pasa: Azúcar sigue en 27 |

**Resumen:** 3 de 3 escenarios pasan (E1 · R2/R4, E2 · R1, E3 · R3).