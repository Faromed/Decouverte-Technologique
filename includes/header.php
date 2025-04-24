<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Veille Tech - <?= $pageTitle ?? 'Accueil' ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="tech-theme">
    <!-- Loader -->
    <div id="preloader">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    </div>

    <!-- Navbar avec effet glass -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="logo-container me-2">
                    <i class="fas fa-brain tech-icon pulse"></i>
                </div>
                <span class="fw-bold">Ma Veille Tech</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajouter_decouverte.php">
                            <i class="fas fa-plus-circle"></i> Ajouter une découverte
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="btn nav-link" id="darkModeToggle">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero section pour l'accueil -->
    <?php if ($pageTitle === "Liste des Découvertes"): ?>
    <div class="hero-banner text-center py-5 mb-4">
        <div class="container">
            <h1 class="display-4 fw-bold" data-aos="fade-up">Ma Veille Technologique</h1>
            <p class="lead" data-aos="fade-up" data-aos-delay="200">
                Explorez et partagez des découvertes pour rester à la pointe de la technologie
            </p>
            <a href="ajouter_decouverte.php" class="btn btn-primary btn-lg mt-3" data-aos="fade-up" data-aos-delay="400">
                <i class="fas fa-lightbulb me-2"></i>Nouvelle découverte
            </a>
        </div>
    </div>
    <?php endif; ?>

    <main class="container py-4">
        <!-- Le contenu spécifique de chaque page sera inséré ici -->