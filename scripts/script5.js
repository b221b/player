
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}
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

var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));

function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    var minute = Math.floor(seconds / 60);
    var secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');
    var currentTrack = tracks[currentTrackIndex];

    if (currentTrack.duration) {
        currentTimeDisplay.textContent = formatTime(currentTrack.currentTime);
        durationDisplay.textContent = formatTime(currentTrack.duration);
    }
}

/* Теперь нужно обеспечить, чтобы меню скрывалось при нажатии на элемент меню. */
var sidebarLinks = document.querySelectorAll('.sidebar button');
sidebarLinks.forEach(function (link) {
    link.addEventListener('click', function () {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('active-menu');
    });
});
// Добавьте необходимую логику обработчиков событий
function toggleSearch() {
    var searchInput = document.getElementById('search-input');
    // ... Остальная логика toggleSearch
}

function muteUnmute() {
    // ... Остальная логика muteUnmute
}

var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале

// Затем используйте объявленные переменные в функциях

var progressBar = document.querySelector('.progress-bar');

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        playTrack(currentTrackIndex);
    } else {
        tracks[currentTrackIndex].pause();
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}

function prevTrack() {
    // Останавливаем текущий трек и сбрасываем время
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Отнимаем 1 от индекса, чтобы вернуться к предыдущему треку
    // Если мы на первом треке, переходим к последнему
    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    // Воспроизводим предыдущий трек
    playTrack(currentTrackIndex);
}

function nextTrack() {
    // Останавливаем текущий трек
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    // Увеличиваем индекс на 1, чтобы перейти к следующему треку
    // Используем модульное сложение, чтобы вернуться к первому треку после последнего
    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    // Воспроизводим следующий трек
    playTrack(currentTrackIndex);
}

// Функция, которая меняет трек 
function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

// Обновите функцию обработчика событий progress bar для настройки времени воспроизведения
document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', function () {
    var progressBar = document.querySelector('.progress-bar');
    var value = (100 / audioPlayer.duration) * audioPlayer.currentTime;
    progressBar.value = value;
});

var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков

function playTrack(index) {
    // остановить все треки и сбросить текущее время
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    // Обновляем текущий индекс трека
    currentTrackIndex = index;

    // Воспроизводим выбранный трек
    tracks[currentTrackIndex].play();

    // Обновляем информацию о треке и прогресс-бар
    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);

    tracks[currentTrackIndex].onloadedmetadata = function () {
        // Обновите отображение продолжительности трека при загрузке метаданных
        updateTimeDisplay();
    };

}

// Обновляем информацию о текущем треке
function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    // Пример получения информации о треке по его индексу
    // Эти данные могут быть загружены из файла плейлиста или настроены вручную
    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;

        // Обновляем отображаемое время
        updateTimeDisplay();
    });
}

// Добавляем события на progressBar
progressBar.addEventListener('input', function (e) {
    var track = tracks[currentTrackIndex];
    if (track.duration > 0) {
        var value = e.target.value;
        track.currentTime = (track.duration * value) / 100;
    }
});

// Функция для регулировки громкости
function setVolume(volume) {
    tracks.forEach(function (track) {
        track.volume = volume;
    });
}

// Обработчик событий для ползунка громкости, который будет вызывать функцию setVolume
document.getElementById('volume-slider').addEventListener('input', function (e) {
    setVolume(e.target.value);
});

function toggleMute() {
    var isMuted = tracks[currentTrackIndex].muted;
    tracks[currentTrackIndex].muted = !isMuted;
    // Обновите иконку звука в зависимости от состояния mute
    var muteButton = document.querySelector('.center button');
    muteButton.textContent = isMuted ? '🔊' : '🔇';
}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.5; // 50% от максимального уровня
    });
}