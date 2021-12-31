@auth
    @if(getSnowSetting() && getSnowSetting() != 'no_snow')

        <script src="https://cdn.jsdelivr.net/npm/confetti-js@0.0.18/dist/index.min.js"></script>

        <script type="text/javascript">

            var wrapperCanvas = {
                target: 'canvas',
                max: 45,
                props: [
                    'square', 'triangle', 'line',
                    {
                        "type": "svg", "src": "images/aod-logo-modern.svg", "weight": ".3"
                    }
                ],

                clock: 10
            };

            var canvas1 = new ConfettiGenerator(wrapperCanvas);
            canvas1.render();

        </script>
    @endif
@endauth