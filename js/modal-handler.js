/**
 * Скрипт для управления модальными окнами на платформе
 * Обеспечивает корректную работу всех модальных окон и обработку действий
 */

console.log("Debug: Modal handler script loaded");

// Глобальная функция для открытия модального окна, которая будет доступна из любого места
window.openModal = function(modalId) {
    console.log("Debug: Trying to open modal:", modalId);
    const modalElement = document.getElementById(modalId);
    
    if (!modalElement) {
        console.error("Debug: Modal element not found:", modalId);
        return false;
    }
    
    try {
        if (typeof bootstrap === 'undefined') {
            console.error("Debug: Bootstrap is not defined!");
            return false;
        }
        
        console.log("Debug: Creating new Bootstrap Modal instance");
        const modalInstance = new bootstrap.Modal(modalElement);
        console.log("Debug: Showing modal");
        modalInstance.show();
        return true;
    } catch (error) {
        console.error("Debug: Error opening modal:", error);
        return false;
    }
};

// Добавляем алиас showModal для совместимости
window.showModal = window.openModal;

// Инициализация после загрузки DOM
document.addEventListener("DOMContentLoaded", function() {
    console.log("Modal handler initialized with showModal alias");
    
    // Проверяем, доступен ли bootstrap
    if (typeof bootstrap === 'undefined') {
        console.error("Debug: Bootstrap not loaded! Check if the script is included before modal-handler.js");
        return;
    }
    
    // При использовании стандартного data-bs-toggle="modal" Bootstrap сам обрабатывает модальные окна
    // Мы не будем мешать стандартной функциональности Bootstrap
    
    // Находим все модальные окна
    const modalElements = document.querySelectorAll('.modal');
    console.log("Debug: Found", modalElements.length, "modal elements");
    
    // Добавим отладочный вывод для всех модальных окон
    modalElements.forEach((modal, index) => {
        console.log(`Debug: Modal ${index + 1} - ID: ${modal.id}`);
        
        // Отслеживаем события открытия и закрытия для отладки
        modal.addEventListener('show.bs.modal', function(event) {
            console.log(`Debug: Modal ${modal.id} is about to be shown`);
        });
        
        modal.addEventListener('shown.bs.modal', function(event) {
            console.log(`Debug: Modal ${modal.id} is now visible`);
        });
        
        modal.addEventListener('hide.bs.modal', function(event) {
            console.log(`Debug: Modal ${modal.id} is about to be hidden`);
        });
        
        modal.addEventListener('hidden.bs.modal', function(event) {
            console.log(`Debug: Modal ${modal.id} is now hidden`);
        });
    });
    
    // Находим все кнопки, которые открывают модальные окна (стандартные Bootstrap)
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    console.log("Debug: Found", modalTriggers.length, "standard bootstrap modal triggers");
    
    modalTriggers.forEach((button, index) => {
        const targetSelector = button.getAttribute('data-bs-target');
        console.log(`Debug: Trigger ${index + 1} - Target: ${targetSelector}, Text: ${button.textContent.trim()}`);
        
        // Мы не будем переопределять стандартное поведение, но добавим дополнительное логирование
        button.addEventListener('click', function(event) {
            console.log(`Debug: Modal trigger clicked for ${targetSelector}`);
            // Не отменяем событие по умолчанию, чтобы Bootstrap мог обработать клик
        });
    });
    
    // Находим все наши собственные кнопки с классом modal-open-btn
    const customModalTriggers = document.querySelectorAll('.modal-open-btn');
    console.log("Debug: Found", customModalTriggers.length, "custom modal triggers");
    
    customModalTriggers.forEach((button, index) => {
        const targetId = button.getAttribute('data-target');
        console.log(`Debug: Custom trigger ${index + 1} - Target: ${targetId}, Text: ${button.textContent.trim()}`);
        
        button.addEventListener('click', function(event) {
            event.preventDefault();
            console.log(`Debug: Custom modal trigger clicked for ${targetId}`);
            
            // Проверяем наличие # в начале ID
            const modalId = targetId.startsWith('#') ? targetId : `#${targetId}`;
            const modalElement = document.querySelector(modalId);
            
            if (!modalElement) {
                console.error(`Debug: Modal with ID ${modalId} not found`);
                return;
            }
            
            try {
                const modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            } catch (error) {
                console.error("Debug: Error showing modal:", error);
            }
        });
    });
    
    // Находим все формы в модальных окнах
    const modalForms = document.querySelectorAll('.modal form');
    console.log("Debug: Found", modalForms.length, "forms in modals");
    
    modalForms.forEach((form, index) => {
        const action = form.getAttribute('action') || 'No action specified';
        const method = form.getAttribute('method') || 'GET';
        console.log(`Debug: Form ${index + 1} - Action: ${action}, Method: ${method}`);
        
        // Проверяем и исправляем атрибуты формы
        if (!form.action || form.action === 'javascript:void(0)' || form.action === '#' || form.action.includes('[object HTMLInputElement]')) {
            form.action = 'index.php?page=dashboard-admin';
            console.log(`Debug: Corrected form action to: ${form.action}`);
        }
        
        if (!form.method || form.method.toLowerCase() !== 'post') {
            form.method = 'post';
            console.log(`Debug: Corrected form method to: post`);
        }
        
        // Если форма имеет атрибут onsubmit, удаляем его
        if (form.hasAttribute('onsubmit')) {
            console.log(`Debug: Removing onsubmit attribute from form`);
            form.removeAttribute('onsubmit');
        }
        
        // Для отладки - отслеживаем отправку формы, но НЕ вмешиваемся в процесс
        form.addEventListener('submit', function(event) {
            console.log(`Debug: Form is being submitted - Action: ${form.action}, Method: ${form.method}`);
            
            // Получим значение поля action для отладки
            const actionField = form.querySelector('input[name="action"]');
            if (actionField) {
                console.log(`Debug: Action field value: ${actionField.value}`);
            }
            
            // Больше НИКАКОГО вмешательства в отправку формы!
            // НЕТ event.preventDefault() - форма отправится стандартным способом
        });
    });
    
    // Добавим возможность открытия модального окна из URL
    const urlParams = new URLSearchParams(window.location.search);
    const openModalParam = urlParams.get('open_modal');
    
    if (openModalParam) {
        console.log("Debug: Opening modal from URL parameter:", openModalParam);
        window.openModal(openModalParam);
    }
});