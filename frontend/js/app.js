document.addEventListener('DOMContentLoaded', function() {
  // Charger les données depuis l'API
  loadDashboardData();
  
  // Configuration des événements
  setupEventListeners();
});

async function loadDashboardData() {
  try {
    // Charger les utilisateurs
    const usersResponse = await fetch('/api/users');
    const users = await usersResponse.json();
    
    // Charger les abonnements
    const subscriptionsResponse = await fetch('/api/subscriptions');
    const subscriptions = await subscriptionsResponse.json();
    
    // Mettre à jour le dashboard avec les données réelles
    updateDashboardStats(users, subscriptions);
    
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error);
  }
}

function updateDashboardStats(users, subscriptions) {
  // Mettre à jour le nombre de membres
  const activeMembers = users.filter(user => user.role === 'client').length;
  document.querySelector('.stat-number').textContent = activeMembers;
  
  // Calculer le revenu mensuel (exemple simplifié)
  const monthlyRevenue = subscriptions.reduce((total, sub) => total + parseFloat(sub.prix), 0);
  document.querySelectorAll('.stat-number')[1].textContent = `${monthlyRevenue.toFixed(2)}€`;
}

function setupEventListeners() {
  // Gestionnaire pour le toggle du menu mobile
  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
    });
  }
  
  // Navigation
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      navItems.forEach(i => i.classList.remove('active'));
      this.classList.add('active');
      
      // Sur mobile, fermer le sidebar après sélection
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('active');
      }
    });
  });
}

// Fonctions pour interagir avec l'API
async function getUsers() {
  const response = await fetch('/api/users');
  return await response.json();
}

async function getSubscriptions() {
  const response = await fetch('/api/subscriptions');
  return await response.json();
}