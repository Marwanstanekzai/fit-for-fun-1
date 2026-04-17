<?php
$pageTitle = "Lessen Beheren";
include '../includes/header.php';
checkAdmin();

$succes = '';
$fout   = '';

// Unhappy lessen
$simulate_db_error = false;

// 1. Toevoegen van een nieuwe les
if (isset($_POST['actie']) && $_POST['actie'] === 'toevoegen') {
    $naam           = trim($_POST['naam']);
    $trainer        = trim($_POST['trainer']);
    $datum          = $_POST['datum'];
    $tijd           = $_POST['tijd'];
    $max_deelnemers = (int)$_POST['max_deelnemers'];
    $prijs          = (float)($_POST['prijs'] ?? 0);

    // Controleer of er lege velden zijn
    if (empty($naam) || empty($trainer) || empty($datum) || empty($tijd) || empty($max_deelnemers) || $prijs === 0.0) {
        $fout = "Vul a.u.b. alle velden in.";
    } elseif ($datum < date('Y-m-d')) {
        $fout = "Je kunt geen lessen in het verleden aanmaken.";
    } else {
        try {
            if ($simulate_db_error) throw new PDOException("Simulatie");
            $stmt = $pdo->prepare("INSERT INTO les (Naam, Trainer, Datum, Tijd, MaxAantalPersonen, Prijs) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$naam, $trainer, $datum, $tijd, $max_deelnemers, $prijs]);
            $succes = "Les succesvol toegevoegd!";
        } catch (PDOException $e) {
            $fout = "Fout bij het toevoegen van de les.";
        }
    }
}

// 2. Verwijderen van een les
if (isset($_GET['verwijder'])) {
    $id = (int)$_GET['verwijder'];
    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $pdo->prepare("DELETE FROM les WHERE Id = ?")->execute([$id]);
        $succes = "Les succesvol verwijderd!";
    } catch (PDOException $e) {
        $fout = "Fout bij het verwijderen van de les.";
    }
}

// 3. Bewerken van een les
if (isset($_POST['actie']) && $_POST['actie'] === 'bewerken') {
    $id             = (int)$_POST['id'];
    $naam           = trim($_POST['naam']);
    $trainer        = trim($_POST['trainer']);
    $datum          = $_POST['datum'];
    $tijd           = $_POST['tijd'];
    $max_deelnemers = (int)$_POST['max_deelnemers'];
    $prijs          = (float)($_POST['prijs'] ?? 0);

    if (empty($naam) || empty($trainer) || empty($datum) || empty($tijd) || empty($max_deelnemers) || $prijs === 0.0) {
        $fout = "Vul a.u.b. alle velden in.";
    } elseif ($datum < date('Y-m-d')) {
        $fout = "Je kunt een les niet naar een datum in het verleden verplaatsen.";
    } else {
        try {
            if ($simulate_db_error) throw new PDOException("Simulatie");
            $stmt = $pdo->prepare("UPDATE les SET Naam=?, Trainer=?, Datum=?, Tijd=?, MaxAantalPersonen=?, Prijs=? WHERE Id=?");
            $stmt->execute([$naam, $trainer, $datum, $tijd, $max_deelnemers, $prijs, $id]);
            $succes = "Les succesvol bijgewerkt!";
        } catch (PDOException $e) {
            $fout = "Fout bij het bijwerken van de les.";
        }
    }
}

// Haal altijd alle lessen op
$tabel_fout = '';
try {
    if ($simulate_db_error) throw new PDOException("Kan les-gegevens niet inladen!");
    $lessen = $pdo->query("SELECT * FROM les ORDER BY Datum, Tijd")->fetchAll();
} catch (PDOException $e) {
    $tabel_fout = "Systeemfout: " . $e->getMessage();
    $lessen = [];
}

