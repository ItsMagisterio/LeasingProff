/**
 * Скрипт для переключения видимости поля пароля
 * Добавляет функциональность "показать/скрыть пароль" для полей ввода пароля
 */

document.addEventListener('DOMContentLoaded', function() {
    // Находим все кнопки переключения пароля
    const toggleButtons = document.querySelectorAll('.password-toggle');
    
    // Добавляем обработчик клика для каждой кнопки
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Получаем ID целевого поля пароля из атрибута data-target
            const targetId = this.getAttribute('data-target');
            const passwordField = document.getElementById(targetId);
            
            if (passwordField) {
                // Переключаем тип поля между password и text
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    // Меняем иконку на "глаз закрытый"
                    this.querySelector('i').classList.remove('fa-eye');
                    this.querySelector('i').classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    // Меняем иконку на "глаз открытый"
                    this.querySelector('i').classList.remove('fa-eye-slash');
                    this.querySelector('i').classList.add('fa-eye');
                }
            }
        });
    });
});