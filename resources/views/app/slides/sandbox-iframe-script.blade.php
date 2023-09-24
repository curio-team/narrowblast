<script>
    var routesWithJavascript = new Map();
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
                    } else {
                        iframe.setAttribute('sandbox', '');
                    }

                    iframe.dataset.narrowBlastInit = 'yes';
                    iframe.src = publicPath;
                    console.log(`🚀 NarrowBlast: iframe (${publicPath}) allowing scripts`, iframe);
                }
            });
        }).observe(iframe, {
            attributes: true
        });
    });
</script>
