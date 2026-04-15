<?php
$pageTitle = "Reserveringen Beheren";
include '../includes/header.php';
checkAdmin();

$succes = '';
$fout   = '';

// Unhappy reserveringen
$simulate_db_error = false;

// 1. Toevoegen van een nieuwe reservering
if (isset($_POST['actie']) && $_POST['actie'] === 'toevoegen') {
    $lid_id = (int)$_POST['lid_id'];
    $les_id = (int)$_POST['les_id'];

    if (empty($lid_id) || empty($les_id)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {

    // Haal lid en les details op om ze te kopieren naar de reservering tabel (conform het nieuwe schema)
    $lid = $pdo->prepare("SELECT * FROM lid WHERE Id = ?"); $lid->execute([$lid_id]); $l = $lid->fetch();
    $les = $pdo->prepare("SELECT * FROM les WHERE Id = ?"); $les->execute([$les_id]); $s = $les->fetch();

    if ($s && $s['Datum'] < date('Y-m-d')) {
        $fout = "Je kunt geen reservering maken voor een les die al is geweest.";
    } else {
        try {
            if ($simulate_db_error) throw new PDOException("Simulatie");
            $stmt = $pdo->prepare("INSERT INTO reservering (Voornaam, Tussenvoegsel, Achternaam, Nummer, Datum, Tijd, Reserveringstatus) VALUES (?, ?, ?, ?, ?, ?, 'Gereserveerd')");
            $stmt->execute([$l['Voornaam'], $l['Tussenvoegsel'], $l['Achternaam'], $l['Relatienummer'], $s['Datum'], $s['Tijd']]);
            $succes = "Reservering succesvol toegevoegd!";
        } catch (PDOException $e) {
            $fout = "Fout bij reserveren: " . $e->getMessage();
        }
    }
    }
}

// 2. Verwijderen van een reservering
if (isset($_GET['verwijder'])) {
    $id = (int)$_GET['verwijder'];
    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $pdo->prepare("DELETE FROM reservering WHERE Id = ?")->execute([$id]);
        $succes = "Reservering succesvol verwijderd!";
    } catch (PDOException $e) {
        $fout = "Fout bij het verwijderen van de reservering.";
    }
}

