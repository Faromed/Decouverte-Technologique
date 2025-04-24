<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

$pageTitle = "Liste des Découvertes"; // Titre spécifique pour cette page

// Inclure l'entête HTML
require_once __DIR__ . '/includes/header.php';

// --- Logique d'affichage des découvertes ---
try {
    $filter_tag_id = filter_var($_GET['tag_id'] ?? null, FILTER_VALIDATE_INT);
    // Ajoutez d'autres filtres ici si nécessaire, par exemple:
    // $filter_category_id = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT);
    // $search_query = trim($_GET['search'] ?? '');


    // Début de la construction de la requête SQL
    $sql = "
        SELECT
            d.*,
            c.nom as categorie_nom,
            GROUP_CONCAT(t.nom SEPARATOR ', ') as tags_noms,
            GROUP_CONCAT(t.id SEPARATOR ',') as tags_ids
        FROM
            decouvertes d
        JOIN
            categories c ON d.categorie_id = c.id
    ";

    $where_clauses = [];
    $params = [];
    $join_clauses = []; // Pour ajouter des JOIN si nécessaire pour le filtrage

    // --- Logique de filtrage ---

    // Exemple de filtre par tag ID
    if ($filter_tag_id) {
        // On doit joindre la table d'association pour filtrer sur un tag spécifique
        // On utilise JOIN ici car on veut *seulement* les découvertes qui ont ce tag.
        $join_clauses[] = " JOIN decouverte_tag dt_filter ON d.id = dt_filter.decouverte_id ";
        $where_clauses[] = "dt_filter.tag_id = ?";
        $params[] = $filter_tag_id;
    }

    // Ajoutez d'autres conditions de filtre ici, par exemple:
    /*
    if ($filter_category_id) {
        $where_clauses[] = "d.categorie_id = ?";
        $params[] = $filter_category_id;
    }

    if (!empty($search_query)) {
        $where_clauses[] = "(d.titre LIKE ? OR d.description LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
         // Si vous voulez chercher dans les tags aussi, cela devient plus complexe
         // et pourrait nécessiter des JOIN supplémentaires et un DISTINCT
    }
    */

    // On ajoute toujours les LEFT JOIN pour récupérer TOUS les tags de TOUTES les découvertes
    // Ceci est différent du JOIN dt_filter utilisé UNIQUEMENT pour le filtrage.
    $sql .= " LEFT JOIN decouverte_tag dt ON d.id = dt.decouverte_id ";
    $sql .= " LEFT JOIN tags t ON dt.tag_id = t.id ";

    // Ajouter les JOINs spécifiques au filtrage si elles existent
    if (!empty($join_clauses)) {
        $sql .= implode(" ", $join_clauses);
    }


    // Ajouter les clauses WHERE si elles existent
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    // Ajouter le GROUP BY et ORDER BY (qui sont toujours présents)
    $sql .= " GROUP BY d.id ORDER BY d.date_decouverte DESC ";


    // --- Préparer et exécuter la requête finale ---
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Exécuter avec les paramètres dynamiques

    $decouvertes = $stmt->fetchAll();

} catch (PDOException $e) {
    // Gérer les erreurs de requête
    // N'affichez pas $e->getMessage() directement en production pour des raisons de sécurité
    error_log('Erreur lors du chargement des découvertes : ' . $e->getMessage()); // Loguer l'erreur
    echo '<div class="alert alert-danger" role="alert">Erreur lors du chargement des découvertes. Veuillez réessayer plus tard.</div>';
    $decouvertes = []; // S'assurer que $decouvertes est vide en cas d'erreur
}
?>

