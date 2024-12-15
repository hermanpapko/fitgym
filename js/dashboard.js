document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('token');
    const promoBanner = document.getElementById('promoBanner');

    if (token && promoBanner) {
        promoBanner.style.display = 'none';
    }

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Добавляем обработчик для кнопки выхода
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('Czy na pewno chcesz się wylogować?')) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = 'login.html';
            }
        });
    }

    // Обновляем имя пользователя в навигации
    const userNameInNav = document.querySelector('nav #userName');
    if (userNameInNav) {
        const user = JSON.parse(localStorage.getItem('user'));
        if (user && user.name) {
            userNameInNav.textContent = user.name;
        }
    }

    initMembership();
    loadProfile();
    loadNotes();

    // Установка минимальной даты и времени для модального окна планирования тренировки
    const trainingDateInput = document.getElementById('trainingDate');
    const trainingTimeInput = document.getElementById('trainingTime');

    if (trainingDateInput && trainingTimeInput) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        // Форматирование текущей даты и времени
        const todayDate = `${year}-${month}-${day}`;
        const currentTime = `${hours}:${minutes}`;

        // Установка минимальных значений
        trainingDateInput.min = todayDate;
        trainingTimeInput.min = currentTime;

        // Обработчик изменения даты
        trainingDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();

            // Проверка, в��брана ли сегодняшняя дата
            if (
                selectedDate.getFullYear() === today.getFullYear() &&
                selectedDate.getMonth() === today.getMonth() &&
                selectedDate.getDate() === today.getDate()
            ) {
                // Если выбрана сегодня, установить минимальное время на текущее
                trainingTimeInput.min = `${hours}:${minutes}`;
            } else {
                // Иначе установить минимальное время как начало дня
                trainingTimeInput.min = '00:00';
            }
        });
    }

    // Валидация формы планирования тренировки
    const planTrainingForm = document.getElementById('planTrainingForm');
    if (planTrainingForm) {
        planTrainingForm.addEventListener('submit', function(e) {
            const selectedDate = new Date(trainingDateInput.value);
            const selectedTimeParts = trainingTimeInput.value.split(':');
            selectedDate.setHours(selectedTimeParts[0], selectedTimeParts[1]);
            
            const now = new Date();
            if (selectedDate < now) {
                e.preventDefault();
                alert('Выбранная дата и время уж�� прошли. Пожалуйста, выберите актуальную дату и время.');
            }
        });
    }
});

