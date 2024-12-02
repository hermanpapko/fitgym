document.addEventListener('DOMContentLoaded', async function() {
    // Проверяем авторизацию
    const user = JSON.parse(localStorage.getItem('user'));
    const token = localStorage.getItem('token');
    
    if (!user || !token) {
        window.location.href = 'login.html';
        return;
    }

    console.log('Current token:', token); // Для отладки

    // Заполняем базовые данные пользователя
    document.getElementById('userName').textContent = user.name;
    document.getElementById('profileName').textContent = user.name;
    document.getElementById('profileEmail').textContent = user.email;

    try {
        // Загружаем данные пользователя, включая аватар
        const userResponse = await fetch('../server/api/user/profile.php', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        const userData = await userResponse.json();
        
        if (userData.status === 'success') {
            if (userData.user.avatar) {
                document.getElementById('avatarImg').src = userData.user.avatar;
            } else {
                document.getElementById('avatarImg').src = '../images/default-avatar.png';
            }
        }

        // Загружаем статистику пользователя
        const statsResponse = await fetch('../server/api/user/stats.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const statsData = await statsResponse.json();
        
        if (statsResponse.ok) {
            document.getElementById('totalWorkouts').textContent = statsData.workouts || '0';
            document.getElementById('totalHours').textContent = statsData.hours || '0';
            document.getElementById('totalCalories').textContent = statsData.calories || '0';
        }

        // Загружаем предстоящие тренировки
        const trainingsResponse = await fetch('../server/api/user/trainings.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const trainingsData = await trainingsResponse.json();
        
        if (trainingsResponse.ok) {
            displayUpcomingTrainings(trainingsData.trainings);
        }

    } catch (error) {
        console.error('Error loading user data:', error);
    }

    // Обработка выхода
    document.getElementById('logoutBtn').addEventListener('click', function() {
        if (confirm('Czy na pewno chcesz się wylogować?')) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        }
    });

    // Обработка загрузки аватара
    document.getElementById('avatarInput').addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (file) {
            // Проверяем тип файла
            if (!file.type.startsWith('image/')) {
                alert('Proszę wybrać plik obrazu');
                return;
            }
            
            // Проверяем размер файла (максимум 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Maksymalny rozmiar pliku to 5MB');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await fetch('../server/api/user/upload-avatar.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: formData
                });

                const data = await response.json();
                console.log('Upload response:', data);

                if (data.status === 'success') {
                    document.getElementById('avatarImg').src = data.avatarUrl;
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error uploading avatar:', error);
                alert('Błąd podczas przesyłania avatara: ' + error.message);
            }
        }
    });
});

