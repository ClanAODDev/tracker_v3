var Platoon = Platoon || {};

(function ($) {

    Platoon = {

        dataTable: null,

        setup: function () {
            this.handleMembers();
            this.handleSquadMembers();
            this.handleForumActivityChart();
            this.handleVoiceActivityChart();
            this.initAutocomplete();
            this.initTagFilter();
            this.initBulkTags();
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

        handleVoiceActivityChart: function () {

            var ctx = $('.voice-activity-chart');

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
                var self = this;
                self.dataTable = $('table.members-table').DataTable({
                    'initComplete': function (settings, json) {
                        setTimeout(function () {
                            $('.ld-loading').removeClass('ld-loading');
                            self.dataTable.columns.adjust();
                        }, 2000);
                    },
                    autoWidth: false, bInfo: false,
                    oLanguage: {
                        sLengthMenu: ''
                    },
                    columnDefs: [{
                        targets: 0, visible: false, orderable: false, searchable: false
                    }, {
                        targets: 8, visible: false
                    }, {
                        targets: 'no-search', searchable: false
                    }, {
                        targets: 'col-hidden', visible: false
                    }, {
                        'iDataSort': 1, 'aTargets': [4]
                    }, {
                        'iDataSort': 12, 'aTargets': [6]
                    }],
                    stateSave: true,
                    stateSaveParams: function(settings, data) {
                        data.columns[0].visible = false;
                        data.columns[3].visible = true;
                    },
                    stateLoadParams: function(settings, data) {
                        data.columns[0].visible = false;
                        data.columns[3].visible = true;
                    },
                    paging: false,
                });

                self.dataTable.column(0).visible(false);

                var resizeTimer;
                $(window).on('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        self.dataTable.columns.adjust().draw();
                    }, 100);
                });

                function updateToggleState(link, column) {
                    if (column.visible()) {
                        $(link).addClass('active');
                    } else {
                        $(link).removeClass('active');
                    }
                }

                $('a.toggle-vis').each(function () {
                    var column = self.dataTable.column($(this).data('column'));
                    updateToggleState(this, column);
                });

                $('a.toggle-vis').on('click', function (e) {
                    e.preventDefault();
                    var column = self.dataTable.column($(this).data('column'));
                    column.visible(!column.visible());
                    updateToggleState(this, column);
                });

                var $searchInput = $('.dataTables_filter input').removeClass('input-sm');
                $searchInput.attr({
                    'placeholder': 'Search Players',
                    'class': 'form-control'
                });

                $('#playerFilter').append($searchInput);
                $('.dataTables_filter label').remove();

                var $tagFilter = $('#tag-filter');
                if ($tagFilter.length) {
                    var $tagWrapper = $('<div class="tag-filter-wrapper"></div>');
                    $tagWrapper.append($tagFilter);
                    $('#playerFilter').append($tagWrapper);
                }

                var $bulkToggle = $('<button type="button" class="btn btn-default bulk-mode-toggle"><i class="fa fa-check-square-o"></i> Bulk Mode</button>');
                $('#playerFilter').append($bulkToggle);

                $('.no-sort').removeClass('sorting');

                var bulkModeActive = false;

                function toggleBulkMode() {
                    bulkModeActive = !bulkModeActive;
                    var checkboxCol = self.dataTable.column(0);
                    checkboxCol.visible(bulkModeActive);

                    if (bulkModeActive) {
                        $bulkToggle.addClass('active btn-accent').removeClass('btn-default');
                        $bulkToggle.html('<i class="fa fa-check-circle"></i> Exit Bulk Mode');
                        $('.members-table').addClass('bulk-mode');
                    } else {
                        $bulkToggle.removeClass('active btn-accent').addClass('btn-default');
                        $bulkToggle.html('<i class="fa fa-check-square-o"></i> Bulk Mode');
                        $('.members-table').removeClass('bulk-mode');
                        $('.member-checkbox, #select-all-members').prop('checked', false);
                        updateBulkSelection();
                    }
                }

                $bulkToggle.on('click', function() {
                    toggleBulkMode();
                });

                function updateBulkSelection() {
                    var selected = [];
                    $('.member-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    if (selected.length >= 2) {
                        $("#selected-data").css('display', 'block');
                        $("#selected-data .status-text").text(selected.length + " members selected");
                        $("#pm-member-data").val(selected);
                        $("#tag-member-data").val(selected);
                    } else {
                        $("#selected-data").hide();
                        $("#pm-member-data").val('');
                        $("#tag-member-data").val('');
                    }

                    var total = $('.member-checkbox').length;
                    var checked = $('.member-checkbox:checked').length;
                    $('#select-all-members').prop('checked', checked > 0 && checked === total);
                    $('#select-all-members').prop('indeterminate', checked > 0 && checked < total);
                }

                $(document).on('click', '.bulk-action-close', function() {
                    $('.member-checkbox, #select-all-members').prop('checked', false);
                    updateBulkSelection();
                });

                $(document).on('change', '.member-checkbox', function() {
                    updateBulkSelection();
                });

                $(document).on('change', '#select-all-members', function() {
                    var isChecked = $(this).prop('checked');
                    $('.member-checkbox').prop('checked', isChecked);
                    updateBulkSelection();
                });

                var isDragging = false;
                var dragStartRow = null;
                var dragCheckState = true;

                $(document).on('mousedown', '.members-table.bulk-mode tbody tr', function(e) {
                    if ($(e.target).closest('a, button, input, .btn').length) return;
                    if (e.which !== 1) return;

                    isDragging = true;
                    dragStartRow = $(this).index();
                    var $checkbox = $(this).find('.member-checkbox');
                    dragCheckState = !$checkbox.prop('checked');
                    $checkbox.prop('checked', dragCheckState);
                    updateBulkSelection();
                    e.preventDefault();
                });

                $(document).on('mouseenter', '.members-table.bulk-mode tbody tr', function() {
                    if (!isDragging) return;
                    var $checkbox = $(this).find('.member-checkbox');
                    $checkbox.prop('checked', dragCheckState);
                    updateBulkSelection();
                });

                $(document).on('mouseup', function() {
                    isDragging = false;
                    dragStartRow = null;
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

        initTagFilter: function () {
            var self = this;
            var $tagFilter = $('#tag-filter');

            if (!$tagFilter.length || !self.dataTable) {
                return;
            }

            $tagFilter.select2({
                placeholder: 'Filter by tag',
                allowClear: true
            }).on('change', function () {
                self.dataTable.draw();
            });

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var selectedTags = $tagFilter.val();

                if (!selectedTags || selectedTags.length === 0) {
                    return true;
                }

                var memberTags = data[13] ? data[13].split(',') : [];

                for (var i = 0; i < selectedTags.length; i++) {
                    if (memberTags.indexOf(selectedTags[i]) !== -1) {
                        return true;
                    }
                }

                return false;
            });
        },

        initBulkTags: function () {
            var $modal = $('#bulk-tags-modal');
            if (!$modal.length) return;

            var csrfToken = $('meta[name=csrf-token]').attr('content');

            function getSelectedMemberIds() {
                return $('#tag-member-data').val() ? $('#tag-member-data').val().split(',') : [];
            }

            function getSelectedTagIds() {
                var tagIds = [];
                $('.bulk-tag-checkbox:checked').each(function() {
                    tagIds.push($(this).val());
                });
                return tagIds;
            }

            function updateButtonState() {
                var hasSelectedTags = getSelectedTagIds().length > 0;
                $('#bulk-assign-tags, #bulk-remove-tags').prop('disabled', !hasSelectedTags);
            }

            $modal.on('show.bs.modal', function() {
                var memberCount = getSelectedMemberIds().length;
                $('#bulk-tags-member-count').text(memberCount + ' member' + (memberCount !== 1 ? 's' : '') + ' selected');
                $('.bulk-tag-checkbox').prop('checked', false).trigger('change');
            });

            $(document).on('change', '.bulk-tag-checkbox', function() {
                var $badge = $(this).siblings('.bulk-tag-badge');
                if (this.checked) {
                    $badge.addClass('selected');
                } else {
                    $badge.removeClass('selected');
                }
                updateButtonState();
            });

            function bulkTagAction(action) {
                var memberIds = getSelectedMemberIds();
                var tagIds = getSelectedTagIds();
                var url = $modal.data('store-url');

                if (tagIds.length === 0) {
                    toastr.warning('Please select at least one tag');
                    return;
                }

                var $btns = $('#bulk-assign-tags, #bulk-remove-tags');
                $btns.prop('disabled', true);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        member_ids: memberIds,
                        tags: tagIds,
                        action: action,
                        _token: csrfToken
                    },
                    success: function() {
                        var msg = action === 'assign' ? 'Tags assigned to ' : 'Tags removed from ';
                        toastr.success(msg + memberIds.length + ' members');
                        $modal.modal('hide');
                        location.reload();
                    },
                    error: function() {
                        toastr.error('Failed to ' + action + ' tags');
                    },
                    complete: function() {
                        updateButtonState();
                    }
                });
            }

            $(document).on('click', '#bulk-assign-tags', function() {
                bulkTagAction('assign');
            });

            $(document).on('click', '#bulk-remove-tags', function() {
                bulkTagAction('remove');
            });
        },
    };
})(window.jQuery);

Platoon.setup();