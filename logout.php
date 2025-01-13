<?php
session_start();

// Distrugge tutte le variabili di sessione
session_unset();

// Distrugge la sessione
session_destroy();

// Reindirizza alla pagina di login
header('Location: login.php');
exit;
?>