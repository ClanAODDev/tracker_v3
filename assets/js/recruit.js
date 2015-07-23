$(function() {

    // warn before leaving recruiting process before completion
    $(window).bind('beforeunload', function(e) {
        if ($('#tab6 button.disabled').length < 1) {
            return "You are in the recruitment process. If you leave, you will lose any data entered.";;
        }
    });

    $('[data-toggle="popover"]').popover();

    $('#games').multiselect({
        includeSelectAllOption: true,
        allSelectedText: 'All games selected'
    });

    $(".progress-bar-rct").attr("class", "bar progress-bar progress-bar-striped progress-bar-danger active");

    $('#rootwizard').bootstrapWizard({
        onNext: function(tab, navigation, index) {

            /**
             * slide validation
             */

            if (index == 2) {

                var forumName = $('#forumname').val(),
                    ingame = $('#ingame').val(),
                    platoon = $('#platoon').val(),
                    squad_id = $('#squad_id').val(),
                    member_id = $('#member_id').val();

                $(".progress-bar").attr("class", "bar progress-bar progress-bar-striped progress-bar-warning active");

                // Validate fields
                if (!$('#member_id').val() || !$('#ingame').val() || !$('#forumname').val()) {
                    $(".message").html("<i class='fa fa-times'></i>  All fields are required.").effect("bounce");
                    $('[class$=group]').each(function() {
                        var $this = $(this);
                        if ($this.find('input').val() == '') {
                            $(this).addClass("has-error");
                        }
                    });

                    return false;
                }

                if (/\D/.test(member_id)) {
                    $(".message").html("<i class='fa fa-times'></i> Forum member id must be a number.").effect("bounce");
                    return false;
                }

                // force selection of a game if the dropdown exists
                if ($('#games').length) {
                    if ($('#games option:selected').length < 1) {
                        $(".message").html("<i class='fa fa-times'></i> At least one game must be selected").effect("bounce");
                        return false;
                    }
                }

                // no errors, so clear any error states
                $(".has-error").removeClass("has-error");
                $(".message").html("");

                // check for matching forum name / ingame
                if (ingame != forumName) {
                    if (!confirm("The member's forum name does not match the ingame name. Are you sure you wish to continue with this information?")) {
                        return false;
                    }
                }

                // post member data to db
                var flag = {};
                $.ajax({
                    type: 'POST',
                    url: 'do/validate-member',
                    data: {
                        member_id: member_id
                    },
                    dataType: 'json',
                    async: false,
                    success: function(response) {
                        if (response.success === false) {

                            if (response.memberExists === true) {
                                flag = {
                                    error: true,
                                    type: 'memberExists'
                                };
                            } else if (response.invalidId === true) {
                                flag = {
                                    error: true,
                                    type: 'invalidId'
                                };
                            }

                        } else {
                            flag = {
                                error: true
                            };
                        }
                    }
                });

                // have to declare a flag so it's not undefined...
                console.log(flag);
                if (flag.error) {

                    if (flag.type == 'memberExists') {

                        $(".memberid-group").addClass('has-error');
                        if (confirm("You have entered a member id which already exists. If you are recruiting a player who was previously an AOD member, you can continue. If not, press cancel and verify the forum member id is correct")) {
                            return true;
                        } else {
                            return false;
                        }

                    } else if (flag.type == 'invalidId') {

                        $(".memberid-group").addClass('has-error');
                        if (confirm("Please verify you entered the correct member id. If you are certain it is correct, select ok. Otherwise, select cancel and correct it.")) {
                            return true;
                        } else {
                            return false;
                        }

                    }
                }

                $(".progress-bar").attr("class", "bar progress-bar progress-bar-striped progress-bar-warning active");
            }


            if (index == 3) {

                // have the division threads loaded?
                if ($('.thread-list').is(':visible')) {

                    // do the number of threads match the number of successful results?
                    if ($('li.thread').length != $('.thread span.alert-success').length) {
                        swal('Oops!', 'Your recruit must post in all the listed threads!', 'error');
                        return false
                    }
                } else {
                    return false
                }
            }

            if (index == 4) {

                if ($('#checkArray :checkbox:checked').length < $('#checkArray :checkbox').length) {
                    $(".checkbox label").addClass('text-danger');
                    swal('Oops!', 'You must complete and mark all the listed steps!', 'error');
                    return false;
                }
                $(".progress-bar").attr("class", "bar progress-bar progress-bar-striped progress-bar-success active");
            }

        },
        onTabShow: function(tab, navigation, index) {

            // panel titles
            switch (index) {
                case 0:
                    $(".tab-title strong").html("Recruiting Introduction")
                    break;
                case 1:
                    $(".tab-title strong").html("Add new member information")
                    break;
                case 2:
                    $(".tab-title strong").html("Rules and Regulations Threads")
                    loadThreadCheck();
                    break;
                case 3:
                    $(".tab-title strong").html("Finishing Up With Your Recruit")
                    break;
                case 4:
                    $(".tab-title strong").html("\"Dreaded Paperwork\"")
                    break;
                case 5:
                    $(".tab-title strong").html("Add New Recruit to Division")
                    break;
            }

            var $total = navigation.find('li').length;
            var $current = index + 1;
            var $percent = ($current / $total) * 100;
            $('#rootwizard').find('.bar').css({
                width: $percent + '%'
            });
        }
    });

    $("#storePlayer").click(function(event) {

        event.preventDefault();
        var forum_name = $('#forumname').val(),
            ingame_name = $('#ingame').val(),
            platoon = $('#platoon').val(),
            squad_id = $('#squad_id').val(),
            division = $('#game').val(),
            member_id = $('#member_id').val();

        var played_games = [];
        $.each($("input[name='games']:checked"), function() {
            played_games.push($(this).val());
        });

        if (member_id != "") {
            storePlayer(member_id, forum_name, platoon, squad_id, ingame_name, division, played_games);
        }
    });
});


