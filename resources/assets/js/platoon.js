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

            $('table.members-table').DataTable({
                "autoWidth": true,
                "columnDefs": [{
                    "targets": 'no-search',
                    "searchable": false
                }, {
                    "targets": 'col-hidden',
                    "visible": false,
                    "searchable": false
                }, {
                    // sort rank by rank id
                    "iDataSort": 0,
                    "aTargets": [3]
                }, {
                    // sort activity by last login date
                    "iDataSort": 1,
                    "aTargets": [5]
                }],
                stateSave: false,
                paging: false,
                "drawCallback": function (settings) {
                    $("#member-footer").empty();
                    $("#members-table_info").contents().appendTo("#member-footer");
                }
            });

            $(".dataTables_filter input").appendTo("#playerFilter").removeClass('input-sm');

            $("#playerFilter input").attr({
                "placeholder": "Search Players",
                "class": "form-control"
            });

            $(".dataTables_info").addClass('panel-footer text-center text-muted')

            $(".dataTables_filter label").remove();

            $(".no-sort").removeClass("sorting");

        },
    }
})(jQuery);

Platoon.setup();