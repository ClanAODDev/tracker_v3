var Platoon = Platoon || {};

function initPlatoon() {
    var $ = window.jQuery;

    if (!$ || typeof $.fn.DataTable !== 'function') {
        setTimeout(initPlatoon, 50);
        return;
    }

    if (document.readyState !== 'complete' && document.readyState !== 'interactive') {
        setTimeout(initPlatoon, 50);
        return;
    }

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
            this.initBulkTransfer();
            this.initSquadAssignments();
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
            if (!$('.manage-squads-grid').length) {
                return;
            }

            var $sortableLists = $('.mod-plt .sortable');

            function updateUnassignedStatus() {
                var $unassignedOrganizer = $('.unassigned-organizer');
                var $unassignedList = $unassignedOrganizer.find('.manage-sortable-list');
                var count = $unassignedList.find('li').length;
                var $header = $unassignedOrganizer.find('.unassigned-organizer-header span');

                if (count === 0) {
                    $unassignedOrganizer.removeClass('unassigned-organizer').addClass('all-assigned-organizer');
                    $header.html('<i class="fa fa-check-circle text-success"></i> All members assigned to a squad');
                } else {
                    $unassignedOrganizer.removeClass('all-assigned-organizer').addClass('unassigned-organizer');
                    $header.html('<i class="fa fa-exclamation-triangle text-warning"></i> ' + count + ' ' + (count === 1 ? 'member' : 'members') + ' not assigned to a squad');
                }
            }

            $sortableLists.sortable({
                connectWith: '.mod-plt .sortable',
                placeholder: 'ui-sortable-placeholder',
                forcePlaceholderSize: true,
                revert: 150,
                tolerance: 'pointer',
                over: function(event, ui) {
                    $(this).closest('.manage-squad-card, .unassign-drop-zone').addClass('drag-hover');
                },
                out: function(event, ui) {
                    $(this).closest('.manage-squad-card, .unassign-drop-zone').removeClass('drag-hover');
                },
                receive: function (event, ui) {
                    var $target = $(this);
                    var $sender = $(ui.sender);
                    var memberId = $(ui.item).attr('data-member-id');
                    var targetSquadId = $target.attr('data-squad-id');
                    var senderCount = $sender.find('li').length;
                    var receiverCount = $target.find('li').length;

                    $sender.closest('.manage-squad-card').find('.count').text(senderCount);
                    $target.closest('.manage-squad-card').find('.count').text(receiverCount);

                    updateUnassignedStatus();

                    if (targetSquadId === '0') {
                        $(ui.item).fadeOut(200, function() {
                            $(this).remove();
                        });
                    }

                    $.ajax({
                        type: 'POST',
                        url: window.Laravel.appPath + '/members/assign-squad',
                        data: {
                            member_id: memberId,
                            squad_id: targetSquadId,
                            _token: $('meta[name=csrf-token]').attr('content')
                        },
                        dataType: 'json',
                        success: function () {
                            if (targetSquadId === '0') {
                                toastr.success('Member unassigned from platoon');
                            } else {
                                toastr.success('Member reassigned!');
                            }
                        },
                        error: function () {
                            toastr.error('Failed to reassign member');
                        }
                    });
                },
                stop: function(event, ui) {
                    $('.manage-squad-card, .unassign-drop-zone').removeClass('drag-hover');
                }
            });
        },

        initSquadAssignments: function () {
            var self = this;
            var organizeMode = false;

            $('.organize-squads-btn').on('click', function() {
                organizeMode = !organizeMode;
                var $btn = $(this);

                if (organizeMode) {
                    $btn.html('<i class="fa fa-check"></i> Done');
                    $btn.removeClass('btn-accent').addClass('btn-success');
                    $('.unassigned-organizer-members').slideDown(200);
                    $('.squad-assignments-section').addClass('organize-mode');
                    self.enableSquadDragDrop();
                } else {
                    $btn.html('<i class="fa fa-arrows-alt"></i> Organize');
                    $btn.removeClass('btn-success').addClass('btn-accent');
                    $('.unassigned-organizer-members').slideUp(200);
                    $('.squad-assignments-section').removeClass('organize-mode');
                }
            });

            $('.squad-drop-target').on('click', function(e) {
                if ($('.squad-assignments-section').hasClass('organize-mode')) {
                    e.preventDefault();
                }
            });

            if (new URLSearchParams(window.location.search).get('organize') === '1') {
                $('.organize-squads-btn').trigger('click');
                var $section = $('.squad-assignments-section');
                if ($section.length) {
                    $('html, body').animate({ scrollTop: $section.offset().top - 20 }, 400);
                }
            }
        },

        enableSquadDragDrop: function () {
            var self = this;

            if ($('.unassigned-squad-member').data('ui-draggable')) {
                return;
            }

            $('.unassigned-squad-member').draggable({
                revert: true,
                revertDuration: 200,
                zIndex: 1000,
                cursor: 'grabbing',
                helper: 'clone',
                appendTo: 'body'
            });

            $('.squad-drop-target').droppable({
                hoverClass: 'squad-drop-target--active',
                drop: function (event, ui) {
                    var $target = $(this);
                    var memberId = ui.draggable.attr('data-member-id');
                    var squadId = $target.attr('data-squad-id');

                    $.ajax({
                        type: 'POST',
                        url: window.Laravel.appPath + '/members/assign-squad',
                        data: {
                            member_id: memberId,
                            squad_id: squadId,
                            _token: $('meta[name=csrf-token]').attr('content')
                        },
                        dataType: 'json',
                        success: function () {
                            toastr.success('Member assigned to squad!');
                            $(ui.draggable).fadeOut(200, function() {
                                $(this).remove();
                                if ($('.unassigned-squad-member').length < 1) {
                                    $('.unassigned-organizer').slideUp();
                                    $('.squad-assignments-section').removeClass('organize-mode');
                                }
                            });
                            var $count = $target.find('.squad-stat-badge .fa-users').parent();
                            var currentCount = parseInt($count.text().trim()) || 0;
                            $count.html('<i class="fa fa-users"></i> ' + (currentCount + 1));
                        },
                        error: function () {
                            toastr.error('Failed to assign member to squad');
                        }
                    });
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
            if (!$('.members-table').length) {
                return;
            }

            var self = this;
            var $table = $('table.members-table');

            var cols = {};
            $table.find('thead th[data-col]').each(function(index) {
                cols[$(this).data('col')] = index;
            });

            self.dataTable = $table.DataTable({
                initComplete: function (settings, json) {
                    setTimeout(function () {
                        $('.ld-loading').removeClass('ld-loading');
                        self.dataTable.columns.adjust();
                    }, 2000);
                },
                autoWidth: false,
                info: false,
                paging: false,
                stateSave: true,
                order: [[cols['member'], 'asc']],
                language: {
                    lengthMenu: ''
                },
                columnDefs: [
                    { targets: cols['checkbox'], visible: false, orderable: false, searchable: false },
                    { targets: cols['tags'], visible: false },
                    { targets: 'no-sort', orderable: false },
                    { targets: 'no-search', searchable: false },
                    { targets: 'col-hidden', visible: false },
                    { targets: cols['rank'], orderData: cols['rank-id'] },
                    { targets: cols['discord-activity'], orderData: cols['discord-activity-date'] }
                ],
                stateSaveParams: function(settings, data) {
                    data.columns[cols['checkbox']].visible = false;
                    data.columns[cols['member']].visible = true;
                    if (data.order && data.order[0] && data.order[0][0] === cols['checkbox']) {
                        data.order = [[cols['member'], 'asc']];
                    }
                },
                stateLoadParams: function(settings, data) {
                    data.columns[cols['checkbox']].visible = false;
                    data.columns[cols['member']].visible = true;
                    if (data.order && data.order[0] && data.order[0][0] === cols['checkbox']) {
                        data.order = [[cols['member'], 'asc']];
                    }
                }
            });

            self.cols = cols;

            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    self.dataTable.columns.adjust().draw();
                }, 100);
            });

            function getColumnIndex(name) {
                return cols[name];
            }

            function updateToggleState(link, column) {
                if (column.visible()) {
                    $(link).addClass('active');
                } else {
                    $(link).removeClass('active');
                }
            }

            $('a.toggle-vis').each(function () {
                var colName = $(this).data('column');
                var colIndex = getColumnIndex(colName);
                if (colIndex !== undefined) {
                    var column = self.dataTable.column(colIndex);
                    updateToggleState(this, column);
                }
            });

            $('a.toggle-vis').on('click', function (e) {
                e.preventDefault();
                var colName = $(this).data('column');
                var colIndex = getColumnIndex(colName);
                if (colIndex !== undefined) {
                    var column = self.dataTable.column(colIndex);
                    column.visible(!column.visible());
                    updateToggleState(this, column);
                }
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
                var checkboxCol = self.dataTable.column(cols['checkbox']);
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

                var hasParttimers = $('.member-checkbox:checked[data-parttimer="1"]').length > 0;
                var $transferBtn = $('#bulk-transfer-btn');

                if (hasParttimers) {
                    $transferBtn.prop('disabled', true).addClass('disabled');
                    $transferBtn.attr('title', 'Cannot transfer: selection includes part-time members');
                } else {
                    $transferBtn.prop('disabled', false).removeClass('disabled');
                    $transferBtn.removeAttr('title');
                }

                if (selected.length >= 2) {
                    $("#selected-data").css('display', 'block');
                    $("#selected-data .status-text").text(selected.length + " members selected");
                    $("#pm-member-data").val(selected);
                    $("#tag-member-data").val(selected);
                    $("#transfer-member-data").val(selected);
                } else {
                    $("#selected-data").hide();
                    $("#pm-member-data").val('');
                    $("#tag-member-data").val('');
                    $("#transfer-member-data").val('');
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

            if (!$tagFilter.length || !self.dataTable || !self.cols) {
                return;
            }

            var tagIdColIndex = self.cols['tag-ids'];

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

                var memberTags = data[tagIdColIndex] ? data[tagIdColIndex].split(',') : [];

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

        initBulkTransfer: function () {
            var $modal = $('#bulk-transfer-modal');
            if (!$modal.length) return;

            var csrfToken = $('meta[name=csrf-token]').attr('content');
            var platoonsData = null;

            function getSelectedMemberIds() {
                return $('#transfer-member-data').val() ? $('#transfer-member-data').val().split(',') : [];
            }

            function updateSubmitState() {
                var platoonId = $('#transfer-platoon').val();
                $('#bulk-transfer-submit').prop('disabled', !platoonId);
            }

            function loadPlatoons() {
                if (platoonsData) {
                    populatePlatoons();
                    return;
                }

                var url = $modal.data('platoons-url');
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        platoonsData = response.platoons;
                        populatePlatoons();
                    },
                    error: function() {
                        toastr.error('Failed to load platoons');
                    }
                });
            }

            function populatePlatoons() {
                var $platoonSelect = $('#transfer-platoon');
                $platoonSelect.find('option:not(:first)').remove();

                platoonsData.forEach(function(platoon) {
                    $platoonSelect.append(
                        $('<option>', { value: platoon.id, text: platoon.name })
                    );
                });
            }

            function populateSquads(platoonId) {
                var $squadSelect = $('#transfer-squad');
                $squadSelect.find('option:not(:first)').remove();

                if (!platoonId) {
                    $squadSelect.prop('disabled', true);
                    return;
                }

                var platoon = platoonsData.find(function(p) {
                    return p.id == platoonId;
                });

                if (platoon && platoon.squads.length > 0) {
                    platoon.squads.forEach(function(squad) {
                        $squadSelect.append(
                            $('<option>', { value: squad.id, text: squad.name })
                        );
                    });
                    $squadSelect.prop('disabled', false);
                } else {
                    $squadSelect.prop('disabled', true);
                }
            }

            $modal.on('show.bs.modal', function() {
                var hasParttimers = $modal.data('parttimers') === 'true';

                if (hasParttimers) {
                    $('#bulk-transfer-parttimer-warning').show();
                    $('#bulk-transfer-form-content').hide();
                    $('#bulk-transfer-submit').hide();
                    return;
                }

                $('#bulk-transfer-parttimer-warning').hide();
                $('#bulk-transfer-form-content').show();
                $('#bulk-transfer-submit').show();

                var memberCount = getSelectedMemberIds().length;
                $('#bulk-transfer-member-count').text(memberCount + ' member' + (memberCount !== 1 ? 's' : '') + ' selected');
                $('#transfer-platoon').val('');
                $('#transfer-squad').val('').prop('disabled', true);
                updateSubmitState();
                loadPlatoons();
            });

            $(document).on('change', '#transfer-platoon', function() {
                var platoonId = $(this).val();
                populateSquads(platoonId);
                updateSubmitState();
            });

            $(document).on('click', '#bulk-transfer-submit', function() {
                var memberIds = getSelectedMemberIds();
                var platoonId = $('#transfer-platoon').val();
                var squadId = $('#transfer-squad').val();
                var url = $modal.data('store-url');

                if (!platoonId) {
                    toastr.warning('Please select a platoon');
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Transferring...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        member_ids: memberIds,
                        platoon_id: platoonId,
                        squad_id: squadId || null,
                        _token: csrfToken
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $modal.modal('hide');
                        location.reload();
                    },
                    error: function() {
                        toastr.error('Failed to transfer members');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fa fa-exchange-alt"></i> Transfer');
                        updateSubmitState();
                    }
                });
            });
        },
    };

    Platoon.setup();
}

initPlatoon();