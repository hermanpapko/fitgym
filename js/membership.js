// Глобальная функция для обновления плана
async function updatePlan(planType) {
    console.log('updatePlan called with:', planType);
    const token = localStorage.getItem('token');
    
    if (!token) {
        window.location.href = 'pages/register.html';
        return;
    }
    
    try {
        const response = await fetch('server/api/user/update-membership.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ membership: planType })
        });
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.status === 'success') {
            // Обновляем данные пользователя в localStorage
            const user = JSON.parse(localStorage.getItem('user'));
            if (user) {
                user.membership = planType;
                localStorage.setItem('user', JSON.stringify(user));
            }
            
            alert('Plan został zmieniony pomyślnie!');
            window.location.href = 'pages/dashboard.html';
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Błąd podczas zmiany planu: ' + error.message);
    }
} 