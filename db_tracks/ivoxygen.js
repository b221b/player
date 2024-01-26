const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "IVOXYGEN",
        title: "disaster",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "hate poem",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "save yourself",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "living in the sea is cool",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "Ghost Town Symphony",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "Kid is afraid to be alone",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "Late Nights",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "silence so loud",
        src: "URL_TO_AUDIO_FILE"
    },
    {
        artist: "IVOXYGEN",
        title: "Lost in the Moment",
        src: "inside comfort"
    },
];

let currentTrack = null;

tracks.forEach(track => {
    const audio = document.createElement('audio');
    audio.src = track.src;
    audio.controls = true;
    audio.volume = 0.1;
    audio.classList.add('track');

    const title = document.createElement('p');
    title.textContent = `${track.artist} - ${track.title}`;

    musicContainer.appendChild(title);
    musicContainer.appendChild(audio);

    audio.addEventListener('play', () => {
        if (currentTrack && currentTrack !== audio) {
            currentTrack.pause();
            currentTrack.classList.remove('playing');
        }
        currentTrack = audio;
        currentTrack.classList.add('playing');
    });

    audio.addEventListener('ended', () => {
        currentTrack.classList.remove('playing');
        currentTrack = null;
    });
});
