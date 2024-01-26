const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Bearded Legend",
        title: "Rocket",
        src: "https://www.example.com/music/bearded_legend-rocket.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Apollo",
        src: "https://www.example.com/music/bearded_legend-apollo.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Orbit",
        src: "https://www.example.com/music/bearded_legend-orbit.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Galaxy",
        src: "https://www.example.com/music/bearded_legend-galaxy.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Starlight",
        src: "https://www.example.com/music/bearded_legend-starlight.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Nebula",
        src: "https://www.example.com/music/bearded_legend-nebula.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Comet",
        src: "https://www.example.com/music/bearded_legend-comet.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Supernova",
        src: "https://www.example.com/music/bearded_legend-supernova.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Black Hole",
        src: "https://www.example.com/music/bearded_legend-black_hole.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Pulsar",
        src: "https://www.example.com/music/bearded_legend-pulsar.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Quasar",
        src: "https://www.example.com/music/bearded_legend-quasar.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Starburst",
        src: "https://www.example.com/music/bearded_legend-starburst.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Cosmic",
        src: "https://www.example.com/music/bearded_legend-cosmic.mp3"
    },
    {
        artist: "Bearded Legend",
        title: "Astral",
        src: "https://www.example.com/music/bearded_legend-astral.mp3"
    }
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
