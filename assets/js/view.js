$(function() {

    $('#games').multiselect({
        includeSelectAllOption: true,
        allSelectedText: 'All games selected'
    });

    // auto select values
    var sqdldr = $("#cur_sqd").val(),
        plt = $("#cur_plt").val(),
        pos = $("#cur_pos").val();

    $("#platoon option[value=" + plt + "]").attr("selected", "selected");
    $("#sqdldr option[value=" + sqdldr + "]").attr("selected", "selected");
    $("#position option[value=" + pos + "]").attr("selected", "selected");

    $("#edit-form").submit(function(event) {
        event.preventDefault();

        $("#edit-form .message").html("Updating member information. Please wait...").addClass("alert-info").show();
        $("#edit-form :submit").html("Saving...").attr('class', 'btn btn-default disabled');

        var formData = $("#edit-form").serializeArray(),
            dataObj = {};

        var played_games = $("#games option:selected").map(function() {
            return $(this).val();
        }).get();

        formData.concat(played_games);

        $(formData).each(function(i, field) {
            dataObj[field.name] = field.value;
        });


        console.log(formData);
        return false;

        updateMember(data);


    });

    $(".user-form-control").change(function() {
        $("#user_change").attr('value', 1);
    });

});

function updateMember(data) {
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
                $("#edit-form :submit").html("Submit Info").attr('class', 'btn btn-success');
                if (data.success === false) {
                    if (data.battlelog === true) {
                        $("#edit-form .battlelog-group").addClass("has-error");
                    }
                    $("#edit-form .message").html(data.message).addClass("alert-danger").show();

                    return false;
                } else {
                    $("#edit-form .message").show().html(data.message).removeClass("alert-danger alert-info").addClass('alert-success').delay(1000).fadeOut();
                    $(".has-error").removeClass("has-error");
                }

            }, "json")
    }, 1000)
}