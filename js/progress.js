// Функция для загрузки заметок
async function loadNotes() {
    try {
        const response = await fetch('../server/api/progress/get-notes.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            const notesList = document.getElementById('notesList');
            notesList.innerHTML = data.notes.map(note => {
                // Создаем объект даты
                const date = new Date(note.created_at);
                
                // Форматируем дату и время
                const formattedDate = date.toLocaleDateString('pl-PL', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                
                const formattedTime = date.toLocaleTimeString('pl-PL', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
                
                return `
                    <div class="note-item">
                        <div class="note-date">
                            <i class="fas fa-calendar-alt"></i>
                            ${formattedDate} ${formattedTime}
                        </div>
                        <div class="note-text">${note.note}</div>
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading notes:', error);
    }
}

// Функция для сохранения новой заметки
async function saveNote() {
    const noteText = document.getElementById('newNote').value.trim();
    if (!noteText) return;
    
    try {
        const response = await fetch('../server/api/progress/save-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ note: noteText })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            document.getElementById('newNote').value = '';
            await loadNotes();
        }
    } catch (error) {
        console.error('Error saving note:', error);
    }
}

// Инициализация
document.addEventListener('DOMContentLoaded', () => {
    loadNotes();
    
    document.getElementById('saveNote').addEventListener('click', saveNote);
    
    document.getElementById('newNote').addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            saveNote();
        }
    });
}); 