$bewerkLes = null;
if (isset($_GET['bewerk'])) {
    $stmt = $pdo->prepare("SELECT * FROM les WHERE Id = ?");
    $stmt->execute([(int)$_GET['bewerk']]);
    $bewerkLes = $stmt->fetch();
}
?>
<div class="container">
    <a href="/admin/index.php" class="terug">← Terug naar dashboard</a>
    <h1>Lessen Beheren</h1>

    <?php if ($succes): ?><div class="succes"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($fout):   ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

    <!-- Formulier voor het toevoegen / bewerken van een les -->
    <form method="POST" class="toevoeg-form" novalidate>
        <!-- Verborgen veld om te zien welke actie we uitvoeren -->
        <input type="hidden" name="actie" value="<?= $bewerkLes ? 'bewerken' : 'toevoegen' ?>">
        <?php if ($bewerkLes): ?>
            <input type="hidden" name="id" value="<?= $bewerkLes['Id'] ?>">
        <?php endif; ?>
        
        <div>
            <label>Lesnaam</label>
            <input type="text" name="naam" required placeholder="bijv. Yoga" value="<?= htmlspecialchars($bewerkLes['Naam'] ?? '') ?>">
        </div>
        <div>
            <label>Trainer</label>
            <input type="text" name="trainer" required placeholder="Naam trainer" value="<?= htmlspecialchars($bewerkLes['Trainer'] ?? '') ?>">
        </div>
        <div>
            <label>Datum</label>
            <input type="date" name="datum" required min="<?= date('Y-m-d') ?>" value="<?= $bewerkLes['Datum'] ?? date('Y-m-d') ?>">
        </div>
        <div>
            <label>Tijd</label>
            <input type="time" name="tijd" required value="<?= $bewerkLes['Tijd'] ?? '' ?>">
        </div>
        <div>
            <label>Prijs</label>
            <input type="number" step="0.01" name="prijs" required placeholder="12.50" value="<?= $bewerkLes['Prijs'] ?? '' ?>" style="width:80px;">
        </div>
        <div>
            <label>Max. pers.</label>
            <input type="number" name="max_deelnemers" required min="1" placeholder="20" value="<?= $bewerkLes['MaxAantalPersonen'] ?? '' ?>" style="width:70px;">
        </div>
        
        <button type="submit" class="button"><?= $bewerkLes ? 'Opslaan' : 'Toevoegen' ?></button>
        <?php if ($bewerkLes): ?>
            <a href="/admin/lessen.php" class="button" style="background:#888; border-color:#555; box-shadow:0 3px 0 #333;">Annuleren</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($tabel_fout)): ?>
        <div class="fout" style="margin-bottom: 20px;"><?= htmlspecialchars($tabel_fout) ?></div>
    <?php endif; ?>
    <!-- Tabel weergave van alle lessen -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Lesnaam</th>
                <th>Trainer</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Prijs</th>
                <th>Max. pers.</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lessen as $les): ?>
            <tr>
                <td data-label="ID"><?= $les['Id'] ?></td>
                <td data-label="Lesnaam"><?= htmlspecialchars($les['Naam']) ?></td>
                <td data-label="Trainer"><?= htmlspecialchars($les['Trainer']) ?></td>
                <td data-label="Datum"><?= date('d-m-Y', strtotime($les['Datum'])) ?></td>
                <td data-label="Tijd"><?= $les['Tijd'] ?></td>
                <td data-label="Prijs">€<?= number_format($les['Prijs'], 2, ',', '.') ?></td>
                <td data-label="Max. pers."><?= $les['MaxAantalPersonen'] ?></td>
                <td data-label="Acties">
                    <a class="btn-bewerk" href="?bewerk=<?= $les['Id'] ?>">Bewerk</a>
                    <a class="btn-verwijder" href="?verwijder=<?= $les['Id'] ?>" onclick="return confirmDelete(this.href)">Verwijder</a>
                </td>

            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($lessen)): ?>
            <tr><td colspan="7" style="text-align:center; color:#999; padding:20px;">Nog geen lessen toegevoegd</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

// CRUD: Lessenbeheer voor medewerkers
