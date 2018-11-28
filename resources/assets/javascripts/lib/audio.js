var initialised = false,
    loaded = false,
    queue = [],
    load_audioplayer = function() {
        AudioPlayer.setup(STUDIP.ASSETS_URL + 'flash/player.swf', {
            animation: 'no',
            transparentpagebg: 'yes',
            width: 300
        });
        loaded = true;

        // Process queue
        var item = queue.shift();
        while (item) {
            Audio.handle(item);
            item = queue.shift();
        }
    },
    initialise = function() {
        if (!initialised) {
            var script = document.createElement('script');
            script.src = STUDIP.ASSETS_URL + 'javascripts/audio-player.js';
            script.onload = load_audioplayer;
            document.getElementsByTagName('head')[0].appendChild(script);
            initialised = true;
        }
        return loaded;
    };

const Audio = {
    handle: function(element) {
        if (!initialise()) {
            queue.push(element);
        } else {
            AudioPlayer.embed(element.id, {
                soundFile: encodeURIComponent(element.src),
                titles: element.title,
                width: element.clientWidth || 300
            });
        }
    }
};

export default Audio;
