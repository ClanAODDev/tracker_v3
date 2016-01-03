$(function() {

    $(".draggable").draggable({
        connectToSortable: 'ul',
        revert: 'invalid',
        scroll: true,
        scrollSensitivity: 100
    });

    $(".view-profile").click(function() {
        var userId = $(this).closest('.list-group-item').attr('data-user-id');
        location.href = "member/" + userId;
    });

    $(".create-squad").click(function(e) {
        e.preventDefault();
        $(".viewPanel .viewer").load("create/squad", {
            division_id: $(this).attr('data-division-id'),
            platoon_id: $(this).attr('data-platoon-id')
        });
        $(".viewPanel").modal();
    });

    $(".modal").delegate("#create_squad_btn", "click", function(e) {
        e.preventDefault();
        var data = $("#create_squad").serialize();
        $.post("do/create-squad", data, function() {
            $(".viewPanel").modal('hide');
            setTimeout(function() {
                location.reload();
            }, 600);
        });
    });

    $(".modify-squad").click(function(e) {
        e.preventDefault();
        $(".viewPanel .viewer").load("modify/squad", {
            division_id: $(this).closest('div').find('ul').attr('data-division-id'),
            platoon_id: $(this).closest('div').find('ul').attr('data-platoon-id'),
            squad_id: $(this).closest('div').find('ul').attr('data-squad-id')
        });
        $(".viewPanel").modal();
    });

    $(".modal").delegate("#modify_squad_btn", "click", function(e) {
        e.preventDefault();
        var data = $("#modify_squad").serialize();
        $.post("do/modify-squad", data, function() {
            $(".viewPanel").modal('hide');
            setTimeout(function() {
                location.reload();
            }, 600);
        });
    });

    var itemMoved, targetplatoon, sourcePlatoon, action = null;
    $(".sortable").sortable({
        revert: true,
        connectWith: 'ul',
        placeholder: "ui-state-highlight",
        receive: function(event, ui) {
            itemMoved = $(ui.item).attr('data-player-id');
            targetList = $(this).attr('id');

            if (targetList == "flagged-inactives") {
                $(ui.item).find('.removed-by').show().html("Flagged by you");
                action = 1;
                context = " flagged for removal.";
                var flagCount = parseInt($(".flagCount").text()) + 1,
                    inactiveCount = parseInt($(".inactiveCount").text()) - 1;
                $(".flagCount").text(flagCount);
                $(".inactiveCount").text(inactiveCount);
            } else {
                $(ui.item).find('.removed-by').empty();
                context = " no longer flagged for removal."
                action = 0;
                var flagCount = parseInt($(".flagCount").text()) - 1,
                    inactiveCount = parseInt($(".inactiveCount").text()) + 1;
                $(".flagCount").text(flagCount);
                $(".inactiveCount").text(inactiveCount);

            }
            var member_id = $("#member_id").val();
            $.ajax({
                type: 'POST',
                url: 'do/update-flag',
                data: {
                    action: action,
                    id: itemMoved,
                    member_id: member_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success === false) {
                        message = response.message;
                        $(".alert-box").stop().html("<div class='alert alert-danger'><i class='fa fa-times'></i> " + message + "</div>").effect('highlight').delay(1000).fadeOut();
                    } else {
                        message = "Player " + itemMoved + context;
                        $(".alert-box").stop().html("<div class='alert alert-success'><i class='fa fa-check'></i> " + message + "</div>").effect('highlight').delay(1000).fadeOut();
                    }
                },
            });
        }
    });

    // manage division
    var itemMoved, targetplatoon, sourcePlatoon;
    $(".sortable-division").sortable({
        connectWith: 'ul',
        placeholder: "ui-state-highlight",
        receive: function(event, ui) {
            itemMoved = $(ui.item).attr('data-member-id');
            targetPlatoon = $(this).attr('id');
            alert("Player " + itemMoved + " now belongs to " + targetPlatoon);

        }
    });

    // manage platoon
    // fix for affix panel
    $('#sidebar').width($('.sidebar-parent').width());
    // activate on resize
    $(window).resize(function() {
        $('#sidebar').width($('.sidebar-parent').width());
    });
    
    var itemMoved, targetplatoon, sourcePlatoon;
    $(".mod-plt .sortable").sortable({
        connectWith: 'ul',
        placeholder: "ui-state-highlight",
        receive: function(event, ui) {
            itemMoved = $(ui.item).attr('data-member-id');
            targetSquad = $(this).attr('data-squad-id');
            senderLength = $(ui.sender).find('li').length;
            receiverLength = $(this).find('li').length;
            if (undefined == targetSquad) {
                alert("You cannot move players to this list");
                $(".mod-plt .sortable").sortable('cancel');
            } else {
                // is genpop empty?
                if ($('.genpop').find('li').length < 1) {
                    $('.genpop').fadeOut();
                }
                // update squad counts
                $(ui.sender).parent().find('.badge').text(senderLength);
                $(this).parent().find('.badge').text(receiverLength);
                $.ajax({
                    type: 'POST',
                    url: 'do/update-member-squad',
                    data: {
                        member_id: itemMoved,
                        squad_id: targetSquad
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success === false) {
                            message = response.message;

                            $(".alert-box").stop().html("<div class='alert alert-danger'><i class='fa fa-times'></i> " + message + "</div>").effect('highlight').delay(1000).fadeOut();
                        } else {
                            //message = "Player " + itemMoved + context;
                            message = "Player #" + itemMoved + " reassigned";

                            $(".alert-box").stop().html("<div class='alert alert-success'><i class='fa fa-check'></i> " + message + "</div>").effect('highlight').delay(1000).fadeOut();
                        }
                    },
                });
            }
        }
    });




    /**
     * LOA management
     */

    // pending view loa

    $(".view-pending-loa").click(function() {
        var id = $(this).closest('tr').attr('data-member-id'),
            loa_id = $(this).closest('tr').attr('data-loa-id'),
            comment = $(this).closest('tr').attr('data-comment');

        $(".viewPanel .viewer").load("views/modals/loa/view-pending.php",
            function() {
                $(".modal #comment").html(comment);
                $(".modal").attr('data-member-id', id).modal();
                $(".modal").attr('data-loa-id', loa_id).modal();
            }
        );
    })

    // deny LOA

    $(".modal").delegate(".deny-loa-btn", "click", function(e) {

        e.preventDefault();

        var url = "do/update-loa",
            member_id = $('.modal').attr('data-member-id'),
            loa_id = $('.modal').attr('data-loa-id');

        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: {
                remove: true,
                member_id: member_id,
                loa_id: loa_id
            },

            success: function(data) {
                if (data.success) {

                    $('.modal').modal('hide');

                    $('*[data-loa-id="' + loa_id + '"]').effect('highlight').hide("fade", {
                        direction: "out"
                    }, "slow");
                    $(".loa-alerts").attr('class', 'alert alert-success loa-alerts').html("<i class='fa fa-check fa-lg'></i> " + data.message).show().delay(3000).fadeOut();

                } else {
                    $(".loa-alerts").attr('class', 'alert alert-danger loa-alerts').html("<i class='fa fa-exclamation-triangle fa-lg'></i> " + data.message).show().delay(3000).fadeOut();
                    $('.modal').modal('hide');
                }
            }
        });

    })


    // approve LOA
    $(".modal").delegate(".approve-loa-btn", "click", function(e) {

        e.preventDefault();

        var url = "do/update-loa",
            member_id = $('.modal').attr('data-member-id'),
            loa_id = $('.modal').attr('data-loa-id');

        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: {
                approve: true,
                member_id: member_id,
                loa_id: loa_id
            },

            success: function(data) {
                if (data.success) {

                    $('.modal').modal('hide');

                    $('*[data-loa-id="' + loa_id + '"]').effect('highlight').hide("fade", {
                        direction: "out"
                    }, "slow");
                    $(".loa-alerts").attr('class', 'alert alert-success loa-alerts').html("<i class='fa fa-check fa-lg'></i> " + data.message).show().delay(3000).fadeOut();

                } else {
                    $(".loa-alerts").attr('class', 'alert alert-danger loa-alerts').html("<i class='fa fa-exclamation-triangle fa-lg'></i> " + data.message).show().delay(3000).fadeOut();
                    $('.modal').modal('hide');
                }
            }
        });

    })

    // active view loa
    $('#loas').delegate(".view-active-loa", "click", function(e) {
        var id = $(this).closest('tr').attr('data-loa-id'),
            member_id = $(this).closest('tr').attr('data-member-id'),
            comment = $(this).closest('tr').attr('data-comment'),
            approval = $(this).closest('tr').attr('data-approval');

        $(".viewPanel .viewer").load("views/modals/loa/view-active.php",
            function() {
                $(".modal #comment").html(comment);
                $(".modal #approval").val(approval);
                $(".modal").attr('data-loa-id', id).modal();
                $(".modal").attr('data-member-id', member_id).modal();
            }
        );
    })

    // revoke LOA
    $(".modal").delegate(".revoke-loa-btn", "click", function(e) {
        $('.modal').modal('hide');
        e.preventDefault();
        swal({
                title: 'Are you sure?',
                text: 'Are you sure you want to revoke this player\'s leave of absence?.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28b62c',
                confirmButtonText: 'Yes, revoke',
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: 'json',
                    data: {
                        remove: true,
                        loa_id: loa_id,
                        member_id: member_id
                    },
                    success: function(data) {
                        if (data.success) {
                            $('*[data-loa-id="' + loa_id + '"]').effect('highlight').hide("fade", {
                                direction: "out"
                            }, "slow");

                            swal('Revoked!', 'Leave of absence has been revoked', 'success');
                        } else {
                            swal('Error', data.message, 'error');
                            $('.modal').modal('hide');
                        }
                    }
                });

            });

        var url = "do/update-loa",
            loa_id = $('.modal').attr('data-loa-id'),
            member_id = $('.modal').attr('data-member-id');

    })

    // LOA ADD
    $("#loa-update").submit(function(e) {
        e.preventDefault();
        var url = "do/update-loa";
        $(".viewPanel .viewer").load("views/modals/loa/add.php");
        $('.modal').modal({
            backdrop: 'static',
            keyboard: false
        })
            .one('click', '#submit', function(e) {
                var comment = $(".modal #comment").val();
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: 'json',
                    data: $("#loa-update").serialize() + "&comment=" + comment,
                    success: function(data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            $('.modal').modal('hide');
                            $(".loa-alerts").attr('class', 'alert alert-danger loa-alerts').html("<i class='fa fa-exclamation-triangle fa-lg'></i> " + data.message).show().delay(3000).fadeOut();
                        }
                    }
                });
                return false;
            });
    });

    $("#datepicker").datepicker({
        changeMonth: true,
        changeYear: true
    });

    // contact
    $(".modal").delegate(".pm-btn", "click", function(e) {
        var pm_url = 'http://www.clanaod.net/forums/private.php?do=newpm&u=' + $('.modal').attr('data-id');
        windowOpener(pm_url, "Mass PM", "width=900,height=600,scrollbars=yes");
    });

    // view profile
    $(".modal").delegate(".profile-btn", "click", function(e) {
        var userId = $('.modal').attr('data-member-id');
        location.href = "member/" + userId;
    });
});
