CREATE DATABASE IF NOT EXISTS elettronica;
USE elettronica;

-- Tabella per i prodotti
CREATE TABLE IF NOT EXISTS prodotti (
    codice INT AUTO_INCREMENT PRIMARY KEY,
    descrizione VARCHAR(255) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    quantita INT NOT NULL,
    data_produzione DATE NOT NULL
);

-- Tabella per gli utenti (registrati e amministratori)
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    ruolo ENUM('cliente', 'amministratore') NOT NULL DEFAULT 'cliente'
);

USE elettronica;

-- Inserimento di alcuni utenti
INSERT INTO elettronica.utenti (username, password, email, ruolo) VALUES
('admin', 'adminpassword', 'admin@example.com', 'amministratore'),
('mario', 'mariopassword', 'mario@example.com', 'cliente'),
('luigi', 'luigipassword', 'luigi@example.com', 'cliente');

-- Inserimento di alcuni prodotti
INSERT INTO prodotti (descrizione, costo, quantita, data_produzione) VALUES
('Smartphone XYZ', 299.99, 50, '2024-01-15'),
('Laptop ABC', 799.99, 20, '2023-12-01'),
('Tablet 123', 199.99, 30, '2024-02-10'),
('Smartwatch 456', 149.99, 25, '2024-03-05');
