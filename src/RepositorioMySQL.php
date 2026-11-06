<?php
declare(strict_types=1);

require_once __DIR__ . '/ServicioVenta.php';

class RepositorioMySQL
{
    public function __construct(private PDO $conexion)
    {
    }

    public function listarProductos(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id, nombre, precio, stock FROM producto ORDER BY nombre'
        );
        $consulta->execute();

        return $consulta->fetchAll();
    }

    public function obtenerProducto(int $id): ?array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id, nombre, precio, stock FROM producto WHERE id = ?'
        );
        $consulta->execute([$id]);
        $producto = $consulta->fetch();

        return $producto === false ? null : $producto;
    }

    public function registrarVenta(string $cliente, array $items): int
    {
        $cliente = trim($cliente);
        if ($cliente === '') {
            throw new DomainException('Ingresa el nombre del cliente.');
        }
        if ($items === []) {
            throw new DomainException('Agrega al menos un producto a la venta.');
        }

        $cantidades = [];
        foreach ($items as $item) {
            if (
                !is_array($item)
                || !isset($item['producto_id'], $item['cantidad'])
                || !is_int($item['producto_id'])
                || !is_int($item['cantidad'])
                || $item['producto_id'] <= 0
                || !ServicioVenta::validarCantidad($item['cantidad'])
            ) {
                throw new DomainException('El producto o la cantidad ingresada no son válidos.');
            }

            $id = $item['producto_id'];
            $cantidad = $item['cantidad'];
            if (($cantidades[$id] ?? 0) > PHP_INT_MAX - $cantidad) {
                throw new DomainException('La cantidad ingresada es demasiado grande.');
            }
            $cantidades[$id] = ($cantidades[$id] ?? 0) + $cantidad;
        }
        ksort($cantidades, SORT_NUMERIC);

        try {
            $this->conexion->beginTransaction();

            $consultaProducto = $this->conexion->prepare(
                'SELECT id, nombre, precio, stock FROM producto WHERE id = ? FOR UPDATE'
            );
            $productos = [];
            foreach ($cantidades as $id => $cantidad) {
                $consultaProducto->execute([$id]);
                $producto = $consultaProducto->fetch();
                if ($producto === false) {
                    throw new DomainException('Uno de los productos seleccionados ya no está disponible.');
                }
                if (!ServicioVenta::hayStock($cantidad, (int) $producto['stock'])) {
                    throw new DomainException(
                        'La cantidad excede el stock disponible del producto ' . $producto['nombre'] . '.'
                    );
                }
                $productos[$id] = $producto;
            }

            $actualizarStock = $this->conexion->prepare(
                'UPDATE producto SET stock = stock - ? WHERE id = ? AND stock >= ?'
            );
            foreach ($cantidades as $id => $cantidad) {
                $actualizarStock->execute([$cantidad, $id, $cantidad]);
                if ($actualizarStock->rowCount() !== 1) {
                    throw new DomainException(
                        'La cantidad excede el stock disponible del producto ' . $productos[$id]['nombre'] . '.'
                    );
                }
            }

            $itemsConPrecio = [];
            foreach ($cantidades as $id => $cantidad) {
                $itemsConPrecio[] = [
                    'producto_id' => $id,
                    'cantidad' => $cantidad,
                    'precio' => (float) $productos[$id]['precio'],
                ];
            }
            $subtotal = ServicioVenta::calcularSubtotal($itemsConPrecio);
            $descuento = ServicioVenta::calcularDescuento($subtotal);
            $total = ServicioVenta::calcularTotal($subtotal);

            $insertarVenta = $this->conexion->prepare(
                'INSERT INTO venta (cliente, subtotal, descuento, total) VALUES (?, ?, ?, ?)'
            );
            $insertarVenta->execute([$cliente, $subtotal, $descuento, $total]);
            $ventaId = (int) $this->conexion->lastInsertId();

            $insertarDetalle = $this->conexion->prepare(
                'INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, importe)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($itemsConPrecio as $item) {
                $importe = round($item['cantidad'] * $item['precio'], 2);
                $insertarDetalle->execute([
                    $ventaId,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['precio'],
                    $importe,
                ]);
            }

            $this->conexion->commit();

            return $ventaId;
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            if ($error instanceof DomainException) {
                throw $error;
            }

            throw new RuntimeException(
                'La actualización de stock falló; la venta fue revertida.',
                0,
                $error
            );
        }
    }
}
