var Platoon = Platoon || {};

(function ($) {

    Platoon = {

        setup: function () {
            this.handleMembers();
        },

        handleMembers: function () {

            var platoonNum = parseInt($('.platoon-number').text()),
                formattedDate = new Date(),
                d = formattedDate.getDate(),
                m = (formattedDate.getMonth() + 1),
                y = formattedDate.getFullYear(),
                nowDate = y + "-" + m + "-" + d,
                selected = new Array();

            /**
             * Handle platoons, squads
             */
            $('table.members-table').DataTable({
                autoWidth: true, bInfo: false,
                columnDefs: [{
                    targets: 'no-search', searchable: false
                }, {
                    targets: 'col-hidden', visible: false, searchable: false
                }, {
                    // sort rank by rank id
                    "iDataSort": 0, "aTargets": [3]
                }, {
                    // sort activity by last login date
                    "iDataSort": 1, "aTargets": [5]
                }],
                stateSave: true, paging: false,
            });

            $(".dataTables_filter input").appendTo("#playerFilter").removeClass('input-sm');

            $("#playerFilter input").attr({
                "placeholder": "Search Players",
                "class": "form-control"
            });

            $(".dataTables_filter label").remove();

            $(".no-sort").removeClass("sorting");

            // omit leader field if using TBA
            $("#is_tba").click(function () {
                toggleTBA();
            });

            toggleTBA();

            function toggleTBA() {
                if ($('#is_tba').is(':checked')) {
                    $("#leader_id, #leader").prop("disabled", true).val('');
                } else {
                    $("#leader_id, #leader").prop("disabled", false)
                }
            }

        },
    }
})(jQuery);

Platoon.setup();