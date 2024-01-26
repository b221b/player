const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Freddie Dredd",
        title: "Blow",
        src: "tracks/2.freddie_dredd/Y2mate.mx - Freddie Dredd - Blow (Official Audio) (128 kbps).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "Delete",
        src: "tracks/all/freddie_dredd (3).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "GTG",
        src: "tracks/all/freddie_dredd (4).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "KILLIN' ON DEMAND",
        src: "tracks/all/freddie_dredd (5).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "LIMBO",
        src: "tracks/all/freddie_dredd (6).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "Stunna",
        src: "tracks/2.freddie_dredd/Y2mate.mx - Freddie Dredd - Stunna (Official Audio) (128 kbps).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "WTH",
        src: "tracks/2.freddie_dredd/Y2mate.mx - Freddie Dredd - WTH (Official Audio) (128 kbps).mp3"
    },
    {
        artist: "Freddie Dredd",
        title: "Opaul",
        src: "tracks/2.freddie_dredd/Y2mate.mx - Opaul (128 kbps).mp3"
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
