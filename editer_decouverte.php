<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

$pageTitle = "Modifier une Découverte";

// Récupérer l'ID de la découverte depuis l'URL
$decouverte_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

// Rediriger si l'ID est manquant ou invalide
if (!$decouverte_id) {
    header("Location: index.php?status=error&message=" . urlencode("ID de découverte manquant ou invalide."));
    exit();
}

// --- Logique pour récupérer la découverte à éditer ---
$decouverte = null;
$tags_associes = []; // Pour stocker les tags associés
try {
    // Récupérer la découverte et les tags associés
     $stmt = $pdo->prepare("
        SELECT
            d.*,
            GROUP_CONCAT(t.nom SEPARATOR ', ') as tags_noms
        FROM
            decouvertes d
        LEFT JOIN
            decouverte_tag dt ON d.id = dt.decouverte_id
        LEFT JOIN
            tags t ON dt.tag_id = t.id
        WHERE
            d.id = ?
        GROUP BY
            d.id
    ");
    $stmt->execute([$decouverte_id]);
    $decouverte = $stmt->fetch();

    // Rediriger si la découverte n'existe pas
    if (!$decouverte) {
        header("Location: index.php?status=error&message=" . urlencode("Découverte introuvable."));
        exit();
    }

    // Les tags sont déjà agrégés dans $decouverte['tags_noms']

} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Erreur lors du chargement de la découverte : ' . $e->getMessage() . '</div>';
    exit(); // Arrêter l'exécution si on ne peut pas charger la découverte
}

// --- Logique pour récupérer les catégories (comme sur la page d'ajout) ---
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, nom FROM categories ORDER BY nom ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">Erreur lors du chargement des catégories : ' . $e->getMessage() . '</div>';
}

// Inclure l'entête HTML (après avoir défini $pageTitle)
require_once __DIR__ . '/includes/header.php';

// --- Affichage du formulaire d'édition ---
?>

<h1>Modifier la Découverte</h1>

<?php
// Afficher les messages d'état (si redirection depuis traiter_edition.php avec une erreur)
if (isset($_GET['status']) && $_GET['status'] == 'error') {
     $message = $_GET['message'] ?? 'Une erreur est survenue lors de la modification.';
     echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Erreur : ' . htmlspecialchars($message) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>


<form action="traiter_edition.php" method="POST">
    <input type="hidden" name="id" value="<?= $decouverte['id'] ?>">

    <div class="mb-3">
        <label for="titre" class="form-label">Titre de la Découverte :</label>
        <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($decouverte['titre']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description / Utilité :</label>
        <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($decouverte['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label for="categorie_id" class="form-label">Catégorie :</label>
        <select class="form-select" id="categorie_id" name="categorie_id" required>
            <option value="">-- Sélectionner une catégorie --</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?= $categorie['id'] ?>" <?= ($categorie['id'] == $decouverte['categorie_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categorie['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
         <?php if (empty($categories)): ?>
            <small class="form-text text-muted">Aucune catégorie trouvée.</small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="liens_utiles" class="form-label">Liens Utiles (un par ligne ou séparés par des virgules) :</label>
        <textarea class="form-control" id="liens_utiles" name="liens_utiles" rows="3"><?= htmlspecialchars($decouverte['liens_utiles']) ?></textarea>
        <small class="form-text text-muted">Ex: https://docs.example.com, https://tutorial.example.com</small>
    </div>

     <div class="mb-3">
        <label for="tags" class="form-label">Tags (séparés par des virgules) :</label>
        <input type="text" class="form-control" id="tags" name="tags" value="<?= htmlspecialchars($decouverte['tags_noms'] ?? '') ?>">
        <small class="form-text text-muted">Ex: Frontend, JavaScript, Animation, Utile au Quotidien</small>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Annuler</a>
</form>

<?php
// Inclure le pied de page HTML
require_once __DIR__ . '/includes/footer.php';
?>