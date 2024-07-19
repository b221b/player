<link rel="stylesheet" href="styles/panel.css">

<div class="top-panel">
    <div class="burger-menu-button" onclick="toggleSidebar()">☰</div>
    <div class="logo">Sportify</div>
    <!-- <div class="search-container">
            <button onclick="toggleSearch()">🔍</button>
            <input id="search-input" type="text" placeholder="Поиск...">
        </div> -->
    <button class="login-button" onclick="window.location.href = 'logreg.html'">Войти</button>
</div>

<div class="sidebar">
    <button onclick="window.location.href = 'index.html'"><img src="shit/house2.png">Главная</button>
    <button onclick="window.location.href = 'profile.html'"><img src="shit/user2.png">Профиль</button>
    <button onclick="window.location.href = 'playlists.html'"><img src="shit/playlist2.png">Плейлисты</button>
    <img src="shit/полоса4.png" alt="" width="100%">
    <button onclick="window.location.href = 'author.html'"><img src="shit/group.png">Авторы</button>
    <img src="shit/полоса4.png" alt="" width="100%">

    <h3>Ваша Библиотека</h3>

    <ul>
        <li><a href="playlists.html">Созданные плейлисты</a></li>
        <li><a href="playlist1.html">Понравившиеся песни</a></li>
        <li><a href="playlists.html">Альбомы</a></li>
        <li><a href="playlists.html">Исполнители</a></li>
        <li><a href="playlists.html">Подкасты</a></li>
    </ul>

    <div>
        <br><br><br>
        <br><br><br>
        <br><br><br>
    </div>

</div>

<script src="scripts/panel.js"></script>