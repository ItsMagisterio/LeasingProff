/**
 * Скрипт для управления модальными окнами на платформе
 * Обеспечивает корректную работу всех модальных окон и обработку действий
 */

// Функция для прямой инициализации модального окна по ID
function openModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        // Проверяем, что Bootstrap доступен
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            return true;
        } else {
            console.error('Bootstrap не загружен. Модальное окно не может быть открыто.');
            return false;
        }
    } else {
        console.error('Модальное окно не найдено: ' + modalId);
        return false;
    }
}

// Инициализация после загрузки DOM
document.addEventListener("DOMContentLoaded", function() {
    console.log("Modal handler initialized");
    
    // Находим все модальные окна и создаем для них экземпляры Modal
    const modalElements = document.querySelectorAll('.modal');
    
    if (modalElements.length > 0) {
        console.log("Found", modalElements.length, "modal windows");
    } else {
        console.warn("No modal windows found on the page");
    }
    
    // Находим все кнопки, которые должны открывать модальные окна
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    if (modalTriggers.length > 0) {
        console.log("Found", modalTriggers.length, "modal triggers");
        
        modalTriggers.forEach(function(button) {
            const targetId = button.getAttribute('data-bs-target');
            console.log("Modal trigger:", button.textContent.trim(), "for target:", targetId);
            
            // Удаляем стандартный обработчик (если есть)
            button.removeAttribute('data-bs-toggle');
            
            // Добавляем свой обработчик клика
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                console.log("Button clicked for modal:", targetId);
                
                // Получаем ID модального окна из атрибута data-bs-target
                const modalId = targetId.substring(1); // Убираем # в начале
                
                // Открываем модальное окно
                openModal(modalId);
            });
        });
    } else {
        console.warn("No modal triggers found on the page");
    }
    
    // Добавляем обработку отправки форм модальных окон
    const modalForms = document.querySelectorAll('.modal form');
    
    if (modalForms.length > 0) {
        console.log("Found", modalForms.length, "forms in modals");
        
        modalForms.forEach(function(form) {
            console.log("Form action:", form.getAttribute('action') || 'Default action');
            
            form.addEventListener('submit', function(e) {
                console.log("Form is being submitted");
                
                // Значение поля action для обработки на сервере
                const actionField = form.querySelector('input[name="action"]');
                if (actionField) {
                    console.log("Action value:", actionField.value);
                }
                
                // Форма будет отправлена стандартным способом
            });
        });
    } else {
        console.warn("No forms found in modals");
    }

    // Добавим возможность открытия модального окна из URL
    const urlParams = new URLSearchParams(window.location.search);
    const openModalParam = urlParams.get('open_modal');
    
    if (openModalParam) {
        console.log("Opening modal from URL parameter:", openModalParam);
        openModal(openModalParam);
    }
});