<?php
// Inizia la sessione
session_start();

// Verifica se l'utente è loggato ed è un amministratore
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'amministratore') {
    header('Location: ../login.php');
    exit;
}

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Carico la configurazione e la connessione al DB
    $config = require_once '../database/databaseConf.php';
    require '../database/DBconn.php';

    $db = DBconn::getDB($config);

    // Recupera i dati dal form
    $descrizione = trim($_POST['descrizione']);
    $costo = floatval($_POST['costo']);
    $quantita = intval($_POST['quantita']);
    $data_produzione = $_POST['data_produzione'];

    // Validazione base
    $errors = [];

    if (empty($descrizione)) {
        $errors[] = 'La descrizione è obbligatoria';
    }

    if ($costo <= 0) {
        $errors[] = 'Il costo deve essere maggiore di zero';
    }

    if ($quantita < 0) {
        $errors[] = 'La quantità non può essere negativa';
    }

    if (empty($data_produzione)) {
        $errors[] = 'La data di produzione è obbligatoria';
    }

    // Se non ci sono errori, inserisce il prodotto
    if (empty($errors)) {
        try {
            $query = 'INSERT INTO prodotti (descrizione, costo, quantita, data_produzione) VALUES (:descrizione, :costo, :quantita, :data_produzione)';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':descrizione', $descrizione);
            $stmt->bindParam(':costo', $costo);
            $stmt->bindParam(':quantita', $quantita);
            $stmt->bindParam(':data_produzione', $data_produzione);
            $stmt->execute();

            // Reindirizza alla dashboard con messaggio di successo
            header('Location: dashboard.php?message=success&text=' . urlencode('Prodotto aggiunto con successo'));
            exit;
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError($e);
            }
            $errors[] = 'Si è verificato un errore durante l\'inserimento del prodotto';
        }
    }

    // Se ci sono errori, reindirizza alla dashboard con errori
    if (!empty($errors)) {
        $error_message = implode(', ', $errors);
        header('Location: dashboard.php?message=error&text=' . urlencode($error_message));
        exit;
    }
} else {
    // Se non è una richiesta POST, reindirizza alla dashboard
    header('Location: dashboard.php');
    exit;
}
?>