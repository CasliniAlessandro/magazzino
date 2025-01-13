<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica credenziali
    $stmt = $pdo->prepare("SELECT * FROM Utenti WHERE nome_utente = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_utente'])) {
        $_SESSION['ID_utente'] = $user['ID_utente'];
        $_SESSION['mansione_utente'] = $user['mansione_utente'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Credenziali non valide! Riprova.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nome utente</label>
                                <input type="text" class="form-control" name="username" id="username" placeholder="Inserisci il tuo nome utente" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Inserisci la tua password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Accedi</button>
                        </form>
                        <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p>Non hai un account? <a href="registrazione.php">Registrati qui</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>