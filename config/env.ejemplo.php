<?php
declare(strict_types=1);

// Plantilla de configuración. Copiar como config/env.php y completar con los datos reales.
// En InfinityFree los datos están en el panel: MySQL Databases.
return [
    'host'    => getenv('DB_HOST') ?: 'sqlXXX.infinityfree.com',
    'usuario' => getenv('DB_USER') ?: 'if0_XXXXXXXX',
    'clave'   => getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : 'CONTRASEÑA_DEL_PANEL',
    'base'    => getenv('DB_NAME') ?: 'if0_XXXXXXXX_bodega',
];
