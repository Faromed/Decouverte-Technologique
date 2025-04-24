<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

// Assurez-vous que la requête est bien une requête POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupérer l'ID de la découverte depuis les données POST
    $decouverte_id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    // Valider l'ID
    if (!$decouverte_id) {
        header("Location: index.php?status=error&message=" . urlencode("ID de découverte manquant ou invalide pour la suppression."));
        exit();
    }

    try {
        // On n'a pas besoin de transaction ici grâce au ON DELETE CASCADE sur la table d'association
        // Supprimer la découverte
        $stmt = $pdo->prepare("DELETE FROM decouvertes WHERE id = ?");
        $stmt->execute([$decouverte_id]);

        // Vérifier si une ligne a été affectée (si la suppression a réussi)
        if ($stmt->rowCount() > 0) {
             header("Location: index.php?status=success&message=" . urlencode("Découverte supprimée avec succès !"));
        } else {
             // Si rowCount est 0, c'est que l'ID n'existait pas
             header("Location: index.php?status=error&message=" . urlencode("La découverte avec l'ID " . $decouverte_id . " n'a pas été trouvée."));
        }
        exit();

    } catch (PDOException $e) {
        // Gérer les erreurs de suppression
        error_log('Erreur suppression découverte : ' . $e->getMessage());
        header("Location: index.php?status=error&message=" . urlencode("Une erreur s'est produite lors de la suppression : " . $e->getMessage()));
        exit();
    }

} else {
    // Si la requête n'est pas POST, rediriger vers l'accueil ou afficher une erreur
    header("Location: index.php?status=error&message=" . urlencode("Méthode non autorisée."));
    exit();
}