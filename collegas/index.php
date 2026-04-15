<?php
$pageTitle = "Collega Dashboard";
include '../includes/header.php';
checkCollega();

// Haal alleen de komende 10 lessen op vanaf vandaag
$lessen = $pdo->query("SELECT * FROM les WHERE Datum >= CURDATE() ORDER BY Datum, Tijd LIMIT 10")->fetchAll();
?>

<div class="container">
    <h1>Aankomende Lessen</h1>
    <!-- Tabel voor overzicht -->
    <table>
        <thead>
            <tr>
                <th>Les</th>
                <th>Trainer</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Max. deelnemers</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lessen as $les): ?>
            <tr>
                <td data-label="Les"><?= htmlspecialchars($les['Naam']) ?></td>
                <td data-label="Trainer"><?= htmlspecialchars($les['Trainer']) ?></td>
                <td data-label="Datum"><?= date('d-m-Y', strtotime($les['Datum'])) ?></td>
                <td data-label="Tijd"><?= $les['Tijd'] ?></td>
                <td data-label="Max. deelnemers"><?= $les['MaxAantalPersonen'] ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($lessen)): ?>
            <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">Geen lessen gepland</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
