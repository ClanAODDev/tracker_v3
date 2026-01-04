(function($) {
    $.fn.bootcomplete = function(options) {
        var defaults = {
            url: '/search.php',
            method: 'get',
            wrapperClass: 'bc-wrapper',
            menuClass: 'bc-menu',
            idField: true,
            idFieldName: $(this).attr('name') + '_id',
            minLength: 3,
            dataParams: {},
            formParams: {}
        };

        var settings = $.extend({}, defaults, options);
        var xhr;
        var $input = $(this);

        $(this).attr('autocomplete', 'off');
        $(this).wrap('<div class="' + settings.wrapperClass + '"></div>');

        if (settings.idField) {
            var $existingHidden = $('input[name="' + settings.idFieldName + '"]');
            if (!$existingHidden || !$existingHidden.length) {
                $('<input type="hidden" name="' + settings.idFieldName + '" value="">').insertBefore($(this));
            }
        }

        $('<div class="' + settings.menuClass + ' list-group"></div>').insertAfter($(this));

        function handleKeyup() {
            var formParamValues = [];
            $.each(settings.formParams, function(key, selector) {
                formParamValues[key] = $(selector).val();
            });

            var params = $.extend({}, formParamValues);
            var data = $.extend({ query: $input.val() }, settings.dataParams, params);

            if (!data.query) {
                $input.next('.' + settings.menuClass).html('');
                $input.next('.' + settings.menuClass).hide();
                return;
            }

            if (data.query.length >= settings.minLength) {
                if (xhr && xhr.readyState !== 4) {
                    xhr.abort();
                }

                xhr = $.ajax({
                    type: settings.method,
                    url: settings.url,
                    data: data,
                    dataType: 'json',
                    success: function(results) {
                        var html = '';
                        $.each(results, function(i, item) {
                            html += '<a href="#" class="list-group-item" data-id="' + item.id + '" data-label="' + item.label + '">' + item.label + '</a>';
                        });
                        $input.next('.' + settings.menuClass).html(html);
                        $input.next('.' + settings.menuClass).children().on('click', handleItemClick);
                        $input.next('.' + settings.menuClass).show();
                    }
                });
            }
        }

        function handleItemClick() {
            $input.val($(this).data('label'));
            if (settings.idField) {
                $('input[name="' + settings.idFieldName + '"]').val($(this).data('id'));
            }
            $input.next('.' + settings.menuClass).hide();
            $input.trigger('bootcomplete.selected', [$(this).data('id'), $(this).data('label')]);
            return false;
        }

        $(this).on('keyup', handleKeyup);

        return this;
    };
})(window.jQuery);
