const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Blue Swede",
        title: "Hooked on a Feeling",
        src: "https://www.example.com/music/hooked_on_a_feeling.mp3"
    },
    {
        artist: "Raspberries",
        title: "Go All the Way",
        src: "https://www.example.com/music/go_all_the_way.mp3"
    },
    {
        artist: "Norman Greenbaum",
        title: "Spirit in the Sky",
        src: "https://www.example.com/music/spirit_in_the_sky.mp3"
    },
    {
        artist: "David Bowie",
        title: "Moonage Daydream",
        src: "https://www.example.com/music/moonage_daydream.mp3"
    },
    {
        artist: "Elvin Bishop",
        title: "Fooled Around and Fell in Love",
        src: "https://www.example.com/music/fooled_around_and_fell_in_love.mp3"
    },
    {
        artist: "10cc",
        title: "I'm Not in Love",
        src: "https://www.example.com/music/im_not_in_love.mp3"
    },
    {
        artist: "The Jackson 5",
        title: "I Want You Back",
        src: "https://www.example.com/music/i_want_you_back.mp3"
    },
    {
        artist: "Redbone",
        title: "Come and Get Your Love",
        src: "https://www.example.com/music/come_and_get_your_love.mp3"
    },
    {
        artist: "The Runaways",
        title: "Cherry Bomb",
        src: "https://www.example.com/music/cherry_bomb.mp3"
    },
    {
        artist: "Sweet",
        title: "Fox on the Run",
        src: "https://www.example.com/music/fox_on_the_run.mp3"
    },
    {
        artist: "The Monkees",
        title: "I'm a Believer",
        src: "https://www.example.com/music/im_a_believer.mp3"
    },
    {
        artist: "The Flamingos",
        title: "I Only Have Eyes for You",
        src: "https://www.example.com/music/i_only_have_eyes_for_you.mp3"
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
