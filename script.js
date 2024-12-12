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

// Функция �� отправки формы
async function submitForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Блокируем кнопку на время отправки
    submitButton.disabled = true;
    submitButton.textContent = 'Wysyłanie...';
    
    try {
        // Собираем данные формы
        const formData = {
            name: form.querySelector('input[name="name"]').value,
            email: form.querySelector('input[name="email"]').value,
            phone: form.querySelector('input[name="phone"]').value,
            message: form.querySelector('textarea[name="message"]').value
        };
        
        console.log('Sending data:', formData); // Отладочная информация
        
        // Отправляем запрос
        const response = await fetch('submit_form.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status); // Отладочная информация
        
        // Проверяем тип ответа
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Otrzymano nieprawidłową odpowiedź z serwera');
        }
        
        const data = await response.json();
        console.log('Response data:', data); // Отладочная информация
        
        if (data.status === 'success') {
            // Показываем сообщение об успехе
            alert(data.message);
            form.reset();
        } else {
            throw new Error(data.message || 'Wystąpił nieznany błąd');
        }
    } catch (error) {
        console.error('Error:', error); // Отладочная информация
        // Показываем ошибку
        alert('Wystąpił błąd podczas wysyłania wiadomości: ' + error.message);
    } finally {
        // Разблокируем кнопку
        submitButton.disabled = false;
        submitButton.textContent = 'Wyślij';
    }
}

document.addEventListener('DOMContentLoaded', async function() {
    console.log('DOM loaded');
    
    // Проверяем авторизацию
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user'));
    
    // Находи элементы навигации
    const navUl = document.querySelector('nav ul');
    
    if (token && user) {
        // Если пользователь авторизован
        navUl.innerHTML = `
            <li><a href="#glowna">Główna</a></li>
            <li><a href="#uslugi">Usługi</a></li>
            <li><a href="#harmonogram">Harmonogram</a></li>
            <li><a href="#kontakt">Kontakt</a></li>
            <li class="user-menu">
                <a href="pages/dashboard.html" class="user-profile">
                    <span id="userName">${user.name}</span>
                </a>
                <button id="logoutBtn" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Wyloguj się
                </button>
            </li>
        `;

        // Добавляем обработчик для кнопки выхода
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('Czy na pewno chcesz się wylogować?')) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.reload();
            }
        });
    } else {
        // Если пользователь не авторизован
        navUl.innerHTML = `
            <li><a href="#glowna">Główna</a></li>
            <li><a href="#uslugi">Usługi</a></li>
            <li><a href="#harmonogram">Harmonogram</a></li>
            <li><a href="#kontakt">Kontakt</a></li>
            <li class="auth-buttons">
                <a href="pages/login.html" class="btn-login">Logowanie</a>
                <a href="pages/register.html" class="btn-register">Rejestracja</a>
            </li>
        `;
    }

    // Обработка формы контактов
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Dziękujemy za wiadomość! Skontaktujemy się z Tobą wkrótce.');
            this.reset();
        });
    }

    // Обработка кнопок записи на услуги
    const serviceButtons = document.querySelectorAll('.btn-service');
    serviceButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const token = localStorage.getItem('token');
            const user = JSON.parse(localStorage.getItem('user'));
            
            if (token && user) {
                window.location.href = 'pages/dashboard.html';
            } else {
                window.location.href = 'pages/register.html';
            }
        });
    });

    // Фильтры для расписания
    const filterButtons = document.querySelectorAll('.schedule-filters button');
    const scheduleDays = document.querySelectorAll('.schedule-day');

    // Показываем все дни при загрузке страницы
    scheduleDays.forEach(day => {
        day.style.display = 'block';
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const selectedDay = this.dataset.day;
            scheduleDays.forEach(day => {
                if (selectedDay === 'all') {
                    day.style.display = 'block';
                } else {
                    day.style.display = day.dataset.day === selectedDay ? 'block' : 'none';
                }
            });
        });
    });

    // Добавляем обработчик для мобильного меню
    const menuButton = document.createElement('button');
    menuButton.className = 'menu-toggle';
    menuButton.innerHTML = '<i class="fas fa-bars"></i>';
    
    const nav = document.querySelector('nav');
    nav.insertBefore(menuButton, nav.firstChild);

    menuButton.addEventListener('click', () => {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('active');
    });

    const menuToggle = document.querySelector('.menu-toggle');


