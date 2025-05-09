/**
 * Калькулятор лизинга для 2Leasing
 * Обеспечивает расчеты лизинга для транспорта и недвижимости
 */

// Данные лизинговых компаний и их предложений
const leasingCompanies = [
    {
        id: 1,
        name: 'Альфа-Лизинг',
        logo: '/images/logos/alfa-leasing.svg',
        rating: 4.9,
        vehicleRates: {
            car: { minRate: 9.5, maxRate: 12.5 },
            truck: { minRate: 10.5, maxRate: 13.5 },
            special: { minRate: 11.5, maxRate: 14.5 }
        },
        realEstateRates: {
            apartment: { minRate: 11.0, maxRate: 14.0 },
            house: { minRate: 12.0, maxRate: 15.0 },
            commercial: { minRate: 13.0, maxRate: 16.0 }
        },
        vehicleDownPaymentMin: 10,
        realEstateDownPaymentMin: 20
    },
    {
        id: 2,
        name: 'ВТБ Лизинг',
        logo: '/images/logos/vtb-leasing.svg',
        rating: 4.7,
        vehicleRates: {
            car: { minRate: 10.0, maxRate: 13.0 },
            truck: { minRate: 11.0, maxRate: 14.0 },
            special: { minRate: 12.0, maxRate: 15.0 }
        },
        realEstateRates: {
            apartment: { minRate: 11.5, maxRate: 14.5 },
            house: { minRate: 12.5, maxRate: 15.5 },
            commercial: { minRate: 13.5, maxRate: 16.5 }
        },
        vehicleDownPaymentMin: 15,
        realEstateDownPaymentMin: 25
    },
    {
        id: 3,
        name: 'Сбербанк Лизинг',
        logo: '/images/logos/sberbank-leasing.svg',
        rating: 4.8,
        vehicleRates: {
            car: { minRate: 9.8, maxRate: 12.8 },
            truck: { minRate: 10.8, maxRate: 13.8 },
            special: { minRate: 11.8, maxRate: 14.8 }
        },
        realEstateRates: {
            apartment: { minRate: 11.3, maxRate: 14.3 },
            house: { minRate: 12.3, maxRate: 15.3 },
            commercial: { minRate: 13.3, maxRate: 16.3 }
        },
        vehicleDownPaymentMin: 12,
        realEstateDownPaymentMin: 22
    },
    {
        id: 4,
        name: 'Газпромбанк Лизинг',
        logo: '/images/logos/gazprombank-leasing.svg',
        rating: 4.6,
        vehicleRates: {
            car: { minRate: 10.2, maxRate: 13.2 },
            truck: { minRate: 11.2, maxRate: 14.2 },
            special: { minRate: 12.2, maxRate: 15.2 }
        },
        realEstateRates: {
            apartment: { minRate: 11.7, maxRate: 14.7 },
            house: { minRate: 12.7, maxRate: 15.7 },
            commercial: { minRate: 13.7, maxRate: 16.7 }
        },
        vehicleDownPaymentMin: 15,
        realEstateDownPaymentMin: 25
    },
    {
        id: 5,
        name: 'Европлан',
        logo: '/images/logos/europlan.svg',
        rating: 4.5,
        vehicleRates: {
            car: { minRate: 10.5, maxRate: 13.5 },
            truck: { minRate: 11.5, maxRate: 14.5 },
            special: { minRate: 12.5, maxRate: 15.5 }
        },
        realEstateRates: null, // Не предлагает лизинг недвижимости
        vehicleDownPaymentMin: 10,
        realEstateDownPaymentMin: null
    },
    {
        id: 6,
        name: 'РЕСО-Лизинг',
        logo: '/images/logos/reso-leasing.svg',
        rating: 4.4,
        vehicleRates: {
            car: { minRate: 10.8, maxRate: 13.8 },
            truck: { minRate: 11.8, maxRate: 14.8 },
            special: { minRate: 12.8, maxRate: 15.8 }
        },
        realEstateRates: null, // Не предлагает лизинг недвижимости
        vehicleDownPaymentMin: 15,
        realEstateDownPaymentMin: null
    }
];

