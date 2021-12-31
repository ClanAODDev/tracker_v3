@auth
    @if(getSnowSetting() && getSnowSetting() != 'no_snow')

        <script src="https://cdn.jsdelivr.net/npm/confetti-js@0.0.18/dist/index.min.js"></script>

        <script type="text/javascript">
            (new ConfettiGenerator({
                target: 'canvas',
                max: 45,
                clock: 10,
                props: [
                    'square', 'triangle', 'line',
                    {
                        "type": "svg", "src": "{{ asset("images/aod-logo-modern.svg") }}", "weight": ".3"
                    }
                ],
            })).render();
        </script>
    @endif
@endauth