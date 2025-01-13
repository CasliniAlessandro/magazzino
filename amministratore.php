<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['ID_utente']) || $_SESSION['mansione_utente'] !== 'amministratore') {
    header('Location: login.php');
    exit;
}

// Recupero tracciato degli operai
$tracciatoOperai = $pdo->query("
    SELECT ta.*, u.nome_utente AS operaio, p.nome_prodotto
    FROM TracciatoAttivita ta
    JOIN Utenti u ON ta.ID_utente = u.ID_utente
    LEFT JOIN Prodotti p ON ta.ID_prodotto = p.ID_prodotto
    WHERE u.mansione_utente = 'operaio'
    ORDER BY ta.data_attivita DESC
")->fetchAll();

// Recupero tracciato dei magazzinieri
$tracciatoMagazzinieri = $pdo->query("
    SELECT ta.*, u.nome_utente AS magazziniere, p.nome_prodotto
    FROM TracciatoAttivita ta
    JOIN Utenti u ON ta.ID_utente = u.ID_utente
    LEFT JOIN Prodotti p ON ta.ID_prodotto = p.ID_prodotto
    WHERE u.mansione_utente = 'magazziniere'
    ORDER BY ta.data_attivita DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Amministratore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Intestazione principale -->
    <header class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Dashboard Amministratore</h1>
        </div>
    </header>

    <div class="container mt-4">
        <!-- Tracciato degli operai -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">Tracciato Operai</h3>
            </div>
            <div class="card-body">
                <?php if (count($tracciatoOperai) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Attività</th>
                                <th>Operaio</th>
                                <th>Prodotto</th>
                                <th>Tipo Attività</th>
                                <th>Descrizione</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracciatoOperai as $attivita): ?>
                            <tr>
                                <td><?= $attivita['ID_attivita'] ?></td>
                                <td><?= $attivita['operaio'] ?></td>
                                <td><?= $attivita['nome_prodotto'] ?? 'N/A' ?></td>
                                <td><?= ucfirst($attivita['tipo_attivita']) ?></td>
                                <td><?= $attivita['descrizione'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($attivita['data_attivita'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Non ci sono attività recenti degli operai.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tracciato dei magazzinieri -->
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Tracciato Magazzinieri</h3>
            </div>
            <div class="card-body">
                <?php if (count($tracciatoMagazzinieri) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Attività</th>
                                <th>Magazziniere</th>
                                <th>Prodotto</th>
                                <th>Tipo Attività</th>
                                <th>Descrizione</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracciatoMagazzinieri as $attivita): ?>
                            <tr>
                                <td><?= $attivita['ID_attivita'] ?></td>
                                <td><?= $attivita['magazziniere'] ?></td>
                                <td><?= $attivita['nome_prodotto'] ?? 'N/A' ?></td>
                                <td><?= ucfirst($attivita['tipo_attivita']) ?></td>
                                <td><?= $attivita['descrizione'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($attivita['data_attivita'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Non ci sono attività recenti dei magazzinieri.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>