<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

$pageTitle = "Ajouter une Découverte"; // Titre spécifique pour cette page

// Inclure l'entête HTML
require_once __DIR__ . '/includes/header.php';

// --- Logique pour récupérer les catégories ---
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, nom FROM categories ORDER BY nom ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Erreur lors du chargement des catégories : ' . $e->getMessage() . '</div>';
}

// --- Affichage du formulaire ---
?>

<h1>Ajouter une Nouvelle Découverte</h1>

<?php
// Afficher les messages d'erreur ou de succès s'il y en a (après redirection depuis traiter_ajout.php)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = $_GET['message'] ?? 'Découverte ajoutée avec succès !';
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($message) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } elseif ($_GET['status'] == 'error') {
        $message = $_GET['message'] ?? 'Une erreur est survenue lors de l\'ajout.';
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Erreur : ' . htmlspecialchars($message) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
?>

<form action="traiter_ajout.php" method="POST">
    <div class="mb-3">
        <label for="titre" class="form-label">Titre de la Découverte :</label>
        <input type="text" class="form-control" id="titre" name="titre" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description / Utilité :</label>
        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
    </div>

    <div class="mb-3">
        <label for="categorie_id" class="form-label">Catégorie :</label>
        <select class="form-select" id="categorie_id" name="categorie_id" required>
            <option value="">-- Sélectionner une catégorie --</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($categories)): ?>
            <small class="form-text text-muted">Aucune catégorie trouvée. Ajoutez-en directement dans la base de données pour l'instant.</small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="liens_utiles" class="form-label">Liens Utiles (un par ligne ou séparés par des virgules) :</label>
        <textarea class="form-control" id="liens_utiles" name="liens_utiles" rows="3"></textarea>
        <small class="form-text text-muted">Ex: https://docs.example.com, https://tutorial.example.com</small>
    </div>

    <div class="mb-3">
        <label for="tags" class="form-label">Tags (séparés par des virgules) :</label>
        <input type="text" class="form-control" id="tags" name="tags">
        <small class="form-text text-muted">Ex: Frontend, JavaScript, Animation, Utile au Quotidien</small>
    </div>


    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer la Découverte</button>
</form>

<?php
// Inclure le pied de page HTML
require_once __DIR__ . '/includes/footer.php';
?>