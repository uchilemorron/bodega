CREATE DATABASE IF NOT EXISTS bodega;
USE bodega;

DROP TABLE IF EXISTS detalle_venta;
DROP TABLE IF EXISTS venta;
DROP TABLE IF EXISTS producto;

CREATE TABLE producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    precio DECIMAL(8,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cliente VARCHAR(80) NOT NULL DEFAULT 'Consumidor Final',
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(8,2) NOT NULL,
    importe DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO producto (nombre, precio, stock) VALUES
    ('Arroz extra Costeño 1 kg', 4.50, 35),
    ('Aceite vegetal Primor 1 L', 10.90, 18),
    ('Leche evaporada Gloria 400 g', 4.20, 0),
    ('Azúcar rubia 1 kg', 3.80, 27),
    ('Atún en agua Florida 170 g', 6.50, 12),
    ('Gaseosa Inca Kola 1.5 L', 8.90, 20);