// Функция отображения предстоящих тренировок
function displayUpcomingTrainings(trainings) {
    const container = document.getElementById('upcomingTrainings');
    if (!trainings || trainings.length === 0) {
        container.innerHTML = '<p class="no-trainings">Brak zaplanowanych treningów</p>';
        return;
    }

    container.innerHTML = trainings.map(training => `
        <div class="training-item">
            <div class="training-date">
                <span class="day">${formatDay(training.date)}</span>
                <span class="month">${formatMonth(training.date)}</span>
            </div>
            <div class="training-info">
                <h4>${training.type}</h4>
                <p><i class="far fa-clock"></i> ${training.time}</p>
                <p><i class="fas fa-user"></i> Trener: ${training.trainer}</p>
            </div>
            <button class="btn-cancel" onclick="cancelTraining(${training.id})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

// Функция отмены тренировки
async function cancelTraining(trainingId) {
    if (!confirm('Czy na pewno chcesz anulować trening?')) return;

    try {
        const token = localStorage.getItem('token');
        const response = await fetch('../server/api/user/cancel-training.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ trainingId })
        });

        const data = await response.json();
        console.log('Cancel response:', data);

        if (data.status === 'success') {
            alert('Trening został anulowany');
            
            // Перезагружаем список тренировок
            const trainingsResponse = await fetch('../server/api/user/trainings.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const trainingsData = await trainingsResponse.json();
            
            if (trainingsData.status === 'success') {
                displayUpcomingTrainings(trainingsData.trainings);
            } else {
                throw new Error('Failed to reload trainings');
            }
        } else {
            throw new Error(data.message || 'Nie udało się anulować treningu');
        }
    } catch (error) {
        console.error('Error canceling training:', error);
        alert('Błąd podczas anulowania treningu');
    }
}

// Вспомогательные функции для форматирования даты
function formatDay(dateStr) {
    return new Date(dateStr).getDate();
}

function formatMonth(dateStr) {
    return new Date(dateStr).toLocaleString('pl-PL', { month: 'short' }).toUpperCase();
}

// Добавьте в начало файла
function checkAuth() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user'));
    
    if (!token || !user) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '../index.html';
        return false;
    }
    return true;
}

// В начале файла добавим функцию для проверки токена
async function validateToken() {
    const token = localStorage.getItem('token');
    if (!token) return false;

    try {
        const response = await fetch('../server/api/auth/check.php', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (!response.ok) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            return false;
        }
        
        return true;
    } catch (error) {
        console.error('Token validation error:', error);
        return false;
    }
}

// Обновим обработчик открытия модального окна
document.querySelector('.btn-add-training').addEventListener('click', async function() {
    const token = localStorage.getItem('token');
    console.log('Current token:', token);
    
    // Проверяем валидность токена
    const isValid = await validateToken();
    if (!isValid) {
        alert('Sesja wygasa. Zaloguj się ponownie.');
        window.location.href = 'login.html';
        return;
    }

    const modal = document.getElementById('newTrainingModal');
    modal.classList.add('active');
    
    try {
        console.log('Sending request with token:', token);
        const response = await fetch('../server/api/trainers/list.php', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (response.status === 401) {
            console.log('Token invalid, redirecting to login');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = 'login.html';
            return;
        }

        if (data.status === 'success' && data.trainers) {
            const trainerSelect = document.getElementById('trainerId');
            trainerSelect.innerHTML = '<option value="">Wybierz trenera</option>' +
                data.trainers.map(trainer => 
                    `<option value="${trainer.id}">${trainer.name}${trainer.specialization ? ` (${trainer.specialization})` : ''}</option>`
                ).join('');
                
            const timeSelect = document.getElementById('trainingTime');
            timeSelect.innerHTML = generateTimeOptions();
        } else {
            throw new Error(data.message || 'Nie udało się załadować listy trenerów');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Błąd podczas ładowania listy trenerów');
    }
});

// Закрытие модального окна
function closeTrainingModal() {
    document.getElementById('newTrainingModal').classList.remove('active');
}

// Генерация временных слотов
function generateTimeOptions() {
    let options = '<option value="">Wybierz godzinę</option>';
    for (let hour = 6; hour <= 21; hour++) {
        const time = `${hour.toString().padStart(2, '0')}:00`;
        options += `<option value="${time}">${time}</option>`;
    }
    return options;
}

// Обработка отправки формы
document.getElementById('newTrainingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const trainingData = {
        type: document.getElementById('trainingType').value,
        date: document.getElementById('trainingDate').value,
        time: document.getElementById('trainingTime').value,
        trainer_id: document.getElementById('trainerId').value
    };

    console.log('Form data:', trainingData); // Добавляем логирование

    // Проверяем, что все поля заполнены
    for (let key in trainingData) {
        if (!trainingData[key]) {
            alert(`Pole ${key} jest wymagane`);
            return;
        }
    }

    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authorization token found');
        }

        console.log('Sending request to create training...'); // Добавляем логирование
        const response = await fetch('../server/api/trainings/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(trainingData)
        });

        console.log('Response status:', response.status); // Добавляем логирование
        const data = await response.json();
        console.log('Response data:', data); // Добавляем логирование

        if (data.status === 'success') {
            alert('Trening został zaplanowany pomyślnie!');
            closeTrainingModal();
            
            // Перезагружаем список тренировок
            const trainingsResponse = await fetch('../server/api/user/trainings.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const trainingsData = await trainingsResponse.json();
            
            if (trainingsData.status === 'success') {
                displayUpcomingTrainings(trainingsData.trainings);
            } else {
                throw new Error(trainingsData.message || 'Failed to reload trainings');
            }
        } else {
            throw new Error(data.message || 'Nie udało się zaplanować treningu');
        }
    } catch (error) {
        console.error('Error creating training:', error);
        alert(error.message || 'Wystąpił błąd podczas planowania treningu');
    }
});

// Установка минимальной даты для выбора
document.getElementById('trainingDate').min = new Date().toISOString().split('T')[0]; 