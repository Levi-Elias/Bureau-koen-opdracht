(function () {
    var track = document.querySelector('.marquee-track');
    if (!track) return;


    var originals = Array.from(track.children);
    if (originals.length === 0) return;

    originals.forEach(function (card) {
        track.appendChild(card.cloneNode(true));
    });


    var oneSetWidth = track.scrollWidth / 2;
    track.style.animationDuration = (oneSetWidth / 40) + 's';
}());
