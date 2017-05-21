let Recruiting = Recruiting || {};

(function ($) {

    Recruiting = {

        main: function () {
            Recruiting.stepOneInit();
            Recruiting.stepTwoInit();
            Recruiting.scrollToError();
            Recruiting.handleThreadCheck();

            $("[name=doThreadCheck]").click(function () {
                Recruiting.handleThreadCheck();
            });
        },

        scrollToError: function () {
            $("[name=doScrollToErrors]").click(function () {
                $('html, body').animate({
                    scrollTop: $(".has-error:first-of-type").offset().top
                }, 2000);
            });
        },

        handleThreadCheck: function () {
            let base_url = window.Laravel.appPath,
                results = $('.thread-results'),
                loadingIcon = $('.refresh-button i'),
                statusText = $('.status'),
                reloadBtn = $('.refresh-button');

            reloadBtn.attr('disabled', 'disabled');

            $.ajax({
                url: base_url + "/search-division-threads",
                type: 'POST',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    string: $('input[name=member-id]').val(),
                    division: $('input[name=division-id]').val(),
                },
                cache: false,
                beforeSend: function () {
                    results.fadeOut();
                    loadingIcon.addClass('fa-spin');
                },
            }).done(function (html) {
                results.empty().prepend(html).fadeIn();
                loadingIcon.removeClass('fa-spin');
                statusText.text('Check Thread Statuses');
                reloadBtn.removeAttr('disabled');
                toastr.success('Thread check finished successfully!', 'Success');
            });
        },

        stepTwoInit: function () {
            $('.continue-btn').click(function (e) {
                e.preventDefault();

                if ($('.thread-list').is(':visible')) {

                    if ($('.thread').length !== $('.thread .text-success').length) {
                        toastr.error('Recruit has not completed all threads.', 'Oops');
                        return false;
                    }

                    $("#member-information").submit();
                } else {
                    toastr.error('Thread check still running...', 'Oops...');
                }
            });
        },

        stepOneInit: function () {

            $("#forum-name").change(function () {
                $("#ingame-name").val($(this).val()).effect('highlight');
            });

            $("#platoon").change(function () {
                let platoon = $(this).val(),
                    base_url = window.Laravel.appPath;

                $.post(base_url + "/search-platoon",
                    {
                        platoon: platoon,
                        _token: $('meta[name=csrf-token]').attr('content')
                    },
                    function (data) {
                        var options = $("#squad");

                        options.empty().attr('disabled', 'disabled');

                        $.each(data, function (name, id) {
                            if (!name) {
                                name = "Squad #" + id
                            }
                            options.append(new Option(name, id));
                        });

                        if (Object.keys(data).length < 1) {
                            options.append(new Option('No Squads Available'));
                            return false;
                        }

                        options.removeAttr('disabled').effect('highlight');
                    })
            });
        }
    }

})(jQuery);

Recruiting.main();


