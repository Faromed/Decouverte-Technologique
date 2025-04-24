<?php
require_once __DIR__ . '/includes/db.php'; // Inclure le fichier de connexion à la DB

$pageTitle = "Liste des Découvertes"; // Titre spécifique pour cette page

// Inclure l'entête HTML
require_once __DIR__ . '/includes/header.php';

// --- Récupérer toutes les catégories pour les filtres ---
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, nom FROM categories ORDER BY nom ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur lors du chargement des catégories : ' . $e->getMessage());
}

// --- Récupérer tous les tags pour les filtres ---
$all_tags = [];
try {
    $stmt = $pdo->prepare("SELECT id, nom FROM tags ORDER BY nom ASC");
    $stmt->execute();
    $all_tags = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur lors du chargement des tags : ' . $e->getMessage());
}

// --- Initialiser les filtres depuis l'URL ---
$filter_tag_id = filter_var($_GET['tag_id'] ?? null, FILTER_VALIDATE_INT);
$filter_category_id = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT);
$search_query = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'date_desc'; // Par défaut: plus récent au plus ancien

// --- Logique d'affichage des découvertes ---
try {
    // Début de la construction de la requête SQL
    $sql = "
        SELECT
            d.*,
            c.nom as categorie_nom,
            GROUP_CONCAT(DISTINCT t.nom SEPARATOR ', ') as tags_noms,
            GROUP_CONCAT(DISTINCT t.id SEPARATOR ',') as tags_ids
        FROM
            decouvertes d
        JOIN
            categories c ON d.categorie_id = c.id
    ";

    $where_clauses = [];
    $params = [];
    $join_clauses = []; // Pour ajouter des JOIN si nécessaire pour le filtrage

    // --- Logique de filtrage ---

    // Filtre par tag ID
    if ($filter_tag_id) {
        $join_clauses[] = " JOIN decouverte_tag dt_filter ON d.id = dt_filter.decouverte_id ";
        $where_clauses[] = "dt_filter.tag_id = ?";
        $params[] = $filter_tag_id;
    }

    // Filtre par catégorie
    if ($filter_category_id) {
        $where_clauses[] = "d.categorie_id = ?";
        $params[] = $filter_category_id;
    }

    // Recherche textuelle
    if (!empty($search_query)) {
        $where_clauses[] = "(d.titre LIKE ? OR d.description LIKE ? OR t.nom LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }

    // On ajoute toujours les LEFT JOIN pour récupérer TOUS les tags de TOUTES les découvertes
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

    // Ajouter le GROUP BY
    $sql .= " GROUP BY d.id";
    
    // Définir l'ordre de tri
    switch ($sort_by) {
        case 'title_asc':
            $sql .= " ORDER BY d.titre ASC";
            break;
        case 'title_desc':
            $sql .= " ORDER BY d.titre DESC";
            break;
        case 'date_asc':
            $sql .= " ORDER BY d.date_decouverte ASC";
            break;
        case 'date_desc':
        default:
            $sql .= " ORDER BY d.date_decouverte DESC";
            break;
    }

    // --- Préparer et exécuter la requête finale ---
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Exécuter avec les paramètres dynamiques

    $decouvertes = $stmt->fetchAll();

} catch (PDOException $e) {
    // Gérer les erreurs de requête
    error_log('Erreur lors du chargement des découvertes : ' . $e->getMessage()); // Loguer l'erreur
    echo '<div class="alert alert-danger" role="alert">Erreur lors du chargement des découvertes. Veuillez réessayer plus tard.</div>';
    $decouvertes = []; // S'assurer que $decouvertes est vide en cas d'erreur
}

// Fonction pour construire l'URL avec tous les filtres actuels sauf ceux qu'on veut exclure
function buildFilterUrl($exclude = []) {
    $params = $_GET;
    foreach ($exclude as $param) {
        unset($params[$param]);
    }
    return '?' . http_build_query($params);
}
?>

<div class="content-section">
    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title" data-aos="fade-right">
            <i class="fas fa-flask me-2"></i>Mes Découvertes Techniques
        </h1>
        <div class="control-buttons" data-aos="fade-left">
            <button id="refresh-btn" class="btn btn-outline-secondary me-1" title="Actualiser">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button id="viewToggle" class="btn btn-outline-primary me-1" title="Changer la vue">
                <i class="fas fa-th-list"></i>
            </button>
            <button id="exportPdfBtn" class="btn btn-outline-danger me-1" title="Exporter en PDF">
                <i class="fas fa-file-pdf"></i>
            </button>
            <a href="ajouter_decouverte.php" class="btn btn-primary">
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

    <!-- Section des filtres et recherche -->
    <div class="filter-section card shadow-sm mb-4" data-aos="fade-up">
        <div class="card-body">
            <form id="filterForm" action="index.php" method="GET" class="row g-3 align-items-end">
                <!-- Champ de recherche -->
                <div class="col-md-4">
                    <label for="search" class="form-label"><i class="fas fa-search me-1"></i>Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search_query) ?>" 
                           placeholder="Titre, description, tag...">
                </div>
                
                <!-- Filtre par catégorie -->
                <div class="col-md-3">
                    <label for="category_id" class="form-label"><i class="fas fa-tag me-1"></i>Catégorie</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?= $categorie['id'] ?>" <?= ($filter_category_id == $categorie['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categorie['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Filtre par tag (si on n'a pas déjà un tag_id dans l'URL) -->
                <div class="col-md-3">
                    <label for="tag_id" class="form-label"><i class="fas fa-hashtag me-1"></i>Tag</label>
                    <select class="form-select" id="tag_id" name="tag_id">
                        <option value="">Tous les tags</option>
                        <?php foreach ($all_tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" <?= ($filter_tag_id == $tag['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tag['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Tri -->
                <div class="col-md-2">
                    <label for="sort" class="form-label"><i class="fas fa-sort me-1"></i>Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="date_desc" <?= ($sort_by == 'date_desc') ? 'selected' : '' ?>>Date ↓</option>
                        <option value="date_asc" <?= ($sort_by == 'date_asc') ? 'selected' : '' ?>>Date ↑</option>
                        <option value="title_asc" <?= ($sort_by == 'title_asc') ? 'selected' : '' ?>>Titre A-Z</option>
                        <option value="title_desc" <?= ($sort_by == 'title_desc') ? 'selected' : '' ?>>Titre Z-A</option>
                    </select>
                </div>
                
                <!-- Boutons -->
                <div class="col-md-12 d-flex justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-1"></i>Réinitialiser
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Badges de filtres actifs -->
    <?php if ($filter_tag_id || $filter_category_id || !empty($search_query)): ?>
        <div class="active-filters mb-4" data-aos="fade-up">
            <div class="d-flex flex-wrap align-items-center">
                <span class="me-2 text-muted"><i class="fas fa-filter me-1"></i>Filtres actifs:</span>
                
                <?php if ($filter_category_id): ?>
                    <?php
                    $category_name = 'Catégorie inconnue';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $filter_category_id) {
                            $category_name = $cat['nom'];
                            break;
                        }
                    }
                    ?>
                    <div class="filter-badge me-2 mb-2">
                        <span class="badge bg-primary d-flex align-items-center">
                            Catégorie: <?= htmlspecialchars($category_name) ?>
                            <a href="<?= buildFilterUrl(['category_id']) ?>" class="ms-2 text-white" title="Retirer ce filtre">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($filter_tag_id): ?>
                    <?php
                    $tag_name = 'Tag inconnu';
                    foreach ($all_tags as $tag) {
                        if ($tag['id'] == $filter_tag_id) {
                            $tag_name = $tag['nom'];
                            break;
                        }
                    }
                    ?>
                    <div class="filter-badge me-2 mb-2">
                        <span class="badge bg-info d-flex align-items-center">
                            Tag: <?= htmlspecialchars($tag_name) ?>
                            <a href="<?= buildFilterUrl(['tag_id']) ?>" class="ms-2 text-white" title="Retirer ce filtre">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($search_query)): ?>
                    <div class="filter-badge me-2 mb-2">
                        <span class="badge bg-secondary d-flex align-items-center">
                            Recherche: "<?= htmlspecialchars($search_query) ?>"
                            <a href="<?= buildFilterUrl(['search']) ?>" class="ms-2 text-white" title="Retirer ce filtre">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="ms-auto">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Tout effacer
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Compteur de résultats -->
    <div class="results-count mb-3" data-aos="fade-up">
        <p class="text-muted">
            <i class="fas fa-clipboard-list me-1"></i>
            <?= count($decouvertes) ?> découverte<?= (count($decouvertes) > 1) ? 's' : '' ?> trouvée<?= (count($decouvertes) > 1) ? 's' : '' ?>
        </p>
    </div>

    <?php if (empty($decouvertes)): ?>
    <div class="empty-state text-center py-5" data-aos="fade-up">
        <div class="empty-icon mb-4">
            <i class="fas fa-search"></i>
        </div>
        <h3>Aucune découverte ne correspond à votre recherche</h3>
        <p class="text-muted">Essayez de modifier vos critères de recherche ou d'ajouter une nouvelle découverte</p>
        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-times me-1"></i>Réinitialiser les filtres
            </a>
            <a href="ajouter_decouverte.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Ajouter une découverte
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 g-4" id="discoveries-container">
        <?php foreach ($decouvertes as $index => $decouverte): ?>
        <div class="col discovery-item" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
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

    <!-- Pagination (pour une future implémentation) -->
    <div class="pagination-container mt-4 d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Suivant</a>
                </li>
            </ul>
        </nav>
    </div>
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

