<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/ServicioVenta.php';

use PHPUnit\Framework\TestCase;

final class ServicioVentaTest extends TestCase
{
    public function testAceptaCantidadIgualAlStock(): void
    {
        self::assertSame(true, ServicioVenta::hayStock(10, 10));
    }

    public function testAceptaCantidadMenorAlStock(): void
    {
        self::assertSame(true, ServicioVenta::hayStock(9, 10));
    }

    public function testRechazaCantidadMayorAlStock(): void
    {
        self::assertSame(false, ServicioVenta::hayStock(11, 10));
    }

    public function testRechazaProductoSinStock(): void
    {
        self::assertSame(false, ServicioVenta::hayStock(1, 0));
    }

        public function testNoAplicaDescuentoAlSubtotalDe9999(): void
    {
        $items = [['cantidad' => 1, 'precio' => 99.99]];

        self::assertEqualsWithDelta(99.99, ServicioVenta::calcularSubtotal($items), 0.001);
        self::assertEqualsWithDelta(0.00, ServicioVenta::calcularDescuento(99.99), 0.001);
        self::assertEqualsWithDelta(99.99, ServicioVenta::calcularTotal(99.99), 0.001);
    }

    public function testNoAplicaDescuentoAlSubtotalExactoDeCien(): void
    {
        $items = [['cantidad' => 1, 'precio' => 100.00]];

        self::assertEqualsWithDelta(100.00, ServicioVenta::calcularSubtotal($items), 0.001);
        self::assertEqualsWithDelta(0.00, ServicioVenta::calcularDescuento(100.00), 0.001);
        self::assertEqualsWithDelta(100.00, ServicioVenta::calcularTotal(100.00), 0.001);
    }

    public function testAplicaDescuentoAlSubtotalPorEncimaDeCien(): void
    {
        $items = [['cantidad' => 1, 'precio' => 100.01]];

        self::assertEqualsWithDelta(100.01, ServicioVenta::calcularSubtotal($items), 0.001);
        self::assertEqualsWithDelta(5.00, ServicioVenta::calcularDescuento(100.01), 0.001);
        self::assertEqualsWithDelta(95.01, ServicioVenta::calcularTotal(100.01), 0.001);
    }

    public function testRechazaCantidadCero(): void
    {
        self::assertSame(false, ServicioVenta::validarCantidad(0));
    }

    public function testRechazaCantidadNegativa(): void
    {
        self::assertSame(false, ServicioVenta::validarCantidad(-1));
    }

    public function testRechazaCantidadDecimal(): void
    {
        $this->expectException(DomainException::class);

        ServicioVenta::normalizarItems([['producto_id' => 1, 'cantidad' => '1.5']]);
    }

    public function testAceptaCantidadMinimaDeUno(): void
    {
        self::assertSame(true, ServicioVenta::validarCantidad(1));
    }

        public function testCalculaSubtotalConVariasLineasYCantidades(): void
    {
        // 2 × 4.50 + 3 × 6.50 = 9.00 + 19.50 = 28.50
        $items = [
            ['cantidad' => 2, 'precio' => 4.50],
            ['cantidad' => 3, 'precio' => 6.50],
        ];

        self::assertEqualsWithDelta(28.50, ServicioVenta::calcularSubtotal($items), 0.001);
    }
}
