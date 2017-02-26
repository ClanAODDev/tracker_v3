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
                "sDom": 'T<"clear">tfrip',
                "order": [],
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
                "bServerSide": false,
                "drawCallback": function (settings) {
                    $("#member-footer").empty();
                    $("#members-table_info").contents().appendTo("#member-footer");
                },

                "oTableTools": {
                    "sRowSelect": "multi",
                    "sSwfPath": "/swf/copy_csv_xls_pdf.swf",
                    "aButtons": [{

                        "sExtends": "text",
                        "fnSelect": function (nButton, oConfig, nRow) {
                            console.log($(nRow).data('id') + " clicked")
                        },
                        "sExtends": "collection",
                        "sButtonText": "",
                        "mColumns": "visible",
                        "aButtons": ["select_all", "select_none", {
                            "sExtends": "pdf",
                            "sPdfOrientation": "landscape",
                            "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".pdf",
                            "mColumns": "visible"
                        }, {
                            "sExtends": "csv",
                            "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".csv",
                            "mColumns": "visible"
                        }],
                        "bSelectedOnly": true
                    }]
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