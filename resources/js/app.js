import './bootstrap';
import '../sass/app.scss';
import Reveal from 'reveal.js';
import Markdown from 'reveal.js/plugin/markdown/markdown.esm.js';

import.meta.glob([
    '../images/**',
    '../fonts/**',
]);

if (document.querySelector('.reveal')) {
    const reveal = new Reveal({
        plugins: [Markdown],

        autoSlide: 15000,
        autoSlideStoppable: false,
        loop: true,
        preloadIframes: true, // data-src will be loaded when within view distance: 3 by default (or 2 for mobile)

        controls: false,
        autoAnimate: false,
        // progress: false,
    });

    // ! Workaround: I'd really like Reveal.js to have a callback for when an iframe is loaded, but don't have the time to make a PR yet.
    // Detour creating iframe elements and call an event (iframecreated) when they are loaded
    window._oldCreateElement = document.createElement;
    document.createElement = function () {
        const element = window._oldCreateElement.apply(this, arguments);

        if (element.tagName === 'IFRAME') {
            document.dispatchEvent(new CustomEvent('iframecreated', { detail: element }));
        }

        return element;
    };
    // ! End of Workaround

    reveal.initialize();

    window.RevealDeck = reveal;
}
