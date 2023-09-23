document.addEventListener('DOMContentLoaded', function() {
    const animatedEl = document.getElementById('animatedEl');
    const elsToModify = document.querySelectorAll('[data-add-class-after-intro]');

    elsToModify.forEach(el => {
        if(el.dataset.noIntro !== undefined)
            el.classList.add(...el.dataset.addClassAfterIntro.split(' '));
    });


    animatedEl.addEventListener("animationend", function() {
        elsToModify.forEach(function(el) {
            el.classList.add(...el.dataset.addClassAfterIntro.split(' '));
        });
    });
});
