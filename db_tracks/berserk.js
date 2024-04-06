const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Berserk",
        title: "Gutz",
        src: "tracks/миксы/berserk1.MP3"
    },
    {
        artist: "Redzed",
        title: "mix",
        src: "tracks/миксы/redzed.MP3"
    },
    {
        artist: "Suicideboys",
        title: "mix",
        src: "tracks/миксы/suicideboys.MP3"
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
