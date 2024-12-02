document.addEventListener('DOMContentLoaded', async function() {
    // Проверяем авторизацию
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user'));
    
    // Находим элементы навигации
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
}); 