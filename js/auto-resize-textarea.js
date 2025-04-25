/**
 * Скрипт для автоматического изменения размера текстовых полей
 * Поле будет растягиваться по мере ввода текста
 */

document.addEventListener('DOMContentLoaded', function() {
    // Находим все текстовые поля на странице
    const textareas = document.querySelectorAll('textarea');
    
    // Функция для автоматического изменения размера
    function autoResize(textarea) {
        // Сбрасываем высоту на минимальную
        textarea.style.height = 'auto';
        // Устанавливаем новую высоту по размеру содержимого
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    
    // Добавляем обработчики событий для каждого текстового поля
    textareas.forEach(function(textarea) {
        // Устанавливаем начальную высоту
        autoResize(textarea);
        
        // Обработчики событий для обновления размера при вводе текста
        textarea.addEventListener('input', function() {
            autoResize(this);
        });
        
        textarea.addEventListener('change', function() {
            autoResize(this);
        });
        
        // Также изменяем размер при фокусе, для корректной работы при загрузке страницы
        textarea.addEventListener('focus', function() {
            autoResize(this);
        });
    });
});