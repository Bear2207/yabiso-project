// js/auth.js
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    
    // Simuler une base d'utilisateurs (en production, cela viendrait d'une API)
    const adminUser = {
        email: 'admin.richard@email.com',
        password: 'adminpass'
    };
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Validation basique
        if (!email || !password) {
            showError('Veuillez remplir tous les champs');
            return;
        }
        
        // Vérification des identifiants (simulée)
        if (email === adminUser.email && password === adminUser.password) {
            // Enregistrement de la connexion (simulé)
            localStorage.setItem('adminLoggedIn', 'true');
            localStorage.setItem('adminEmail', email);
            
            // Redirection vers le dashboard
            window.location.href = 'dashboard.html';
        } else {
            showError('Email ou mot de passe incorrect');
        }
    });
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        
        // Masquer le message après 5 secondes
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }
});