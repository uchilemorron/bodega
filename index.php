<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

$productos = [];
$error = null;
try {
    $repositorio = new RepositorioMySQL(conectar());
    $productos = $repositorio->listarProductos();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'No se pudo consultar el inventario. Verifica que MySQL esté iniciado y vuelve a intentarlo.';
}

$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Bodega Doña Rosa</h1>
            <p class="text-secondary mb-0">Inventario y stock disponible</p>
        </div>
        <a class="btn btn-primary" href="venta.php">Registrar venta</a>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert"><?= $escapar($error) ?></div>
    <?php elseif ($productos === []): ?>
        <div class="alert alert-info" role="status">No hay productos registrados en el inventario.</div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr><th>Producto</th><th>Precio unitario</th><th>Stock</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= $escapar($producto['nombre']) ?></td>
                            <td>S/ <?= number_format((float) $producto['precio'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ((int) $producto['stock'] === 0): ?>
                                    <span class="badge text-bg-secondary">Agotado (0)</span>
                                <?php else: ?>
                                    <?= $escapar((int) $producto['stock']) ?> unidades
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