// Вспомогательная функция для форматирования чисел с пробелами
function formatNumberWithSpaces(number) {
    if (!number) return '';
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

// Обратная функция для удаления пробелов из числа
function removeSpacesFromNumber(numberString) {
    if (!numberString) return '';
    return numberString.toString().replace(/\s/g, "");
}

// Функции для калькулятора транспорта
function updateVehiclePrice() {
    const vehiclePriceRange = document.getElementById('vehiclePriceRange');
    const vehiclePrice = document.getElementById('vehiclePrice');
    
    if (vehiclePriceRange && vehiclePrice) {
        // Для полей input type="text" можно использовать пробелы в value
        vehiclePrice.value = formatNumberWithSpaces(vehiclePriceRange.value);
    }
}

function updateVehiclePriceRange() {
    const vehiclePriceRange = document.getElementById('vehiclePriceRange');
    const vehiclePrice = document.getElementById('vehiclePrice');
    
    if (vehiclePriceRange && vehiclePrice) {
        vehiclePriceRange.value = removeSpacesFromNumber(vehiclePrice.value);
    }
}

function updateVehicleDownPayment() {
    const vehicleDownPaymentRange = document.getElementById('vehicleDownPaymentRange');
    const vehicleDownPayment = document.getElementById('vehicleDownPayment');
    
    if (vehicleDownPaymentRange && vehicleDownPayment) {
        vehicleDownPayment.value = vehicleDownPaymentRange.value;
    }
}

function updateVehicleDownPaymentRange() {
    const vehicleDownPaymentRange = document.getElementById('vehicleDownPaymentRange');
    const vehicleDownPayment = document.getElementById('vehicleDownPayment');
    
    if (vehicleDownPaymentRange && vehicleDownPayment) {
        vehicleDownPaymentRange.value = vehicleDownPayment.value;
    }
}

function updateVehicleTerm() {
    const vehicleTermRange = document.getElementById('vehicleTermRange');
    const vehicleTerm = document.getElementById('vehicleTerm');
    
    if (vehicleTermRange && vehicleTerm) {
        vehicleTerm.value = vehicleTermRange.value;
    }
}

function updateVehicleTermRange() {
    const vehicleTermRange = document.getElementById('vehicleTermRange');
    const vehicleTerm = document.getElementById('vehicleTerm');
    
    if (vehicleTermRange && vehicleTerm) {
        vehicleTermRange.value = vehicleTerm.value;
    }
}

// Функции для калькулятора недвижимости
function updateRealEstatePrice() {
    const realEstatePriceRange = document.getElementById('realEstatePriceRange');
    const realEstatePrice = document.getElementById('realEstatePrice');
    
    if (realEstatePriceRange && realEstatePrice) {
        // Для полей input type="text" можно использовать пробелы в value
        realEstatePrice.value = formatNumberWithSpaces(realEstatePriceRange.value);
    }
}

function updateRealEstatePriceRange() {
    const realEstatePriceRange = document.getElementById('realEstatePriceRange');
    const realEstatePrice = document.getElementById('realEstatePrice');
    
    if (realEstatePriceRange && realEstatePrice) {
        realEstatePriceRange.value = removeSpacesFromNumber(realEstatePrice.value);
    }
}

function updateRealEstateDownPayment() {
    const realEstateDownPaymentRange = document.getElementById('realEstateDownPaymentRange');
    const realEstateDownPayment = document.getElementById('realEstateDownPayment');
    
    if (realEstateDownPaymentRange && realEstateDownPayment) {
        realEstateDownPayment.value = realEstateDownPaymentRange.value;
    }
}

function updateRealEstateDownPaymentRange() {
    const realEstateDownPaymentRange = document.getElementById('realEstateDownPaymentRange');
    const realEstateDownPayment = document.getElementById('realEstateDownPayment');
    
    if (realEstateDownPaymentRange && realEstateDownPayment) {
        realEstateDownPaymentRange.value = realEstateDownPayment.value;
    }
}

function updateRealEstateTerm() {
    const realEstateTermRange = document.getElementById('realEstateTermRange');
    const realEstateTerm = document.getElementById('realEstateTerm');
    
    if (realEstateTermRange && realEstateTerm) {
        realEstateTerm.value = realEstateTermRange.value;
    }
}

function updateRealEstateTermRange() {
    const realEstateTermRange = document.getElementById('realEstateTermRange');
    const realEstateTerm = document.getElementById('realEstateTerm');
    
    if (realEstateTermRange && realEstateTerm) {
        realEstateTermRange.value = realEstateTerm.value;
    }
}

// Функция расчета лизинга транспорта
function calculateVehicleLeasing() {
    const price = parseFloat(removeSpacesFromNumber(document.getElementById('vehiclePrice').value));
    const downPaymentPercent = parseFloat(removeSpacesFromNumber(document.getElementById('vehicleDownPayment').value));
    const term = parseInt(document.getElementById('vehicleTerm').value);
    const vehicleType = document.getElementById('vehicleType').value;
    
    const downPaymentAmount = price * (downPaymentPercent / 100);
    const baseLoanAmount = price - downPaymentAmount;
    
    // Базовая ставка для расчета (может варьироваться в зависимости от типа транспорта)
    let baseRate = 11.0; // Базовая годовая процентная ставка
    if (vehicleType === 'truck') {
        baseRate = 12.0;
    } else if (vehicleType === 'special') {
        baseRate = 13.0;
    }
    
    // Расчет ежемесячного платежа
    const monthlyRate = baseRate / 100 / 12;
    const monthlyPayment = baseLoanAmount * (monthlyRate * Math.pow(1 + monthlyRate, term)) / (Math.pow(1 + monthlyRate, term) - 1);
    
    // Общая стоимость лизинга
    const totalCost = monthlyPayment * term + downPaymentAmount;
    
    // Обновляем отображение результатов
    document.getElementById('vehicleMonthlyPayment').textContent = formatCurrency(monthlyPayment);
    document.getElementById('vehicleTotalCost').textContent = formatCurrency(totalCost);
    document.getElementById('vehicleDownPaymentAmount').textContent = formatCurrency(downPaymentAmount);
    
    // Отображаем результаты
    document.getElementById('vehicleResult').style.display = 'block';
    
    // Подбираем подходящие предложения лизинговых компаний
    showMatchingVehicleCompanies(price, downPaymentPercent, term, vehicleType, monthlyPayment);
}

// Функция расчета лизинга недвижимости
function calculateRealEstateLeasing() {
    const price = parseFloat(removeSpacesFromNumber(document.getElementById('realEstatePrice').value));
    const downPaymentPercent = parseFloat(removeSpacesFromNumber(document.getElementById('realEstateDownPayment').value));
    const term = parseInt(document.getElementById('realEstateTerm').value);
    const realEstateType = document.getElementById('realEstateType').value;
    
    const downPaymentAmount = price * (downPaymentPercent / 100);
    const baseLoanAmount = price - downPaymentAmount;
    
    // Базовая ставка для расчета (может варьироваться в зависимости от типа недвижимости)
    let baseRate = 12.5; // Базовая годовая процентная ставка
    if (realEstateType === 'house') {
        baseRate = 13.0;
    } else if (realEstateType === 'commercial') {
        baseRate = 14.0;
    }
    
    // Расчет ежемесячного платежа
    const monthlyRate = baseRate / 100 / 12;
    const monthlyPayment = baseLoanAmount * (monthlyRate * Math.pow(1 + monthlyRate, term)) / (Math.pow(1 + monthlyRate, term) - 1);
    
    // Общая стоимость лизинга
    const totalCost = monthlyPayment * term + downPaymentAmount;
    
    // Обновляем отображение результатов
    document.getElementById('realEstateMonthlyPayment').textContent = formatCurrency(monthlyPayment);
    document.getElementById('realEstateTotalCost').textContent = formatCurrency(totalCost);
    document.getElementById('realEstateDownPaymentAmount').textContent = formatCurrency(downPaymentAmount);
    
    // Отображаем результаты
    document.getElementById('realEstateResult').style.display = 'block';
    
    // Подбираем подходящие предложения лизинговых компаний
    showMatchingRealEstateCompanies(price, downPaymentPercent, term, realEstateType, monthlyPayment);
}

// Показать подходящие компании для лизинга транспорта
function showMatchingVehicleCompanies(price, downPaymentPercent, term, vehicleType, monthlyPayment) {
    const companiesContainer = document.querySelector('#vehicleCompanies .row');
    companiesContainer.innerHTML = '';
    
    // Получаем vehicle_id из URL, если он там есть
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get('id') || 0;
    
    let matchingCompanies = leasingCompanies.filter(company => {
        // Проверяем, что компания предлагает лизинг транспорта выбранного типа
        if (!company.vehicleRates || !company.vehicleRates[vehicleType]) {
            return false;
        }
        
        // Проверяем минимальный первоначальный взнос
        return company.vehicleDownPaymentMin <= downPaymentPercent;
    });
    
    // Сортируем по привлекательности предложения (по ставке)
    matchingCompanies.sort((a, b) => {
        return a.vehicleRates[vehicleType].minRate - b.vehicleRates[vehicleType].minRate;
    });
    
    if (matchingCompanies.length === 0) {
        companiesContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">К сожалению, не найдено подходящих предложений. Попробуйте изменить параметры расчета.</div></div>';
        return;
    }
    
    // Отображаем топ-3 компании
    matchingCompanies.slice(0, 3).forEach(company => {
        // Рассчитываем примерную ставку для этой компании
        let rate = company.vehicleRates[vehicleType].minRate + 
            ((downPaymentPercent - company.vehicleDownPaymentMin) / 30) * 
            (company.vehicleRates[vehicleType].maxRate - company.vehicleRates[vehicleType].minRate);
            
        // Ограничиваем ставку минимальным и максимальным значением
        rate = Math.max(company.vehicleRates[vehicleType].minRate, 
                    Math.min(rate, company.vehicleRates[vehicleType].maxRate));
                    
        // Рассчитываем примерный ежемесячный платеж для этой компании
        const companyMonthlyRate = rate / 100 / 12;
        const loanAmount = price * (1 - downPaymentPercent / 100);
        const companyMonthlyPayment = loanAmount * (companyMonthlyRate * Math.pow(1 + companyMonthlyRate, term)) / (Math.pow(1 + companyMonthlyRate, term) - 1);
        
        // Форматированный платеж для отображения
        const formattedPayment = formatCurrency(companyMonthlyPayment);
        
        // Создаем карточку компании
        const companyCard = document.createElement('div');
        companyCard.className = 'col';
        companyCard.innerHTML = `
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-0 text-center py-3">
                    <img src="${company.logo}" alt="${company.name}" class="img-fluid" style="height: 40px; max-width: 80%;">
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary rounded-pill fs-6">${rate.toFixed(1)}%</span>
                        <div class="text-warning">
                            ${getStarRating(company.rating)}
                        </div>
                    </div>
                    <h5 class="text-center mb-3">${formattedPayment}</h5>
                    <p class="text-center text-muted mb-0">в месяц</p>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="index.php?page=application&type=vehicle&company=${encodeURIComponent(company.name)}&monthly=${encodeURIComponent(formattedPayment)}&vehicle_id=${vehicleId}" class="btn btn-outline-primary rounded-pill px-4">Оформить</a>
                </div>
            </div>
        `;
        
        companiesContainer.appendChild(companyCard);
    });
}

// Показать подходящие компании для лизинга недвижимости
function showMatchingRealEstateCompanies(price, downPaymentPercent, term, realEstateType, monthlyPayment) {
    const companiesContainer = document.querySelector('#realEstateCompanies .row');
    companiesContainer.innerHTML = '';
    
    // Получаем real_estate_id из URL, если он там есть
    const urlParams = new URLSearchParams(window.location.search);
    const realEstateId = urlParams.get('id') || 0;
    
    let matchingCompanies = leasingCompanies.filter(company => {
        // Проверяем, что компания предлагает лизинг недвижимости выбранного типа
        if (!company.realEstateRates || !company.realEstateRates[realEstateType]) {
            return false;
        }
        
        // Проверяем минимальный первоначальный взнос
        return company.realEstateDownPaymentMin <= downPaymentPercent;
    });
    
    // Сортируем по привлекательности предложения (по ставке)
    matchingCompanies.sort((a, b) => {
        return a.realEstateRates[realEstateType].minRate - b.realEstateRates[realEstateType].minRate;
    });
    
    if (matchingCompanies.length === 0) {
        companiesContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">К сожалению, не найдено подходящих предложений. Попробуйте изменить параметры расчета.</div></div>';
        return;
    }
    
    // Отображаем топ-3 компании
    matchingCompanies.slice(0, 3).forEach(company => {
        // Рассчитываем примерную ставку для этой компании
        let rate = company.realEstateRates[realEstateType].minRate + 
            ((downPaymentPercent - company.realEstateDownPaymentMin) / 40) * 
            (company.realEstateRates[realEstateType].maxRate - company.realEstateRates[realEstateType].minRate);
            
        // Ограничиваем ставку минимальным и максимальным значением
        rate = Math.max(company.realEstateRates[realEstateType].minRate, 
                    Math.min(rate, company.realEstateRates[realEstateType].maxRate));
                    
        // Рассчитываем примерный ежемесячный платеж для этой компании
        const companyMonthlyRate = rate / 100 / 12;
        const loanAmount = price * (1 - downPaymentPercent / 100);
        const companyMonthlyPayment = loanAmount * (companyMonthlyRate * Math.pow(1 + companyMonthlyRate, term)) / (Math.pow(1 + companyMonthlyRate, term) - 1);
        
        // Форматированный платеж для отображения
        const formattedPayment = formatCurrency(companyMonthlyPayment);
        
        // Создаем карточку компании
        const companyCard = document.createElement('div');
        companyCard.className = 'col';
        companyCard.innerHTML = `
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-0 text-center py-3">
                    <img src="${company.logo}" alt="${company.name}" class="img-fluid" style="height: 40px; max-width: 80%;">
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary rounded-pill fs-6">${rate.toFixed(1)}%</span>
                        <div class="text-warning">
                            ${getStarRating(company.rating)}
                        </div>
                    </div>
                    <h5 class="text-center mb-3">${formattedPayment}</h5>
                    <p class="text-center text-muted mb-0">в месяц</p>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="index.php?page=application&type=real_estate&company=${encodeURIComponent(company.name)}&monthly=${encodeURIComponent(formattedPayment)}&real_estate_id=${realEstateId}" class="btn btn-outline-primary rounded-pill px-4">Оформить</a>
                </div>
            </div>
        `;
        
        companiesContainer.appendChild(companyCard);
    });
}

// Вспомогательная функция для форматирования валюты
function formatCurrency(amount) {
    // Использует ru-RU локаль для форматирования, которая использует пробелы между разрядами
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
        useGrouping: true // Убедимся, что группировка цифр включена (пробелы между разрядами)
    }).format(amount);
}

