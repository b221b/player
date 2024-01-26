const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Looking Glass",
        title: "Brandy (You're a Fine Girl)",
        src: "https://www.example.com/music/brandy_youre_a_fine_girl.mp3"
    },
    {
        artist: "Wham!",
        title: "Careless Whisper",
        src: "https://www.example.com/music/careless_whisper.mp3"
    },
    {
        artist: "The Jets",
        title: "You Got It All",
        src: "https://www.example.com/music/you_got_it_all.mp3"
    },
    {
        artist: "Fleetwood Mac",
        title: "The Chain",
        src: "https://www.example.com/music/the_chain.mp3"
    },
    {
        artist: "Sweet",
        title: "Fox on the Run",
        src: "https://www.example.com/music/fox_on_the_run.mp3"
    },
    {
        artist: "Aliotta Haynes Jeremiah",
        title: "Lake Shore Drive",
        src: "https://www.example.com/music/lake_shore_drive.mp3"
    },
    {
        artist: "Sam Cooke",
        title: "Bring It on Home to Me",
        src: "https://www.example.com/music/bring_it_on_home_to_me.mp3"
    },
    {
        artist: "Glen Campbell",
        title: "Southern Nights",
        src: "https://www.example.com/music/southern_nights.mp3"
    },
    {
        artist: "George Harrison",
        title: "My Sweet Lord",
        src: "https://www.example.com/music/my_sweet_lord.mp3"
    },
    {
        artist: "Looking Glass",
        title: "Brandy (You're a Fine Girl) (Reprise)",
        src: "https://www.example.com/music/brandy_youre_a_fine_girl_reprise.mp3"
    },
    {
        artist: "Jay and the Americans",
        title: "Come a Little Bit Closer",
        src: "https://www.example.com/music/come_a_little_bit_closer.mp3"
    },
    {
        artist: "Silver",
        title: "Wham Bam Shang-A-Lang",
        src: "https://www.example.com/music/wham_bam_shang_a_lang.mp3"
    },
    {
        artist: "Electric Light Orchestra",
        title: "Mr. Blue Sky",
        src: "https://www.example.com/music/mr_blue_sky.mp3"
    },
    {
        artist: "Father John Misty",
        title: "The Night Won't Scare You",
        src: "https://www.example.com/music/the_night_wont_scare_you.mp3"
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
