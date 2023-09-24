<x-common-layout>
    <div class="reveal">
        <div class="slides">
            <section data-background-iframe="{{ $publicPath }}">
            </section>
        </div>
    </div>

    <div class="flex flex-col gap-4 bg-white/75 fixed top-0 p-4 rounded-br md:min-w-[350px]">
        <h2>Preview Tools</h2>
        <ul class="flex flex-col gap-2"
        x-data="{
            'has_javascript_powerup': {{ ($slide->data['has_javascript_powerup'] ?? false) ? 'true' : 'false' }},
            'enable_invite_system': false,
        }"
        x-effect="window.enable_invite_system = enable_invite_system; if (enable_invite_system) has_javascript_powerup = true; routesWithJavascript.set('{{ $publicPath }}', has_javascript_powerup); refreshIframes()">
            <li>
                <x-inputs.checkbox name="has_javascript_powerup" label="With Javascript Powerup" x-model="has_javascript_powerup" />
            </li>
            <li class="flex flex-col gap-2">
                <x-inputs.checkbox name="ask_for_invite_system" label="Invite System" x-model="enable_invite_system" />
                <div x-cloak class="flex flex-col pl-4" x-show="enable_invite_system">
                    <x-buttons.link @click="simulateOnPlayerJoin()">simulate onPlayerJoin()</x-buttons.link>
                    <x-buttons.link @click="simulateOnPlayerLeave()">simulate onPlayerLeave()</x-buttons.link>
                </div>
            </li>
        </ul>
    </div>

    @include('app.slides.sandbox-iframe-script')

    <script>
        const slideContainerEl = document.querySelector('.reveal');
        let entryFee = 0;

        window.addEventListener('message', function(event) {
            if (!window.enable_invite_system) return;

            if (event.data.type === 'getInviteCode') {
                entryFee = event.data.entryFee;
                simulateOnInviteCode();
            }
        });

        function getIframe() {
            return slideContainerEl.querySelector('iframe');
        }

        function refreshIframes() {
            const iframe = getIframe();
            iframe.dataset.narrowBlastInit = 'no';
            iframe.src = iframe.src;
        }

        function simulateOnInviteCode() {
            const iframe = getIframe();

            iframe.contentWindow.postMessage({
                type: 'onInviteCode',
                inviteCode: '123-123 (preview only)',
            }, '*');
        }

        const randomPlayerNames = [
            'Aaron', 'Abel', 'Abraham', 'Adam', 'Anne', 'Benjamin', 'Cain', 'Cindy', 'Dililah', 'David', 'Eve', 'Elijah', 'Ezekiel', 'Fiona', 'Gina', 'Hannah', 'Heather', 'Irene', 'Jasmine', 'Jenny', 'Katie', 'Lily', 'Linda', 'Mandy',
        ];
        const players = [];
        function simulateOnPlayerJoin() {
            const iframe = getIframe();
            const player = {
                id: Math.floor(Math.random() * 1000000),
                name: randomPlayerNames[Math.floor(Math.random() * randomPlayerNames.length)],
                entryFee: entryFee,
            };
            players.push(player);

            iframe.contentWindow.postMessage({
                type: 'onPlayerJoin',
                player: player,
            }, '*');
        }

        function simulateOnPlayerLeave() {
            const iframe = getIframe();
            const player = players.pop();

            if (!player) return;

            iframe.contentWindow.postMessage({
                type: 'onPlayerLeave',
                player: player,
            }, '*');
        }
    </script>
</x-common-layout>
