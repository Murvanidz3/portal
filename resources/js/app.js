import axios from 'axios';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Alpine.js
Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Global utility functions
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('კოპირებულია!', 'success');
    }).catch(err => {
        console.error('Copy failed:', err);
    });
};

window.showToast = function(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 animate-fade-in ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        type === 'warning' ? 'bg-yellow-600' :
        'bg-blue-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

// Sidebar toggle for mobile
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    if (overlay) {
        overlay.classList.toggle('hidden');
    }
};

// Format number as currency
window.formatCurrency = function(amount, currency = '$') {
    return currency + new Intl.NumberFormat('en-US').format(amount);
};

// Format date
window.formatDate = function(date) {
    return new Date(date).toLocaleDateString('ka-GE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

console.log('🚗 OneCar CRM v2.0 loaded');
