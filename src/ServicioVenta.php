<?php
declare(strict_types=1);

class ServicioVenta
{
    public const UMBRAL_DESCUENTO = 100.00;
    public const TASA_DESCUENTO = 0.05;

    public static function validarCantidad(int $cantidad): bool
    {
        return $cantidad > 0;
    }

    public static function validarCliente(string $cliente): string
    {
        $cliente = trim($cliente);
        if ($cliente === '') {
            throw new DomainException('Ingresa el nombre del cliente.');
        }

        return $cliente;
    }

    public static function normalizarItems(
        array $items,
        string $mensajeDatosInvalidos = 'El producto o la cantidad ingresada no son válidos.',
        string $mensajeCantidadInvalida = 'El producto o la cantidad ingresada no son válidos.',
        string $mensajeSinItems = 'Agrega al menos un producto a la venta.'
    ): array {
        if ($items === []) {
            throw new DomainException($mensajeSinItems);
        }

        $cantidades = [];

        foreach ($items as $item) {
            if (
                !is_array($item)
                || !isset($item['producto_id'], $item['cantidad'])
                || (!is_string($item['producto_id']) && !is_int($item['producto_id']))
                || !preg_match('/\A[1-9][0-9]*\z/', (string) $item['producto_id'])
            ) {
                throw new DomainException($mensajeDatosInvalidos);
            }

            $id = filter_var($item['producto_id'], FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new DomainException($mensajeDatosInvalidos);
            }

            if (
                (!is_string($item['cantidad']) && !is_int($item['cantidad']))
                || !preg_match('/\A[0-9]+\z/', (string) $item['cantidad'])
            ) {
                throw new DomainException($mensajeCantidadInvalida);
            }

            $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
            if ($cantidad === false || !self::validarCantidad($cantidad)) {
                throw new DomainException($mensajeCantidadInvalida);
            }

            if (($cantidades[$id] ?? 0) > PHP_INT_MAX - $cantidad) {
                throw new DomainException('La cantidad ingresada es demasiado grande.');
            }
            $cantidades[$id] = ($cantidades[$id] ?? 0) + $cantidad;
        }

        ksort($cantidades, SORT_NUMERIC);
        $itemsNormalizados = [];
        foreach ($cantidades as $id => $cantidad) {
            $itemsNormalizados[] = [
                'producto_id' => (int) $id,
                'cantidad' => $cantidad,
            ];
        }

        return $itemsNormalizados;
    }

    public static function hayStock(int $cantidad, int $stock): bool
    {
        return $cantidad > 0 && $stock >= $cantidad;
    }

    public static function validarStock(
        array $items,
        array $productos,
        string $mensajeProductoNoEncontrado = 'Uno de los productos seleccionados ya no está disponible.'
    ): array {
        $itemsConPrecio = [];

        foreach ($items as $item) {
            $id = $item['producto_id'];
            $producto = $productos[$id] ?? null;
            if ($producto === null) {
                throw new DomainException($mensajeProductoNoEncontrado);
            }

            if (!self::hayStock($item['cantidad'], (int) $producto['stock'])) {
                throw new DomainException(
                    'La cantidad excede el stock disponible del producto ' . $producto['nombre'] . '.'
                );
            }

            $itemsConPrecio[] = [
                'producto_id' => $id,
                'cantidad' => $item['cantidad'],
                'precio' => (float) $producto['precio'],
            ];
        }

        return $itemsConPrecio;
    }

    public static function validarReduccionStock(int $filasAfectadas, string $nombreProducto): void
    {
        if ($filasAfectadas !== 1) {
            throw new DomainException(
                'La cantidad excede el stock disponible del producto ' . $nombreProducto . '.'
            );
        }
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

    public static function calcularImporte(int $cantidad, float $precio): float
    {
        if (!self::validarCantidad($cantidad) || !is_finite($precio) || $precio <= 0) {
            throw new InvalidArgumentException('La cantidad o el precio del producto no son válidos.');
        }

        return round($cantidad * $precio, 2);
    }

    public static function calcularDescuento(float $subtotal): float
    {
        if (!is_finite($subtotal) || $subtotal < 0) {
            throw new InvalidArgumentException('El subtotal no es válido.');
        }

        if ($subtotal > self::UMBRAL_DESCUENTO) {
            return round($subtotal * self::TASA_DESCUENTO, 2);
        }
        return 0.0;
    }

    public static function calcularTotal(float $subtotal): float
    {
        return round($subtotal - self::calcularDescuento($subtotal), 2);
    }
}
