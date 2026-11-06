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
    $error = 'No se pudo cargar el inventario. Verifica que MySQL esté iniciado y vuelve a intentarlo.';
}

$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<option value="">Selecciona un producto</option>
<?php foreach ($productos as $producto): ?>
    <option value="<?= $escapar((int) $producto['id']) ?>" <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>>
        <?= $escapar($producto['nombre']) ?> — S/ <?= number_format((float) $producto['precio'], 2, ',', '.') ?>
        (stock: <?= $escapar((int) $producto['stock']) ?>)
    </option>
<?php endforeach; ?>
<?php
$opcionesProductos = ob_get_clean();
$hayProductosDisponibles = false;
foreach ($productos as $producto) {
    if ((int) $producto['stock'] > 0) {
        $hayProductosDisponibles = true;
        break;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar venta | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="mb-4">
        <a href="index.php" class="link-secondary text-decoration-none">&larr; Volver al inventario</a>
        <h1 class="h2 mt-3 mb-1">Registrar venta</h1>
        <p class="text-secondary mb-0">Agrega uno o más productos y sus cantidades.</p>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert"><?= $escapar($error) ?></div>
    <?php elseif (!$hayProductosDisponibles): ?>
        <div class="alert alert-info" role="status">No hay productos con stock disponible para vender.</div>
    <?php else: ?>
        <form action="guardar_venta.php" method="post" class="card shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <label for="cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" maxlength="80"
                           value="Consumidor Final" required>
                </div>

                <div id="lineas-productos" class="vstack gap-3">
                    <div class="row g-2 align-items-end linea-producto">
                        <div class="col-md-9">
                            <label class="form-label">Producto</label>
                            <select class="form-select" name="productos[]" required>
                                <?= $opcionesProductos ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidades[]" min="1" step="1" required>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" class="btn btn-outline-danger quitar-linea" aria-label="Quitar producto" disabled>&times;</button>
                        </div>
                    </div>
                </div>

                <template id="plantilla-linea">
                    <div class="row g-2 align-items-end linea-producto">
                        <div class="col-md-9">
                            <label class="form-label">Producto</label>
                            <select class="form-select" name="productos[]" required>
                                <?= $opcionesProductos ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidades[]" min="1" step="1" required>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" class="btn btn-outline-danger quitar-linea" aria-label="Quitar producto">&times;</button>
                        </div>
                    </div>
                </template>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="button" class="btn btn-outline-primary" id="agregar-linea">Agregar otro producto</button>
                    <button type="submit" class="btn btn-primary">Confirmar venta</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>
<script>
    const lineas = document.getElementById('lineas-productos');
    const plantilla = document.getElementById('plantilla-linea');
    const agregar = document.getElementById('agregar-linea');

    if (lineas && plantilla && agregar) {
        const actualizarBotones = () => {
            lineas.querySelectorAll('.quitar-linea').forEach((boton) => {
                boton.disabled = lineas.querySelectorAll('.linea-producto').length === 1;
            });
        };

        agregar.addEventListener('click', () => {
            lineas.append(plantilla.content.cloneNode(true));
            actualizarBotones();
        });

        lineas.addEventListener('click', (evento) => {
            if (evento.target.matches('.quitar-linea')) {
                evento.target.closest('.linea-producto').remove();
                actualizarBotones();
            }
        });
    }
</script>
</body>
</html>
