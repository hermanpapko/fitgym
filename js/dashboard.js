document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('token');
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
Proszę o szczegółowy plan treningowy, który pomoże mi osiągnąć ten cel. Generuj odpowiedź jako html.
    `;

   
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
        const response = await fetch('../server/api/notes/list.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();

        if (data.status === 'success' && data.notes) {
            notesList.innerHTML = data.notes.map(note => `
                <div class="note-item" data-id="${note.id}">
                    <div class="note-header">
                        <span class="note-date">${new Date(note.created_at).toLocaleDateString()}</span>
                        <button class="btn-delete-note" onclick="deleteNote(${note.id})">
                            ×
                        </button>
                    </div>
                    <p class="note-content">${note.content}</p>
                </div>
            `).join('');
        } else {
            notesList.innerHTML = '<p class="no-notes">Brak notatek o postępie</p>';
        }
    } catch (error) {
        console.error('Error loading notes:', error);
        notesList.innerHTML = '<p class="error">Błąd podczas ładowania notatek</p>';
    }
}