// 3. Bewerken van een reservering
if (isset($_POST['actie']) && $_POST['actie'] === 'bewerken') {
    $id             = (int)$_POST['id'];
    $voornaam       = trim($_POST['voornaam']);
    $achternaam     = trim($_POST['achternaam']);
    $nummer         = (int)$_POST['nummer'];
    $datum          = $_POST['datum'];
    $tijd           = $_POST['tijd'];
    $status         = $_POST['status'];

    if (empty($voornaam) || empty($achternaam) || empty($datum) || empty($tijd)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {
        try {
            if ($simulate_db_error) throw new PDOException("Simulatie");
            $stmt = $pdo->prepare("UPDATE reservering SET Voornaam=?, Achternaam=?, Nummer=?, Datum=?, Tijd=?, Reserveringstatus=? WHERE Id=?");
            $stmt->execute([$voornaam, $achternaam, $nummer, $datum, $tijd, $status, $id]);
            $succes = "Reservering succesvol bijgewerkt!";
        } catch (PDOException $e) {
            $fout = "Fout bij het bijwerken van de reservering.";
        }
    }
}

// Haal altijd alle gegevens op
$tabel_fout = '';
try {
    if ($simulate_db_error) throw new PDOException("Kan data niet inladen!");
    $reserveringen = $pdo->query("SELECT * FROM reservering ORDER BY Datum DESC, Tijd DESC")->fetchAll();
    $leden = $pdo->query("SELECT Id, Voornaam, Achternaam, Relatienummer FROM lid ORDER BY Achternaam, Voornaam")->fetchAll();
    $lessen = $pdo->query("SELECT Id, Naam, Datum, Tijd FROM les WHERE Datum >= CURDATE() ORDER BY Datum, Tijd")->fetchAll();
} catch (PDOException $e) {
    $tabel_fout = "Systeemfout: " . $e->getMessage();
    $reserveringen = [];
    $leden = [];
    $lessen = [];
}

$bewerkRes = null;
if (isset($_GET['bewerk'])) {
    $stmt = $pdo->prepare("SELECT * FROM reservering WHERE Id = ?");
    $stmt->execute([(int)$_GET['bewerk']]);
    $bewerkRes = $stmt->fetch();
}
?>

<div class="container">
    <a class="terug" href="/admin/index.php">← Terug naar dashboard</a>
    <h1>Reserveringen Beheren</h1>
    <?php if ($succes): ?><div class="succes"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($fout):   ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

    <?php if ($bewerkRes): ?>
        <!-- Formulier voor het bewerken van een bestaande reservering -->
        <form method="POST" class="toevoeg-form" novalidate>
            <input type="hidden" name="actie" value="bewerken">
            <input type="hidden" name="id" value="<?= $bewerkRes['Id'] ?>">
            <div>
                <label>Voornaam</label>
                <input type="text" name="voornaam" value="<?= htmlspecialchars($bewerkRes['Voornaam']) ?>" required>
            </div>
            <div>
                <label>Achternaam</label>
                <input type="text" name="achternaam" value="<?= htmlspecialchars($bewerkRes['Achternaam']) ?>" required>
            </div>
            <div>
                <label>Relatienummer</label>
                <input type="number" name="nummer" value="<?= $bewerkRes['Nummer'] ?>" required>
            </div>
            <div>
                <label>Datum</label>
                <input type="date" name="datum" value="<?= $bewerkRes['Datum'] ?>" required>
            </div>
            <div>
                <label>Tijd</label>
                <input type="time" name="tijd" value="<?= $bewerkRes['Tijd'] ?>" required>
            </div>
            <div>
                <label>Status</label>
                <select name="status" required>
                    <option value="Gereserveerd" <?= $bewerkRes['Reserveringstatus'] === 'Gereserveerd' ? 'selected' : '' ?>>Gereserveerd</option>
                    <option value="Geannuleerd" <?= $bewerkRes['Reserveringstatus'] === 'Geannuleerd' ? 'selected' : '' ?>>Geannuleerd</option>
                    <option value="Aanwezig" <?= $bewerkRes['Reserveringstatus'] === 'Aanwezig' ? 'selected' : '' ?>>Aanwezig</option>
                </select>
            </div>
            <button type="submit" class="button">Opslaan</button>
            <a href="/admin/reserveringen.php" class="button" style="background:#888; box-shadow:0 3px 0 #555; color:white;">Annuleren</a>
        </form>
    <?php else: ?>
        <!-- Formulier voor het toevoegen van een nieuwe reservering -->
        <form method="POST" class="toevoeg-form" novalidate>
            <input type="hidden" name="actie" value="toevoegen">
            <div>
                <label>Lid</label>
                <select name="lid_id" required>
                    <option value="">Selecteer lid...</option>
                    <?php foreach ($leden as $l): ?>
                        <option value="<?= $l['Id'] ?>"><?= htmlspecialchars($l['Voornaam'] . ' ' . $l['Achternaam']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Les</label>
                <select name="les_id" required>
                    <option value="">Selecteer les...</option>
                    <?php foreach ($lessen as $s): ?>
                        <option value="<?= $s['Id'] ?>"><?= htmlspecialchars($s['Naam'] . ' (' . date('d-m-Y', strtotime($s['Datum'])) . ' ' . $s['Tijd'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="button">Toevoegen</button>
        </form>
    <?php endif; ?>

    <?php if (!empty($tabel_fout)): ?>
        <div class="fout" style="margin-bottom: 20px;"><?= htmlspecialchars($tabel_fout) ?></div>
    <?php endif; ?>
    <table>
        <thead><tr><th>#</th><th>Lid</th><th>Les/Datum</th><th>Status</th><th>Acties</th></tr></thead>
        <tbody>
            <?php foreach ($reserveringen as $r): ?>
            <tr>
                <td data-label="ID"><?= $r['Id'] ?></td>
                <td data-label="Lid"><?= htmlspecialchars($r['Voornaam'] . ' ' . $r['Achternaam']) ?></td>
                <td data-label="Les/Datum"><?= date('d-m-Y', strtotime($r['Datum'])) ?> <?= $r['Tijd'] ?></td>
                <td data-label="Status"><strong><?= htmlspecialchars($r['Reserveringstatus']) ?></strong></td>
                <td data-label="Acties">
                    <a class="btn-bewerk" href="?bewerk=<?= $r['Id'] ?>">Bewerk</a>
                    <a class="btn-verwijder" href="?verwijder=<?= $r['Id'] ?>" onclick="return confirmDelete(this.href)">Verwijderen</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reserveringen)): ?>
            <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">Geen reserveringen gevonden</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
