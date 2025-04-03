<?php
// Inizia la sessione
session_start();

// Se l'utente è già loggato, reindirizza
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['ruolo'] === 'amministratore') {
        header('Location: admin_page.php');
    } else {
        header('Location: prodotti.php');
    }
    exit;
}

$errors = [];
$success = false;

// Verifica se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Carico la configurazione e la connessione al DB
    $config = require_once './database/databaseConf.php';
    require './database/DBconn.php';

    $db = DBconn::getDB($config);

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validazione
    if (empty($username)) {
        $errors[] = 'Il campo username è obbligatorio';
    }

    if (empty($email)) {
        $errors[] = 'Il campo email è obbligatorio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email non valida';
    }

    if (empty($password)) {
        $errors[] = 'Il campo password è obbligatorio';
    } elseif (strlen($password) < 6) {
        $errors[] = 'La password deve contenere almeno 6 caratteri';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Le password non corrispondono';
    }

    // Se non ci sono errori di validazione
    if (empty($errors)) {
        try {
            // Verifica se username o email esistono già
            $query = 'SELECT id FROM utenti WHERE username = :username OR email = :email';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errors[] = 'Username o email già in uso';
            } else {
                // Hash della password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Inserimento dell'utente nel DB
                $query = 'INSERT INTO utenti (username, email, password, ruolo) VALUES (:username, :email, :password, :ruolo)';
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $ruolo = 'cliente'; // Ruolo predefinito
                $stmt->bindParam(':ruolo', $ruolo);
                $stmt->execute();

                $success = true;
            }
        } catch (Exception $e) {
            // Funzione logError per gestire gli errori
            if (function_exists('logError')) {
                logError($e);
            }
            $errors[] = 'Si è verificato un errore durante la registrazione. Riprova più tardi.';
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
                        <h4 class="mb-0">Registrazione</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                Registrazione completata con successo! <a href="login.php">Accedi ora</a>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="register.php">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">La password deve contenere almeno 6 caratteri.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Conferma Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Registrati</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">Hai già un account? <a href="login.php">Accedi</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// Includi il footer
include './templates/footer.php';
?>