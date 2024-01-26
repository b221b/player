const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Bones",
        title: "HDMI",
        src: "https://www.example.com/music/bones-sleepyhead.mp3"
    },
    {
        artist: "Bones",
        title: "LooseScrew",
        src: "https://music.yandex.ru/album/11203512/track/67925548"
    },
    {
        artist: "Bones",
        title: "Timberlake",
        src: "https://www.example.com/music/bones-teenage_goths.mp3"
    },
    {
        artist: "Bones",
        title: "Sodium",
        src: "https://www.example.com/music/bones-soda.mp3"
    },
    {
        artist: "Bones",
        title: "Timbaland",
        src: "https://www.example.com/music/bones-underground.mp3"
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
