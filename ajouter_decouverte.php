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

<div class="content-section">
    <div class="section-header mb-4" data-aos="fade-up">
        <div class="d-flex align-items-center">
            <div class="icon-circle bg-primary text-white me-3">
                <i class="fas fa-lightbulb"></i>
            </div>
            <h1 class="section-title mb-0">Ajouter une Nouvelle Découverte</h1>
        </div>
        <p class="text-muted mt-2 lead">Partagez une nouvelle trouvaille technique qui pourrait être utile</p>
    </div>

    <?php
    // Afficher les messages d'erreur ou de succès s'il y en a (après redirection depuis traiter_ajout.php)
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            $message = $_GET['message'] ?? 'Découverte ajoutée avec succès !';
            echo '<div class="alert alert-success alert-dismissible fade show custom-alert" role="alert" data-aos="fade-in">
                <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($message) . 
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } elseif ($_GET['status'] == 'error') {
            $message = $_GET['message'] ?? 'Une erreur est survenue lors de l\'ajout.';
            echo '<div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert" data-aos="fade-in">
                <i class="fas fa-exclamation-circle me-2"></i>Erreur : ' . htmlspecialchars($message) . 
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
    ?>

    <div class="card shadow-sm form-card" data-aos="fade-up" data-aos-delay="100">
        <div class="card-body p-4">
            <form action="traiter_ajout.php" method="POST" id="addDiscoveryForm">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="titre" name="titre" placeholder="Titre" required>
                            <label for="titre"><i class="fas fa-heading me-2"></i>Titre de la Découverte</label>
                        </div>
                    </div>

                    <div class="col-md-12 mb-4">
                        <div class="form-floating">
                            <textarea class="form-control" id="description" name="description" style="height: 150px" placeholder="Description" required></textarea>
                            <label for="description"><i class="fas fa-align-left me-2"></i>Description / Utilité</label>
                        </div>
                        <div class="form-text">Décrivez ce que vous avez découvert et pourquoi c'est utile</div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="form-floating">
                            <select class="form-select" id="categorie_id" name="categorie_id" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="categorie_id"><i class="fas fa-tag me-2"></i>Catégorie</label>
                        </div>
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Aucune catégorie trouvée. Ajoutez-en directement dans la base de données pour l'instant.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="Tags">
                            <label for="tags"><i class="fas fa-hashtag me-2"></i>Tags (séparés par des virgules)</label>
                        </div>
                        <div class="form-text">Ex: Frontend, JavaScript, Animation, Utile au Quotidien</div>
                    </div>

                    <div class="col-md-12 mb-4">
                        <div class="form-floating">
                            <textarea class="form-control" id="liens_utiles" name="liens_utiles" style="height: 100px" placeholder="Liens"></textarea>
                            <label for="liens_utiles"><i class="fas fa-link me-2"></i>Liens Utiles</label>
                        </div>
                        <div class="form-text">Un par ligne ou séparés par des virgules. Ex: https://docs.example.com, https://tutorial.example.com</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4 submit-btn">
                        <i class="fas fa-save me-2"></i>Enregistrer la Découverte
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="tips-section mt-5" data-aos="fade-up" data-aos-delay="300">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2 text-primary"></i>Conseils pour une bonne découverte</h5>
                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                            </div>
                            <div>
                                <strong>Soyez précis</strong>
                                <p class="small mb-0">Un titre clair permet de retrouver facilement l'information plus tard</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                            </div>
                            <div>
                                <strong>Ajoutez des liens</strong>
                                <p class="small mb-0">Les ressources externes permettent d'approfondir le sujet</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                            </div>
                            <div>
                                <strong>Utilisez des tags</strong>
                                <p class="small mb-0">Ils facilitent le filtrage et la recherche par thématique</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des labels flottants
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Animation du bouton de soumission
    const form = document.getElementById('addDiscoveryForm');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.submit-btn');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            // Pour éviter un blocage permanent si le formulaire a un problème
            setTimeout(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }, 5000);
        });
    }
    
    // Effet de focus sur la carte du formulaire
    const formCard = document.querySelector('.form-card');
    if (formCard) {
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                formCard.classList.add('card-focus');
            });
            
            control.addEventListener('blur', function() {
                formCard.classList.remove('card-focus');
            });
        });
    }
    
    // Support pour les tags avec saisie intelligente (simulation)
    const tagsInput = document.getElementById('tags');
    if (tagsInput) {
        tagsInput.addEventListener('keyup', function(e) {
            if (e.key === ',') {
                // Animation subtile quand un tag est ajouté
                this.classList.add('tag-added');
                setTimeout(() => {
                    this.classList.remove('tag-added');
                }, 300);
            }
        });
    }
});
</script>

<style>
.content-section {
    max-width: 950px;
    margin: 0 auto;
    padding: 20px;
}

.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.form-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.card-focus {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25) !important;
}

.form-floating {
    position: relative;
}

.form-floating.focused label {
    color: #0d6efd;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.custom-alert {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.tips-section .card {
    border-radius: 12px;
    border: none;
}

.tag-added {
    animation: pulse 0.3s ease;
}

.submit-btn {
    position: relative;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Adaptation au mode sombre */
.dark-mode .form-card {
    background-color: #2d3748;
    color: #e2e8f0;
}

.dark-mode .form-control,
.dark-mode .form-select {
    background-color: #1a202c;
    border-color: #4a5568;
    color: #e2e8f0;
}

.dark-mode .form-floating label {
    color: #a0aec0;
}

.dark-mode .form-text {
    color: #a0aec0;
}

.dark-mode .tips-section .card {
    background-color: #2d3748 !important;
    color: #e2e8f0;
}

/* Animation lors du défilement */
@media (prefers-reduced-motion: no-preference) {
    [data-aos] {
        transition-duration: 600ms !important;
    }
}
</style>
<?php
// Inclure le pied de page HTML
require_once __DIR__ . '/includes/footer.php';
?>