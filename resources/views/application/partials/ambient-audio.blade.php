@auth
<div id="ambient-audio-container" style="position: fixed; top: -1000px; left: -1000px; pointer-events: none;"></div>

<script>
var ambientPlayer = null;
var ambientReady = false;
var ambientShouldPlay = false;
var ambientVolume = 0.3;

function onYouTubeIframeAPIReady() {
    ambientReady = true;
    if (ambientShouldPlay) {
        startAmbientAudio(ambientVolume);
    }
}

function initAmbientAudio(enabled, volume) {
    ambientVolume = volume || 0.3;
    ambientShouldPlay = enabled;

    if (!enabled) {
        stopAmbientAudio();
        return;
    }

    if (!document.getElementById('youtube-iframe-api')) {
        var tag = document.createElement('script');
        tag.id = 'youtube-iframe-api';
        tag.src = 'https://www.youtube.com/iframe_api';
        var firstScript = document.getElementsByTagName('script')[0];
        firstScript.parentNode.insertBefore(tag, firstScript);
    } else if (ambientReady) {
        startAmbientAudio(ambientVolume);
    }
}

function startAmbientAudio(volume) {
    if (ambientPlayer) {
        ambientPlayer.setVolume(volume * 100);
        ambientPlayer.playVideo();
        return;
    }

    ambientPlayer = new YT.Player('ambient-audio-container', {
        height: '1',
        width: '1',
        videoId: 'j7a32YkGDGY',
        playerVars: {
            autoplay: 1,
            loop: 1,
            playlist: 'j7a32YkGDGY',
            controls: 0,
            disablekb: 1,
            fs: 0,
            modestbranding: 1,
            rel: 0
        },
        events: {
            onReady: function(event) {
                event.target.setVolume(volume * 100);
                event.target.playVideo();
            },
            onStateChange: function(event) {
                if (event.data === YT.PlayerState.ENDED) {
                    event.target.playVideo();
                }
            }
        }
    });
}

function stopAmbientAudio() {
    if (ambientPlayer && ambientPlayer.stopVideo) {
        ambientPlayer.stopVideo();
    }
}

function setAmbientVolume(volume) {
    ambientVolume = volume;
    if (ambientPlayer && ambientPlayer.setVolume) {
        ambientPlayer.setVolume(volume * 100);
    }
}

@php
    $theme = auth()->user()->settings['theme'] ?? 'traditional';
    $ambientEnabled = (auth()->user()->settings['ambient_sound'] ?? false) && $theme === 'shattrath';
    $ambientVolume = auth()->user()->settings['ambient_volume'] ?? 0.3;
@endphp

document.addEventListener('DOMContentLoaded', function() {
    initAmbientAudio({{ $ambientEnabled ? 'true' : 'false' }}, {{ $ambientVolume }});
});
</script>
@endauth
