// ======== REFORME CENTER - SCRIPT PRINCIPAL ========

class ReformeCenterApp {
    constructor() {
        this.init();
    }

    init() {
        this.initializeTheme();
        this.initializeComponents();
        this.setupEventListeners();
    }

    // ======== THEME MANAGEMENT ========
    initializeTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.setTheme(savedTheme);
        this.updateThemeToggleIcon(savedTheme);
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.updateChartColors();
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        this.updateThemeToggleIcon(newTheme);
        this.showNotification(`Mode ${newTheme === 'dark' ? 'sombre' : 'clair'} activé`, 'info');
    }

    updateThemeToggleIcon(theme) {
        const toggleBtn = document.querySelector('.theme-toggle');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    }

    // ======== CHART.JS INITIALIZATION ========
    initializeCharts() {
        this.createProgressChart();
        this.createStatsChart();
        this.createGoalChart();
    }

    getChartColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            text: isDark ? '#e9ecef' : '#333',
            grid: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
            background: isDark ? '#1e1e1e' : '#ffffff'
        };
    }

    createProgressChart() {
        const ctx = document.getElementById('progressChart');
        if (!ctx) return;

        const colors = this.getChartColors();
        
        // Données d'exemple - À remplacer par vos données réelles
        const data = {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Poids (kg)',
                data: [85, 82, 80, 78, 76, 74],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: colors.background,
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: colors.text,
                            font: { family: 'Poppins', size: 14 }
                        }
                    },
                    tooltip: {
                        backgroundColor: colors.background,
                        titleColor: colors.text,
                        bodyColor: colors.text,
                        borderColor: '#007bff',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text }
                    },
                    y: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text }
                    }
                }
            }
        });
    }

    createStatsChart() {
        const ctx = document.getElementById('statsChart');
        if (!ctx) return;

        const colors = this.getChartColors();

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Objectifs Atteints', 'En Cours', 'À Commencer'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: colors.background
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colors.text,
                            padding: 20,
                            font: { family: 'Poppins' }
                        }
                    }
                }
            }
        });
    }

    createGoalChart() {
        const ctx = document.getElementById('goalChart');
        if (!ctx) return;

        const colors = this.getChartColors();

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                datasets: [{
                    label: 'Calories Brûlées',
                    data: [450, 520, 480, 600, 550, 300, 400],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: '#28a745',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: colors.grid },
                        ticks: { color: colors.text }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: colors.text }
                    }
                }
            }
        });
    }

    updateChartColors() {
        // Re-initialize charts when theme changes
        this.initializeCharts();
    }

    // ======== COMPONENT INITIALIZATION ========
    initializeComponents() {
        this.initializeCharts();
        this.initializeAnimations();
        this.initializeNotifications();
    }

    // ======== ANIMATIONS ========
    initializeAnimations() {
        this.animateOnScroll();
        this.setupCardAnimations();
        this.setupProgressBars();
    }

    animateOnScroll() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card-custom, .stat-card').forEach(el => {
            observer.observe(el);
        });
    }

    setupCardAnimations() {
        document.querySelectorAll('.card-custom').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px) scale(1.02)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    setupProgressBars() {
        document.querySelectorAll('.progress-bar-custom').forEach(bar => {
            const width = bar.style.width || bar.getAttribute('aria-valuenow') + '%';
            bar.style.width = '0%';
            setTimeout(() => bar.style.width = width, 500);
        });
    }

    // ======== NOTIFICATIONS ========
    initializeNotifications() {
        document.querySelectorAll('.alert-success').forEach(alert => {
            setTimeout(() => this.fadeOut(alert), 5000);
        });
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        toast.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(toast);
        setTimeout(() => this.fadeOut(toast), 5000);
    }

    fadeOut(element) {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.5s ease';
        setTimeout(() => element.remove(), 500);
    }

    // ======== EVENT LISTENERS ========
    setupEventListeners() {
        // Theme toggle
        document.querySelector('.theme-toggle')?.addEventListener('click', () => this.toggleTheme());

        // Delete confirmation
        document.querySelectorAll('.delete-link, .btn-danger').forEach(link => {
            link.addEventListener('click', (e) => {
                if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                    e.preventDefault();
                }
            });
        });

        // Form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
    }

    handleFormSubmit(e) {
        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) this.showLoadingState(submitBtn);
    }

    showLoadingState(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Chargement...';
        button.disabled = true;
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    handleKeyboardShortcuts(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            this.toggleTheme();
        }
    }
}

// ======== INITIALIZATION ========
document.addEventListener('DOMContentLoaded', () => {
    new ReformeCenterApp();
    
    // Bootstrap components
    const tooltipList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(
        el => new bootstrap.Tooltip(el)
    );
    
    const popoverList = [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(
        el => new bootstrap.Popover(el)
    );
});