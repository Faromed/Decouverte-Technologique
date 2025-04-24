// document.addEventListener('DOMContentLoaded', function() {
//     // Exemple simple : ajouter une classe pour animer l'apparition des cartes
//     // Nécessite un peu de CSS pour l'animation (voir style.css potentiel)

//     const cards = document.querySelectorAll('.card');
//     cards.forEach(card => {
//         // Ajoutez une classe après un court délai pour déclencher une transition CSS
//         setTimeout(() => {
//             card.classList.add('show'); // Assurez-vous d'avoir une règle CSS pour .card.show
//         }, 100); // Délai en ms
//     });

//     // Exemple d'animation CSS simple à ajouter dans style.css :
//     /*
//     .card {
//         opacity: 0;
//         transform: translateY(20px);
//         transition: opacity 0.6s ease-out, transform 0.6s ease-out;
//     }
//     .card.show {
//         opacity: 1;
//         transform: translateY(0);
//     }
//     */

//     // Ajoutez ici d'autres scripts JS natifs si besoin (validation côté client, etc.)
// });

// document.addEventListener('DOMContentLoaded', function() {
//     // ... code existant pour l'animation des cartes si vous l'avez gardé ...

//     // Ajouter la confirmation de suppression aux formulaires concernés
//     const deleteForms = document.querySelectorAll('form[action="supprimer_decouverte.php"]');
//     deleteForms.forEach(form => {
//         form.addEventListener('submit', function(event) {
//             // La confirmation est déjà dans le onsubmit="" dans index.php,
//             // mais une approche purement JS serait :
//             // if (!confirm('Êtes-vous sûr de vouloir supprimer cette découverte ?')) {
//             //     event.preventDefault(); // Annuler la soumission si l'utilisateur clique sur Annuler
//             // }
//             // L'approche 'onsubmit' dans le HTML est plus simple pour du PHP natif rapide.
//             // On peut ajouter ici d'autres interactions si nécessaire.
//         });
//     });

//     // Ajouter d'autres scripts JS natifs ici si besoin (validation côté client, etc.)
//     // Ex: faire disparaître les alertes après un délai
//     const alerts = document.querySelectorAll('.alert-dismissible');
//     alerts.forEach(alert => {
//         if (alert.classList.contains('fade')) { // Si l'alerte a la classe fade
//             //  Bootstrap 5 gère déjà la fermeture avec le bouton close
//             //  Pour une fermeture automatique après un délai:
//              setTimeout(() => {
//                 const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);
//                 bootstrapAlert.close();
//              }, 5000); // Ferme après 5 secondes
//         }
//     });

// });




// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // ---------- Initialisation ----------

    // Initialiser AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        once: true,
        offset: 100,
        easing: 'ease-in-out'
    });

    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            boundary: document.body
        });
    });

    // ---------- Gestion du préchargeur ----------
    window.addEventListener('load', function() {
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        }, 500);
    });

    // ---------- Système d'animation des cartes ----------
    const cards = document.querySelectorAll('.discovery-item');
    if (cards.length > 0) {
        // Animation initiale au chargement
        setTimeout(() => {
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('show');
                }, index * 100);
            });
        }, 100);
    }

    // ---------- Gestion du contenu expandable ----------
    const expandableContents = document.querySelectorAll('.content-expandable');
    expandableContents.forEach(content => {
        // Déterminer si le contenu est trop long et nécessite expansion
        if (content.scrollHeight > content.clientHeight) {
            const expandBtn = document.createElement('button');
            expandBtn.className = 'btn btn-sm btn-link expand-btn mt-2';
            expandBtn.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Voir plus';
            content.insertAdjacentElement('afterend', expandBtn);
            
            expandBtn.addEventListener('click', function() {
                if (content.classList.contains('expanded')) {
                    content.classList.remove('expanded');
                    this.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Voir plus';
                    
                    // Faire défiler vers le haut de la carte
                    const card = this.closest('.card');
                    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    content.classList.add('expanded');
                    this.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Voir moins';
                }
            });
        }
    });

    // ---------- Gestion du mode sombre ----------
    const darkModeToggle = document.getElementById('darkModeToggle');
    const bodyElement = document.body;
    
    // Vérifier la préférence enregistrée
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        bodyElement.classList.add('dark-mode');
        if (darkModeToggle) {
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            bodyElement.classList.toggle('dark-mode');
            const isDark = bodyElement.classList.contains('dark-mode');
            
            // Enregistrer la préférence
            localStorage.setItem('darkMode', isDark);
            
            // Changer l'icône
            this.innerHTML = isDark ? 
                '<i class="fas fa-sun"></i>' : 
                '<i class="fas fa-moon"></i>';
                
            // Animation de transition
            this.classList.add('rotate-icon');
            setTimeout(() => {
                this.classList.remove('rotate-icon');
            }, 300);
        });
    }

    // ---------- Bouton retour en haut ----------
    const backToTopButton = document.getElementById('btn-back-to-top');
    
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ---------- Gestion de la suppression avec confirmation ----------
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = document.getElementById('deleteConfirmModal');
    
    if (deleteButtons.length > 0 && deleteModal) {
        const deleteModalObj = new bootstrap.Modal(deleteModal);
        const deleteForm = document.getElementById('deleteForm');
        const deleteItemId = document.getElementById('deleteItemId');
        const deleteItemTitle = document.getElementById('deleteItemTitle');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                
                deleteItemId.value = id;
                deleteItemTitle.textContent = title;
                deleteModalObj.show();
            });
        });
    }

    // ---------- Alternance vue grille/liste ----------
    const viewToggleBtn = document.getElementById('viewToggle');
    const discoveriesContainer = document.getElementById('discoveries-container');
    
    if (viewToggleBtn && discoveriesContainer) {
        // Vérifier la préférence enregistrée
        const isListView = localStorage.getItem('viewMode') === 'list';
        if (isListView) {
            discoveriesContainer.classList.add('list-view');
            viewToggleBtn.innerHTML = '<i class="fas fa-th-large"></i>';
        }
        
        viewToggleBtn.addEventListener('click', function() {
            discoveriesContainer.classList.toggle('list-view');
            const isList = discoveriesContainer.classList.contains('list-view');
            
            // Enregistrer la préférence
            localStorage.setItem('viewMode', isList ? 'list' : 'grid');
            
            // Changer l'icône
            this.innerHTML = isList ? 
                '<i class="fas fa-th-large"></i>' : 
                '<i class="fas fa-th-list"></i>';
                
            // Animer la transition
            discoveriesContainer.classList.add('fade');
            setTimeout(() => {
                discoveriesContainer.classList.remove('fade');
            }, 300);
        });
    }

    // ---------- Gestion des alertes ----------
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        if (alert.classList.contains('fade')) {
            // Fermeture automatique après délai
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        }
    });

    // ---------- Animations des liens ----------
    const links = document.querySelectorAll('.link-hover-effect');
    links.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.classList.add('pulse');
        });
        
        link.addEventListener('mouseleave', function() {
            this.classList.remove('pulse');
        });
    });

    // ---------- Protection contre double-clic sur les formulaires ----------
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = this.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;
                
                // Ajouter un spinner pour indiquer le chargement
                const originalContent = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
                
                // Restaurer après un délai pour éviter de bloquer complètement le bouton
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalContent;
                }, 3000);
            });
        });
    });

    // ---------- Animation logo navbar au survol ----------
    const navbarBrand = document.querySelector('.navbar-brand');
    if (navbarBrand) {
        navbarBrand.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.tech-icon');
            if (icon) {
                icon.classList.add('shake');
                setTimeout(() => {
                    icon.classList.remove('shake');
                }, 800);
            }
        });
    }

    // ---------- Animation des badges de catégorie ----------
    const categoryBadges = document.querySelectorAll('.category-badge');
    categoryBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // ---------- Détection de l'activité utilisateur ----------
    let userIdleTime = 0;
    const idleInterval = setInterval(timerIncrement, 60000); // Vérifier chaque minute
    
    // Réinitialiser le compteur d'inactivité sur les événements utilisateur
    const resetIdleTime = () => { userIdleTime = 0; };
    
    // Écouter les événements d'activité
    document.addEventListener('mousemove', resetIdleTime);
    document.addEventListener('keypress', resetIdleTime);
    document.addEventListener('touchstart', resetIdleTime);
    
    function timerIncrement() {
        userIdleTime++;
        
        // Suggestion après 3 minutes d'inactivité
        if (userIdleTime >= 3) {
            // Ne montrer la suggestion qu'une seule fois par session
            if (!sessionStorage.getItem('suggestionShown')) {
                suggestionToast();
                sessionStorage.setItem('suggestionShown', 'true');
            }
        }
    }
    
    function suggestionToast() {
        // Utiliser SweetAlert2 pour une notification élégante
        Swal.fire({
            title: 'Besoin d\'idées?',
            text: 'Pourquoi ne pas ajouter une nouvelle découverte technique?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3f51b5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ajouter',
            cancelButtonText: 'Plus tard'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'ajouter_decouverte.php';
            }
        });
    }
});