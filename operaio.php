<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['ID_utente']) || $_SESSION['mansione_utente'] !== 'operaio') {
    header('Location: login.php');
    exit;
}

// Recupero dei prodotti disponibili
$prodotti = $pdo->query("SELECT * FROM Prodotti WHERE quantita > 0")->fetchAll();

// Recupero delle richieste approvate per l'operaio
$richiesteApprovate = $pdo->prepare("
    SELECT r.ID_richiesta, p.nome_prodotto, r.quantita, p.descrizione, r.data_richiesta
    FROM Richieste r
    JOIN Prodotti p ON r.ID_prodotto = p.ID_prodotto
    WHERE r.ID_utente = :ID_utente AND r.stato = 'approvata'
    ORDER BY r.data_richiesta DESC
");
$richiesteApprovate->execute(['ID_utente' => $_SESSION['ID_utente']]);
$carrello = $richiesteApprovate->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ID_prodotto = $_POST['ID_prodotto'];
    $quantita = $_POST['quantita'];

    // Recupero quantità disponibile del prodotto
    $stmt = $pdo->prepare("SELECT quantita FROM Prodotti WHERE ID_prodotto = :ID_prodotto");
    $stmt->execute(['ID_prodotto' => $ID_prodotto]);
    $prodotto = $stmt->fetch();

    if ($quantita > $prodotto['quantita']) {
        $error = "Errore: la quantità richiesta supera quella disponibile (" . $prodotto['quantita'] . ").";
    } else {
        // Inserimento della richiesta nella tabella `Richieste`
        $stmt = $pdo->prepare("INSERT INTO Richieste (ID_utente, ID_prodotto, quantita) VALUES (:ID_utente, :ID_prodotto, :quantita)");
        $stmt->execute([
            'ID_utente' => $_SESSION['ID_utente'],
            'ID_prodotto' => $ID_prodotto,
            'quantita' => $quantita
        ]);

        // Log della richiesta nella tabella `TracciatoAttivita`
        $stmtLog = $pdo->prepare("
            INSERT INTO TracciatoAttivita (ID_utente, ID_prodotto, tipo_attivita, descrizione) 
            VALUES (:ID_utente, :ID_prodotto, 'richiesta', :descrizione)
        ");
        $stmtLog->execute([
            'ID_utente' => $_SESSION['ID_utente'],
            'ID_prodotto' => $ID_prodotto,
            'descrizione' => "Richiesta di $quantita unità del prodotto #$ID_prodotto"
        ]);

        $success = "Richiesta inviata con successo!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operaio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Intestazione principale -->
    <header class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Dashboard Operaio</h1>
        </div>
    </header>

    <div class="container mt-4">
        <!-- Richiedi un prodotto -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Richiedi un prodotto</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="ID_prodotto" class="form-label">Seleziona il prodotto</label>
                        <select name="ID_prodotto" id="ID_prodotto" class="form-select" required>
                            <?php foreach ($prodotti as $prodotto): ?>
                                <option value="<?= $prodotto['ID_prodotto'] ?>">
                                    <?= $prodotto['nome_prodotto'] ?> (Disponibili: <?= $prodotto['quantita'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantita" class="form-label">Quantità richiesta</label>
                        <input type="number" name="quantita" id="quantita" class="form-control" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Invia Richiesta</button>
                </form>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mt-3"><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success mt-3"><?= $success ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Carrello: Richieste approvate -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-warning text-dark">
                <h3 class="mb-0">Carrello: Prodotti Approvati</h3>
            </div>
            <div class="card-body">
                <?php if (count($carrello) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Richiesta</th>
                                <th>Prodotto</th>
                                <th>Quantità</th>
                                <th>Descrizione</th>
                                <th>Data Approvazione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrello as $item): ?>
                            <tr>
                                <td><?= $item['ID_richiesta'] ?></td>
                                <td><?= $item['nome_prodotto'] ?></td>
                                <td><?= $item['quantita'] ?></td>
                                <td><?= $item['descrizione'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($item['data_richiesta'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Non ci sono prodotti approvati.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>