// Функция инициализации членства
async function initMembership() {
    const badge = document.getElementById('membershipText');
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const userNameInNav = document.querySelector('nav #userName');

    try {
        const response = await fetch('../server/api/user/profile.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        
        if (data.user) {
            // Обновляем информацию в профиле
            if (profileName) profileName.textContent = data.user.name || 'Nieznany użytkownik';
            if (profileEmail) profileEmail.textContent = data.user.email || 'Brak adresu e-mail';
            
            // Обновляем имя в навигации
            if (userNameInNav) userNameInNav.textContent = data.user.name;

            // Обновляем статус членства
            if (badge && data.user.membership) {
                const membership = data.user.membership.toLowerCase();
                badge.textContent = membership === 'vip' ? 'VIP' : 
                    membership.charAt(0).toUpperCase() + membership.slice(1);
            }
        }
    } catch (e) {
        console.error('Error loading user profile:', e);
    }
}

document.getElementById('generate-plan').addEventListener('click', async () => {
    const height = parseInt(document.getElementById('height').value, 10);
    const weight = parseInt(document.getElementById('weight').value, 10);
    const goal = document.getElementById('goal').value;
    const output = document.getElementById('plan-output');

    // Показываем блок вывода
    output.classList.add('visible');

    if (!height || !weight) {
        output.innerHTML = "<p class='error'>Proszę wypełnić wszystkie pola poprawnie.</p>";
        return;
    }

    // Показываем индикатор загрузки
    output.innerHTML = `
        <div class="loading-indicator">
            Generowanie planu treningowego...
        </div>
    `;

    const prompt = `
      Tworzę plan treningowy. Mój wzrost to ${height} cm, moja waga to ${weight} kg. Moim celem jest ${goal === 'gain' ? 'przybranie na wadze' : goal === 'lose' ? 'schudnięcie' : 'utrzymanie wagi'}.
Proszę o szczegółowy plan treningowy, który pomoże mi osiągnąć ten cel. Generuj odpowiedź jako html. Nie pisz niczego poza planem treningowym.
    `;
    
    // const apiKey = ""; // Niebezpieczne, если ключ виден!
    
    const endpoint = "https://api.openai.com/v1/chat/completions";

    const requestData = {
        model: "gpt-4o-mini",
        messages: [{ role: "user", content: prompt }],
    };

    try {
        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${apiKey}`,
            },
            body: JSON.stringify(requestData),
        });

        if (!response.ok) {
            throw new Error(`Błąd: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        const plan = data.choices[0].message.content;

        output.innerHTML = `<h3>Twój plan treningowy</h3><p>${plan}</p>`;
    } catch (error) {
        console.error("Error:", error);
        output.innerHTML = `<p>Wystąpił błąd podczas generowania planu. Spróbuj ponownie później.</p>`;
    }
});

async function loadNotes() {
    const notesList = document.getElementById('notesList');
    if (!notesList) return;

    try {
        const response = await fetch('../server/api/progress/get-notes.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();

        if (data.status === 'success' && data.notes) {
            notesList.innerHTML = data.notes.map(note => `
                <div class="note-item" data-id="${note.id}">
                    <p class="note-content">${note.note}</p>
                    <button class="btn-delete-note" onclick="deleteNote(${note.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');
        } else {
            notesList.innerHTML = '<p class="no-notes">Brak notatek o postępie</p>';
        }
    } catch (error) {
        console.error('Ошибка при загрузке заметок:', error);
        notesList.innerHTML = '<p class="error">Błąd podczas ładowania notatek</p>';
    }
}

// Добавляем обработчик загрузки аватара
document.getElementById('avatarInput').addEventListener('change', async function(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Создаем FormData
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
        
        if (data.status === 'success') {
            alert('Аватар успешно загружен!');
            // Обновляем аватар в навигации и профиле
            await loadProfile();
        } else {
            alert('Ошибка при загрузке аватара: ' + data.message);
        }
    } catch (error) {
        console.error('Ошибка при загрузке аватара:', error);
        alert('Произошла ошибка при загрузке аватара');
    }
});

// Функция загрузки профиля
async function loadProfile() {
    try {
        const response = await fetch('../server/api/user/profile.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        if (!response.ok) {
            throw new Error('Сетевая ошибка');
        }
        
        const data = await response.json();
        
        if (data.status === 'success' && data.user) {
            // Обновляем имя пользователя
            document.getElementById('userName').textContent = data.user.name;
            
            // Обновляем аватар в навигации
            const avatarImg = document.getElementById('avatarImg');
            if (avatarImg && data.user.avatar) {
                const mimeType = data.user.avatar_mime || 'image/jpeg';
                const src = `data:${mimeType};base64,${data.user.avatar}`;
                avatarImg.src = src;
            }
            
            // Дополнительно обновляем другие данные профиля, если есть
        }
    } catch (error) {
        console.error('Ошибка при загрузке профиля:', error);
    }
}

// Вызываем loadProfile при загрузке страницы
document.addEventListener('DOMContentLoaded', loadProfile);

// Функция для удаления заметки
async function deleteNote(noteId) {
    if (!confirm('Czy na pewno chcesz usunąć tę notatkę?')) {
        return;
    }

    try {
        const response = await fetch('../server/api/progress/delete-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ note_id: noteId })
        });

        const data = await response.json();

        if (data.status === 'success') {
            alert('Заметка успешно удалена!');
            await loadNotes(); // Перезагружаем список заметок
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Ошибка при удалении заметки:', error);
        alert('Произошла ошибка при удалении заметки: ' + error.message);
    }
}