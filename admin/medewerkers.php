<?php
$pageTitle = "Medewerkers Beheren";
include '../includes/header.php';
checkAdmin();

$succes = '';
$fout   = '';

// Unhappy medewerkers
$simulate_db_error = false;

// 1. Toevoegen van een nieuwe medewerker
if (isset($_POST['actie']) && $_POST['actie'] === 'toevoegen') {
    $voornaam   = trim($_POST['voornaam']);
    $achternaam = trim($_POST['achternaam']);
    $username   = trim($_POST['username']);
    $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
    $rol_naam   = $_POST['rol'] ?? '';

    if (empty($voornaam) || empty($achternaam) || empty($username) || empty($_POST['wachtwoord']) || empty($rol_naam)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {

    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO gebruiker (Voornaam, Achternaam, Gebruikersnaam, Wachtwoord) VALUES (?, ?, ?, ?)");
        $stmt->execute([$voornaam, $achternaam, $username, $wachtwoord]);
        $gebruiker_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO rol (GebruikerId, Naam) VALUES (?, ?)");
        $stmt->execute([$gebruiker_id, $rol_naam]);
        $pdo->commit();
        $succes = "Medewerker succesvol toegevoegd!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $fout = "Fout: Gebruikersnaam bestaat al of systeemfout.";
    }
    }
}

// 2. Verwijderen van een medewerker
if (isset($_GET['verwijder'])) {
    $id = (int)$_GET['verwijder'];
    if ($id === $_SESSION['gebruiker_id']) {
        $fout = "Je kunt je eigen account niet verwijderen!";
    } else {
        try {
            if ($simulate_db_error) throw new PDOException("Simulatie");
            $pdo->prepare("DELETE FROM gebruiker WHERE Id = ?")->execute([$id]);
            $succes = "Medewerker succesvol verwijderd!";
        } catch (PDOException $e) {
            $fout = "Fout bij het verwijderen van de medewerker.";
        }
    }
}

