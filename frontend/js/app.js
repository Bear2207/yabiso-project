// Configuration de l'API
const API_BASE_URL = window.location.hostname === 'localhost' 
    ? 'http://localhost:3000/api' 
    : '/api';

// Fonctions pour interagir avec l'API
async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error(`API call to ${endpoint} failed:`, error);
        throw error;
    }
}

async function getUsers() {
    return await apiCall('/users');
}

async function getSubscriptions() {
    return await apiCall('/subscriptions');
}

async function getDashboardStats() {
    return await apiCall('/dashboard/stats');
}

async function getRecentPayments() {
    return await apiCall('/payments/recent');
}

async function getUpcomingSessions() {
    return await apiCall('/sessions/upcoming');
}

async function getNotificationsCount() {
    return await apiCall('/notifications/count');
}