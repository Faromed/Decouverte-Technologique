<?php
// Fichier de configuration pour la base de données

define('DB_HOST', 'localhost');
define('DB_NAME', 'ma_veille_tech'); // Nom de la base de données que vous avez créée
define('DB_USER', 'root'); // Votre nom d'utilisateur de base de données
define('DB_PASS', ''); // Votre mot de passe de base de données

// Options PDO (facultatif mais recommandé)
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Rapporte les erreurs SQL sous forme d'exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère les résultats sous forme de tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                // Désactive l'émulation des requêtes préparées (plus sûr)
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"   // Définit le jeu de caractères
];
?>