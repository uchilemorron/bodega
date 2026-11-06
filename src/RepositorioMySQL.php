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
        $cliente = ServicioVenta::validarCliente($cliente);
        $items = ServicioVenta::normalizarItems($items);

        try {
            $this->conexion->beginTransaction();

            $consultaProducto = $this->conexion->prepare(
                'SELECT id, nombre, precio, stock FROM producto WHERE id = ? FOR UPDATE'
            );
            $productos = [];
            foreach ($items as $item) {
                $id = $item['producto_id'];
                $consultaProducto->execute([$id]);
                $producto = $consultaProducto->fetch();
                if ($producto !== false) {
                    $productos[$id] = $producto;
                }
            }
            $itemsConPrecio = ServicioVenta::validarStock($items, $productos);

            $actualizarStock = $this->conexion->prepare(
                'UPDATE producto SET stock = stock - ? WHERE id = ? AND stock >= ?'
            );
            foreach ($itemsConPrecio as $item) {
                $id = $item['producto_id'];
                $cantidad = $item['cantidad'];
                $actualizarStock->execute([$cantidad, $id, $cantidad]);
                ServicioVenta::validarReduccionStock(
                    $actualizarStock->rowCount(),
                    $productos[$id]['nombre']
                );
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
                $importe = ServicioVenta::calcularImporte($item['cantidad'], $item['precio']);
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
