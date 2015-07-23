$(function() {

    $(".user-form-control").change(function() {
        $("#user_change").attr('value', 1);
    });

    $('#games').multiselect({
        includeSelectAllOption: true,
        allSelectedText: 'All games selected'
    });

    // auto select values
    var squad_id = $("#cur_squad_id").val(),
        platoon_id = $("#cur_platoon_id").val(),
        position_id = $("#cur_position_id").val();

    $("#platoon_id option[value=" + platoon_id + "]").attr("selected", "selected");
    $("#squad_id option[value=" + squad_id + "]").attr("selected", "selected");
    $("#position_id option[value=" + position_id + "]").attr("selected", "selected");

    $("#submit-form").click(function(event) {
        event.preventDefault();

        $(".modal .message").html("Updating member information. Please wait...").addClass("alert-info").show();
        $(".save-btn").html("Saving...").attr('class', 'btn btn-default disabled save-btn');

        var memberData = $("#member-form,#alias-form,#div-form").serializeArray(),
            userData = $("#user-form").serializeArray(),
            userAliases = $("#alias-form").serializeArray(),
            played_games = $("#games option:selected").map(function() {
                return $(this).val();
            }).get();

        updateMember(memberData, userData, userAliases, played_games);
    });

    // erase existing handles from dropdown
    function cleanupHandles() {
        $("tr.member-handle").each(function() {
            var handleType = $(this).attr('data-handle-type');
            $("#alias-selector option[value*='" + handleType + "']").remove();
        });
    }

    // cleanup handles for the first time
    cleanupHandles();

    // clone all available handles in case we need it later
    var alias_options = $("#alias-selector option").clone();

    $("button.add-alias").click(function(e) {
        e.preventDefault();
        var $this = $("#alias-selector").find(":selected"),
            type = $this.attr('value'),
            description = $this.attr('data-description'),
            name = $this.attr('data-type'),
            $newRow = $("<tr class=\"member-handle\" data-handle-type='" + type + "'><td><strong>" + description + "</strong></td><td><input placeholder='Alias name' type='text' class='form-control' name='" + name + "' required /></td><td><button class=\"btn btn-danger btn-block rem-alias\"><i class=\"fa fa-minus fa-lg\"></i></button></td></tr>");
        $("#aliases tr:last").before($newRow);
        $newRow.effect("highlight", {}, 1000);
        if ($("#alias-selector option").length > 1) {
            $this.remove();
        } else {
            $(this).closest("tr").fadeOut();
        }
    });

    $(".modal").delegate(".rem-alias", "click", function(e) {
        e.preventDefault();
        var $this = $(this).closest("tr");
        $this.remove();

        // restore all options and then cleanup
        $("#alias-selector").html(alias_options.clone());
        cleanupHandles();

        // restore add form if it went away
        $("#alias-selector").closest("tr").fadeIn();

    });

});

function updateMember(memberData, userData, userAliases, played_games) {
    setTimeout(function() {

        var memberObj = {},
            userObj = {},
            aliasesObj = {};

        $(memberData).each(function(i, field) {
            memberObj[field.name] = field.value;
        });

        $(userData).each(function(i, field) {
            userObj[field.name] = field.value;
        });

        $(userAliases).each(function(i, field) {
            aliasesObj[field.name] = field.value;
        });

        $.post("do/update-member", {
                memberData: memberObj,
                userData: userObj,
                userAliases: aliasesObj,
                played_games: played_games
            },

            function(data) {
                $(".save-btn").html("Submit Info").attr('class', 'btn btn-success save-btn');
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
