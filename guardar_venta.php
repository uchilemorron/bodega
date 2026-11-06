<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/ServicioVenta.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

$mensajeError = null;
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $mensajeError = 'Esta página solo acepta ventas enviadas desde el formulario.';
} else {
    try {
        $cliente = ServicioVenta::validarCliente((string) ($_POST['cliente'] ?? ''));

        $ids = $_POST['productos'] ?? null;
        $cantidadesPost = $_POST['cantidades'] ?? null;
        if (!is_array($ids) || !is_array($cantidadesPost) || count($ids) !== count($cantidadesPost)) {
            throw new DomainException('Agrega al menos un producto y una cantidad válida.');
        }

        $itemsEntrada = [];
        foreach ($ids as $indice => $idPost) {
            $itemsEntrada[] = [
                'producto_id' => $idPost,
                'cantidad' => $cantidadesPost[$indice] ?? null,
            ];
        }
        $items = ServicioVenta::normalizarItems(
            $itemsEntrada,
            'La cantidad debe ser un entero mayor que cero.',
            'La cantidad debe ser un entero mayor que cero.',
            'Agrega al menos un producto y una cantidad válida.'
        );

        $repositorio = new RepositorioMySQL(conectar());
        $productos = [];
        foreach ($items as $item) {
            $producto = $repositorio->obtenerProducto($item['producto_id']);
            if ($producto !== null) {
                $productos[$item['producto_id']] = $producto;
            }
        }
        $items = ServicioVenta::validarStock(
            $items,
            $productos,
            'Uno de los productos seleccionados no está registrado.'
        );

        $subtotal = ServicioVenta::calcularSubtotal($items);
        $descuento = ServicioVenta::calcularDescuento($subtotal);
        $total = ServicioVenta::calcularTotal($subtotal);
        $ventaId = $repositorio->registrarVenta($cliente, $items);
        $resultado = compact('ventaId', 'subtotal', 'descuento', 'total');
    } catch (DomainException $exception) {
        $mensajeError = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $mensajeError = 'No se pudo conectar con la base de datos. Verifica MySQL e inténtalo nuevamente.';
    } catch (RuntimeException $exception) {
        error_log($exception->getMessage());
        $mensajeError = $exception->getMessage();
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        $mensajeError = 'No se pudo procesar la venta. No se confirmó ningún cambio; inténtalo nuevamente.';
    }
}

$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de venta | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 42rem">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Resultado de la venta</h1>
            <?php if ($mensajeError !== null): ?>
                <div class="alert alert-danger" role="alert"><?= $escapar($mensajeError) ?></div>
            <?php else: ?>
                <div class="alert alert-success" role="status">
                    Venta #<?= $escapar($resultado['ventaId']) ?> registrada correctamente.
                </div>
                <dl class="row">
                    <dt class="col-sm-6">Subtotal</dt>
                    <dd class="col-sm-6">S/ <?= number_format($resultado['subtotal'], 2, ',', '.') ?></dd>
                    <dt class="col-sm-6">Descuento</dt>
                    <dd class="col-sm-6">S/ <?= number_format($resultado['descuento'], 2, ',', '.') ?></dd>
                    <dt class="col-sm-6">Total</dt>
                    <dd class="col-sm-6 fw-bold">S/ <?= number_format($resultado['total'], 2, ',', '.') ?></dd>
                </dl>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <a class="btn btn-primary" href="index.php">Ver inventario</a>
                <a class="btn btn-outline-secondary" href="venta.php">Otra venta</a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
