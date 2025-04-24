<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

// Vérifier si la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valider et récupérer les données du formulaire
    $decouverte_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT); // Récupérer l'ID
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorie_id = filter_var($_POST['categorie_id'] ?? '', FILTER_VALIDATE_INT);
    $liens_utiles = trim($_POST['liens_utiles'] ?? '');
    $tags_input = trim($_POST['tags'] ?? ''); // Récupérer l'input des tags

     // Validation basique côté serveur (incluant l'ID)
    if (!$decouverte_id || empty($titre) || empty($description) || $categorie_id === false || $categorie_id === null) {
        // Rediriger vers la page d'édition avec un message d'erreur
        $error_message = "Données de formulaire incomplètes ou ID de découverte invalide.";
        $redirect_url = "editer_decouverte.php"; // URL par défaut en cas d'ID manquant
        if ($decouverte_id) { // Si l'ID est valide, rediriger vers la page d'édition spécifique
             $redirect_url .= "?id=" . $decouverte_id;
        } else { // Sinon, rediriger vers l'accueil
             $redirect_url = "index.php";
        }
        header("Location: " . $redirect_url . "&status=error&message=" . urlencode($error_message));
        exit();
    }


     // Séparer les tags par les virgules et nettoyer les espaces
    $tags_array = array_filter(array_map('trim', explode(',', $tags_input)));
    $tags_array = array_unique($tags_array); // Supprimer les doublons

    try {
        // --- Début de la transaction ---
        $pdo->beginTransaction();

        // 1. Mettre à jour la découverte dans la table decouvertes
        $stmt_decouverte = $pdo->prepare("UPDATE decouvertes SET titre = ?, description = ?, categorie_id = ?, liens_utiles = ? WHERE id = ?");
        $stmt_decouverte->execute([$titre, $description, $categorie_id, $liens_utiles, $decouverte_id]);

        // 2. Gérer les tags et les associations
        // Supprimer les anciennes liaisons pour cette découverte
        $stmt_delete_links = $pdo->prepare("DELETE FROM decouverte_tag WHERE decouverte_id = ?");
        $stmt_delete_links->execute([$decouverte_id]);

        // Ajouter les nouvelles liaisons
        if (!empty($tags_array)) {
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
                     // Insérer la nouvelle liaison (on a déjà supprimé les anciennes)
                    $stmt_link_tag->execute([$decouverte_id, $tag_id]);
                 }
            }
        }

        // --- Commit de la transaction ---
        $pdo->commit();

        // Rediriger l'utilisateur après l'édition réussie
        header("Location: index.php?status=success&message=" . urlencode("Découverte '" . $titre . "' modifiée avec succès !"));
        exit();

    } catch (PDOException $e) {
        // --- Rollback en cas d'erreur ---
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erreur d\'édition découverte (avec tags) : ' . $e->getMessage());
        // Rediriger vers la page d'édition avec un message d'erreur, en passant l'ID
        header("Location: editer_decouverte.php?id=" . $decouverte_id . "&status=error&message=" . urlencode("Une erreur s'est produite lors de la modification : " . $e->getMessage()));
        exit();
    }

} else {
    // Si la requête n'est pas POST, rediriger vers l'accueil
    header("Location: index.php");
    exit();
}