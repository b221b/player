const imageSources = [
    "shit/midPic/midPic.jpg",
    "shit/midPic/midPic2.jpg",
    "shit/midPic/midPic3.jpg",
    "shit/midPic/midPic4.jpg",
    "shit/midPic/midPic5.jpg",
    "shit/midPic/midPic6.jpg",
    "shit/midPic/midPic7.jpg",
    "shit/midPic/midPic8.jpg",
    "shit/midPic/midPic9.jpg",
    "shit/midPic/midPic10.jpg",
    "shit/midPic/midPic11.jpg",
    "shit/midPic/midPic12.jpg",
    "shit/midPic/midPic13.jpg",
    "shit/midPic/midPic14.jpg",
    "shit/midPic/midPic15.jpg",
    "shit/midPic/midPic16.jpg",
    "shit/midPic/midPic17.jpg",
    "shit/midPic/midPic18.jpg",
    "shit/midPic/midPic19.jpg",
    "shit/midPic/midPic20.jpg",
    "shit/midPic/midPic21.jpg",
    "shit/midPic/midPic22.jpg",
    "shit/midPic/midPic24.jpg",
    "shit/midPic/midPic25.jpg"
];

const randomIndex = Math.floor(Math.random() * imageSources.length);
const randomImageSource = imageSources[randomIndex];

document.getElementById("randomImage").src = randomImageSource;

function updatePlayPauseButton() {
    var playButton = document.getElementById("togglePlayButton");
    if (tracks[currentTrackIndex].paused) {
        playButton.innerHTML = '<i class="fas fa-play"></i>';
    } else {
        playButton.innerHTML = '<i class="fas fa-pause"></i>';
    }
}

window.addEventListener('load', function () {
    // Check if any audio is playing and stop the spinning animation if it is
    if (tracks.some(track => !track.paused)) {
        document.querySelector('.cover-image').style.animationPlayState = 'running';
    } else {
        document.querySelector('.cover-image').style.animationPlayState = 'paused';
    }
});

function togglePlay() {
    if (tracks[currentTrackIndex].paused) {
        tracks[currentTrackIndex].currentTime = currentAudioTime;
        tracks[currentTrackIndex].play();
        document.querySelector('.cover-image').style.animationPlayState = 'running';
    } else {
        // If the track is playing, pause and save the current time
        currentAudioTime = tracks[currentTrackIndex].currentTime;
        tracks[currentTrackIndex].pause();
        document.querySelector('.cover-image').style.animationPlayState = 'paused';
    }
    // Также переключим иконку воспроизведения/паузы
    updatePlayPauseButton();
}
