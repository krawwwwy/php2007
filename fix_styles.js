document.addEventListener('DOMContentLoaded', function() {
    // Немедленное выполнение стилизации для обеспечения видимости
    applyStyles();
    
    // Дополнительный запуск после короткой задержки для надежности
    setTimeout(applyStyles, 100);
    
    function applyStyles() {
        // Исправление стилей для главного меню
        const mainMenuLinks = document.querySelectorAll('.main-menu a');
        mainMenuLinks.forEach(link => {
            // Обновляем стили всех ссылок в главном меню
            link.style.setProperty('display', 'block', 'important');
            link.style.setProperty('padding', '8px 15px', 'important');
            link.style.setProperty('color', '#333', 'important'); // Темный цвет для видимости
            link.style.setProperty('font-weight', 'bold', 'important');
            link.style.setProperty('border-radius', '4px', 'important');
            link.style.setProperty('transition', 'all 0.3s', 'important');
            link.style.setProperty('visibility', 'visible', 'important');
            link.style.setProperty('opacity', '1', 'important');
            
            // Добавляем обработчик для hover эффекта
            link.addEventListener('mouseenter', function() {
                this.style.setProperty('background-color', '#0099cc', 'important');
                this.style.setProperty('color', '#fff', 'important');
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.setProperty('background-color', 'transparent', 'important');
                this.style.setProperty('color', '#333', 'important');
            });
        });
        
        // Исправление стилей для кнопки регистрации - принудительная видимость
        const registerButtons = document.querySelectorAll('a.auth-btn, button.auth-btn, input[type="submit"].auth-btn');
        registerButtons.forEach(btn => {
            // Обновляем стили всех кнопок авторизации/регистрации с максимальным приоритетом
            // Устанавливаем НЕ синий цвет по умолчанию
            btn.style.setProperty('display', 'inline-block', 'important');
            btn.style.setProperty('padding', '5px 10px', 'important');
            btn.style.setProperty('background-color', '#f8f8f8', 'important'); // Светло-серый фон по умолчанию
            btn.style.setProperty('color', '#333', 'important'); // Темный текст для контраста
            btn.style.setProperty('text-decoration', 'none', 'important');
            btn.style.setProperty('border-radius', '4px', 'important');
            btn.style.setProperty('font-weight', 'bold', 'important');
            btn.style.setProperty('border', '1px solid #ccc', 'important'); // Добавляем рамку для лучшей видимости
            btn.style.setProperty('cursor', 'pointer', 'important');
            btn.style.setProperty('visibility', 'visible', 'important');
            btn.style.setProperty('opacity', '1', 'important');
            btn.style.setProperty('position', 'static', 'important');
            btn.style.setProperty('height', 'auto', 'important');
            btn.style.setProperty('width', 'auto', 'important');
            btn.style.setProperty('overflow', 'visible', 'important');
            btn.style.setProperty('clip', 'auto', 'important');
            btn.style.setProperty('margin', '0', 'important');
            
            // Добавляем обработчик для hover эффекта - синий цвет ТОЛЬКО при наведении
            btn.addEventListener('mouseenter', function() {
                this.style.setProperty('background-color', '#337ab7', 'important'); // Синий при наведении
                this.style.setProperty('color', '#fff', 'important'); // Белый текст при наведении
                this.style.setProperty('border-color', '#2e6da4', 'important'); // Тёмно-синяя рамка при наведении
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.setProperty('background-color', '#f8f8f8', 'important'); // Возврат к светло-серому
                this.style.setProperty('color', '#333', 'important'); // Возврат к тёмному тексту
                this.style.setProperty('border-color', '#ccc', 'important'); // Возврат к серой рамке
            });
            
            // Создаем дополнительную оболочку для принудительной видимости
            btn.classList.add('force-visible');
        });
        
        // Специальное исправление для кнопок в форме авторизации в шапке
        const headerAuthButtons = document.querySelectorAll('form.auth-form .auth-btn');
        headerAuthButtons.forEach(btn => {
            btn.style.setProperty('background', '#fff8e1', 'important');
            btn.style.setProperty('border', '2px solid #ffa500', 'important');
            btn.style.setProperty('color', '#000', 'important');
            
            // Добавляем специальный hover-эффект для кнопок авторизации
            btn.addEventListener('mouseenter', function() {
                this.style.setProperty('background', '#ffa500', 'important');
                this.style.setProperty('color', '#fff', 'important');
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.setProperty('background', '#fff8e1', 'important');
                this.style.setProperty('color', '#000', 'important');
            });
        });
        
        // Добавляем глобальные CSS-правила для усиления видимости
        const forceStyle = document.createElement('style');
        forceStyle.innerHTML = `
            .auth-btn, a.auth-btn, button.auth-btn, input[type="submit"].auth-btn {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: static !important;
                overflow: visible !important;
                height: auto !important;
                width: auto !important;
                clip: auto !important;
                margin: 0 !important;
                background-color: #f8f8f8 !important; /* НЕ синий по умолчанию */
                color: #333 !important;
                text-decoration: none !important;
                border-radius: 4px !important;
                padding: 5px 10px !important;
                font-weight: bold !important;
                border: 1px solid #ccc !important;
            }
            .auth-btn:hover, a.auth-btn:hover, button.auth-btn:hover, input[type="submit"].auth-btn:hover {
                background-color: #337ab7 !important; /* Синий ТОЛЬКО при наведении */
                color: white !important;
                border-color: #2e6da4 !important;
            }
            .force-visible {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            /* Специальные стили для кнопок в форме авторизации */
            form.auth-form .auth-btn {
                background: #fff8e1 !important;
                border: 2px solid #ffa500 !important;
                color: #000 !important;
            }
            form.auth-form .auth-btn:hover {
                background: #ffa500 !important;
                color: #fff !important;
            }
        `;
        document.head.appendChild(forceStyle);
    }
}); 