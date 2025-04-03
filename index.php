<?php
include './templates/header.php';



// Carico la configurazione e la connessione al DB
$config = require_once './database/databaseConf.php';
require './database/DBconn.php';

$db = DBconn::getDB($config);

// Query per recuperare tutti i prodotti
$query = 'SELECT * FROM elettronica.prodotti';

try {
$stm = $db->prepare($query);
$stm->execute();
// Recupera tutti i prodotti come oggetti
$prodotti = $stm->fetchAll(PDO::FETCH_OBJ);
$stm->closeCursor();
} catch (Exception $e) {
// Funzione logError definita nel tuo progetto per gestire gli errori
logError($e);
$prodotti = []; // Se c'è un errore, impostiamo un array vuoto per evitare errori di loop
}
?>

<!-- Sezione Jumbotron per la presentazione del negozio -->
<div class="jumbotron p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Benvenuti nel Negozio di Elettronica</h1>
        <p class="col-md-8 fs-4">Scopri la nostra ampia selezione di prodotti elettronici, dalle ultime novità tecnologiche a offerte imperdibili!</p>
        <a class="btn btn-primary btn-lg" href="prodotti.php" role="button">Visualizza Prodotti</a>
    </div>
</div>

<!-- Sezione Prodotti -->
<div class="container">
    <h2 class="mb-4">Prodotti in Evidenza</h2>
    <div class="row">
        <?php if(count($prodotti) > 0): ?>
            <?php foreach($prodotti as $prodotto): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- Se hai immagini per i prodotti, puoi aggiungere un tag <img> con il percorso corretto -->
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($prodotto->descrizione); ?></h5>
                            <p class="card-text">
                                <strong>Costo:</strong> € <?php echo number_format($prodotto->costo, 2, ',', '.'); ?><br>
                                <strong>Quantità:</strong> <?php echo $prodotto->quantita; ?><br>
                                <strong>Data di produzione:</strong> <?php echo date("d/m/Y", strtotime($prodotto->data_produzione)); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="prodotti.php" class="btn btn-outline-primary btn-sm">Dettagli</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">Non sono presenti prodotti al momento.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include './templates/footer.php';
?>
