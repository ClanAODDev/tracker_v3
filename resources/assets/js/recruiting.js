window.Recruiting = {
    main: function () {
        Recruiting.stepOneInit();
        Recruiting.stepTwoInit();

        $("[name=trngMode]").click(function () {
            // @TODO: Define test user in environment
            $('[name=member_id]').val(99999).effect('highlight');
            $('[name=forum_name]').val('test-user').effect('highlight');
            $('[name=ingame_name]').val('test-user').effect('highlight');
            Recruiting.scrollTo("[name=member_id]");
        });
    },

    handleThreadCheck: function () {
        $(document).ready(function () {
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
                    string: $('input[name=member_id]').val(),
                    memberName: $('input[name=forum_name]').val(),
                    division: $('input[name=division_id]').val(),
                    isTesting: $('input[name=is_testing]').val(),
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
        });
    },

    scrollTo: function (element) {
        $('html, body').animate({
            scrollTop: $(element).offset().top
        }, 1000);
    },

    stepTwoInit: function () {
        $('.step-two-submit').click(function (e) {
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

        $("#forum_name").change(function () {
            $("#ingame_name").val($(this).val()).effect('highlight');
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
};
Recruiting.main();


