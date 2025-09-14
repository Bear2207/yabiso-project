// js/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si l'utilisateur est connecté
    const isLoggedIn = localStorage.getItem('adminLoggedIn');
    
    if (!isLoggedIn || isLoggedIn !== 'true') {
        // Rediriger vers la page de connexion si non connecté
        window.location.href = 'index.html';
        return;
    }
    
    // Gérer la déconnexion
    const logoutBtn = document.getElementById('logout-btn');
    logoutBtn.addEventListener('click', function() {
        localStorage.removeItem('adminLoggedIn');
        localStorage.removeItem('adminEmail');
        window.location.href = 'index.html';
    });
    
    // Gérer la navigation
    const navItems = document.querySelectorAll('.sidebar-nav li');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Retirer la classe active de tous les éléments
            navItems.forEach(i => i.classList.remove('active'));
            // Ajouter la classe active à l'élément cliqué
            this.classList.add('active');
        });
    });
});