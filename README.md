# Registro de ventas · Bodega «Doña Rosa»

Aplicación web en PHP 8 + MySQL (sin framework) para registrar productos y ventas, descontar stock y aplicar el descuento por volumen (reglas R1–R4).

**URL pública:** http://bodega.infinityfree.me *(actualizar si cambia el subdominio)*

## Ejecución local (Laragon)

1. Iniciar Laragon (Apache y MySQL).
2. Importar la base: `mysql -u root --default-character-set=utf8mb4 < sql\bodega.sql`
3. Copiar `config/env.ejemplo.php` como `config/env.php` y dejar `localhost`, `root`, sin clave, `bodega`.
4. Abrir http://localhost/bodega/

## Pruebas

- Unitarias: `php phpunit.phar --testsuite unitarias`
- Integración (usa `bodega_test`, nunca `bodega`): `mysql -u root < sql\bodega_test.sql` y luego `php phpunit.phar --testsuite integracion`
- Todas: `php phpunit.phar`

`phpunit.phar`, `config/env.php` y `config/env_test.php` no se versionan (ver `.gitignore`).

## Despliegue en InfinityFree

1. Crear cuenta gratuita en https://www.infinityfree.com y un hosting con subdominio gratuito.
2. Panel del hosting → **MySQL Databases** → crear la base `bodega`. Anotar **MySQL Hostname** (`sqlXXX.infinityfree.com`), **MySQL Username** (`if0_...`), **MySQL Password** y el nombre completo de la base (`if0_..._bodega`).
3. Panel → **phpMyAdmin** de esa base → **Importar** → `sql/bodega_produccion.sql` (sin `CREATE DATABASE` ni `USE`).
4. Panel → **File Manager** → carpeta `htdocs` → subir el proyecto **excepto** `.git`, `tests/`, `docs/`, `sql/`, `.kiro/`, `phpunit.phar`, `phpunit.xml` y `config/env.php`. Archivos a subir: `index.php`, `venta.php`, `guardar_venta.php`, `src/`, `config/conexion.php`, `config/env.ejemplo.php`.
5. En el servidor, crear `htdocs/config/env.php` copiando `env.ejemplo.php` y reemplazando los cuatro datos del panel.
6. Prueba de aceptación: desde un celular con datos móviles, abrir la URL pública, registrar una venta y verificar que el stock bajó en el inventario.

**Contingencia:** si el hosting no se activa a tiempo, `ngrok http 80` expone el Laragon local en una URL temporal.
