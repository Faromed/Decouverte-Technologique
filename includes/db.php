<?php
// Fichier pour la connexion à la base de données via PDO

require_once __DIR__ . '/../config.php';

$pdo = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    // En cas d'erreur de connexion
    // En mode développement, vous pouvez afficher l'erreur :
    // die('Erreur de connexion à la base de données : ' . $e->getMessage());

    // En mode production, il est préférable de logger l'erreur et d'afficher un message générique :
    error_log('Database Connection Error: ' . $e->getMessage());
    die('Une erreur est survenue lors de la connexion à la base de données.');
}
?>