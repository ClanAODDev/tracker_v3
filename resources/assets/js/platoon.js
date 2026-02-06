var Platoon = Platoon || {};

function initPlatoon() {
    const $ = window.jQuery;

    if (!$ || typeof $.fn.DataTable !== 'function' || typeof $.fn.bootcomplete !== 'function' || typeof $.fn.select2 !== 'function') {
        setTimeout(initPlatoon, 50);
        return;
    }

    if (document.readyState !== 'complete' && document.readyState !== 'interactive') {
        setTimeout(initPlatoon, 50);
        return;
    }

    const csrfToken = $('meta[name=csrf-token]').attr('content');

    Platoon = {

        dataTable: null,

        setup() {
            this.handleMembers();
            this.handleSquadMembers();
            this.handleVoiceActivityChart();
            this.initAutocomplete();
            this.initTagFilter();
            this.initBulkTags();
            this.initBulkTransfer();
            this.initSquadAssignments();
            this.initBulkReminder();
        },

        handleVoiceActivityChart() {
            const canvas = document.getElementById('voice-activity-chart');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const values = JSON.parse(canvas.dataset.values || '[]');
            const labels = JSON.parse(canvas.dataset.labels || '[]');
            const colors = JSON.parse(canvas.dataset.colors || '[]');

            if (!values || !labels || values.length === 0) {
                return;
            }

            const styles = getComputedStyle(document.documentElement);
            const textColor = styles.getPropertyValue('--color-text-light').trim() || '#949ba2';
            const bgColor = styles.getPropertyValue('--color-bg-panel').trim() || '#1a1d21';
            const total = values.reduce((a, b) => a + b, 0);

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: bgColor,
                        borderWidth: 1,
                        hoverBorderColor: bgColor,
                        hoverOffset: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 11
                                },
                                padding: 12,
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: { size: 12 },
                            bodyFont: { size: 11 },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: (context) => {
                                    const pct = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                    return `${context.label}: ${context.parsed} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },

        handleSquadMembers() {
            if (!$('.manage-squads-grid').length) {
                return;
            }

            const $sortableLists = $('.mod-plt .sortable');

            const updateUnassignedStatus = () => {
                const $unassignedOrganizer = $('.unassigned-organizer');
                const $unassignedList = $unassignedOrganizer.find('.manage-sortable-list');
                const count = $unassignedList.find('li').length;
                const $header = $unassignedOrganizer.find('.unassigned-organizer-header span');

                if (count === 0) {
                    $unassignedOrganizer.removeClass('unassigned-organizer').addClass('all-assigned-organizer');
                    $header.html('<i class="fa fa-check-circle text-success"></i> All members assigned to a squad');
                } else {
                    $unassignedOrganizer.removeClass('all-assigned-organizer').addClass('unassigned-organizer');
                    $header.html(`<i class="fa fa-exclamation-triangle text-warning"></i> ${count} ${count === 1 ? 'member' : 'members'} not assigned to a squad`);
                }
            };

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
                    const $target = $(this);
                    const $sender = $(ui.sender);
                    const memberId = $(ui.item).attr('data-member-id');
                    const targetSquadId = $target.attr('data-squad-id');
                    const senderCount = $sender.find('li').length;
                    const receiverCount = $target.find('li').length;
                    const $item = $(ui.item);

                    $sender.closest('.manage-squad-card').find('.count').text(senderCount);
                    $target.closest('.manage-squad-card').find('.count').text(receiverCount);

                    updateUnassignedStatus();

                    if (targetSquadId === '0') {
                        $item.fadeOut(200, function() {
                            $(this).remove();
                        });
                    }

                    $.ajax({
                        type: 'POST',
                        url: `${window.Laravel.appPath}/members/assign-squad`,
                        data: {
                            member_id: memberId,
                            squad_id: targetSquadId,
                            _token: csrfToken
                        },
                        dataType: 'json',
                        success: () => {
                            if (targetSquadId === '0') {
                                toastr.success('Member unassigned from platoon');
                            } else {
                                toastr.success('Member reassigned!');
                            }
                        },
                        error: () => {
                            toastr.error('Failed to reassign member');
                        }
                    });
                },
                stop: function(event, ui) {
                    $('.manage-squad-card, .unassign-drop-zone').removeClass('drag-hover');
                }
            });
        },

        initSquadAssignments() {
            let organizeMode = false;

            $('.organize-squads-btn').on('click', (e) => {
                organizeMode = !organizeMode;
                const $btn = $(e.currentTarget);

                if (organizeMode) {
                    $btn.html('<i class="fa fa-check"></i> Done');
                    $btn.removeClass('btn-accent').addClass('btn-success');
                    $('.unassigned-organizer-members').slideDown(200);
                    $('.squad-assignments-section').addClass('organize-mode');
                    this.enableSquadDragDrop();
                } else {
                    $btn.html('<i class="fa fa-arrows-alt"></i> Organize');
                    $btn.removeClass('btn-success').addClass('btn-accent');
                    $('.unassigned-organizer-members').slideUp(200);
                    $('.squad-assignments-section').removeClass('organize-mode');
                }
            });

            $('.squad-drop-target').on('click', (e) => {
                if ($('.squad-assignments-section').hasClass('organize-mode')) {
                    e.preventDefault();
                }
            });

            if (new URLSearchParams(window.location.search).get('organize') === '1') {
                $('.organize-squads-btn').trigger('click');
                const $section = $('.squad-assignments-section');
                if ($section.length) {
                    $('html, body').animate({ scrollTop: $section.offset().top - 20 }, 400);
                }
            }
        },

        enableSquadDragDrop() {
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
                    const $target = $(this);
                    const memberId = ui.draggable.attr('data-member-id');
                    const squadId = $target.attr('data-squad-id');
                    const $draggable = $(ui.draggable);

                    $.ajax({
                        type: 'POST',
                        url: `${window.Laravel.appPath}/members/assign-squad`,
                        data: {
                            member_id: memberId,
                            squad_id: squadId,
                            _token: csrfToken
                        },
                        dataType: 'json',
                        success: () => {
                            toastr.success('Member assigned to squad!');
                            $draggable.fadeOut(200, function() {
                                $(this).remove();
                                if ($('.unassigned-squad-member').length < 1) {
                                    $('.unassigned-organizer').slideUp();
                                    $('.squad-assignments-section').removeClass('organize-mode');
                                }
                            });
                            const $count = $target.find('.squad-stat-badge .fa-users').parent();
                            const currentCount = parseInt($count.text().trim()) || 0;
                            $count.html(`<i class="fa fa-users"></i> ${currentCount + 1}`);
                        },
                        error: () => {
                            toastr.error('Failed to assign member to squad');
                        }
                    });
                }
            });
        },

        initAutocomplete() {
            $('#leader').bootcomplete({
                url: `${window.Laravel.appPath}/search-member/`,
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: {_token: csrfToken}
            });
        },

        handleMembers() {
            if (!$('.members-table').length) {
                return;
            }

            const $table = $('table.members-table');

            const cols = {};
            $table.find('thead th[data-col]').each(function(index) {
                cols[$(this).data('col')] = index;
            });

            this.dataTable = $table.DataTable({
                initComplete: (settings, json) => {
                    setTimeout(() => {
                        $('.ld-loading').removeClass('ld-loading');
                        this.dataTable.columns.adjust();
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
                    { targets: cols['inactivity-reminder'], visible: false },
                    { targets: 'no-sort', orderable: false },
                    { targets: 'no-search', searchable: false },
                    { targets: 'col-hidden', visible: false },
                    { targets: cols['rank'], orderData: cols['rank-id'] },
                    { targets: cols['discord-activity'], orderData: cols['discord-activity-date'] },
                    { targets: cols['inactivity-reminder'], orderData: cols['reminder-date'] }
                ],
                stateSaveParams: (settings, data) => {
                    data.columns[cols['checkbox']].visible = false;
                    data.columns[cols['member']].visible = true;
                    if (data.order && data.order[0] && data.order[0][0] === cols['checkbox']) {
                        data.order = [[cols['member'], 'asc']];
                    }
                },
                stateLoadParams: (settings, data) => {
                    data.columns[cols['checkbox']].visible = false;
                    data.columns[cols['member']].visible = true;
                    if (data.order && data.order[0] && data.order[0][0] === cols['checkbox']) {
                        data.order = [[cols['member'], 'asc']];
                    }
                }
            });

            this.cols = cols;

            let resizeTimer;
            $(window).on('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.dataTable.columns.adjust().draw();
                }, 100);
            });

            const getColumnIndex = (name) => cols[name];

            const updateToggleState = (link, column) => {
                const $link = $(link);
                const $icon = $link.find('i.fa');
                if (column.visible()) {
                    $link.addClass('active');
                    $icon.removeClass('fa-square-o').addClass('fa-check');
                } else {
                    $link.removeClass('active');
                    $icon.removeClass('fa-check').addClass('fa-square-o');
                }
            };

            $('a.toggle-vis').each(function () {
                const colName = $(this).data('column');
                const colIndex = getColumnIndex(colName);
                if (colIndex !== undefined) {
                    const column = Platoon.dataTable.column(colIndex);
                    updateToggleState(this, column);
                }
            });

            $('a.toggle-vis').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const colName = $(this).data('column');
                const colIndex = getColumnIndex(colName);
                if (colIndex !== undefined) {
                    const column = Platoon.dataTable.column(colIndex);
                    column.visible(!column.visible());
                    updateToggleState(this, column);
                }
            });

            const $columnDropdown = $('.column-dropdown');
            const $columnToggle = $columnDropdown.find('.dropdown-toggle');
            const $columnMenu = $columnDropdown.find('.column-toggle-menu');

            $columnToggle.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = $columnDropdown.hasClass('show');
                if (isOpen) {
                    $columnDropdown.removeClass('show');
                    $columnMenu.removeClass('show');
                } else {
                    $columnDropdown.addClass('show');
                    $columnMenu.addClass('show');
                    $columnMenu.scrollTop(0);
                }
            });

            $(document).on('click', (e) => {
                if (!$(e.target).closest('.column-dropdown').length) {
                    $columnDropdown.removeClass('show');
                    $columnMenu.removeClass('show');
                }
            });

            const $searchInput = $('.dataTables_filter input').removeClass('input-sm');
            $searchInput.attr({
                'placeholder': 'Search Players',
                'class': 'form-control'
            });

            $('#playerFilter').append($searchInput);
            $('.dataTables_filter label').remove();

            const $tagFilter = $('#tag-filter');
            if ($tagFilter.length) {
                const $tagWrapper = $('<div class="tag-filter-wrapper"></div>');
                $tagWrapper.append($tagFilter);
                $('#playerFilter').append($tagWrapper);
            }

            let $bulkToggle = null;
            let bulkModeActive = false;

            if (window.Laravel && window.Laravel.canUseBulkMode) {
                $bulkToggle = $('<button type="button" class="btn btn-default bulk-mode-toggle">Bulk Mode</button>');
                $('#playerFilter').append($bulkToggle);

                $bulkToggle.on('click', () => {
                    toggleBulkMode();
                });
            }

            $('.no-sort').removeClass('sorting');

            const toggleBulkMode = () => {
                if (!$bulkToggle) return;

                bulkModeActive = !bulkModeActive;
                const checkboxCol = this.dataTable.column(cols['checkbox']);
                checkboxCol.visible(bulkModeActive);

                if (bulkModeActive) {
                    $bulkToggle.addClass('active btn-accent').removeClass('btn-default');
                    $bulkToggle.html('<i class="fa fa-check-circle"></i> Exit Bulk Mode');
                    $('.members-table').addClass('bulk-mode');
                } else {
                    $bulkToggle.removeClass('active btn-accent').addClass('btn-default');
                    $bulkToggle.html('Bulk Mode');
                    $('.members-table').removeClass('bulk-mode');
                    $('.member-checkbox, #select-all-members').prop('checked', false);
                    updateBulkSelection();
                }
            };

            const updateBulkSelection = () => {
                const selected = [];
                $('.member-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });

                const hasParttimers = $('.member-checkbox:checked[data-parttimer="1"]').length > 0;
                const $transferBtn = $('#bulk-transfer-btn');

                if (hasParttimers) {
                    $transferBtn.prop('disabled', true).addClass('disabled');
                    $transferBtn.attr('title', 'Cannot transfer: selection includes part-time members');
                } else {
                    $transferBtn.prop('disabled', false).removeClass('disabled');
                    $transferBtn.removeAttr('title');
                }

                if (selected.length >= 1) {
                    $("#selected-data").css('display', 'block');
                    $("#selected-data .status-text").text(`${selected.length} member${selected.length === 1 ? "" : "s"} selected`);
                    $("#pm-member-data").val(selected);
                    $("#tag-member-data").val(selected);
                    $("#transfer-member-data").val(selected);
                } else {
                    $("#selected-data").hide();
                    $("#pm-member-data").val('');
                    $("#tag-member-data").val('');
                    $("#transfer-member-data").val('');
                }

                const total = $('.member-checkbox').length;
                const checked = $('.member-checkbox:checked').length;
                $('#select-all-members').prop('checked', checked > 0 && checked === total);
                $('#select-all-members').prop('indeterminate', checked > 0 && checked < total);
            };

            $(document).on('click', '.bulk-action-close', () => {
                $('.member-checkbox, #select-all-members').prop('checked', false);
                updateBulkSelection();
            });

            $(document).on('change', '.member-checkbox', () => {
                updateBulkSelection();
            });

            $(document).on('change', '#select-all-members', function() {
                const isChecked = $(this).prop('checked');
                $('.member-checkbox').prop('checked', isChecked);
                updateBulkSelection();
            });

            let isDragging = false;
            let dragStartRow = null;
            let dragCheckState = true;

            $(document).on('mousedown', '.members-table.bulk-mode tbody tr', function(e) {
                if ($(e.target).closest('a, button, input, .btn').length) return;
                if (e.which !== 1) return;

                isDragging = true;
                dragStartRow = $(this).index();
                const $checkbox = $(this).find('.member-checkbox');
                dragCheckState = !$checkbox.prop('checked');
                $checkbox.prop('checked', dragCheckState);
                updateBulkSelection();
                e.preventDefault();
            });

            $(document).on('mouseenter', '.members-table.bulk-mode tbody tr', function() {
                if (!isDragging) return;
                const $checkbox = $(this).find('.member-checkbox');
                $checkbox.prop('checked', dragCheckState);
                updateBulkSelection();
            });

            $(document).on('mouseup', () => {
                isDragging = false;
                dragStartRow = null;
            });

            $('#is_tba').click(() => {
                toggleTBA();
            });

            toggleTBA();

            const toggleTBA = () => {
                if ($('#is_tba').is(':checked')) {
                    $('#leader_id, #leader').prop('disabled', true).val('');
                } else {
                    $('#leader_id, #leader').prop('disabled', false);
                }
            };
        },

        initTagFilter() {
            const $tagFilter = $('#tag-filter');

            if (!$tagFilter.length || !this.dataTable || !this.cols) {
                return;
            }

            const tagIdColIndex = this.cols['tag-ids'];

            $tagFilter.select2({
                placeholder: 'Filter by tag',
                allowClear: true
            }).on('change', () => {
                this.dataTable.draw();
            });

            $.fn.dataTable.ext.search.push((settings, data, dataIndex) => {
                const selectedTags = $tagFilter.val();

                if (!selectedTags || selectedTags.length === 0) {
                    return true;
                }

                const memberTags = data[tagIdColIndex] ? data[tagIdColIndex].split(',') : [];

                return selectedTags.some((tag) => memberTags.includes(tag));
            });
        },

        initBulkTags() {
            const $modal = $('#bulk-tags-modal');
            if (!$modal.length) return;

            const getSelectedMemberIds = () => {
                return $('#tag-member-data').val() ? $('#tag-member-data').val().split(',') : [];
            };

            const getSelectedTagIds = () => {
                const tagIds = [];
                $('.bulk-tag-checkbox:checked').each(function() {
                    tagIds.push($(this).val());
                });
                return tagIds;
            };

            const updateButtonState = () => {
                const hasSelectedTags = getSelectedTagIds().length > 0;
                $('#bulk-assign-tags, #bulk-remove-tags').prop('disabled', !hasSelectedTags);
            };

            $modal.on('show.bs.modal', () => {
                const memberCount = getSelectedMemberIds().length;
                $('#bulk-tags-member-count').text(`${memberCount} member${memberCount !== 1 ? 's' : ''} selected`);
                $('.bulk-tag-checkbox').prop('checked', false).trigger('change');
            });

            $(document).on('change', '.bulk-tag-checkbox', function() {
                const $badge = $(this).siblings('.bulk-tag-badge');
                if (this.checked) {
                    $badge.addClass('selected');
                } else {
                    $badge.removeClass('selected');
                }
                updateButtonState();
            });

            const bulkTagAction = (action) => {
                const memberIds = getSelectedMemberIds();
                const tagIds = getSelectedTagIds();
                const url = $modal.data('store-url');

                if (tagIds.length === 0) {
                    toastr.warning('Please select at least one tag');
                    return;
                }

                const $btns = $('#bulk-assign-tags, #bulk-remove-tags');
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
                    success: () => {
                        const msg = action === 'assign' ? 'Tags assigned to ' : 'Tags removed from ';
                        toastr.success(`${msg}${memberIds.length} members`);
                        $modal.modal('hide');
                        location.reload();
                    },
                    error: () => {
                        toastr.error(`Failed to ${action} tags`);
                    },
                    complete: () => {
                        updateButtonState();
                    }
                });
            };

            $(document).on('click', '#bulk-assign-tags', () => {
                bulkTagAction('assign');
            });

            $(document).on('click', '#bulk-remove-tags', () => {
                bulkTagAction('remove');
            });
        },

        initBulkTransfer() {
            const $modal = $('#bulk-transfer-modal');
            if (!$modal.length) return;

            let platoonsData = null;

            const getSelectedMemberIds = () => {
                return $('#transfer-member-data').val() ? $('#transfer-member-data').val().split(',') : [];
            };

            const updateSubmitState = () => {
                const platoonId = $('#transfer-platoon').val();
                $('#bulk-transfer-submit').prop('disabled', !platoonId);
            };

            const populatePlatoons = () => {
                const $platoonSelect = $('#transfer-platoon');
                $platoonSelect.find('option:not(:first)').remove();

                platoonsData.forEach((platoon) => {
                    $platoonSelect.append(
                        $('<option>', { value: platoon.id, text: platoon.name })
                    );
                });
            };

            const loadPlatoons = () => {
                if (platoonsData) {
                    populatePlatoons();
                    return;
                }

                const url = $modal.data('platoons-url');
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: (response) => {
                        platoonsData = response.platoons;
                        populatePlatoons();
                    },
                    error: () => {
                        toastr.error('Failed to load platoons');
                    }
                });
            };

            const populateSquads = (platoonId) => {
                const $squadSelect = $('#transfer-squad');
                $squadSelect.find('option:not(:first)').remove();

                if (!platoonId) {
                    $squadSelect.prop('disabled', true);
                    return;
                }

                const platoon = platoonsData.find((p) => p.id == platoonId);

                if (platoon && platoon.squads.length > 0) {
                    platoon.squads.forEach((squad) => {
                        $squadSelect.append(
                            $('<option>', { value: squad.id, text: squad.name })
                        );
                    });
                    $squadSelect.prop('disabled', false);
                } else {
                    $squadSelect.prop('disabled', true);
                }
            };

            $modal.on('show.bs.modal', () => {
                const hasParttimers = $modal.data('parttimers') === 'true';

                if (hasParttimers) {
                    $('#bulk-transfer-parttimer-warning').show();
                    $('#bulk-transfer-form-content').hide();
                    $('#bulk-transfer-submit').hide();
                    return;
                }

                $('#bulk-transfer-parttimer-warning').hide();
                $('#bulk-transfer-form-content').show();
                $('#bulk-transfer-submit').show();

                const memberCount = getSelectedMemberIds().length;
                $('#bulk-transfer-member-count').text(`${memberCount} member${memberCount !== 1 ? 's' : ''} selected`);
                $('#transfer-platoon').val('');
                $('#transfer-squad').val('').prop('disabled', true);
                updateSubmitState();
                loadPlatoons();
            });

            $(document).on('change', '#transfer-platoon', function() {
                const platoonId = $(this).val();
                populateSquads(platoonId);
                updateSubmitState();
            });

            $(document).on('click', '#bulk-transfer-submit', function() {
                const memberIds = getSelectedMemberIds();
                const platoonId = $('#transfer-platoon').val();
                const squadId = $('#transfer-squad').val();
                const url = $modal.data('store-url');

                if (!platoonId) {
                    toastr.warning('Please select a platoon');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="themed-spinner spinner-sm"></span> Transferring...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        member_ids: memberIds,
                        platoon_id: platoonId,
                        squad_id: squadId || null,
                        _token: csrfToken
                    },
                    success: (response) => {
                        toastr.success(response.message);
                        $modal.modal('hide');
                        location.reload();
                    },
                    error: () => {
                        toastr.error('Failed to transfer members');
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html('<i class="fa fa-exchange-alt"></i> Transfer');
                        updateSubmitState();
                    }
                });
            });
        },

        initBulkReminder() {
            const $btn = $('#bulk-reminder-btn');
            if (!$btn.length) return;

            $btn.on('click', () => {
                const memberIds = $('#pm-member-data').val();
                if (!memberIds) {
                    toastr.warning('No members selected');
                    return;
                }

                const memberIdArray = memberIds.split(',');
                const url = $btn.data('url');

                $btn.prop('disabled', true).html('<span class="themed-spinner spinner-sm"></span>');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        member_ids: memberIdArray
                    },
                    success: (response) => {
                        let message = `${response.count} member${response.count !== 1 ? 's' : ''} marked as reminded`;
                        if (response.skipped > 0) {
                            message += ` (${response.skipped} skipped - already reminded today)`;
                        }
                        toastr.success(message);

                        response.updatedIds.forEach((memberId) => {
                            const $toggleBtn = $(`.activity-reminder-toggle[data-member-id="${memberId}"]`);
                            if ($toggleBtn.length) {
                                $toggleBtn.removeClass('btn-success').addClass('btn-default');
                                $toggleBtn.html(`<i class="fa fa-bell"></i> <span class="reminded-date">${response.date}</span>`);
                                $toggleBtn.attr('title', 'Reminded just now');
                                $toggleBtn.prop('disabled', true);
                            }
                        });

                        $('.bulk-action-close').click();
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.message || 'Failed to set reminders';
                        toastr.error(message);
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html('<i class="fa fa-bell text-accent"></i> <span class="hidden-xs hidden-sm">Reminder</span>');
                    }
                });
            });
        },
    };

    Platoon.setup();
}

initPlatoon();
