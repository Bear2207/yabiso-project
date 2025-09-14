// js/charts.js
document.addEventListener('DOMContentLoaded', function() {
    // Données simulées pour les graphiques
    const revenueData = {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Revenus (€)',
            data: [1200, 1900, 1500, 2100, 1800, 2500, 2200, 2400, 2100, 2300, 2500, 2700],
            backgroundColor: 'rgba(102, 126, 234, 0.2)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 2,
            tension: 0.4
        }]
    };
    
    const subscriptionsData = {
        labels: ['Basique', 'Premium', 'Famille', 'Étudiant'],
        datasets: [{
            data: [15, 10, 5, 2],
            backgroundColor: [
                'rgba(102, 126, 234, 0.7)',
                'rgba(118, 75, 162, 0.7)',
                'rgba(76, 175, 80, 0.7)',
                'rgba(255, 152, 0, 0.7)'
            ],
            borderWidth: 1
        }]
    };
    
    // Configuration des graphiques
    const revenueConfig = {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    
    const subscriptionsConfig = {
        type: 'doughnut',
        data: subscriptionsData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    };
    
    // Création des graphiques
    const revenueChart = new Chart(
        document.getElementById('revenue-chart'),
        revenueConfig
    );
    
    const subscriptionsChart = new Chart(
        document.getElementById('subscriptions-chart'),
        subscriptionsConfig
    );
});