document.addEventListener('DOMContentLoaded', function() {
    // Exemple simple : ajouter une classe pour animer l'apparition des cartes
    // Nécessite un peu de CSS pour l'animation (voir style.css potentiel)

    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        // Ajoutez une classe après un court délai pour déclencher une transition CSS
        setTimeout(() => {
            card.classList.add('show'); // Assurez-vous d'avoir une règle CSS pour .card.show
        }, 100); // Délai en ms
    });

    // Exemple d'animation CSS simple à ajouter dans style.css :
    /*
    .card {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    .card.show {
        opacity: 1;
        transform: translateY(0);
    }
    */

    // Ajoutez ici d'autres scripts JS natifs si besoin (validation côté client, etc.)
});

document.addEventListener('DOMContentLoaded', function() {
    // ... code existant pour l'animation des cartes si vous l'avez gardé ...

    // Ajouter la confirmation de suppression aux formulaires concernés
    const deleteForms = document.querySelectorAll('form[action="supprimer_decouverte.php"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            // La confirmation est déjà dans le onsubmit="" dans index.php,
            // mais une approche purement JS serait :
            // if (!confirm('Êtes-vous sûr de vouloir supprimer cette découverte ?')) {
            //     event.preventDefault(); // Annuler la soumission si l'utilisateur clique sur Annuler
            // }
            // L'approche 'onsubmit' dans le HTML est plus simple pour du PHP natif rapide.
            // On peut ajouter ici d'autres interactions si nécessaire.
        });
    });

    // Ajouter d'autres scripts JS natifs ici si besoin (validation côté client, etc.)
    // Ex: faire disparaître les alertes après un délai
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        if (alert.classList.contains('fade')) { // Si l'alerte a la classe fade
            //  Bootstrap 5 gère déjà la fermeture avec le bouton close
            //  Pour une fermeture automatique après un délai:
             setTimeout(() => {
                const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bootstrapAlert.close();
             }, 5000); // Ferme après 5 secondes
        }
    });

});