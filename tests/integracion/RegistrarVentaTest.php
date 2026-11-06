<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/ServicioVenta.php';
require_once __DIR__ . '/../../src/RepositorioMySQL.php';

use PHPUnit\Framework\TestCase;

final class RegistrarVentaTest extends TestCase
{
    private PDO $conexion;
    private RepositorioMySQL $repositorio;
    private int $productoConStockId;
    private int $productoAgotadoId;

    protected function setUp(): void
    {
        parent::setUp();

        $config = require __DIR__ . '/../../config/env_test.php';
        if ($config['base'] !== 'bodega_test') {
            throw new LogicException('La configuración de integración debe usar únicamente bodega_test.');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['base']
        );
        $this->conexion = new PDO($dsn, $config['usuario'], $config['clave'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->repositorio = new RepositorioMySQL($this->conexion);

        $this->limpiarTablas();

        $insertarProducto = $this->conexion->prepare(
            'INSERT INTO producto (nombre, precio, stock) VALUES (?, ?, ?)'
        );
        $insertarProducto->execute(['Producto de prueba con stock', 5.00, 10]);
        $this->productoConStockId = (int) $this->conexion->lastInsertId();

        $insertarProducto->execute(['Producto de prueba agotado', 4.00, 0]);
        $this->productoAgotadoId = (int) $this->conexion->lastInsertId();
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->conexion) && $this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            if (isset($this->conexion)) {
                $this->limpiarTablas();
            }
        } finally {
            parent::tearDown();
        }
    }

    public function testRegistraVentaYReduceStockEnLaBaseDePruebas(): void
    {
        $ventaId = $this->repositorio->registrarVenta('Cliente de prueba', [
            ['producto_id' => $this->productoConStockId, 'cantidad' => 3],
        ]);

        $consultaStock = $this->conexion->prepare(
            'SELECT stock FROM producto WHERE id = ?'
        );
        $consultaStock->execute([$this->productoConStockId]);

        $consultaConteos = $this->conexion->prepare(
            'SELECT
                (SELECT COUNT(*) FROM venta WHERE id = ?) AS ventas,
                (SELECT COUNT(*) FROM detalle_venta WHERE venta_id = ?) AS detalles'
        );
        $consultaConteos->execute([$ventaId, $ventaId]);
        $conteos = $consultaConteos->fetch();

        self::assertSame(7, (int) $consultaStock->fetchColumn());
        self::assertSame(1, (int) $conteos['ventas']);
        self::assertSame(1, (int) $conteos['detalles']);
    }

    public function testRechazaVentaDeProductoSinStockSinInsertarDatos(): void
    {
        try {
            $this->repositorio->registrarVenta('Cliente de prueba', [
                ['producto_id' => $this->productoAgotadoId, 'cantidad' => 1],
            ]);
            self::fail('Se esperaba una excepción al vender un producto sin stock.');
        } catch (DomainException $exception) {
            self::assertStringContainsString('excede el stock disponible', $exception->getMessage());
        }

        $consultaConteos = $this->conexion->prepare(
            'SELECT
                (SELECT COUNT(*) FROM venta) AS ventas,
                (SELECT COUNT(*) FROM detalle_venta) AS detalles,
                (SELECT stock FROM producto WHERE id = ?) AS stock'
        );
        $consultaConteos->execute([$this->productoAgotadoId]);
        $conteos = $consultaConteos->fetch();

        self::assertSame(0, (int) $conteos['ventas']);
        self::assertSame(0, (int) $conteos['detalles']);
        self::assertSame(0, (int) $conteos['stock']);
    }

    private function limpiarTablas(): void
    {
        $this->conexion->exec('DELETE FROM detalle_venta');
        $this->conexion->exec('DELETE FROM venta');
        $this->conexion->exec('DELETE FROM producto');
    }
}
