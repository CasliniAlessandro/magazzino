<?php
include 'db.php';
session_start();

if (!isset($_SESSION['ID_utente'])) {
    header('Location: login.php');
    exit;
}

$mansione = $_SESSION['mansione_utente'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <div>
            <?php
            if ($mansione == 'operaio') {
                include 'operaio.php';
            } elseif ($mansione == 'magazziniere') {
                include 'magazziniere.php';
            } elseif ($mansione == 'amministratore') {
                include 'amministratore.php';
            }
            ?>
        </div>
    </div>
</body>
</html>