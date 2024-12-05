// Функция загрузки предстоящих тренировок
async function loadUpcomingTrainings() {
    try {
        const response = await fetch('../server/api/trainings/get-upcoming.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        const trainingsList = document.getElementById('upcomingTrainings');
        if (!trainingsList) return;

        if (!data.trainings || data.trainings.length === 0) {
            trainingsList.innerHTML = `
                <div class="no-trainings">
                    <p>Nie masz zaplanowanych treningów</p>
                </div>
            `;
            return;
        }
        
        trainingsList.innerHTML = data.trainings.map(training => {
            const date = new Date(training.date);
            const time = training.time.substring(0, 5);
            
            return `
                <div class="training-item">
                    <div class="training-info">
                        <h4>${training.type}</h4>
                        <p><i class="fas fa-calendar"></i> ${date.toLocaleDateString('pl-PL')}</p>
                        <p><i class="fas fa-clock"></i> ${time}</p>
                        <p><i class="fas fa-user"></i> ${training.trainer_name}</p>
                    </div>
                    <button class="btn-cancel" onclick="cancelTraining(${training.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Error loading trainings:', error);
    }
}

// Обновляем функцию отмены тренировки
async function cancelTraining(trainingId) {
    if (!confirm('Czy na pewno chcesz anulować ten trening?')) {
        return;
    }
    
    try {
        const response = await fetch('../server/api/trainings/cancel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ training_id: trainingId })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Перезагружаем список тренировок
            await loadUpcomingTrainings();
            // Показываем сообщение об успехе
            alert('Trening został pomyślnie anulowany');
        } else {
            throw new Error(data.message || 'Nie udało się anulować treningu');
        }
    } catch (error) {
        console.error('Error canceling training:', error);
        alert('Wystąpił błąd podczas anulowania treningu: ' + error.message);
    }
}

// Загружаем тренировки при загрузке страницы
document.addEventListener('DOMContentLoaded', loadUpcomingTrainings);