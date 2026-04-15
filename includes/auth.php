<?php
function checkLogin() {
    if (!isset($_SESSION['gebruiker_id'])) {
        header("Location: /");
        exit();
    }
}

function checkAdmin() {
    checkLogin();
    if ($_SESSION['rol'] !== 'admin') {
        header("Location: /collegas/index.php");
        exit();
    }
}

function checkCollega() {
    checkLogin();
    if (!in_array($_SESSION['rol'], ['admin', 'collega'])) {
        header("Location: /");
        exit();
    }
}
?>