<?php
// Inizia la sessione (deve essere all'inizio)
session_start();

// Se l'utente è già loggato, reindirizza alla pagina appropriata
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['ruolo'] === 'amministratore') {
        header('Location: admin_page.php');
    } else {
        header('Location: prodotti.php');
    }
    exit;
}

// Verifica se il form è stato inviato
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Carico la configurazione e la connessione al DB
    $config = require_once './database/databaseConf.php';
    require './database/DBconn.php';

    $db = DBconn::getDB($config);

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Controllo se i campi sono vuoti
    if (empty($username) || empty($password)) {
        $error = 'Per favore, compila tutti i campi';
    } else {
        try {
            // Prepara e esegui la query
            $query = 'SELECT id, username, password, ruolo FROM utenti WHERE username = :username';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Verifica se l'utente esiste
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verifica la password (assicurati di usare password_hash quando registri gli utenti)
                if (password_verify($password, $user['password'])) {
                    // Password corretta, imposta la sessione
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['ruolo'] = $user['ruolo'];

                    // Reindirizza in base al ruolo
                    if ($user['ruolo'] === 'amministratore') {
                        header('Location: admin_page.php');
                    } else {
                        header('Location: prodotti.php');
                    }
                    exit;
                } else {
                    $error = 'Username o password non validi';
                }
            } else {
                $error = 'Username o password non validi';
            }
        } catch (Exception $e) {
            // Funzione logError per gestire gli errori
            if (function_exists('logError')) {
                logError($e);
            }
            $error = 'Si è verificato un errore durante il login. Riprova più tardi.';
        }
    }
}

// Includi l'header
include './templates/header.php';
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Accedi</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Accedi</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">Non hai un account? <a href="registrazione.php">Registrati</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// Includi il footer
include './templates/footer.php';
?>