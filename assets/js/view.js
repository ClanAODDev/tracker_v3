$(function() {

    $('#games').multiselect();

    // auto select values
    var sqdldr = $("#cur_sqd").val(),
        plt = $("#cur_plt").val(),
        pos = $("#cur_pos").val();

    $("#platoon option[value=" + plt + "]").attr("selected", "selected");
    $("#sqdldr option[value=" + sqdldr + "]").attr("selected", "selected");
    $("#position option[value=" + pos + "]").attr("selected", "selected");

    $("#edit-form").submit(function(event) {
        event.preventDefault();

        $("#edit-form :submit").html("<img src='assets/images/loading.gif' /> Updating profile information...").attr('class', 'btn btn-block btn-default disabled');

        var uid = $("#uid").val(),
            mid = $("#member_id").val(),
            fname = $("#forum_name").val(),
            platoon = $("#platoon").val(),
            sqdldr = $("#sqdldr").val(),
            blog = $("#battlelog").val(),
            recruiter = $("#recruiter").val(),
            position = $("#position").val();

        var played_games = $("#games option:selected").map(function() {
            return $(this).val();
        }).get();

        updateMember(uid, mid, fname, blog, platoon, sqdldr, position, recruiter, played_games);
    });

});

function updateMember(uid, mid, fname, blog, platoon, sqdldr, position, recruiter, played_games) {
    setTimeout(function() {
        $.post("do/update-member", {
                uid: uid,
                mid: mid,
                fname: fname,
                blog: blog,
                platoon: platoon,
                squad: sqdldr,
                position: position,
                recruiter: recruiter,
                played_games: played_games
            },

            function(data) {
                $("#edit-form :submit").html("Submit Info").attr('class', 'btn btn-block btn-success');
                if (data.success === false) {
                    if (data.battlelog === true) {
                        $("#edit-form .battlelog-group").addClass("has-error");
                    }
                    $("#edit-form .message").html(data.message).addClass("alert-danger").show();

                    return false;
                } else {
                    $("#edit-form .message").show().html(data.message).removeClass("alert-danger").addClass('alert-success').delay(1000).fadeOut();
                    $(".has-error").removeClass("has-error");
                }

            }, "json")
    }, 1000)
}