function loadThreadCheck() {

    // setting these here since we know we have them
    var player = $('#forumname').val(),
        ingame = $('#ingame').val(),
        game = $("#game").val(),
        member_id = $("#member_id").val(),


        /**
         * big copy paste blurbs for posts
         */

        // division structure
        postString = $("#division-post").find(".post-code").attr('data-post');
        postCode = postString.replace(/%%member_id%%/g, member_id).replace(/%%member_name%%/g, player);
        $("#division-post .post-code").html(postCode);
        $('.division-code-btn').attr("data-clipboard-text", postCode);


        // welcome PM
        postString = $("#welcome-pm").find(".welcome-code").attr('data-post');
        welcomeCode = postString.replace(/%%member_name%%/g, player);
        $("#welcome-pm .welcome-code").html(welcomeCode);
        $('.welcome-pm-btn').attr("data-clipboard-text", welcomeCode);
        $(".pm-link").click(function(e) {
            e.preventDefault();
            windowOpener($(this).attr("href") + member_id, "AOD Squad Tracking", "width=1000,height=600,scrollbars=yes");
        });


    if (ingame) {
        $(".rank-name").html("AOD_Rct_" + ucwords(ingame));
        $(".player-name").html(ucwords(ingame));

        // full name copy
        $('.player-name-copy').attr("data-clipboard-text", "AOD_Rct_" + ucwords(ingame))

        // final member id for request
        $(".final_member_id").html(member_id);
        $('.member-status-btn').attr("data-clipboard-text", member_id);
    }

    if (player) {
        $(".search-subject").html("<p class='text-muted'>Searching threads for posts by: <code>" + ucwords(player) + "</code></p>");
    }

    $(".thread-results").html('<img src="assets/images/loading.gif " class="margin-top-20" />');

    $.ajax({
        url: "do/check-division-threads",
        type: 'POST',
        data: {
            player: player,
            game: game
        },
        cache: false,
        beforeSend: function() {
            $('#content').hide();
            $('#loading').show();
        },
    })

    .done(function(html) {
        $(".thread-results ").empty().prepend(html);
        $('.tool').powerTip({
            placement: 'n'
        });
    });
}

function storePlayer(member_id, forum_name, platoon, squad_id, ingame_name, division, played_games) {

    var played_games = $("#games option:selected").map(function() {
        return $(this).val();
    }).get();

    $("#storePlayer").html("Submitting player data...").attr("class", "btn btn-info disabled");

    $.ajax({
        type: 'POST',
        url: 'do/add-member',
        data: {
            member_id: member_id,
            forum_name: forum_name,
            platoon_id: platoon,
            squad_id: squad_id,
            ingame_name: ingame_name,
            game_id: division,
            played_games: played_games
        },
        dataType: 'json',
        async: false,
        success: function(response) {
            message = response.message;
            if (response.success === false) {
                swal('Error!', message, 'error');
            } else {
                $("#storePlayer").html("<i class='fa fa-check'></i> " + message).attr("class", "btn btn-success disabled").delay(1000).fadeOut();
                $("#storePlayer").after("<br /><br /><a href='./' class='btn btn-info'>Go Home</a>");
                swal('Success!', ucwords(forum_name) + ' has been successfully added to the division', 'success');
            }
        }
    });
}