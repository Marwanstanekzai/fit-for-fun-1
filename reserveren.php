<?php
$pageTitle = "Reserveren";
include 'includes/header.php';

// Moet ingelogd zijn om te reserveren
if (!isset($_SESSION['rol'])) {
    header("Location: /login.php");
    exit();
}

$les_id = isset($_GET['les_id']) ? (int)$_GET['les_id'] : 0;
$succes = '';
$fout = '';

// Verwerk reservering
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bevestig'])) {
    $les_id = (int)$_POST['les_id'];
    $gebruiker_id = $_SESSION['gebruiker_id'];

    try {
        // Haal les details op
        $stmt = $pdo->prepare("SELECT * FROM les WHERE Id = ?");
        $stmt->execute([$les_id]);
        $les = $stmt->fetch();

        // Haal gebruiker details op (lid details)
        $stmt = $pdo->prepare("SELECT * FROM gebruiker WHERE Id = ?");
        $stmt->execute([$gebruiker_id]);
        $user = $stmt->fetch();

        if ($les) {
            // Check of al gereserveerd (simpel)
            $stmt = $pdo->prepare("INSERT INTO reservering (Voornaam, Achternaam, Nummer, Datum, Tijd, Reserveringstatus) VALUES (?, ?, ?, ?, ?, 'Gereserveerd')");
            $stmt->execute([$user['Voornaam'], $user['Achternaam'], 1234, $les['Datum'], $les['Tijd']]);
            $succes = "Je reservering voor " . htmlspecialchars($les['Naam']) . " is bevestigd!";
        }
    } catch (PDOException $e) {
        $fout = "Er is iets misgegaan bij het reserveren.";
    }
}

// Haal les op voor weergave
$les = null;
if ($les_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM les WHERE Id = ?");
    $stmt->execute([$les_id]);
    $les = $stmt->fetch();
}
?>

<div class="container">
    <h1>Les Reserveren</h1>

    <?php if ($succes): ?>
        <div class="succes"><?= htmlspecialchars($succes) ?></div>
        <a href="/lessen.php" class="button">Terug naar lessen</a>
    <?php elseif ($fout): ?>
        <div class="fout"><?= htmlspecialchars($fout) ?></div>
        <a href="/lessen.php" class="button">Terug naar lessen</a>
    <?php elseif ($les): ?>
        <div style="max-width:500px; border:2px solid #000; padding:30px; background:#f9f9f9;">
            <h2 style="margin-bottom:15px;"><?= htmlspecialchars($les['Naam']) ?></h2>
            <p style="margin-bottom:10px;"><strong>Datum:</strong> <?= date('d-m-Y', strtotime($les['Datum'])) ?></p>
            <p style="margin-bottom:10px;"><strong>Tijd:</strong> <?= $les['Tijd'] ?></p>
            <p style="margin-bottom:10px;"><strong>Trainer:</strong> <?= htmlspecialchars($les['Trainer']) ?></p>
            
            <form method="POST" style="margin-top:20px;">
                <input type="hidden" name="les_id" value="<?= $les['Id'] ?>">
                <button type="submit" name="bevestig" class="button" style="width:100%;">Bevestig Reservering</button>
            </form>
            <a href="/lessen.php" style="display:block; text-align:center; margin-top:15px; color:#1F3864; font-weight:bold; text-decoration:none;">Annuleren</a>
        </div>
    <?php else: ?>
        <p>Geen les geselecteerd. <a href="/lessen.php">Bekijk hier alle lessen.</a></p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


