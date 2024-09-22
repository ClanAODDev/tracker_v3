if ($('.members-table').length) {
    var dataTable = $('table.members-table').DataTable({
        'initComplete': function (settings, json) {
            setTimeout(function () {
                $('.ld-loading').removeClass('ld-loading');
            }, 2000);
        },
        oLanguage: {
            sLengthMenu: '' // _MENU_
        },
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0
        },
            {
                targets: 'col-hidden', visible: false
            },

        ],
        select: {
            style: 'os',
            selector: 'td:first-child',
        },
        stateSave: true, paging: false,
        autoWidth: true, bInfo: false,
        searching: false, info: false,
    });
    // handle PM selection
    dataTable.on("select", function (e, t, a, d) {
        let l = dataTable.rows($(".selected")).data().toArray().map(function (e) {
            return e[6]
        });
        if (l.length >= 2) {
            $("#selected-data").show(),
                $("#selected-data .status-text").text("With selected (" + l.length + ")"),
                $("#pm-member-data").val(l);
        }
    });
}