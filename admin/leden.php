<?php
$pageTitle = "Leden Beheren";
$showSearch = true;
include '../includes/header.php';
checkAdmin();

$succes = '';
$fout   = '';

// Unhappy leden
$simulate_db_error = false;

// 1. Toevoegen van een nieuw lid
if (isset($_POST['actie']) && $_POST['actie'] === 'toevoegen') {
    $voornaam       = trim($_POST['voornaam']);
    $achternaam     = trim($_POST['achternaam']);
    $email          = trim($_POST['email']);
    $mobiel         = trim($_POST['telefoon']);
    $relatienr      = rand(1000, 9999);

    if (empty($voornaam) || empty($achternaam) || empty($email) || empty($mobiel)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {
    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $stmt = $pdo->prepare("INSERT INTO lid (Voornaam, Achternaam, Email, Mobiel, Relatienummer) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$voornaam, $achternaam, $email, $mobiel, $relatienr]);
        $succes = "Lid succesvol toegevoegd!";
    } catch (PDOException $e) {
        $fout = "Fout: e-mailadres bestaat al.";
    }
    }
}

// 2. Verwijderen van een lid
if (isset($_GET['verwijder'])) {
    $id = (int)$_GET['verwijder'];
    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $pdo->prepare("DELETE FROM lid WHERE Id = ?")->execute([$id]);
        $succes = "Lid succesvol verwijderd!";
    } catch (PDOException $e) {
        $fout = "Fout bij het verwijderen van lid.";
    }
}

// 3. Bewerken van een lid
if (isset($_POST['actie']) && $_POST['actie'] === 'bewerken') {
    $id             = (int)$_POST['id'];
    $voornaam       = trim($_POST['voornaam']);
    $achternaam     = trim($_POST['achternaam']);
    $email          = trim($_POST['email']);
    $mobiel         = trim($_POST['telefoon']);

    if (empty($voornaam) || empty($achternaam) || empty($email) || empty($mobiel)) {
        $fout = "Vul a.u.b. alle velden in.";
    } else {
    try {
        if ($simulate_db_error) throw new PDOException("Simulatie");
        $stmt = $pdo->prepare("UPDATE lid SET Voornaam=?, Achternaam=?, Email=?, Mobiel=? WHERE Id=?");
        $stmt->execute([$voornaam, $achternaam, $email, $mobiel, $id]);
        $succes = "Lid succesvol bijgewerkt!";
    } catch (PDOException $e) {
        $fout = "Fout bij het bijwerken van lid. Controleer of het e-mailadres niet al bestaat.";
    }
    }
}

// --- NIEUWE ZOEK LOGICA ---
$zoekterm = $_GET['zoek'] ?? '';

$tabel_fout = '';
try {
    if ($simulate_db_error) throw new PDOException("Kan gegevens niet inladen!");

    if ($zoekterm !== '') {
        $stmt = $pdo->prepare("SELECT * FROM lid WHERE Voornaam LIKE ? OR Achternaam LIKE ? OR CONCAT(Voornaam, ' ', Achternaam) LIKE ? ORDER BY Achternaam, Voornaam");
        $termLike = '%' . $zoekterm . '%';
        $stmt->execute([$termLike, $termLike, $termLike]);
        $leden = $stmt->fetchAll();
    } else {
        $leden = $pdo->query("SELECT * FROM lid ORDER BY Achternaam, Voornaam")->fetchAll();
    }
} catch (PDOException $e) {
    $tabel_fout = "Systeemfout: " . $e->getMessage();
    $leden = [];
}

$bewerkLid = null;
if (isset($_GET['bewerk'])) {
    $stmt = $pdo->prepare("SELECT * FROM lid WHERE Id = ?");
    $stmt->execute([(int)$_GET['bewerk']]);
    $bewerkLid = $stmt->fetch();
}
?>
<div class="container">
    <a class="terug" href="/admin/index.php">← Terug naar dashboard</a>
    
    <!-- Laten zien of we een zoek filter hebben -->
    <?php if ($zoekterm !== ''): ?>
        <h1>Leden (gezocht op: "<?= htmlspecialchars($zoekterm) ?>")</h1>
        <!-- Knop om de zoekfilters te wissen door het veld leeg te maken via 'leden.php' zonder parameters -->
        <a href="/admin/leden.php" style="color:red; margin-bottom:15px; display:inline-block;">Zoekopdracht wissen</a>
    <?php else: ?>
        <h1>Leden Beheren</h1>
    <?php endif; ?>

    <?php if ($succes): ?><div class="succes"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($fout):   ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

    <!-- Formulier voor het toevoegen / bewerken -->
    <form method="POST" class="toevoeg-form" novalidate>
        <input type="hidden" name="actie" value="<?= $bewerkLid ? 'bewerken' : 'toevoegen' ?>">
        <?php if ($bewerkLid): ?>
            <input type="hidden" name="id" value="<?= $bewerkLid['Id'] ?>">
        <?php endif; ?>
        <div>
            <label>Voornaam</label>
            <input type="text" name="voornaam" required placeholder="Voornaam" value="<?= htmlspecialchars($bewerkLid['Voornaam'] ?? '') ?>">
        </div>
        <div>
            <label>Achternaam</label>
            <input type="text" name="achternaam" required placeholder="Achternaam" value="<?= htmlspecialchars($bewerkLid['Achternaam'] ?? '') ?>">
        </div>
        <div>
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="email@voorbeeld.nl" value="<?= htmlspecialchars($bewerkLid['Email'] ?? '') ?>">
        </div>
        <div>
            <label>Telefoon</label>
            <input type="text" name="telefoon" placeholder="06-12345678" value="<?= htmlspecialchars($bewerkLid['Mobiel'] ?? '') ?>">
        </div>
        
        <button type="submit" class="button"><?= $bewerkLid ? 'Opslaan' : 'Toevoegen' ?></button>
        
        <?php if ($bewerkLid): ?>
            <a href="/admin/leden.php" class="button" style="background:#888; border-color:#555; box-shadow:0 3px 0 #333;">Annuleren</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($tabel_fout)): ?>
        <div class="fout" style="margin-bottom: 20px;"><?= htmlspecialchars($tabel_fout) ?></div>
    <?php endif; ?>
    <!-- Tabel om de resultaten te tonen -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Voornaam</th>
                <th>Achternaam</th>
                <th>E-mail</th>
                <th>Telefoon</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leden as $lid): ?>
            <tr>
                <td data-label="ID"><?= $lid['Id'] ?></td>
                <td data-label="Voornaam"><?= htmlspecialchars($lid['Voornaam']) ?></td>
                <td data-label="Achternaam"><?= htmlspecialchars($lid['Achternaam']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($lid['Email']) ?></td>
                <td data-label="Telefoon"><?= htmlspecialchars($lid['Mobiel']) ?></td>
                <td data-label="Acties">
                    <a class="btn-bewerk" href="?bewerk=<?= $lid['Id'] ?>">Bewerk</a>
                    <a class="btn-verwijder" href="?verwijder=<?= $lid['Id'] ?>" onclick="return confirmDelete(this.href)">Verwijder</a>
                </td>

            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($leden)): ?>
            <tr>
                <td colspan="7" style="text-align:center; color:#999; padding:20px;">
                    Geen leden gevonden <?= $zoekterm ? 'voor "'.htmlspecialchars($zoekterm).'"' : '' ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>