// Вспомогательная функция для отображения рейтинга звездами
function getStarRating(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    
    if (halfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

// Инициализация форматирования полей ввода при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded - initializing calculator');
    
    // Получаем все поля ввода
    const vehiclePrice = document.getElementById('vehiclePrice');
    const vehiclePriceRange = document.getElementById('vehiclePriceRange');
    const vehicleDownPayment = document.getElementById('vehicleDownPayment');
    const vehicleDownPaymentRange = document.getElementById('vehicleDownPaymentRange');
    const realEstatePrice = document.getElementById('realEstatePrice');
    const realEstatePriceRange = document.getElementById('realEstatePriceRange');
    const realEstateDownPayment = document.getElementById('realEstateDownPayment');
    const realEstateDownPaymentRange = document.getElementById('realEstateDownPaymentRange');
    
    console.log('Поля инициализированы');
    
    // Инициализация форм калькулятора при загрузке
    if (vehiclePrice && vehiclePriceRange) {
        updateVehiclePrice();
    }
    if (vehicleDownPayment && vehicleDownPaymentRange) {
        updateVehicleDownPayment();
    }
    if (realEstatePrice && realEstatePriceRange) {
        updateRealEstatePrice();
    }
    if (realEstateDownPayment && realEstateDownPaymentRange) {
        updateRealEstateDownPayment();
    }
    
    // Кнопки "Оформить" теперь имеют прямые ссылки в HTML-разметке
    // Обработчик событий не требуется
    
    // Обработчики событий уже определены в HTML с помощью атрибутов oninput
});

console.log('Debug: Checking calculator.js loading');
