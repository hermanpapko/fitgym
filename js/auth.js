// Обработка формы логина
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    // Здесь будет логика аутентификации
    window.location.href = 'dashboard.html';
});

// Обработка формы регистрации
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    // Здесь будет логика регистрации
    window.location.href = 'dashboard.html';
}); 