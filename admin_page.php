<?php
// Inizia la sessione
session_start();

// Verifica se l'utente è loggato ed è un amministratore
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'amministratore') {
    header('Location: ../login.php');
    exit;
}

// Carico la configurazione e la connessione al DB
$config = require_once './database/databaseConf.php';
require './database/DBconn.php';

$db = DBconn::getDB($config);

// Gestione delle azioni (elimina, aggiorna)
$message = '';
$messageType = '';

// Eliminazione prodotto
if (isset($_POST['delete']) && isset($_POST['codice'])) {
    $codice = $_POST['codice'];

    try {
        $query = 'DELETE FROM prodotti WHERE codice = :codice';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':codice', $codice);
        $stmt->execute();

        $message = 'Prodotto eliminato con successo.';
        $messageType = 'success';
    } catch (Exception $e) {
        if (function_exists('logError')) {
            logError($e);
        }
        $message = 'Errore durante l\'eliminazione del prodotto.';
        $messageType = 'danger';
    }
}

// Aggiornamento prezzo
if (isset($_POST['update']) && isset($_POST['codice']) && isset($_POST['nuovo_prezzo'])) {
    $codice = $_POST['codice'];
    $nuovo_prezzo = $_POST['nuovo_prezzo'];

    if (!is_numeric($nuovo_prezzo) || $nuovo_prezzo <= 0) {
        $message = 'Il prezzo deve essere un numero positivo.';
        $messageType = 'danger';
    } else {
        try {
            $query = 'UPDATE prodotti SET costo = :nuovo_prezzo WHERE codice = :codice';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nuovo_prezzo', $nuovo_prezzo);
            $stmt->bindParam(':codice', $codice);
            $stmt->execute();

            $message = 'Prezzo aggiornato con successo.';
            $messageType = 'success';
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError($e);
            }
            $message = 'Errore durante l\'aggiornamento del prezzo.';
            $messageType = 'danger';
        }
    }
}

// Query per recuperare tutti i prodotti
$query = 'SELECT * FROM prodotti ORDER BY codice';

try {
    $stm = $db->prepare($query);
    $stm->execute();
    $prodotti = $stm->fetchAll(PDO::FETCH_OBJ);
    $stm->closeCursor();
} catch (Exception $e) {
    if (function_exists('logError')) {
        logError($e);
    }
    $prodotti = []; // Se c'è un errore, impostiamo un array vuoto
}

// Includi l'header
include './templates/header.php';
?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard Amministratore</h1>
            <div>
                <span class="me-3">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Sezione per aggiungere un nuovo prodotto -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Aggiungi Nuovo Prodotto</h5>
            </div>
            <div class="card-body">
                <form action="aggiungi_prodotto.php" method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="descrizione" name="descrizione" required>
                    </div>
                    <div class="col-md-2">
                        <label for="costo" class="form-label">Costo (€)</label>
                        <input type="number" class="form-control" id="costo" name="costo" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label for="quantita" class="form-label">Quantità</label>
                        <input type="number" class="form-control" id="quantita" name="quantita" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label for="data_produzione" class="form-label">Data Produzione</label>
                        <input type="date" class="form-control" id="data_produzione" name="data_produzione" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Aggiungi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabella dei prodotti con opzioni per modifica ed eliminazione -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestione Prodotti</h5>
            </div>
            <div class="card-body">
                <?php if (count($prodotti) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Codice</th>
                                <th>Descrizione</th>
                                <th>Costo</th>
                                <th>Quantità</th>
                                <th>Data di Produzione</th>
                                <th>Azioni</th>
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
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $prodotto->codice; ?>">
                                            Aggiorna Prezzo
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $prodotto->codice; ?>">
                                            Elimina
                                        </button>

                                        <!-- Modal per aggiornare il prezzo -->
                                        <div class="modal fade" id="updateModal<?php echo $prodotto->codice; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Aggiorna Prezzo</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="codice" value="<?php echo $prodotto->codice; ?>">
                                                            <p>Prodotto: <strong><?php echo htmlspecialchars($prodotto->descrizione); ?></strong></p>
                                                            <p>Prezzo attuale: <strong>€ <?php echo number_format($prodotto->costo, 2, ',', '.'); ?></strong></p>
                                                            <div class="mb-3">
                                                                <label for="nuovo_prezzo" class="form-label">Nuovo Prezzo (€)</label>
                                                                <input type="number" class="form-control" id="nuovo_prezzo" name="nuovo_prezzo" step="0.01" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                                            <button type="submit" name="update" class="btn btn-primary">Aggiorna</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal per conferma eliminazione -->
                                        <div class="modal fade" id="deleteModal<?php echo $prodotto->codice; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Conferma Eliminazione</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Sei sicuro di voler eliminare il prodotto <strong><?php echo htmlspecialchars($prodotto->descrizione); ?></strong>?</p>
                                                        <p>Questa azione non può essere annullata.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="codice" value="<?php echo $prodotto->codice; ?>">
                                                            <button type="submit" name="delete" class="btn btn-danger">Elimina</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
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