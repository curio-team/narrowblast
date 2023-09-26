<script>
    let latestCsrfToken = '{{ csrf_token() }}';

    (function(){
        const inviteRequestSetInteractionDataEndpoint = `{{ route('slides.inviteRequestSetInteractionData') }}`;
        const inviteRedistributeRequestEndpoint = `{{ route('slides.inviteRedistributeRequest') }}`;
        const inviteCodeRequestEndpoint = `{{ route('slides.inviteCodeRequest') }}`;
        const inviteCodeUpdateEndpoint = `{{ route('slides.inviteCodeUpdate') }}`;
        const slideContainerEl = document.getElementById('slideContainer');
        const routesWithJavascript = new Map();
        const routesData = new Map();
        const routesIframes = new Map();
        let inviteCode = null;

        function setCurrentInviteCode(newInviteCode) {
            inviteCode = newInviteCode;
        }
        window.setCurrentInviteCode = setCurrentInviteCode;

        // Apply maximum sandbox by default and check (once the src has been applied) if the iframe should be sandboxed
        document.addEventListener('iframecreated', function (event) {
            const iframe = event.detail;
            iframe.setAttribute('sandbox', '');

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'src' && iframe.dataset.narrowBlastInit !== 'yes') {
                        const publicPath = iframe.getAttribute('src');
                        const hasJavascriptPowerup = routesWithJavascript.get(publicPath);

                        if(hasJavascriptPowerup) {
                            iframe.setAttribute('sandbox', 'allow-scripts');
                            console.log(`🚀 NarrowBlast: iframe (${publicPath}) allowing scripts`, iframe);
                        } else {
                            iframe.setAttribute('sandbox', '');
                        }

                        iframe.dataset.narrowBlastInit = 'yes';
                        iframe.src = publicPath;

                        routesIframes.set(publicPath, iframe);
                    }
                });
            }).observe(iframe, {
                attributes: true
            });
        });

        function sendPostMessageToRelevantIframe(publicPath, type, data) {
            const iframe = routesIframes.get(publicPath);

            if(iframe) {
                iframe.contentWindow.postMessage({
                    type: type,
                    data: data,
                }, '*');
            } else {
                console.log(`Could not find iframe for public path ${publicPath}`);
            }

            window.dispatchEvent(new CustomEvent('narrowblastinviteupdate', {
                detail: {
                    publicPath: publicPath,
                    type: type,
                    data: data,
                }
            }));
        }

        window.addEventListener('message', function(event) {
            if (!window.enable_invite_system) return;

            if (event.data.type === 'getInviteCode') {
                // ! This is unreliable if we ever not have the id be the slide path
                const slideId = window._narrowBlastPreviewSlideId ? window._narrowBlastPreviewSlideId : event.data.data.split('/').pop().split('.').shift();
                requestInviteCode(slideId, function (publicPath, inviteCode, inviteCodeQr) {
                    sendPostMessageToRelevantIframe(publicPath, 'onInviteCode', {
                        inviteCode: inviteCode,
                        inviteCodeQr: inviteCodeQr,
                    });
                });
            } else if (event.data.type === 'requestRedistributePrizePool') {
                requestRedistributePrizePool(event.data.data, function (publicPath, wasSuccesful) {
                    sendPostMessageToRelevantIframe(publicPath, 'onRedistributePrizePool', wasSuccesful);
                });
            } else if (event.data.type === 'requestSetInteractionData') {
                requestSetInteractionData(event.data.data, function (publicPath, wasSuccesful) {
                    sendPostMessageToRelevantIframe(publicPath, 'onSetInteractionData', wasSuccesful);
                });
            } else {
                console.error('Ignoring unknown message type', event.data.type);
            }
        });

        function getSlideContainer() {
            return slideContainerEl;
        }
        window.getSlideContainer = getSlideContainer;

        function clearSlides() {
            slideContainerEl.innerHTML = '';
        }
        window.clearSlides = clearSlides;

        function addSlide(slide) {
            slideContainerEl.innerHTML += `<section data-background-iframe="${slide.publicPath}"></section>`;

            if (slide.data.has_javascript_powerup) {
                setRouteJavascriptPowerup(slide.publicPath, true);
            }
            if (slide.data.invite_system_shop_item_user_id != null) {
                setRouteJavascriptPowerup(slide.publicPath, true);
                window.enable_invite_system = true;
            }
        }
        window.addSlide = addSlide;

        function findSlideByPublicPath(publicPath) {
            return slideContainerEl.querySelector(`section[data-background-iframe="${publicPath}"]`);
        }
        window.findSlideByPublicPath = findSlideByPublicPath;

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
        window.passwordModal = passwordModal;

        /**
         * Enables or disables javascript for the given route.
         * You should refresh the iframe if it's already fully loaded.
         */
        function setRouteJavascriptPowerup(route, hasJavascriptPowerup) {
            routesWithJavascript.set(route, hasJavascriptPowerup);
        }
        window.setRouteJavascriptPowerup = setRouteJavascriptPowerup;

        function errorAndReset(error) {
            console.error(error);
            if(!window.RevealDeck.isAutoSliding()) {
                window.RevealDeck.toggleAutoSlide();
            }
            inviteCode = null;
        }

        /**
         * Requests an invite code from the server.
         * If this is called without providing the secret tick key, then a preview invite
         * system is generated.
         */
        function requestInviteCode(slideId, callback) {
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;
            const screenId = {{ isset($screen) ? $screen->id : 'null' }};

            fetch(inviteCodeRequestEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': latestCsrfToken,
                    ...(
                        secretTickKey != null
                        ? { 'X-Secret-Tick-Key': secretTickKey }
                        : {}
                    )
                },
                body: JSON.stringify({
                    'screen_id': screenId,
                    'slide_id': slideId,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error || data.exception) {
                    errorAndReset(data.error);
                    return;
                }
                console.log(data);

                setCurrentInviteCode(data.inviteCode);
                callback(data.publicPath, data.inviteCode, data.inviteCodeQr);
                latestCsrfToken = data.csrfToken;
            })
            .catch(error => {
                errorAndReset(error);
            });
        }
        window.requestInviteCode = requestInviteCode;

        function requestRedistributePrizePool(redistributedBalance, callback) {
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;
            const screenId = {{ isset($screen) ? $screen->id : 'null' }};

            fetch(inviteRedistributeRequestEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': latestCsrfToken,
                    ...(
                        secretTickKey != null
                        ? { 'X-Secret-Tick-Key': secretTickKey }
                        : {}
                    )
                },
                body: JSON.stringify({
                    'invite_code': inviteCode,
                    'redistributed_balance': redistributedBalance,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error || data.exception) {
                    errorAndReset(data.error);
                    return;
                }
                console.log(data);

                callback(data.publicPath, data.wasSuccesful === true);
                latestCsrfToken = data.csrfToken;
            })
            .catch(error => {
                errorAndReset(error);
            });
        }
        window.requestRedistributePrizePool = requestRedistributePrizePool;

        function requestSetInteractionData(interactionData, callback) {
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;
            const screenId = {{ isset($screen) ? $screen->id : 'null' }};

            fetch(inviteRequestSetInteractionDataEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': latestCsrfToken,
                    ...(
                        secretTickKey != null
                        ? { 'X-Secret-Tick-Key': secretTickKey }
                        : {}
                    )
                },
                body: JSON.stringify({
                    'invite_code': inviteCode,
                    'interaction_data': interactionData,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error || data.exception) {
                    errorAndReset(data.error);
                    return;
                }
                console.log(data);

                callback(data.publicPath, data.wasSuccesful === true);
                latestCsrfToken = data.csrfToken;
            })
            .catch(error => {
                errorAndReset(error);
            });
        }

        function checkForUpdates() {
            if (inviteCode == null) {
                return;
            }

            fetch(inviteCodeUpdateEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Secret-Tick-Key': localStorage.getItem('secretTickKey'),
                    'X-CSRF-TOKEN': latestCsrfToken
                },
                body: JSON.stringify({
                    'invite_code': inviteCode,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error || data.exception) {
                    errorAndReset(data.error);
                    return;
                }

                if(window.RevealDeck.isAutoSliding()) {
                    window.RevealDeck.toggleAutoSlide();
                }

                console.log(data);

                const routeData = routesData.get(data.publicPath) || {};
                const invitees = routeData.invitees || [];
                const newInvitees = data.invitees.filter(invitee => !invitees.find(i => i.id === invitee.id));
                const leftInvitees = invitees.filter(invitee => !data.invitees.find(i => i.id === invitee.id));

                for(const invitee of newInvitees) {
                    sendPostMessageToRelevantIframe(data.publicPath, 'onInviteeJoin', invitee);
                }

                for(const invitee of leftInvitees) {
                    sendPostMessageToRelevantIframe(data.publicPath, 'onInviteeLeave', invitee);
                }

                if(data.interactionData != null || data.interactionData != routeData.interactionData) {
                    sendPostMessageToRelevantIframe(data.publicPath, 'onInteractionData', data.interactionData);
                }

                routeData.interactionData = data.interactionData;
                routeData.invitees = data.invitees;
                routesData.set(data.publicPath, routeData);

                latestCsrfToken = data.csrfToken;
            })
            .catch(error => {
                errorAndReset(error);
            });
        }

        setInterval(() => {
            checkForUpdates();
        }, 1000);
    })();
</script>
