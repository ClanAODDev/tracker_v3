$(function() {

    $(".user-form-control").change(function() {
        $("#user_change").attr('value', 1);
    });

    $('#games').multiselect({
        includeSelectAllOption: true,
        allSelectedText: 'All games selected'
    });

    // auto select values
    var squad_leader_id = $("#cur_squad_leader_id").val(),
        platoon_id = $("#cur_platoon_id").val(),
        position_id = $("#cur_position_id").val();

    $("#platoon_id option[value=" + platoon_id + "]").attr("selected", "selected");
    $("#squad_leader_id option[value=" + squad_leader_id + "]").attr("selected", "selected");
    $("#position_id option[value=" + position_id + "]").attr("selected", "selected");

    $("#submit-form").click(function(event) {
        event.preventDefault();

        $(".modal .message").html("Updating member information. Please wait...").addClass("alert-info").show();
        $(".modal :submit").html("Saving...").attr('class', 'btn btn-default disabled');

        var memberData = $("#member-form,#alias-form,#div-form").serializeArray(),
            userData = $("#user-form").serializeArray(),
            played_games = $("#games option:selected").map(function() {
                return $(this).val();
            }).get();

        updateMember(memberData, userData, played_games);
    });

});

function updateMember(memberData, userData, played_games) {
    setTimeout(function() {

        var memberObj = {},
            userObj = {};

        $(memberData).each(function(i, field) {
            memberObj[field.name] = field.value;
        });

        $(userData).each(function(i, field) {
            userObj[field.name] = field.value;
        });

        $.post("do/update-member", {
                memberData: memberObj,
                userData: userObj,
                played_games: played_games
            },

            function(data) {
                $(".modal :submit").html("Submit Info").attr('class', 'btn btn-success');
                if (data.success === false) {
                    $(".modal .message").html(data.message).addClass("alert-danger").show();
                    return false;
                } else {
                    $(".modal .message").show().html(data.message).removeClass("alert-danger alert-info").addClass('alert-success').delay(1000).fadeOut();
                    $(".has-error").removeClass("has-error");
                }

            }, "json")
    }, 1000)
}