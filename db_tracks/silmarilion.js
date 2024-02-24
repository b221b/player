const musicContainer = document.getElementById('music-container');

var tracks = [
    {
        artist: "Сильмариллион",
        title: "1. Айнулиндалэ",
        src: "tracks/Silmarillion/Y2mate.mx - 1. Сильмариллион _ _Айнулиндалэ_ _ Аудиокнига _ The Silmarillion _ Айнулиндалэ (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "2. Валаквента",
        src: "tracks/Silmarillion/Y2mate.mx - 2. Сильмариллион _ _Валаквента_ _ Аудиокнига _ The Silmarillion _ Валаквента (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "3. Глава 1 | О Начале дней",
        src: "tracks/Silmarillion/Y2mate.mx - 3. Сильмариллион _ Глава 1 _ Аудиокнига _ The Silmarillion _ О Начале дней (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "4. Глава 2 | Об Аулэ и Йаванне",
        src: "tracks/Silmarillion/Y2mate.mx - 4. Сильмариллион _ Глава 2 _ Аудиокнига _ The Silmarillion _ Об Аулэ и Йаванне (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "5. Глава 3 | О приходе эльфов и пленении Мелькора",
        src: "tracks/Silmarillion/Y2mate.mx - 5. Сильмариллион _ Глава 3 _ Аудиокнига _ The Silmarillion _ О приходе эльфов и пленении Мелькора (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "6. Глава 4 | О Тинголе и Мелиан",
        src: "tracks/Silmarillion/Y2mate.mx - 6. Сильмариллион _ Глава 4 _ Аудиокнига _ The Silmarillion _ О Тинголе и Мелиан (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "7. Глава 5 | Об Эльдамаре и владыках Эльдалиэ",
        src: "tracks/Silmarillion/Y2mate.mx - 7. Сильмариллион _ Глава 5 _ Аудиокнига _ The Silmarillion _ Об Эльдамаре и владыках Эльдалиэ (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "8. Глава 6 | О Феаноре и освобождении Мелькора",
        src: "tracks/Silmarillion/Y2mate.mx - 8. Сильмариллион _ Глава 6 _ Аудиокнига _ The Silmarillion _ О Феаноре и освобождении Мелькора (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "9. Глава 7 | О сильмарилях и смуте среди нолдор",
        src: "tracks/Silmarillion/Y2mate.mx - 9. Сильмариллион _ Глава 7 _ Аудиокнига _ The Silmarillion _ О сильмарилях и смуте среди нолдор (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "10. Глава 8 | О том, как на Валинор пала тьма",
        src: "tracks/Silmarillion/Y2mate.mx - 10. Сильмариллион _ Глава 8 _ Аудиокнига _ The Silmarillion _ О том, как на Валинор пала тьма (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "11. Глава 9 | О бегстве нолдор",
        src: "tracks/Silmarillion/Y2mate.mx - 11. Сильмариллион _ Глава 9 _ Аудиокнига _ The Silmarillion _ О бегстве нолдор (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "12. Глава 10 | О синдар",
        src: "tracks/Silmarillion/Y2mate.mx - 12. Сильмариллион _ Глава 10 _ Аудиокнига _ The Silmarillion _ О синдар (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "13. Глава 11 | О Солнце, Луне и о сокрытии Валинора",
        src: "tracks/Silmarillion/Y2mate.mx - 13. Сильмариллион _ Глава 11 _ Аудиокнига _ The Silmarillion _ О Солнце, Луне и о сокрытии Валинора (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "14. Глава 12 | О людях",
        src: "tracks/Silmarillion/Y2mate.mx - 14. Сильмариллион _ Глава 12 _ Аудиокнига _ The Silmarillion _ О людях (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "15. Глава 13 | О возвращении нолдор",
        src: "tracks/Silmarillion/Y2mate.mx - 15. Сильмариллион _ Глава 13 _ Аудиокнига _ The Silmarillion _ О возвращении нолдор (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "16. Глава 14 | О Белерианде и его королевствах",
        src: "tracks/Silmarillion/Y2mate.mx - 16. Сильмариллион _ Глава 14 _ Аудиокнига _ The Silmarillion _ О Белерианде и его королевствах (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "17. Глава 15 | О нолдор в Белерианде",
        src: "tracks/Silmarillion/Y2mate.mx - 17. Сильмариллион _ Глава 15 _ Аудиокнига _ The Silmarillion _ О нолдор в Белерианде (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "18. Глава 16 | О Маэглине",
        src: "tracks/Silmarillion/Y2mate.mx - 18. Сильмариллион _ Глава 16 _ Аудиокнига _ The Silmarillion _ О Маэглине (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "19. Глава 17 | О приходе людей на Запад",
        src: "tracks/Silmarillion/Y2mate.mx - 19. Сильмариллион _ Глава 17 _ Аудиокнига _ The Silmarillion _ О приходе людей на Запад (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "20. Глава 18 | О гибели Белерианда и смерти Финголфина",
        src: "tracks/Silmarillion/Y2mate.mx - 20. Сильмариллион _ Глава 18 _ Аудиокнига _ О гибели Белерианда и смерти Финголфина (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "21. Глава 19 | О Берене и Лутиэн",
        src: "tracks/Silmarillion/Y2mate.mx - 21. Сильмариллион _ Глава 19 _ Аудиокнига _ The Silmarillion _ О Берене и Лутиэн (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "22. Глава 20 | О пятой битве: Нирнаэт Арноэдиад",
        src: "tracks/Silmarillion/Y2mate.mx - 22. Сильмариллион _ Глава 20 _ Аудиокнига _ The Silmarillion _ О пятой битве_ Нирнаэт Арноэдиад (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "23. Глава 21 | О Турине Турамбаре",
        src: "tracks/Silmarillion/Y2mate.mx - 23. Сильмариллион _ Глава 21 _ Аудиокнига _ The Silmarillion _ О Турине Турамбаре (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "24. Глава 22 | О гибели Дориата",
        src: "tracks/Silmarillion/Y2mate.mx - 24. Сильмариллион _ Глава 22 _ Аудиокнига _ The Silmarillion _ О гибели Дориата (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "25. Глава 23 | О Туоре и падении Гондолина",
        src: "tracks/Silmarillion/Y2mate.mx - 25. Сильмариллион _ Глава 23 _ Аудиокнига _ The Silmarillion _ О Туоре и падении Гондолина (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "26. Глава 24 | О плавании Эарендиля и Войне Гнева",
        src: "tracks/Silmarillion/Y2mate.mx - 26. Сильмариллион _ Глава 24 _ Аудиокнига _ The Silmarillion _ О плавании Эарендиля и Войне Гнева (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "27. Акаллабет | The Silmarillion",
        src: "tracks/Silmarillion/Y2mate.mx - 27. Сильмариллион _ _Акаллабет_ _ Аудиокнига _ The Silmarillion (128 kbps).mp3"
    },
    {
        artist: "Сильмариллион",
        title: "28. О Кольцах Власти и Третьей Эпохе | The Silmarillion",
        src: "tracks/Silmarillion/Y2mate.mx - 28. Сильмариллион _ _О Кольцах Власти и Третьей Эпохе_ _ Аудиокнига _ The Silmarillion (128 kbps).mp3"
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
