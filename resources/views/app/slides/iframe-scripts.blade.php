<!--
    File: iframe-scripts.blade.php
    Description: Manages interactions and functionalities related to iframes, including invite code handling,
                 setting interaction data, communicating with iframes via postMessage, and checking for updates.
-->

<script>
    // Initializing the CSRF token with a value obtained from server-side
    let latestCsrfToken = '{{ csrf_token() }}';

    // Immediately invoked function expression (IIFE) begins
    (function() {
        // Defining various endpoints using Blade syntax (presumably for server routes)
        const inviteRequestSetInteractionDataEndpoint = `{{ route('slides.inviteRequestSetInteractionData') }}`;
        const inviteRedistributeRequestEndpoint = `{{ route('slides.inviteRedistributeRequest') }}`;
        const inviteCodeRequestEndpoint = `{{ route('slides.inviteCodeRequest') }}`;
        const inviteCodeUpdateEndpoint = `{{ route('slides.inviteCodeUpdate') }}`;

        // Getting the DOM element for the slide container and initializing Maps for route data
        const slideContainerEl = document.getElementById('slideContainer');
        const routesWithJavascript = new Map();
        const routesData = new Map();
        const routesIframes = new Map();
        let inviteCode = null;

        // Function to set the current invite code
        function setCurrentInviteCode(newInviteCode) {
            inviteCode = newInviteCode;
        }
        window.setCurrentInviteCode = setCurrentInviteCode;

        // Function to update slide creator information based on the current slide
        function updateSlideCreator(slide) {
            // Retrieving elements related to the slide creator
            const slideCreator = document.getElementById('slideCreator');
            const slideCreatorInitials = document.getElementById('slideCreatorInitials');
            const slideCreatorName = document.getElementById('slideCreatorName');

            // Updating slide creator initials and name based on dataset attributes of the slide
            if (slide.dataset.creatorInitials) {
                slideCreatorInitials.innerText = slide.dataset.creatorInitials;
            } else {
                slideCreatorInitials.innerText = '';
            }

            if (slide.dataset.creatorName) {
                slideCreatorName.innerText = slide.dataset.creatorName;
            } else {
                slideCreatorName.innerText = '';
            }

            // Removing opacity class to display the slide creator information
            slideCreator.classList.remove('opacity-0');
        }

        // Event listener for DOMContentLoaded event to update slide creator on slide change
        document.addEventListener('DOMContentLoaded', function() {
            window.RevealDeck.on('slidechanged', function(event) {
                updateSlideCreator(event.currentSlide);
            });
        });

        // Event listener for iframe creation to apply sandbox attribute and narrow sandbox if required
        document.addEventListener('iframecreated', function(event) {
            // Accessing the created iframe element
            const iframe = event.detail;
            iframe.setAttribute('sandbox', '');

            // Observing mutations to the iframe's 'src' attribute to adjust sandboxing
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    // Checking if the iframe should allow scripts based on route data
                    if (mutation.attributeName === 'src' && iframe.dataset
                        .narrowBlastInit !== 'yes') {
                        // Obtaining public path and checking if it requires JavaScript execution
                        const publicPath = iframe.getAttribute('src');
                        const hasJavascriptPowerup = routesWithJavascript.get(publicPath);

                        // Adjusting sandbox attribute accordingly and logging the action
                        if (hasJavascriptPowerup) {
                            iframe.setAttribute('sandbox', 'allow-scripts');
                            console.log(
                                `🚀 NarrowBlast: iframe (${publicPath}) allowing scripts`,
                                iframe);
                        } else {
                            iframe.setAttribute('sandbox', '');
                        }

                        // Initializing the iframe source and storing it in the routesIframes Map
                        iframe.dataset.narrowBlastInit = 'yes';
                        iframe.src = publicPath;
                        routesIframes.set(publicPath, iframe);
                    }
                });
            }).observe(iframe, {
                attributes: true
            });
        });

        // Function to send postMessage to the relevant iframe
        function sendPostMessageToRelevantIframe(publicPath, type, data) {
            // Retrieving the iframe associated with the given public path
            const iframe = routesIframes.get(publicPath);

            // Posting the message to the iframe if found, else logging an error
            if (iframe) {
                iframe.contentWindow.postMessage({
                    type: type,
                    data: data,
                }, '*');
            } else {
                console.log(`Could not find iframe for public path ${publicPath}`);
            }

            // Dispatching a custom event for invite updates
            window.dispatchEvent(new CustomEvent('narrowblastinviteupdate', {
                detail: {
                    publicPath: publicPath,
                    type: type,
                    data: data,
                }
            }));
        }

        // Event listener for receiving messages from iframes
        window.addEventListener('message', function(event) {
            // Checking if invite system is enabled
            if (!window.enable_invite_system) return;

            // Handling different message types received from iframes
            if (event.data.type === 'getInviteCode') {
                // Obtaining slide ID and requesting an invite code
                const slideId = window._narrowBlastPreviewSlideId ? window._narrowBlastPreviewSlideId :
                    event.data.data.split('/').pop().split('.').shift();
                requestInviteCode(slideId, function(publicPath, inviteCode, inviteCodeQr) {
                    sendPostMessageToRelevantIframe(publicPath, 'onInviteCode', {
                        inviteCode: inviteCode,
                        inviteCodeQr: inviteCodeQr,
                    });
                });
            } else if (event.data.type === 'requestRedistributePrizePool') {
                // Requesting redistribution of the prize pool
                requestRedistributePrizePool(event.data.data, function(publicPath, wasSuccesful) {
                    sendPostMessageToRelevantIframe(publicPath, 'onRedistributePrizePool',
                        wasSuccesful);
                });
            } else if (event.data.type === 'requestSetInteractionData') {
                // Requesting to set interaction data
                requestSetInteractionData(event.data.data, function(publicPath, wasSuccesful) {
                    sendPostMessageToRelevantIframe(publicPath, 'onSetInteractionData',
                        wasSuccesful);
                });
            } else {
                console.error('Ignoring unknown message type', event.data.type);
            }
        });

        // Function to get the slide container element
        function getSlideContainer() {
            return slideContainerEl;
        }
        window.getSlideContainer = getSlideContainer; // Making the function accessible globally

        // Function to clear slides inside the slide container
        function clearSlides() {
            slideContainerEl.innerHTML = '';
        }
        window.clearSlides = clearSlides; // Making the function accessible globally

        // Function to add a new slide to the slide container
        function addSlide(slide) {
            // Creating data attributes for the slide element
            let elementData = [];
            elementData["data-background-iframe"] = slide.publicPath;
            elementData["data-creator-name"] = slide.creator.name;
            // TODO: Avatar
            elementData["data-creator-initials"] = slide.creator.initials;

            const section = document.createElement('section');

            // Setting data attributes for the section element
            for (const [key, value] of Object.entries(elementData)) {
                section.setAttribute(key, value);
            }

            slideContainerEl.appendChild(section); // Appending the created section to the slide container

            // Checking and setting javascript powerup and invite system based on slide data
            if (slide.data.has_javascript_powerup) {
                setRouteJavascriptPowerup(slide.publicPath, true);
            }
            if (slide.data.invite_system_shop_item_user_id != null) {
                setRouteJavascriptPowerup(slide.publicPath, true);
                window.enable_invite_system = true;
            }
        }
        window.addSlide = addSlide; // Making the function accessible globally

        // Function to find a slide by its public path
        function findSlideByPublicPath(publicPath) {
            return slideContainerEl.querySelector(`section[data-background-iframe="${publicPath}"]`);
        }
        window.findSlideByPublicPath = findSlideByPublicPath; // Making the function accessible globally

        // Function to create a password modal for user interaction
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
        window.passwordModal = passwordModal; // Making the function accessible globally

        /**
         * Enables or disables JavaScript for the given route.
         * You should refresh the iframe if it's already fully loaded.
         */
        function setRouteJavascriptPowerup(route, hasJavascriptPowerup) {
            routesWithJavascript.set(route,
                hasJavascriptPowerup); // Setting javascript powerup for a specific route
        }
        window.setRouteJavascriptPowerup = setRouteJavascriptPowerup; // Making the function accessible globally

        // Function to handle errors and reset certain variables/state
        function errorAndReset(error) {
            console.error(error); // Logging the error
            if (!window.RevealDeck.isAutoSliding()) {
                window.RevealDeck.toggleAutoSlide(); // Toggling auto slide if not already auto sliding
            }
            inviteCode = null; // Resetting inviteCode to null
        }


        /**
         * Function to request an invite code from the server.
         * If this is called without providing the secret tick key, then a preview invite system is generated.
         */
        function requestInviteCode(slideId, callback) {
            // Retrieving the secret tick key and screen ID
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;
            const screenId =
                {{ isset($screen) ? $screen->id : 'null' }}; // Retrieving the screen ID if it exists, else setting it to null

            // Making a fetch request to obtain an invite code
            fetch(inviteCodeRequestEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': latestCsrfToken, // Including the CSRF token in the request headers
                        ...(secretTickKey != null ? {
                            'X-Secret-Tick-Key': secretTickKey
                        } : {}) // Including the secret tick key if it exists
                    },
                    body: JSON.stringify({
                        'screen_id': screenId, // Sending the screen ID in the request body
                        'slide_id': slideId, // Sending the slide ID in the request body
                    })
                })
                .then(response => response.json()) // Parsing the response as JSON
                .then(data => {
                    if (data.error || data.exception) {
                        errorAndReset(data
                            .error); // Handling errors and resetting if an error or exception occurs
                        return;
                    }
                    console.log(data); // Logging the received data

                    setCurrentInviteCode(data.inviteCode); // Setting the current invite code
                    callback(data.publicPath, data.inviteCode, data // Stan was here
                        .inviteCodeQr); // Executing the callback with received data
                    latestCsrfToken = data.csrfToken; // Updating the latest CSRF token
                })
                .catch(error => {
                    errorAndReset(error); // Handling errors and resetting in case of a fetch error
                });
        }
        window.requestInviteCode = requestInviteCode; // Making the function accessible globally

        /**
         * Function to request redistribution of the prize pool.
         */
        function requestRedistributePrizePool(redistributedBalance, callback) {
            // Retrieving the secret tick key and screen ID
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;
            const screenId =
                {{ isset($screen) ? $screen->id : 'null' }}; // Retrieving the screen ID if it exists, else setting it to null

            // Making a fetch request to redistribute the prize pool
            fetch(inviteRedistributeRequestEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': latestCsrfToken, // Including the CSRF token in the request headers
                        ...(secretTickKey != null ? {
                            'X-Secret-Tick-Key': secretTickKey
                        } : {}) // Including the secret tick key if it exists
                    },
                    body: JSON.stringify({
                        'invite_code': inviteCode, // Sending the invite code in the request body
                        'redistributed_balance': redistributedBalance, // Sending the redistributed balance in the request body
                    })
                })
                .then(response => response.json()) // Parsing the response as JSON
                .then(data => {
                    if (data.error || data.exception) {
                        errorAndReset(data
                            .error); // Handling errors and resetting if an error or exception occurs
                        return;
                    }
                    console.log(data); // Logging the received data

                    callback(data.publicPath, data.wasSuccesful ===
                        true); // Executing the callback with received data
                    latestCsrfToken = data.csrfToken; // Updating the latest CSRF token
                })
                .catch(error => {
                    errorAndReset(error); // Handling errors and resetting in case of a fetch error
                });
        }
        window.requestRedistributePrizePool =
            requestRedistributePrizePool; // Making the function accessible globally


        function requestSetInteractionData(interactionData, callback) {
            // Retrieving a secret key from local storage or setting it as null
            const secretTickKey = localStorage.getItem('secretTickKey') ?? null;

            // Checking if $screen variable is set; if yes, assigns its id, otherwise assigns null
            const screenId = {{ isset($screen) ? $screen->id : 'null' }};

            // Initiating a POST request to the inviteRequestSetInteractionDataEndpoint
            fetch(inviteRequestSetInteractionDataEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': latestCsrfToken,
                        // Conditional inclusion of 'X-Secret-Tick-Key' header based on secretTickKey value
                        ...(
                            secretTickKey != null ? {
                                'X-Secret-Tick-Key': secretTickKey
                            } : {}
                        )
                    },
                    // Converting interaction data and invite code to JSON and sending it in the request body
                    body: JSON.stringify({
                        'invite_code': inviteCode,
                        'interaction_data': interactionData,
                    })
                })
                .then(response => response.json()) // Parsing the response as JSON
                .then(data => {
                    // Handling errors or exceptions; if present, triggering errorAndReset function
                    if (data.error || data.exception) {
                        errorAndReset(data.error);
                        return;
                    }
                    console.log(data); // Logging the received data

                    // Invoking the callback function with data.publicPath and success status
                    callback(data.publicPath, data.wasSuccesful === true);

                    // Updating the latest CSRF token from the response
                    latestCsrfToken = data.csrfToken;
                })
                .catch(error => {
                    // Catching and handling errors during the fetch operation
                    errorAndReset(error);
                });
        }

        // Function to check for updates
        function checkForUpdates() {
            // If invite code is null, exit the function
            if (inviteCode == null) {
                return;
            }

            // Initiating a POST request to inviteCodeUpdateEndpoint
            fetch(inviteCodeUpdateEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        // Including 'X-Secret-Tick-Key' header from local storage and 'X-CSRF-TOKEN'
                        'X-Secret-Tick-Key': localStorage.getItem('secretTickKey'),
                        'X-CSRF-TOKEN': latestCsrfToken
                    },
                    // Sending invite code in the request body as JSON
                    body: JSON.stringify({
                        'invite_code': inviteCode,
                    })
                })
                .then(response => response.json()) // Parsing the response as JSON
                .then(data => {
                    // Handling errors or exceptions; if present, triggering errorAndReset function
                    if (data.error || data.exception) {
                        errorAndReset(data.error);
                        return;
                    }

                    // Pausing auto-sliding if active
                    if (window.RevealDeck.isAutoSliding()) {
                        window.RevealDeck.toggleAutoSlide();
                    }

                    console.log(data); // Logging the received data

                    // Retrieving routeData or initializing an empty object if not present
                    const routeData = routesData.get(data.publicPath) || {};
                    const invitees = routeData.invitees || [];

                    // Finding new invitees by comparing with existing invitees
                    const newInvitees = data.invitees.filter(invitee => !invitees.find(i => i.id === invitee
                        .id));
                    const leftInvitees = invitees.filter(invitee => !data.invitees.find(i => i.id === invitee
                        .id));

                    // Notifying relevant iframes about new and left invitees
                    for (const invitee of newInvitees) {
                        sendPostMessageToRelevantIframe(data.publicPath, 'onInviteeJoin', invitee);
                    }

                    for (const invitee of leftInvitees) {
                        sendPostMessageToRelevantIframe(data.publicPath, 'onInviteeLeave', invitee);
                    }

                    // Sending interaction data to relevant iframe if it has changed
                    if (data.interactionData != null || data.interactionData != routeData.interactionData) {
                        sendPostMessageToRelevantIframe(data.publicPath, 'onInteractionData', data
                            .interactionData);
                    }

                    // Updating routeData with new interaction data and invitees
                    routeData.interactionData = data.interactionData;
                    routeData.invitees = data.invitees;
                    routesData.set(data.publicPath, routeData);

                    // Updating the latest CSRF token from the response
                    latestCsrfToken = data.csrfToken;
                })
                .catch(error => {
                    // Catching and handling errors during the fetch operation
                    errorAndReset(error);
                });
        }

        // Setting an interval to periodically call the checkForUpdates function every 1000 milliseconds (1 second)
        setInterval(() => {
            checkForUpdates();
        }, 1000);
    })();
</script>
