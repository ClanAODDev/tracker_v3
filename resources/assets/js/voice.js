function initVoice() {
    const $ = window.jQuery;

    if (!$ || typeof $.fn.DataTable !== 'function') {
        setTimeout(initVoice, 50);
        return;
    }

    if (!$('.members-table').length) {
        return;
    }
    const dataTable = $('table.members-table').DataTable({
        oLanguage: {
            sLengthMenu: '' // _MENU_
        }, columnDefs: [{
            orderable: false, className: 'select-checkbox', targets: 0
        }, {
            targets: 'col-hidden', visible: false
        },

        ], select: {
            style: 'os', selector: 'td:first-child',
        }, stateSave: true, paging: false, autoWidth: true, bInfo: false, searching: false, info: false,
    });
    dataTable.on("select", (e, t, a, d) => {
        const l = dataTable.rows($(".selected")).data().toArray().map((e) => e[6]);
        if (l.length >= 2) {
            $("#selected-data").show();
            $("#selected-data .status-text").text(`With selected (${l.length})`);
            $("#pm-member-data").val(l);
        }
    });
}

initVoice();