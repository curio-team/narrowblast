require('./bootstrap');

import Reveal from 'reveal.js';
import Markdown from 'reveal.js/plugin/markdown/markdown.esm.js';

if (document.querySelector('.reveal')) {
    let deck = new Reveal({
        plugins: [Markdown],

        autoSlide: 2000,
        autoSlideStoppable: false,
        loop: true,
        preloadIframes: true, // data-src will be loaded when within view distance: 3 by default (or 2 for mobile)

        controls: false,
        // progress: false,
    });

    deck.initialize();
}
