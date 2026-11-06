<?php
declare(strict_types=1);

class ServicioVenta
{
    public static function validarCantidad(int $cantidad): bool
    {
        return $cantidad > 0;
    }

    public static function hayStock(int $cantidad, int $stock): bool
    {
        return $cantidad > 0 && $stock >= $cantidad;
    }

    public static function calcularSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            if (
                !is_array($item)
                || !isset($item['cantidad'], $item['precio'])
                || !is_int($item['cantidad'])
                || !is_numeric($item['precio'])
            ) {
                throw new InvalidArgumentException('Cada producto debe tener cantidad y precio válidos.');
            }

            $cantidad = $item['cantidad'];
            $precio = (float) $item['precio'];
            if (!self::validarCantidad($cantidad) || !is_finite($precio) || $precio <= 0) {
                throw new InvalidArgumentException('La cantidad o el precio del producto no son válidos.');
            }

            $subtotal += $cantidad * $precio;
        }

        return round($subtotal, 2);
    }

    public static function calcularDescuento(float $subtotal): float
    {
        if (!is_finite($subtotal) || $subtotal < 0) {
            throw new InvalidArgumentException('El subtotal no es válido.');
        }

        return $subtotal > 100.00 ? round($subtotal * 0.05, 2) : 0.0;
    }

    public static function calcularTotal(float $subtotal): float
    {
        return round($subtotal - self::calcularDescuento($subtotal), 2);
    }
}
