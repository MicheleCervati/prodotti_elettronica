<?php
// Inizia la sessione
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Carico la configurazione e la connessione al DB
$config = require_once './database/databaseConf.php';
require './database/DBconn.php';

$db = DBconn::getDB($config);

// Query per recuperare tutti i prodotti disponibili
$query = 'SELECT * FROM elettronica.prodotti WHERE quantita > 0 ORDER BY codice';

try {
    $stm = $db->prepare($query);
    $stm->execute();
    // Recupera tutti i prodotti come oggetti
    $prodotti = $stm->fetchAll(PDO::FETCH_OBJ);
    $stm->closeCursor();
} catch (Exception $e) {
    // Funzione logError per gestire gli errori
    if (function_exists('logError')) {
        logError($e);
    }
    $prodotti = []; // Se c'è un errore, impostiamo un array vuoto per evitare errori di loop
}

// Includi l'header
include './templates/header.php';
?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Prodotti Disponibili</h1>
            <div>
                <span class="me-3">Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <?php if (count($prodotti) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                            <tr>
                                <th>Codice</th>
                                <th>Descrizione</th>
                                <th>Costo</th>
                                <th>Quantità</th>
                                <th>Data di Produzione</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($prodotti as $prodotto): ?>
                                <tr>
                                    <td><?php echo $prodotto->codice; ?></td>
                                    <td><?php echo htmlspecialchars($prodotto->descrizione); ?></td>
                                    <td>€ <?php echo number_format($prodotto->costo, 2, ',', '.'); ?></td>
                                    <td><?php echo $prodotto->quantita; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($prodotto->data_produzione)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Non ci sono prodotti disponibili al momento.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
// Includi il footer
include './templates/footer.php';
?>