// 3. Bewerken van een medewerker
if (isset($_POST['actie']) && $_POST['actie'] === 'bewerken') {
    $id       = (int)$_POST['id'];
    $voornaam = trim($_POST['voornaam']);
    $achternaam = trim($_POST['achternaam']);
    $username = trim($_POST['username']);
    $rol_naam = $_POST['rol'] ?? '';

    if (empty($voornaam) || empty($achternaam) || empty($username) || empty($rol_naam)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {

    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        if (!empty($_POST['wachtwoord'])) {
            $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE gebruiker SET Voornaam=?, Achternaam=?, Gebruikersnaam=?, Wachtwoord=? WHERE Id=?");
            $stmt->execute([$voornaam, $achternaam, $username, $wachtwoord, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE gebruiker SET Voornaam=?, Achternaam=?, Gebruikersnaam=? WHERE Id=?");
            $stmt->execute([$voornaam, $achternaam, $username, $id]);
        }
        
        $stmt = $pdo->prepare("UPDATE rol SET Naam=? WHERE GebruikerId=?");
        $stmt->execute([$rol_naam, $id]);
        
        $succes = "Medewerker succesvol bijgewerkt!";
    } catch (PDOException $e) {
        $fout = "Fout bij het bijwerken van de medewerker.";
    }
    }
}

// Haal alle medewerkers op
$tabel_fout = '';
try {
    if ($simulate_db_error) throw new PDOException("Kan medewerkers niet inladen!");
    $medewerkers = $pdo->query("SELECT g.*, r.Naam as rol FROM gebruiker g JOIN rol r ON g.Id = r.GebruikerId ORDER BY r.Naam, g.Voornaam")->fetchAll();
} catch (PDOException $e) {
    $tabel_fout = "Systeemfout: " . $e->getMessage();
    $medewerkers = [];
}

$bewerkMedewerker = null;
if (isset($_GET['bewerk'])) {
    $stmt = $pdo->prepare("SELECT g.*, r.Naam as rol FROM gebruiker g JOIN rol r ON g.Id = r.GebruikerId WHERE g.Id = ?");
    $stmt->execute([(int)$_GET['bewerk']]);
    $bewerkMedewerker = $stmt->fetch();
}
?>

<div class="container">
    <a class="terug" href="/admin/index.php">← Terug naar dashboard</a>
    <h1>Medewerkers Beheren</h1>
    <?php if ($succes): ?><div class="succes"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($fout):   ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

    <form method="POST" class="toevoeg-form" novalidate>
        <input type="hidden" name="actie" value="<?= $bewerkMedewerker ? 'bewerken' : 'toevoegen' ?>">
        <?php if ($bewerkMedewerker): ?>
            <input type="hidden" name="id" value="<?= $bewerkMedewerker['Id'] ?>">
        <?php endif; ?>
        <div>
            <label>Voornaam</label>
            <input type="text" name="voornaam" required placeholder="Voornaam" value="<?= htmlspecialchars($bewerkMedewerker['Voornaam'] ?? '') ?>">
        </div>
        <div>
            <label>Achternaam</label>
            <input type="text" name="achternaam" required placeholder="Achternaam" value="<?= htmlspecialchars($bewerkMedewerker['Achternaam'] ?? '') ?>">
        </div>
        <div>
            <label>Gebruikersnaam</label>
            <input type="text" name="username" required placeholder="Gebruikersnaam" value="<?= htmlspecialchars($bewerkMedewerker['Gebruikersnaam'] ?? '') ?>">
        </div>
        <div>
            <label>Wachtwoord <?= $bewerkMedewerker ? '(leeglaten om te behouden)' : '' ?></label>
            <input type="password" name="wachtwoord" <?= $bewerkMedewerker ? '' : 'required' ?> placeholder="Wachtwoord">
        </div>
        <div>
            <label>Rol</label>
            <select name="rol" required>
                <option value="Administrator" <?= (isset($bewerkMedewerker) && $bewerkMedewerker['rol'] === 'Administrator') ? 'selected' : '' ?>>Administrator</option>
                <option value="Medewerker" <?= (isset($bewerkMedewerker) && $bewerkMedewerker['rol'] === 'Medewerker') ? 'selected' : '' ?>>Medewerker</option>
                <option value="Lid" <?= (isset($bewerkMedewerker) && $bewerkMedewerker['rol'] === 'Lid') ? 'selected' : '' ?>>Lid</option>
            </select>
        </div>
        <button type="submit" class="button" style="height:38px; padding:0 20px; font-size:14px;"><?= $bewerkMedewerker ? 'Opslaan' : 'Toevoegen' ?></button>
        <?php if ($bewerkMedewerker): ?>
            <a href="/admin/medewerkers.php" class="button" style="height:38px; padding:0 20px; background:#888; border-color:#555; box-shadow:0 3px 0 #333; font-size:14px;">Annuleren</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($tabel_fout)): ?>
        <div class="fout" style="margin-bottom: 20px;"><?= htmlspecialchars($tabel_fout) ?></div>
    <?php endif; ?>
    <table>
        <thead><tr><th>#</th><th>Naam</th><th>Gebruikersnaam</th><th>Rol</th><th>Acties</th></tr></thead>
        <tbody>
            <?php foreach ($medewerkers as $m): ?>
            <tr>
                <td data-label="ID"><?= $m['Id'] ?></td>
                <td data-label="Naam"><?= htmlspecialchars($m['Voornaam'] . ' ' . $m['Achternaam']) ?></td>
                <td data-label="Gebruikersnaam"><?= htmlspecialchars($m['Gebruikersnaam']) ?></td>
                <td data-label="Rol"><?= htmlspecialchars($m['rol']) ?></td>
                <td data-label="Acties">
                    <a class="btn-bewerk" href="?bewerk=<?= $m['Id'] ?>">Bewerk</a>
                    <?php if ($m['Id'] != $_SESSION['gebruiker_id']): ?>
                    <a class="btn-verwijder" href="?verwijder=<?= $m['Id'] ?>" onclick="return confirmDelete(this.href)">Verwijder</a>
                    <?php endif; ?>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>