<div class="content-section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title" data-aos="fade-right">
            <i class="fas fa-flask me-2"></i>Mes Découvertes Techniques
        </h1>
        <div class="control-buttons" data-aos="fade-left">
            <button id="viewToggle" class="btn btn-outline-primary">
                <i class="fas fa-th-list"></i>
            </button>
            <a href="ajouter_decouverte.php" class="btn btn-primary ms-2">
                <i class="fas fa-plus-circle me-1"></i> Ajouter
            </a>
        </div>
    </div>

    <?php
    // Afficher les messages d'état (succès/erreur) après redirection depuis les actions
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            $message = $_GET['message'] ?? 'Opération réussie !';
            echo '<div class="alert alert-success alert-dismissible fade show custom-alert" role="alert" data-aos="fade-in">
                <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($message) . 
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } elseif ($_GET['status'] == 'error') {
            $message = $_GET['message'] ?? 'Une erreur est survenue.';
            echo '<div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert" data-aos="fade-in">
                <i class="fas fa-exclamation-circle me-2"></i>Erreur : ' . htmlspecialchars($message) . 
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
    ?>

    <?php if ($filter_tag_id): // Afficher le filtre actif ?>
        <?php
        $filtered_tag_name = 'Tag inconnu';
        try {
            $stmt_tag = $pdo->prepare("SELECT nom FROM tags WHERE id = ?");
            $stmt_tag->execute([$filter_tag_id]);
            $tag_info = $stmt_tag->fetchColumn();
            if ($tag_info) {
                $filtered_tag_name = htmlspecialchars($tag_info);
            }
        } catch(PDOException $e) {
            error_log('Erreur chargement nom tag filtre : ' . $e->getMessage());
        }
        ?>
        <div class="filter-badge alert alert-secondary d-flex justify-content-between align-items-center mb-4" role="alert" data-aos="fade-up">
            <div>
                <i class="fas fa-filter me-2"></i>
                Filtré par Tag : <span class="badge bg-dark ms-2"><i class="fas fa-hashtag me-1"></i><?= $filtered_tag_name ?></span>
            </div>
            <a href="index.php" class="btn btn-sm btn-outline-dark" aria-label="Enlever le filtre">
                <i class="fas fa-times"></i> Retirer le filtre
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($decouvertes)): ?>
    <div class="empty-state text-center py-5" data-aos="fade-up">
        <div class="empty-icon mb-4">
            <i class="fas fa-lightbulb"></i>
        </div>
        <h3>Aucune découverte enregistrée<?php if($filter_tag_id) echo ' pour ce tag' ; ?></h3>
        <p class="text-muted">Commencez à enregistrer vos découvertes techniques dès maintenant</p>
        <a href="ajouter_decouverte.php" class="btn btn-primary mt-3">
            <i class="fas fa-plus-circle me-2"></i>Ajouter une découverte
        </a>
    </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 g-4" id="discoveries-container">
        <?php foreach ($decouvertes as $index => $decouverte): ?>
        <div class="col discovery-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
            <div class="card h-100 discovery-card">
                <div class="card-header bg-transparent">
                    <span class="category-badge">
                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($decouverte['categorie_nom']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <?= htmlspecialchars($decouverte['titre']) ?>
                    </h5>
                    <div class="card-text content-expandable">
                        <?= nl2br(htmlspecialchars($decouverte['description'])) ?>
                    </div>

                    <?php if (!empty($decouverte['liens_utiles'])): ?>
                    <div class="links-section mt-3">
                        <h6 class="links-title"><i class="fas fa-external-link-alt me-2"></i>Liens Utiles</h6>
                        <ul class="links-list">
                            <?php
                            $links = preg_split("/[\r\n,]+/", $decouverte['liens_utiles'], -1, PREG_SPLIT_NO_EMPTY);
                            foreach($links as $link) {
                                $link = trim($link);
                                if (!empty($link)) {
                                    if (!preg_match("~^(?:f|ht)tps?://~i", $link)) {
                                        $link = "http://" . $link;
                                    }
                                    $displayLink = preg_replace('~^https?://~', '', $link);
                                    echo '<li><a href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener noreferrer" class="link-hover-effect"><i class="fas fa-link me-1"></i>' . htmlspecialchars($displayLink) . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($decouverte['tags_noms'])): ?>
                    <div class="tags-container mt-3">
                        <?php
                        $tags_noms_array = explode(', ', $decouverte['tags_noms']);
                        $tags_ids_array = explode(',', $decouverte['tags_ids']);
                        
                        // Assurez-vous que les deux tableaux ont la même taille avant de combiner
                        if (count($tags_noms_array) === count($tags_ids_array)) {
                            $tags_combined = array_combine($tags_ids_array, $tags_noms_array);
                        } else {
                            $tags_combined = array_flip($tags_noms_array);
                        }

                        foreach($tags_combined as $tag_id => $tag_nom) {
                            if (!empty($tag_nom)) {
                                echo '<a href="index.php?tag_id=' . htmlspecialchars($tag_id) . '" 
                                    class="tag-badge"><i class="fas fa-hashtag me-1"></i>' . 
                                    htmlspecialchars($tag_nom) . '</a>';
                            }
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="date-info">
                        <i class="far fa-calendar-alt me-1"></i>
                        <?= date('d/m/Y', strtotime($decouverte['date_decouverte'])) ?>
                    </div>
                    <div class="action-buttons">
                        <a href="editer_decouverte.php?id=<?= $decouverte['id'] ?>" 
                           class="btn btn-sm btn-outline-primary me-1" 
                           data-bs-toggle="tooltip" 
                           title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger delete-btn" 
                                data-id="<?= $decouverte['id'] ?>" 
                                data-title="<?= htmlspecialchars($decouverte['titre']) ?>"
                                data-bs-toggle="tooltip" 
                                title="Supprimer">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmation pour la suppression -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la découverte "<span id="deleteItemTitle"></span>" ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" action="supprimer_decouverte.php" method="POST">
                    <input type="hidden" name="id" id="deleteItemId">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page HTML
require_once __DIR__ . '/includes/footer.php';
?>