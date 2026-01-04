(function($) {
    $.fn.stickyTabs = function(options) {
        var context = this;

        var settings = $.extend({
            getHashCallback: function(hash, btn) {
                return hash;
            },
            selectorAttribute: 'href',
            backToTop: false,
            initialTab: $('li.active > a', context)
        }, options);

        var showTabFromHash = function() {
            var hash = settings.selectorAttribute == 'href' ? window.location.hash : window.location.hash.substring(1);
            if (hash != '') {
                var selector = hash ? 'a[' + settings.selectorAttribute + '="' + hash + '"]' : settings.initialTab;
                $(selector, context).tab('show');
                setTimeout(backToTop, 1);
            }
        };

        var changeHash = function(hash) {
            if (history && history.pushState) {
                history.pushState(null, null, window.location.pathname + window.location.search + '#' + hash);
            } else {
                var scrollV = document.body.scrollTop;
                var scrollH = document.body.scrollLeft;
                window.location.hash = hash;
                document.body.scrollTop = scrollV;
                document.body.scrollLeft = scrollH;
            }
        };

        var backToTop = function() {
            if (settings.backToTop === true) {
                window.scrollTo(0, 0);
            }
        };

        showTabFromHash();

        $(window).on('hashchange', showTabFromHash);

        $('a', context).on('click', function(e) {
            var hash = this.href.split('#')[1];
            var adjustedHash = settings.getHashCallback(hash, $(this));
            changeHash(adjustedHash);
            setTimeout(backToTop, 1);
        });

        return this;
    };
})(window.jQuery);
