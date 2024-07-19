const audioPlayer = document.querySelector('.audio-player');
const currentTimeDisplay = document.querySelector('.current-time');
const durationDisplay = document.querySelector('.duration-time');
let currentAudioTime = 0;
var currentTrackIndex = 0;
var isPlaying = false;  // Новая переменная для хранения состояния воспроизведения
var tracks = Array.from(document.querySelectorAll('.audio-player'));
var audioPlayers = document.querySelectorAll('.audio-player'); // должно быть объявлено в начале
var currentTrackNumber = 1;
var totalTracks = 9; // Укажите общее количество треков


var progressBar = document.querySelector('.progress-bar');
function resetTracks() {
    tracks.forEach(function (track, index) {
        if (index !== currentTrackIndex) {
            track.pause();
            track.currentTime = 0;
        }
    });
}

function formatTime(seconds) {
    const minute = Math.floor(seconds / 60);
    const secondLeft = Math.floor(seconds - minute * 60);
    return `${minute}:${(secondLeft < 10 ? '0' : '') + secondLeft}`;
}

function updateTimeDisplay() {
    var currentTimeDisplay = document.querySelector('.current-time');
    var durationDisplay = document.querySelector('.duration-time');

    if (tracks[currentTrackIndex].duration) {
        currentTimeDisplay.textContent = formatTime(currentAudioTime);
        durationDisplay.textContent = formatTime(tracks[currentTrackIndex].duration);
    }
}

progressBar.addEventListener('input', (e) => {
    const audioPlayer = document.querySelector('.audio-player');
    const duration = audioPlayer.duration;
    if (duration > 0) {
        const value = e.target.value;
        const currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

audioPlayer.addEventListener('timeupdate', () => {
    const progressBar = document.querySelector('.progress-bar');
    const duration = audioPlayer.duration;
    const currentTime = audioPlayer.currentTime;
    if (duration > 0) {
        const value = (100 / duration) * currentTime;
        progressBar.value = value;
    }
    currentAudioTime = currentTime; // Update currentAudioTime here
    updateTimeDisplay();
});

function playTrack(index) {
    tracks.forEach(function (track) {
        track.pause();
        track.currentTime = 0;
    });

    currentAudioTime = tracks[index].currentTime; // Update currentAudioTime here

    currentTrackIndex = index;

    tracks[currentTrackIndex].play();

    // Update the current time of the audio player to the current time of the new track
    tracks[currentTrackIndex].currentTime = currentAudioTime;

    updateTrackInfo(currentTrackIndex);
    updateProgressBar(currentTrackIndex);
    updateTimeDisplay();

    tracks[currentTrackIndex].onloadedmetadata = function () {
        updateTimeDisplay();
    };
}

audioPlayer.addEventListener('ended', function () {
    currentAudioTime = 0; // Reset currentAudioTime when the track ends
    nextTrack();
});

tracks.forEach(function (track) {
    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;
    });
});

updatePlayPauseButton();

function prevTrack() {
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    currentTrackIndex = (currentTrackIndex - 1 + tracks.length) % tracks.length;

    playTrack(currentTrackIndex);

    updatePlayPauseButton();
}

function nextTrack() {
    tracks[currentTrackIndex].pause();
    tracks[currentTrackIndex].currentTime = 0;

    currentTrackIndex = (currentTrackIndex + 1) % tracks.length;

    playTrack(currentTrackIndex);

    updatePlayPauseButton();
}

function changeTrack(trackPath) {
    audioPlayer.src = trackPath;
    togglePlay(); // Начинаем воспроизведение
}

document.querySelector('.progress-bar').addEventListener('input', function (e) {
    var duration = audioPlayer.duration;
    if (duration > 0) {
        var value = e.target.value;
        var currentTime = (duration * value) / 100;
        audioPlayer.currentTime = currentTime;
    }
});

function updateTrackInfo(index) {
    var trackInfoTitle = document.querySelector('.track-current-title');
    var trackInfoArtist = document.querySelector('.track-current-artist');
    var trackInfoImage = document.querySelector('.track-current-img');

    var title = 'Трек ' + (index + 1); // Получаем название трека
    var artist = 'Исполнитель ' + (index + 1); // Получаем исполнителя

    trackInfoTitle.textContent = title;
    trackInfoArtist.textContent = artist;
    trackInfoImage.src = "path-to-image-for-track.jpg"; // Установить путь к изображению трека
    updateTimeDisplay();
}

// Обновляем прогресс-бар при воспроизведении трека
function updateProgressBar(index) {
    var track = tracks[currentTrackIndex];

    track.addEventListener('loadedmetadata', function () {
        progressBar.max = track.duration;
    });

    track.addEventListener('timeupdate', function () {
        var value = (100 / track.duration) * track.currentTime;
        progressBar.value = value;
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

}

// Объявление setVolumeToHalf
function setVolumeToHalf() {
    var audioPlayers = document.querySelectorAll('.audio-player');
    audioPlayers.forEach(function (audio) {
        audio.volume = 0.1; // 50% от максимального уровня
    });
}