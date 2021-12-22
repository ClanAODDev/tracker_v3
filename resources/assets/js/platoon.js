let Platoon = Platoon || {};

(function ($) {

    Platoon = {

        setup: function () {
            this.handleMembers();
            this.handleSquadMembers();
            this.handleForumActivityChart();
            this.handleTSActivityChart();
            this.initAutocomplete();
        },

        handleForumActivityChart: function () {

            var ctx = $('.forum-activity-chart');

            if (ctx.length) {
                var myDoughnutChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                data: ctx.data('values'),
                                backgroundColor: ctx.data('colors'),
                                borderWidth: 0,
                            }],
                        labels: ctx.data('labels'),
                    },
                    options: {
                        rotation: 1 * Math.PI,
                        circumference: 1 * Math.PI,
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 5,
                                fontColor: '#949ba2'
                            },
                            label: {
                                fullWidth: false
                            }
                        }
                    }
                });
            }
        },
        handleTSActivityChart: function () {

            var ctx = $('.ts-activity-chart');

            if (ctx.length) {
                var myDoughnutChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                data: ctx.data('values'),
                                backgroundColor: ctx.data('colors'),
                                borderWidth: 0,
                            }],
                        labels: ctx.data('labels'),
                    },
                    options: {
                        rotation: 1 * Math.PI,
                        circumference: 1 * Math.PI,
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 5,
                                fontColor: '#949ba2'
                            },
                            label: {
                                fullWidth: false
                            }
                        }
                    }
                });
            }
        },
        handleSquadMembers: function () {
            $('.sortable-squad').sortable({
                revert: 'invalid',
            });

            $('.draggable').draggable({
                connectToSortable: 'ul',
                revert: 'invalid',
                scroll: true,
                scrollSensitivity: 100
            });

            let itemMoved, targetSquad, senderLength, receiverLength;
            $('.mod-plt .sortable').sortable({
                connectWith: 'ul',
                placeholder: 'ui-state-highlight',
                forcePlaceholderSize: true,
                revert: 'invalid',
                receive: function (event, ui) {
                    itemMoved = $(ui.item).attr('data-member-id');
                    targetSquad = $(this).attr('data-squad-id');
                    senderLength = $(ui.sender).find('li').length;
                    receiverLength = $(this).find('li').length;
                    if (undefined === targetSquad) {
                        toastr.error('You cannot move members to the unassigned list');
                        $('.mod-plt .sortable').sortable('cancel');
                    } else {
                        // is genpop empty?
                        if ($('.genpop').find('li').length < 1) {
                            $('.genpop').fadeOut();
                        }
                        // update squad counts
                        $(ui.sender).parent().find('.count').text(senderLength);
                        $(this).parent().find('.count').text(receiverLength).effect('highlight');
                        $.ajax({
                            type: 'POST',
                            url: window.Laravel.appPath + '/members/assign-squad',
                            data: {
                                member_id: itemMoved,
                                squad_id: targetSquad,
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            dataType: 'json',
                            success: function () {
                                toastr.success('Member reassigned!');
                            },
                            error: function () {
                                toastr.error('Something bad happened...');
                            }
                        });
                    }
                }
            });
        },
        initAutocomplete: function () {
            $('#leader').bootcomplete({
                url: window.Laravel.appPath + '/search-member/',
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: {_token: $('meta[name=csrf-token]').attr('content')}
            });
        },

        handleMembers: function () {

            var platoonNum = parseInt($('.platoon-number').text()),
                formattedDate = new Date(),
                d = formattedDate.getDate(),
                m = (formattedDate.getMonth() + 1),
                y = formattedDate.getFullYear(),
                nowDate = y + '-' + m + '-' + d,
                selected = new Array();

            /**
             * Handle platoons, squads, members tables
             */
            if ($('.members-table').length) {
                var dataTable = $('table.members-table').DataTable({
                    'initComplete': function (settings, json) {
                        setTimeout(function () {
                            $('.ld-loading').removeClass('ld-loading');
                        }, 2000);
                    },
                    autoWidth: true, bInfo: false,
                    oLanguage: {
                        sLengthMenu: '' // _MENU_
                    },
                    columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0
                    }, {
                        targets: 'no-search', searchable: false
                    }, {
                        targets: 'col-hidden', visible: false
                    }, {
                        // sort rank by rank id
                        'iDataSort': 0, 'aTargets': [3]
                    }, {
                        // sort activity by last login date
                        'iDataSort': 1, 'aTargets': [5]
                    },
                        {
                            // sort ts activity by date
                            'iDataSort': 7, 'aTargets': [6]
                        }
                    ],
                    select: {
                        style: 'os',
                        selector: 'td:first-child',
                    },
                    stateSave: true, paging: false,
                });

                $('a.toggle-vis').on('click', function (e) {
                    e.preventDefault();

                    // Get the column API object
                    var column = dataTable.column($(this).attr('data-column'));

                    // Toggle the visibility
                    column.visible(!column.visible());
                });

                $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm');

                $('#playerFilter input').attr({
                    'placeholder': 'Search Players',
                    'class': 'form-control'
                });

                $('.dataTables_filter label').remove();

                $('.no-sort').removeClass('sorting');

                // handle PM selection
                dataTable.on("select", function (e, t, a, d) {
                    let l = dataTable.rows($(".selected")).data().toArray().map(function (e) {
                        return e[11]
                    });
                    if (l.length >= 2) {
                        $("#selected-data").show(),
                            $("#selected-data .status-text").text("With selected (" + l.length + ")"),
                            $("#pm-member-data").val(l);
                    }
                });
            }

            // omit leader field if using TBA
            $('#is_tba').click(function () {
                toggleTBA();
            });

            toggleTBA();

            function toggleTBA() {
                if ($('#is_tba').is(':checked')) {
                    $('#leader_id, #leader').prop('disabled', true).val('');
                } else {
                    $('#leader_id, #leader').prop('disabled', false);
                }
            }

        },
    };
})(jQuery);

Platoon.setup();