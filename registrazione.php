<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $mansione = $_POST['mansione'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Utenti (nome_utente, password_utente, mansione_utente) VALUES (:username, :password, :mansione)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'mansione' => $mansione
        ]);
        $success = "Utente registrato con successo! <a href='login.php'>Accedi qui</a>";
    } catch (PDOException $e) {
        $error = "Errore nella registrazione: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Registrazione</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nome utente</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="mansione" class="form-label">Mansione</label>
                                <select name="mansione" id="mansione" class="form-select" required>
                                    <option value="operaio">Operaio</option>
                                    <option value="magazziniere">Magazziniere</option>
                                    <option value="amministratore">Amministratore</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Registrati</button>
                        </form>
                        <?php if (isset($success)) echo "<div class='alert alert-success mt-3'>$success</div>"; ?>
                        <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="login.php">Hai già un account? Accedi qui</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>