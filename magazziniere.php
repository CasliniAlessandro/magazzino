<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['ID_utente']) || $_SESSION['mansione_utente'] !== 'magazziniere') {
    header('Location: login.php');
    exit;
}

$alertMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ID_richiesta'], $_POST['azione'])) {
        // Gestione approvazione/rifiuto richieste
        $ID_richiesta = $_POST['ID_richiesta'];
        $azione = $_POST['azione'];

        $stmt = $pdo->prepare("SELECT ID_prodotto, quantita FROM richieste WHERE ID_richiesta = :ID_richiesta");
        $stmt->execute(['ID_richiesta' => $ID_richiesta]);
        $richiesta = $stmt->fetch();

        if ($richiesta) {
            if ($azione === 'approva') {
                $pdo->prepare("UPDATE richieste SET stato = 'approvata' WHERE ID_richiesta = :ID_richiesta")
                    ->execute(['ID_richiesta' => $ID_richiesta]);
                $pdo->prepare("UPDATE prodotti SET quantita = quantita - :quantita WHERE ID_prodotto = :ID_prodotto")
                    ->execute(['quantita' => $richiesta['quantita'], 'ID_prodotto' => $richiesta['ID_prodotto']]);

                // Log attività
                $pdo->prepare("
                    INSERT INTO TracciatoAttivita (ID_utente, ID_prodotto, tipo_attivita, descrizione) 
                    VALUES (:ID_utente, :ID_prodotto, 'gestione', :descrizione)
                ")->execute([
                    'ID_utente' => $_SESSION['ID_utente'],
                    'ID_prodotto' => $richiesta['ID_prodotto'],
                    'descrizione' => "Gestione richiesta #$ID_richiesta (approvata)"
                ]);

                $alertMessage = "Richiesta approvata e magazzino aggiornato!";
            } elseif ($azione === 'respinge') {
                $pdo->prepare("UPDATE richieste SET stato = 'respinta' WHERE ID_richiesta = :ID_richiesta")
                    ->execute(['ID_richiesta' => $ID_richiesta]);

                // Log attività
                $pdo->prepare("
                    INSERT INTO TracciatoAttivita (ID_utente, ID_prodotto, tipo_attivita, descrizione) 
                    VALUES (:ID_utente, :ID_prodotto, 'gestione', :descrizione)
                ")->execute([
                    'ID_utente' => $_SESSION['ID_utente'],
                    'ID_prodotto' => $richiesta['ID_prodotto'],
                    'descrizione' => "Gestione richiesta #$ID_richiesta (respinta)"
                ]);

                $alertMessage = "Richiesta respinta!";
            }
        } else {
            $alertMessage = "Errore: richiesta non trovata.";
        }
    }

    if (isset($_POST['aggiungi_prodotto'])) {
        // Aggiunta nuovi prodotti
        $nome_prodotto = $_POST['nome_prodotto'];
        $quantita = $_POST['quantita'];
        $descrizione = $_POST['descrizione'];

        $pdo->prepare("INSERT INTO prodotti (nome_prodotto, quantita, descrizione, creato_da) 
                       VALUES (:nome_prodotto, :quantita, :descrizione, :creato_da)")
            ->execute([
                'nome_prodotto' => $nome_prodotto,
                'quantita' => $quantita,
                'descrizione' => $descrizione,
                'creato_da' => $_SESSION['ID_utente']
            ]);

        // Log attività
        $pdo->prepare("
            INSERT INTO TracciatoAttivita (ID_utente, ID_prodotto, tipo_attivita, descrizione) 
            VALUES (:ID_utente, :ID_prodotto, 'aggiunta_prodotto', :descrizione)
        ")->execute([
            'ID_utente' => $_SESSION['ID_utente'],
            'ID_prodotto' => $pdo->lastInsertId(),
            'descrizione' => "Aggiunto prodotto '$nome_prodotto'"
        ]);

        $alertMessage = "Prodotto aggiunto con successo!";
    }
}

// Recupero richieste e prodotti
$richieste = $pdo->query("
    SELECT richieste.*, utenti.nome_utente, prodotti.nome_prodotto 
    FROM richieste
    JOIN utenti ON richieste.ID_utente = utenti.ID_utente
    JOIN prodotti ON richieste.ID_prodotto = prodotti.ID_prodotto
    WHERE stato = 'in attesa'
    ORDER BY richieste.data_richiesta DESC
")->fetchAll();

$prodotti = $pdo->query("SELECT * FROM prodotti")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Magazziniere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Intestazione principale -->
    <header class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Dashboard Magazziniere</h1>
        </div>
    </header>

    <div class="container mt-4">
        <!-- Gestione richieste -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-warning text-dark">
                <h3 class="mb-0">Gestione Richieste</h3>
            </div>
            <div class="card-body">
                <?php if (count($richieste) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Utente</th>
                                <th>Prodotto</th>
                                <th>Quantità</th>
                                <th>Data</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($richieste as $richiesta): ?>
                            <tr>
                                <td><?= $richiesta['ID_richiesta'] ?></td>
                                <td><?= $richiesta['nome_utente'] ?></td>
                                <td><?= $richiesta['nome_prodotto'] ?></td>
                                <td><?= $richiesta['quantita'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($richiesta['data_richiesta'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="ID_richiesta" value="<?= $richiesta['ID_richiesta'] ?>">
                                        <button type="submit" name="azione" value="approva" class="btn btn-success btn-sm">Approva</button>
                                        <button type="submit" name="azione" value="respinge" class="btn btn-danger btn-sm">Respinge</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Non ci sono richieste in attesa.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aggiungi prodotto -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Aggiungi Prodotto</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nome_prodotto" class="form-label">Nome Prodotto</label>
                        <input type="text" class="form-control" name="nome_prodotto" id="nome_prodotto" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantita" class="form-label">Quantità</label>
                        <input type="number" class="form-control" name="quantita" id="quantita" required>
                    </div>
                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <textarea class="form-control" name="descrizione" id="descrizione" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="aggiungi_prodotto" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>

        <!-- Elenco prodotti -->
        <div class="card shadow-lg">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Elenco Prodotti</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Quantità</th>
                            <th>Descrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prodotti as $prodotto): ?>
                        <tr>
                            <td><?= $prodotto['ID_prodotto'] ?></td>
                            <td><?= $prodotto['nome_prodotto'] ?></td>
                            <td><?= $prodotto['quantita'] ?></td>
                            <td><?= $prodotto['descrizione'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>