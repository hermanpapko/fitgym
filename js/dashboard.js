document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    initMembership();
});

// Функция инициализации членства
async function initMembership() {
    const badge = document.getElementById('membershipText');
    if (badge) {
        try {
            const response = await fetch('../server/api/user/profile.php', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            const data = await response.json();
            if (data.user?.membership) {
                const membership = data.user.membership.toLowerCase();
                badge.textContent = membership === 'vip' ? 'VIP' : 
                    membership.charAt(0).toUpperCase() + membership.slice(1);
            }
        } catch (e) {
            console.error('Error loading membership:', e);
        }
    }
}

