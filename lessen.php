<?php
$pageTitle = "Onze Lessen";
include 'includes/header.php';

// Zoek- en filterfunctionaliteit
$zoekopdracht = isset($_GET['search']) ? trim($_GET['search']) : '';
$lessen = [];
$fout = '';

try {
    // Basis query: toon alleen lessen vanaf vandaag
    $sql = "SELECT * FROM les WHERE Datum >= CURDATE()";
    $params = [];

    // Als er een zoekopdracht is, voegen we die toe aan de query
    if (!empty($zoekopdracht)) {
        $sql .= " AND (Naam LIKE ? OR Trainer LIKE ?)";
        $params[] = "%$zoekopdracht%";
        $params[] = "%$zoekopdracht%";
    }

    $sql .= " ORDER BY Datum ASC, Tijd ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lessen = $stmt->fetchAll();
} catch (PDOException $e) {
    $fout = "Systeemfout bij het inladen van de lessen.";
}
?>

<div class="container">
    <h1>Bekijk onze Groepslessen</h1>
    <p style="margin-bottom:20px;">Vind de les die bij jou past en reserveer direct je plek!</p>

    <!-- Filter Sectie (zoals gevraagd met de zoekfunctie) -->
    <div class="filter-sectie">
        <form method="GET" class="filter-form">
            <div class="filter-groep">
                <label for="search">Zoeken op naam of trainer</label>
                <input type="text" name="search" id="search" placeholder="Type hier..." value="<?= htmlspecialchars($zoekopdracht) ?>">
            </div>
            <button type="submit" class="button">Filteren</button>
            <?php if (!empty($zoekopdracht)): ?>
                <a href="/lessen.php" class="button" style="background:#888; border-color:#555; box-shadow:0 3px 0 #333;">Wissen</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($fout): ?>
        <div class="fout"><?= htmlspecialchars($fout) ?></div>
    <?php endif; ?>

    <?php if (empty($lessen)): ?>
        <div class="fout" style="background:#fdfdfd; border-style:dashed;">
            Geen lessen gevonden die voldoen aan je zoekopdracht.
        </div>
    <?php else: ?>
        <div class="lessen-grid">
            <?php foreach ($lessen as $les): ?>
                <div class="les-kaart">
                    <div style="background: #1F3864; color: #ff8c00; padding: 5px 10px; font-weight: bold; margin-bottom: 10px; font-size: 12px; display: inline-block; border: 1px solid #000;">
                        €<?= number_format($les['Prijs'], 2, ',', '.') ?>
                    </div>
                    <h3><?= htmlspecialchars($les['Naam']) ?></h3>
                    <p><strong>Trainer:</strong> <?= htmlspecialchars($les['Trainer']) ?></p>
                    <p><strong>Datum:</strong> <?= date('d-m-Y', strtotime($les['Datum'])) ?></p>
                    <p><strong>Tijd:</strong> <?= $les['Tijd'] ?></p>
                    <p><strong>Max. deelnemers:</strong> <?= $les['MaxAantalPersonen'] ?></p>
                    
                    <div style="margin-top:20px;">
                        <a href="/reserveren.php?les_id=<?= $les['Id'] ?>" class="button" style="width:100%;">Meteen Reserveren</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
