// Обработчик клика для бургерного меню
document.querySelector('.burger-menu-button').addEventListener('click', toggleSidebar);

document.addEventListener('DOMContentLoaded', function () {
    var burgerButton = document.querySelector('.burger-menu-button');
    var sidebar = document.querySelector('.sidebar');

    // Переключение видимости боковой панели
    burgerButton.addEventListener('click', function () {
        sidebar.classList.toggle('active-menu');
    });

    // Скрытие боковой панели при выборе пункта меню
    var sidebarLinks = document.querySelectorAll('.sidebar button');
    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            sidebar.classList.remove('active-menu');
        });
    });
});

function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active-menu');
    }
}

function toggleSearch() {
    var searchInput = document.getElementById('search-input');
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