<!-- Script pour l'exportation PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion du bouton de changement de vue
    const viewToggleBtn = document.getElementById('viewToggle');
    const container = document.getElementById('discoveries-container');
    
    if (viewToggleBtn && container) {
        // Vérifier si une préférence est stockée
        const currentView = localStorage.getItem('discoveriesView') || 'grid';
        
        // Appliquer la vue initiale
        if (currentView === 'list') {
            container.classList.add('list-view');
            viewToggleBtn.innerHTML = '<i class="fas fa-th"></i>';
        } else {
            viewToggleBtn.innerHTML = '<i class="fas fa-th-list"></i>';
        }
        
        // Gérer le clic sur le bouton
        viewToggleBtn.addEventListener('click', function() {
            if (container.classList.contains('list-view')) {
                container.classList.remove('list-view');
                this.innerHTML = '<i class="fas fa-th-list"></i>';
                localStorage.setItem('discoveriesView', 'grid');
            } else {
                container.classList.add('list-view');
                this.innerHTML = '<i class="fas fa-th"></i>';
                localStorage.setItem('discoveriesView', 'list');
            }
        });
    }
    
    // Gestion de la suppression
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = document.getElementById('deleteConfirmModal');
    const deleteItemTitle = document.getElementById('deleteItemTitle');
    const deleteItemId = document.getElementById('deleteItemId');
    
    if (deleteButtons.length && deleteModal && deleteItemTitle && deleteItemId) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                
                deleteItemTitle.textContent = title;
                deleteItemId.value = id;
                
                const modal = new bootstrap.Modal(deleteModal);
                modal.show();
            });
        });
    }
    
    // Gestion du bouton d'actualisation
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('rotate-animation');
            // Recharger la page avec les mêmes paramètres
            location.reload();
        });
    }
    
            // Gestion du bouton d'exportation PDF
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            if (exportPdfBtn) {
                exportPdfBtn.addEventListener('click', function() {
                    // Créer un conteneur pour l'exportation
                    const contentToPrint = document.createElement('div');
                    contentToPrint.classList.add('pdf-export-container');
                    
                    // Ajouter un en-tête professionnel
                    const header = document.createElement('div');
                    header.classList.add('pdf-header');
                    header.innerHTML = `
                        <div style="text-align: center; margin-bottom: 15px;">
                            <h2 style="color: #2c3e50; margin-bottom: 5px;">Mes Découvertes Techniques</h2>
                            <p style="color: #7f8c8d; font-size: 0.9em;">Export généré le ${new Date().toLocaleDateString('fr-FR', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</p>
                        </div>
                        <hr style="border-top: 1px solid #eee; margin: 10px 0;">
                    `;
                    contentToPrint.appendChild(header);
                    
                    // Ajouter les filtres actifs avec un style compact
                    const activeFilters = document.querySelector('.active-filters');
                    if (activeFilters && activeFilters.children.length > 0) {
                        const filtersClone = activeFilters.cloneNode(true);
                        filtersClone.classList.add('pdf-filters');
                        filtersClone.style.margin = '0 0 15px 0';
                        filtersClone.style.padding = '8px 12px';
                        filtersClone.style.backgroundColor = '#f8f9fa';
                        filtersClone.style.borderRadius = '4px';
                        filtersClone.style.fontSize = '0.85em';
                        contentToPrint.appendChild(filtersClone);
                    }
                    
                    // Cloner chaque carte de découverte avec un style optimisé
                    const discoveries = document.querySelectorAll('.discovery-card');
                    discoveries.forEach(discovery => {
                        const clone = discovery.cloneNode(true);
                        
                        // Supprimer les éléments non nécessaires
                        const actionButtons = clone.querySelector('.action-buttons');
                        if (actionButtons) actionButtons.remove();
                        
                        // Développer tout le contenu
                        const expandable = clone.querySelector('.content-expandable');
                        if (expandable) expandable.classList.add('expanded');
                        
                        // Appliquer un style compact et professionnel
                        clone.style.margin = '0 0 15px 0';
                        clone.style.border = '1px solid #e0e0e0';
                        clone.style.borderRadius = '6px';
                        clone.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                        clone.style.overflow = 'hidden';
                        clone.style.pageBreakInside = 'avoid';
                        
                        // Style pour l'en-tête de la carte
                        const cardHeader = clone.querySelector('.card-header');
                        if (cardHeader) {
                            cardHeader.style.padding = '8px 12px';
                            cardHeader.style.backgroundColor = '#f8f9fa';
                            cardHeader.style.borderBottom = '1px solid #e0e0e0';
                            
                            // Style pour le titre
                            const title = cardHeader.querySelector('h3, h4, h5');
                            if (title) {
                                title.style.margin = '0';
                                title.style.fontSize = '1.1em';
                                title.style.color = '#2c3e50';
                            }
                        }
                        
                        // Style pour le corps de la carte
                        const cardBody = clone.querySelector('.card-body');
                        if (cardBody) {
                            cardBody.style.padding = '12px';
                            cardBody.style.fontSize = '0.9em';
                            cardBody.style.lineHeight = '1.5';
                            
                            // Style pour la description
                            const description = cardBody.querySelector('.discovery-description');
                            if (description) {
                                description.style.margin = '0 0 10px 0';
                                description.style.color = '#34495e';
                            }
                            
                            // Style pour les liens
                            const linksSection = cardBody.querySelector('.links-section');
                            if (linksSection) {
                                linksSection.style.margin = '10px 0 0 0';
                                linksSection.style.fontSize = '0.85em';
                                
                                const linksList = linksSection.querySelector('ul');
                                if (linksList) {
                                    linksList.style.paddingLeft = '20px';
                                    linksList.style.margin = '5px 0 0 0';
                                }
                            }
                        }
                        
                        // Style pour le pied de page de la carte
                        const cardFooter = clone.querySelector('.card-footer');
                        if (cardFooter) {
                            cardFooter.style.padding = '8px 12px';
                            cardFooter.style.backgroundColor = '#f8f9fa';
                            cardFooter.style.borderTop = '1px solid #e0e0e0';
                            cardFooter.style.fontSize = '0.8em';
                            cardFooter.style.display = 'flex';
                            cardFooter.style.justifyContent = 'space-between';
                            cardFooter.style.alignItems = 'center';
                            cardFooter.style.flexWrap = 'wrap';
                            
                            // Style pour les badges de catégorie et tags
                            const categoryBadge = cardFooter.querySelector('.category-badge');
                            if (categoryBadge) {
                                categoryBadge.style.fontSize = '0.85em';
                                categoryBadge.style.padding = '3px 8px';
                                categoryBadge.style.borderRadius = '4px';
                                categoryBadge.style.backgroundColor = '#e0e0e0';
                            }
                            
                            const tagsContainer = cardFooter.querySelector('.tags-container');
                            if (tagsContainer) {
                                tagsContainer.style.display = 'inline';
                                tagsContainer.style.margin = '0';
                            }
                            
                            const tagBadges = cardFooter.querySelectorAll('.tag-badge');
                            tagBadges.forEach(badge => {
                                badge.style.fontSize = '0.75em';
                                badge.style.padding = '2px 6px';
                                badge.style.margin = '2px';
                            });
                            
                            // Style pour la date
                            const dateInfo = cardFooter.querySelector('.date-info');
                            if (dateInfo) {
                                dateInfo.style.color = '#7f8c8d';
                                dateInfo.style.fontSize = '0.8em';
                                dateInfo.style.margin = '0';
                            }
                        }
                        
                        contentToPrint.appendChild(clone);
                    });
                    
                    // Ajouter des styles optimisés pour le PDF
                    const styles = `
                        <style>
                            .pdf-export-container {
                                font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                                padding: 15px;
                                color: #333;
                                max-width: 100%;
                            }
                            
                            .discovery-card {
                                margin-bottom: 12px !important;
                                break-inside: avoid;
                            }
                            
                            /* Optimisation pour l'impression */
                            @media print {
                                body, .pdf-export-container {
                                    padding: 0 !important;
                                    margin: 0 !important;
                                    background: white !important;
                                }
                                
                                .discovery-card {
                                    margin-bottom: 10px !important;
                                    page-break-inside: avoid !important;
                                }
                                
                                .pdf-header {
                                    margin-bottom: 10px !important;
                                }
                            }
                        </style>
                    `;
                    contentToPrint.insertAdjacentHTML('beforeend', styles);
                    
                    // Options optimisées pour html2pdf
                    const options = {
                        margin: [10, 10, 10, 10],
                        filename: `mes_decouvertes_techniques_${new Date().toISOString().slice(0,10)}.pdf`,
                        image: { type: 'jpeg', quality: 0.95 },
                        html2canvas: { 
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            scrollX: 0,
                            scrollY: 0,
                            windowWidth: document.documentElement.scrollWidth,
                            windowHeight: document.documentElement.scrollHeight
                        },
                        jsPDF: { 
                            unit: 'mm', 
                            format: 'a4', 
                            orientation: 'portrait',
                            compress: true
                        },
                        pagebreak: { 
                            mode: ['avoid-all', 'css', 'legacy'] 
                        }
                    };
                    
                    // Créer un clone du contenu pour l'export
                    const exportElement = document.createElement('div');
                    exportElement.appendChild(contentToPrint.cloneNode(true));
                    document.body.appendChild(exportElement);
                    
                    // Générer le PDF
                    html2pdf()
                        .from(exportElement)
                        .set(options)
                        .save()
                        .then(() => {
                            document.body.removeChild(exportElement);
                            showAlert('success', 'PDF généré avec succès!');
                        })
                        .catch((error) => {
                            console.error('Erreur PDF:', error);
                            document.body.removeChild(exportElement);
                            showAlert('danger', 'Erreur lors de la génération du PDF');
                        });
                });
            }

            function showAlert(type, message) {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
                alert.style.zIndex = '9999';
                alert.innerHTML = `
                    <strong>${type === 'success' ? '<i class="fas fa-check-circle me-2"></i>' : '<i class="fas fa-exclamation-circle me-2"></i>'}${type === 'success' ? 'Succès!' : 'Erreur!'}</strong>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }
    
    // Initialiser les sélecteurs avec la bibliothèque Select2 si elle est disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $('#category_id, #tag_id').select2({
            placeholder: 'Sélectionner...',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Rendre les cartes extensibles
    const expandButtons = document.querySelectorAll('.content-expandable');
    expandButtons.forEach(content => {
        if (content.scrollHeight > content.clientHeight || content.scrollWidth > content.clientWidth) {
            const expandBtn = document.createElement('button');
            expandBtn.classList.add('btn', 'btn-sm', 'btn-outline-secondary', 'mt-2', 'expand-btn');
            expandBtn.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Voir plus';
            content.parentNode.insertBefore(expandBtn, content.nextSibling);
            
            expandBtn.addEventListener('click', function() {
                if (content.classList.contains('expanded')) {
                    content.classList.remove('expanded');
                    this.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Voir plus';
                    
                    // Scroll back to the card
                    const card = content.closest('.card');
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else {
                    content.classList.add('expanded');
                    this.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Voir moins';
                }
            });
        }
    });
    
    // Animation pour afficher les sections progressivement
    const animateEntries = () => {
        const entries = document.querySelectorAll('.discovery-item');
        entries.forEach((entry, index) => {
            setTimeout(() => {
                entry.classList.add('visible');
            }, index * 100);
        });
    };
    
    // Déclencher l'animation après le chargement
    animateEntries();
});
</script>

<!-- CSS supplémentaire pour les nouvelles fonctionnalités -->
<style>
/* Styles pour les vues (grille/liste) */
#discoveries-container {
    transition: all 0.3s ease;
}

#discoveries-container.list-view {
    display: block !important;
}

#discoveries-container.list-view .col {
    width: 100%;
    max-width: 100%;
    flex: 0 0 100%;
    margin-bottom: 1rem;
}

#discoveries-container.list-view .card {
    flex-direction: row;
    align-items: center;
}

#discoveries-container.list-view .card-header {
    width: 150px;
    border-right: 1px solid rgba(0,0,0,.125);
    border-bottom: none;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#discoveries-container.list-view .card-body {
    flex: 1;
}

#discoveries-container.list-view .content-expandable {
    max-height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

#discoveries-container.list-view .links-section {
    display: none;
}

#discoveries-container.list-view .card-footer {
    width: 200px;
    border-left: 1px solid rgba(0,0,0,.125);
    border-top: none;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Animation pour le bouton d'actualisation */
.rotate-animation {
    animation: rotate 1s linear;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Styles pour la section des filtres */
.filter-section {
    background-color: var(--bs-light);
    border-radius: 10px;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.filter-section:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,.15);
}

/* Badges de filtres actifs */
.filter-badge .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: 50px;
}

.filter-badge a {
    opacity: 0.7;
    transition: opacity 0.2s;
}

.filter-badge a:hover {
    opacity: 1;
}

/* Conteneur extensible pour le contenu */
.content-expandable {
    max-height: 100px;
    overflow: hidden;
    transition: max-height 0.5s ease;
}

.content-expandable.expanded {
    max-height: 1000px;
}

.expand-btn {
    display: block;
    width: 100%;
    transition: all 0.3s ease;
}

/* Animation pour l'entrée des items */
.discovery-item {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.discovery-item.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Styles pour l'impression et l'export PDF */
@media print {
    .filter-section, .control-buttons, .action-buttons, .expand-btn, .pagination-container {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 20px;
        border: 1px solid #ddd !important;
    }
    
    .content-expandable {
        max-height: none !important;
        overflow: visible !important;
    }
    
    body {
        padding: 20px;
    }
}
</style>

<?php
// Inclure le pied de page HTML
require_once __DIR__ . '/includes/footer.php';
?>