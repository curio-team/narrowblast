<x-partials.header />

{{ $slot }}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @isset($errors)
            @if($errors->any())
                @foreach($errors->all() as $error)
                    window.Notyf.error(@js($error));
                @endforeach
            @endif
        @endisset

        @if(session()->has('success'))
            window.Notyf.success(@js(session()->get('success')));
        @endif

        @if(session()->has('debug'))
            console.log('Request returned the following debug information:')
            console.log(@js(session()->get('debug')));
        @endif
    });
</script>

<x-partials.footer />
