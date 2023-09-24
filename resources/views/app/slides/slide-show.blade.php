<x-common-layout>
    <div class="reveal">
        <div class="slides" id="slideContainer">
            {{-- @foreach ($screenSlides as $screenSlide)
                <section data-background-iframe="{{ $screenSlide->slide->getKnownPath() }}">
                </section>
            @endforeach --}}
            <section role="status" data-background-gradient="linear-gradient(to bottom right, #2D4747, #000000)">
                <div class="grid place-items-center w-full h-full gap-2">
                    <svg aria-hidden="true" class="inline w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-gray-600 dark:fill-gray-300" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="text-sm">Loading...</span>
                </div>
            </section>
        </div>
    </div>

    <script>
        const tickUrl = `{{ route('slides.slideShowTick', $screen) }}`;
        const slideContainerEl = document.getElementById('slideContainer');
        let hasLoaded = false;

        function clearSlides() {
            slideContainerEl.innerHTML = '';
        }

        function addSlide(publicPath) {
            slideContainerEl.innerHTML += `<section data-background-iframe="${publicPath}"></section>`;
        }

        function findSlideByPublicPath(publicPath) {
            return slideContainerEl.querySelector(`section[data-background-iframe="${publicPath}"]`);
        }

        function updateSlideChanges(publicPaths) {
            const slideEls = slideContainerEl.querySelectorAll('section[data-background-iframe]');
            let madeChanges = false;

            for (const slideEl of slideEls) {
                const publicPath = slideEl.getAttribute('data-background-iframe');

                if (!publicPaths.includes(publicPath)) {
                    slideEl.remove();
                    madeChanges = true;
                }
            }

            for (const publicPath of publicPaths) {
                if (!findSlideByPublicPath(publicPath)) {
                    addSlide(publicPath);
                    madeChanges = true;
                }
            }

            if (madeChanges) {
                window.RevealDeck.sync();

                // For some reason, sync doesn't always work the first time
                setTimeout(() => {
                    window.RevealDeck.sync();
                }, 250);
            }
        }

        function doTick(secretTickKey) {
            // POST and update slides
            fetch(tickUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Secret-Tick-Key': secretTickKey,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    'screen_id': {{ $screen->id }},
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);

                if(data.error && data.error.indexOf('secret') > -1) {
                    localStorage.removeItem('secretTickKey');
                    window.location.reload();
                    return;

                }
                if(!hasLoaded) {
                    hasLoaded = true;
                    clearSlides();
                }

                updateSlideChanges(data.public_paths);
            })
            .catch(error => {
                console.error(error);
            });
        }

        function passwordModal(message, callback) {
            const modal = document.createElement('div');
            modal.classList.add('fixed', 'top-0', 'left-0', 'w-screen', 'h-screen', 'flex', 'justify-center', 'items-center', 'bg-black', 'bg-opacity-50', 'z-50');

            const modalContent = document.createElement('div');
            modalContent.classList.add('bg-white', 'p-4', 'rounded', 'shadow-lg', 'text-center', 'flex', 'flex-col', 'gap-4');

            const modalMessage = document.createElement('span');
            modalMessage.innerText = message;

            const modalInput = document.createElement('input');
            modalInput.setAttribute('type', 'password');
            modalInput.classList.add('border', 'border-gray-300', 'rounded', 'p-2');

            const modalSubmit = document.createElement('button');
            modalSubmit.classList.add('bg-blue-500', 'text-white', 'rounded', 'p-2', 'hover:bg-blue-600', 'transition-colors', 'duration-200', 'ease-in-out');
            modalSubmit.innerText = 'Submit';

            modalSubmit.addEventListener('click', function() {
                modal.remove();
                localStorage.setItem('secretTickKey', modalInput.value);
                callback(modalInput.value);
            });

            modalContent.appendChild(modalMessage);
            modalContent.appendChild(modalInput);
            modalContent.appendChild(modalSubmit);
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
        }

        let secretTickKey = localStorage.getItem('secretTickKey');

        function setup(secretTickKey) {
            doTick(secretTickKey);
            setInterval(function() {
                doTick(secretTickKey);
            }, {{ config('app.slide_show_tick_interval_in_seconds') * 1000 }});
        }

        if (!secretTickKey) {
            secretTickKey = passwordModal('Please enter the secret tick key (.env: )', setup);
        } else {
            setup(secretTickKey);
        }
    </script>
</x-common-layout>
