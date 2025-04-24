<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

// Vérifier si la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valider et récupérer les données du formulaire
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorie_id = filter_var($_POST['categorie_id'] ?? '', FILTER_VALIDATE_INT);
    $liens_utiles = trim($_POST['liens_utiles'] ?? '');
    $tags_input = trim($_POST['tags'] ?? ''); // Récupérer l'input des tags

    // Validation basique côté serveur
    if (empty($titre) || empty($description) || $categorie_id === false || $categorie_id === null) {
        header("Location: ajouter_decouverte.php?status=error&message=" . urlencode("Le titre, la description et la catégorie sont obligatoires."));
        exit();
    }

    // Séparer les tags par les virgules et nettoyer les espaces
    $tags_array = array_filter(array_map('trim', explode(',', $tags_input)));
    // Supprimer les doublons de tags
    $tags_array = array_unique($tags_array);

    try {
        // --- Début de la transaction ---
        $pdo->beginTransaction();

        // 1. Insérer la découverte dans la table decouvertes
        $stmt_decouverte = $pdo->prepare("INSERT INTO decouvertes (titre, description, categorie_id, liens_utiles) VALUES (?, ?, ?, ?)");
        $stmt_decouverte->execute([$titre, $description, $categorie_id, $liens_utiles]);
        $decouverte_id = $pdo->lastInsertId(); // Récupérer l'ID de la découverte nouvellement insérée

        // 2. Gérer les tags et les associations
        if (!empty($tags_array) && $decouverte_id) {
            $stmt_find_tag = $pdo->prepare("SELECT id FROM tags WHERE nom = ?");
            $stmt_insert_tag = $pdo->prepare("INSERT INTO tags (nom) VALUES (?)");
            $stmt_link_tag = $pdo->prepare("INSERT INTO decouverte_tag (decouverte_id, tag_id) VALUES (?, ?)");

            foreach ($tags_array as $tag_nom) {
                if (empty($tag_nom)) continue; // Sauter les tags vides

                // Chercher si le tag existe déjà
                $stmt_find_tag->execute([$tag_nom]);
                $tag = $stmt_find_tag->fetch();

                $tag_id = null;
                if ($tag) {
                    $tag_id = $tag['id']; // Tag existant, utiliser son ID
                } else {
                    // Tag n'existe pas, l'insérer
                    $stmt_insert_tag->execute([$tag_nom]);
                    $tag_id = $pdo->lastInsertId(); // Récupérer l'ID du nouveau tag
                }

                // Lier la découverte au tag dans la table d'association
                if ($tag_id) {
                    // Utiliser REPLACE INTO pour éviter les erreurs si le lien existe déjà (utile pour l'édition)
                    // ou juste INSERT IGNORE si on est sûr que l'ajout est neuf
                    $stmt_link_tag->execute([$decouverte_id, $tag_id]);
                }
            }
        }

        // --- Commit de la transaction ---
        $pdo->commit();

        // Rediriger l'utilisateur après l'ajout réussi
        header("Location: index.php?status=success&message=" . urlencode("Découverte '" . $titre . "' ajoutée avec succès !"));
        exit();

    } catch (PDOException $e) {
        // --- Rollback en cas d'erreur ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erreur d\'insertion découverte (avec tags) : ' . $e->getMessage());
        header("Location: ajouter_decouverte.php?status=error&message=" . urlencode("Une erreur s'est produite lors de l'enregistrement : " . $e->getMessage()));
        exit();
    }

} else {
    // Si la requête n'est pas POST, rediriger vers la page d'ajout
    header("Location: ajouter_decouverte.php");
    exit();
}