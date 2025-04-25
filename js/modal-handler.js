/**
 * Скрипт для управления модальными окнами на платформе
 * Обеспечивает корректную работу всех модальных окон и обработку действий
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("Modal handler initialized");
    
    // Находим все кнопки, которые должны открывать модальные окна
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    modalTriggers.forEach(function(button) {
        console.log("Found modal trigger:", button.textContent.trim());
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSelector = this.getAttribute('data-bs-target');
            console.log("Opening modal:", targetSelector);
            
            const modalElement = document.querySelector(targetSelector);
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error("Modal not found:", targetSelector);
            }
        });
    });
    
    // Обработка отправки форм внутри модальных окон
    const modalForms = document.querySelectorAll('.modal form');
    
    modalForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            console.log("Form submitted in modal");
            // Форма будет отправлена стандартным способом
        });
    });
});