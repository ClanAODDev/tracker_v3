var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function () {
            Tracker.InitNavToggle();
            Tracker.InitBackToTop();
            Tracker.InitClipboard();
            Tracker.InitDataTables();
            Tracker.InitSparklines();
            Tracker.InitPanels();
            Tracker.InitSubNavCollapse();
            Tracker.InitMemberAutocomplete();
            Tracker.InitMobileNav();
            Tracker.InitMobileSearch();
            Tracker.InitRarityFilter();
            Tracker.InitRepeater();
            Tracker.InitTabActivate();
            Tracker.InitMemberSearch();
            Tracker.InitCollectionSearch();
            Tracker.InitSmoothScroll();
            Tracker.InitSettings();
            Tracker.InitProfileModals();
            Tracker.InitNoSquadModal();
            Tracker.InitLeaderboardTabs();
            Tracker.InitInactiveTabs();
            Tracker.InitParttimerSearch();
            Tracker.InitAddParttimer();
        },

        InitNavToggle: function () {
            var $toggle = $('.left-nav-toggle a');
            if (!$toggle.length) return;

            $toggle.on('click', function (e) {
                e.preventDefault();
                $('body').toggleClass('nav-toggle');

                if ($('body').hasClass('nav-toggle')) {
                    $.get(window.Laravel.appPath + '/primary-nav/collapse');
                } else {
                    $.get(window.Laravel.appPath + '/primary-nav/decollapse');
                }

                Tracker.RefreshSparklines();
            });
        },

        InitBackToTop: function () {
            if (($(window).height() + 100) >= $(document).height()) return;

            $('#top-link-block').removeClass('hidden').affix({
                offset: { top: 100 }
            });
        },

        InitSmoothScroll: function () {
            $('.smooth-scroll').on('click', function (e) {
                e.preventDefault();
                var targetId = $(this).attr('href');
                var $target = $(targetId);
                if (!$target.length) return;

                var top = $target.offset().top - 90;
                $('html, body').stop().animate({ scrollTop: top }, 750);
                window.location.hash = targetId.substr(1);
            });
        },

        InitClipboard: function () {
            if (typeof Clipboard === 'undefined') return;
            if (!$('.copy-to-clipboard').length) return;

            var clipboard = new Clipboard('.copy-to-clipboard');
            clipboard.on('success', function (e) {
                toastr.success('Copied!');
                e.clearSelection();
            });
        },

        InitDataTables: function () {
            var $basicTable = $('table.basic-datatable');
            var $advTable = $('table.adv-datatable');

            if ($basicTable.length) {
                var basicDatatable = $basicTable.DataTable({
                    paging: false,
                    bFilter: false,
                    stateSave: true,
                    bInfo: false,
                    order: [],
                    columnDefs: [
                        { targets: 'no-sort', orderable: false }
                    ],
                    select: {
                        style: 'os',
                        selector: 'td:first-child'
                    }
                });

                if ($('.for-pm-selection').length) {
                    basicDatatable.on('select', function () {
                        var selected = basicDatatable.rows($('.selected')).data().toArray().map(function (row) {
                            return row[4];
                        });
                        if (selected.length >= 2) {
                            $('#selected-data').show();
                            $('#selected-data .status-text').text('With selected (' + selected.length + ')');
                            $('#pm-member-data').val(selected);
                        }
                    });
                }
            }

            if ($advTable.length) {
                $advTable.DataTable({
                    order: [],
                    columnDefs: [
                        { targets: 'no-sort', orderable: false }
                    ]
                });
            }
        },

        InitSparklines: function () {
            Tracker.RefreshSparklines();

            var resizeTimer;
            $(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(Tracker.RefreshSparklines, 100);
            });
        },

        RefreshSparklines: function () {
            $('[census-data]').each(function () {
                var $el = $(this);
                var inContainer = $el.closest('.census-sparkline-container').length > 0;
                var chartHeight = inContainer ? 80 : 50;

                $el.sparkline($el.data('counts'), {
                    type: 'line',
                    lineColor: '#fff',
                    lineWidth: 2,
                    fillColor: '#404652',
                    height: chartHeight,
                    width: '100%'
                });

                if ($el.data('weekly-voice')) {
                    $el.sparkline($el.data('weekly-voice'), {
                        type: 'line',
                        lineColor: '#1bbf89',
                        lineWidth: 2,
                        fillColor: 'rgba(27, 191, 137, 0.2)',
                        height: chartHeight,
                        composite: true
                    });
                }
            });

            $('.census-pie').each(function () {
                var $el = $(this);
                $el.sparkline($el.data('counts'), {
                    type: 'pie',
                    sliceColors: $el.data('colors')
                });
            });
        },

        InitPanels: function () {
            $('.panel-toggle').on('click', function (e) {
                e.preventDefault();
                var $panel = $(e.target).closest('div.panel');
                var $icon = $(e.target).closest('i.toggle-icon');
                var $iconNotLinked = $(e.target).find('i.toggle-icon');

                $panel.find('div.panel-body').slideToggle(300);
                $panel.find('div.panel-footer').slideToggle(200);

                $icon.toggleClass('fa-chevron-up fa-chevron-down');
                $iconNotLinked.toggleClass('fa-chevron-up fa-chevron-down');
                $panel.toggleClass('panel-collapse');

                setTimeout(function () {
                    $panel.resize();
                    $panel.find('[id^=map-]').resize();
                }, 50);
            });

            $('.panel-close').on('click', function (e) {
                e.preventDefault();
                $(e.target).closest('div.panel').remove();
            });
        },

        InitSubNavCollapse: function () {
            $('.nav-second').on('show.bs.collapse', function () {
                $('.nav-second.in').collapse('hide');
            });
        },

        InitMemberAutocomplete: function () {
            var $search = $('.search-member');
            if (!$search.length) return;

            $search.bootcomplete({
                url: window.Laravel.appPath + '/search-member/',
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: { _token: $('meta[name=csrf-token]').attr('content') }
            });
        },

        InitMobileNav: function () {
            var $toggle = $('.mobile-nav-toggle');
            var $drawer = $('.mobile-nav-drawer');
            var $overlay = $('.mobile-nav-overlay');
            var $close = $('.mobile-nav-close');

            if (!$drawer.length) return;

            function openNav() {
                $drawer.addClass('active');
                $overlay.addClass('active');
                $('body').addClass('mobile-nav-open');
            }

            function closeNav() {
                $drawer.removeClass('active');
                $overlay.removeClass('active');
                $('body').removeClass('mobile-nav-open');
            }

            $toggle.on('click', function (e) {
                e.preventDefault();
                if ($drawer.hasClass('active')) {
                    closeNav();
                } else {
                    openNav();
                }
            });

            $close.on('click', function (e) {
                e.preventDefault();
                closeNav();
            });

            $overlay.on('click', closeNav);

            $drawer.find('a').on('click', function () {
                var $link = $(this);
                if ($link.attr('data-toggle') === 'collapse' || $link.attr('href') === '#') {
                    return;
                }
                closeNav();
            });
        },

        InitMobileSearch: function () {
            var $toggle = $('.mobile-search-toggle');
            var $modal = $('.mobile-search-modal');
            var $input = $('#mobile-member-search');
            var $close = $('.mobile-search-close');
            var $clear = $('.mobile-searchclear');
            var $results = $('.mobile-search-results');
            var $loader = $('.mobile-search-loader');

            if (!$modal.length) return;

            var timer = null;
            var delay = 500;

            function openSearch() {
                $modal.addClass('active');
                $('body').addClass('mobile-search-open');
                $input.focus();
            }

            function closeSearch() {
                $modal.removeClass('active');
                $('body').removeClass('mobile-search-open');
                $input.val('');
                $clear.removeClass('visible');
                $results.empty();
                $loader.removeClass('active');
            }

            function performSearch() {
                var query = $input.val().trim();
                if (!query) {
                    $loader.removeClass('active');
                    $results.empty();
                    return;
                }

                $.ajax({
                    url: window.Laravel.appPath + '/search/members/' + query,
                    type: 'GET',
                    success: function (response) {
                        $loader.removeClass('active');
                        $results.html(response);
                    }
                });
            }

            $toggle.on('click', function (e) {
                e.preventDefault();
                openSearch();
            });

            $close.on('click', closeSearch);

            $clear.on('click', function () {
                $input.val('').focus();
                $clear.removeClass('visible');
                $results.empty();
            });

            $input.on('input', function () {
                var value = $input.val().trim();
                $clear.toggleClass('visible', value.length > 0);

                if (timer) {
                    clearTimeout(timer);
                }

                if (!value) {
                    $results.empty();
                    $loader.removeClass('active');
                    return;
                }

                $loader.addClass('active');
                timer = setTimeout(performSearch, delay);
            });

            $input.on('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeSearch();
                }
            });
        },

        InitRarityFilter: function () {
            var $rarityFilters = $('.rarity-filter');
            var $divisionSelect = $('#division-filter');
            if (!$rarityFilters.length && !$divisionSelect.length) return;

            function applyRarityFilter() {
                var activeRarities = $('.rarity-filter.active').map(function () {
                    return $(this).data('rarity');
                }).get();

                $('.award-card').each(function () {
                    var $card = $(this);
                    var $col = $card.closest('[class*="col-"]');
                    var cardRarity = null;

                    ['mythic', 'legendary', 'epic', 'rare', 'common'].forEach(function (r) {
                        if ($card.hasClass('award-card-' + r)) {
                            cardRarity = r;
                        }
                    });

                    var show = activeRarities.length === 0 || activeRarities.indexOf(cardRarity) !== -1;
                    var wasHidden = $col.hasClass('filter-hidden');

                    if (show) {
                        $col.removeClass('filter-hidden filter-hiding');
                        if (wasHidden) {
                            $col.addClass('filter-entering');
                            setTimeout(function() {
                                $col.removeClass('filter-entering').addClass('filter-visible');
                            }, 300);
                        } else {
                            $col.addClass('filter-visible');
                        }
                    } else if (!$col.hasClass('filter-hidden') && !$col.hasClass('filter-hiding')) {
                        $col.removeClass('filter-visible filter-entering').addClass('filter-hiding');
                        setTimeout(function() {
                            $col.removeClass('filter-hiding').addClass('filter-hidden');
                        }, 250);
                    }
                });
            }

            $rarityFilters.on('click', function () {
                $(this).toggleClass('active');
                applyRarityFilter();
            });

            $divisionSelect.on('change', function () {
                var division = $(this).val();
                var url = new URL(window.location.href);
                if (division) {
                    url.searchParams.set('division', division);
                } else {
                    url.searchParams.delete('division');
                }
                window.location.href = url.toString();
            });

            var $raritySort = $('#rarity-sort');
            var rarityOrder = ['mythic', 'legendary', 'epic', 'rare', 'common'];

            function sortAwards(sortType) {
                $('.award-grid').each(function () {
                    var $grid = $(this);
                    var $items = $grid.children('[class*="col-"]').detach().toArray();

                    if (sortType === 'default') {
                        $items.sort(function (a, b) {
                            var orderA = parseInt($(a).data('original-order')) || 0;
                            var orderB = parseInt($(b).data('original-order')) || 0;
                            return orderA - orderB;
                        });
                    } else {
                        $items.sort(function (a, b) {
                            var $cardA = $(a).find('.award-card');
                            var $cardB = $(b).find('.award-card');
                            var rarityA = 4, rarityB = 4;

                            rarityOrder.forEach(function (r, idx) {
                                if ($cardA.hasClass('award-card-' + r)) rarityA = idx;
                                if ($cardB.hasClass('award-card-' + r)) rarityB = idx;
                            });

                            return sortType === 'rarity-desc' ? rarityA - rarityB : rarityB - rarityA;
                        });
                    }

                    $items.forEach(function (item, idx) {
                        var $item = $(item);
                        if (!$item.data('original-order')) {
                            $item.attr('data-original-order', idx);
                        }
                        $item.removeClass('filter-visible filter-entering filter-hiding filter-hidden');
                        $grid.append(item);
                    });

                    setTimeout(function () {
                        $grid.children('[class*="col-"]').each(function (idx) {
                            var $el = $(this);
                            setTimeout(function () {
                                $el.addClass('filter-entering');
                                setTimeout(function () {
                                    $el.removeClass('filter-entering').addClass('filter-visible');
                                }, 300);
                            }, idx * 30);
                        });
                    }, 10);
                });
            }

            $('.award-grid').each(function () {
                $(this).children('[class*="col-"]').each(function (idx) {
                    $(this).attr('data-original-order', idx);
                });
            });

            $raritySort.on('change', function () {
                sortAwards($(this).val());
                applyRarityFilter();
            });
        },

        InitMemberSearch: function () {
            var $input = $('#member-search');
            if (!$input.length) return;

            var $loader = $('.desktop-search-loader');
            var $clearBtn = $('#searchclear');
            var timer = null;
            var delay = 1000;

            function triggerSearch() {
                $loader.addClass('active');

                if (timer) {
                    clearTimeout(timer);
                }

                timer = setTimeout(function () {
                    timer = null;
                    var value = $input.val().trim();
                    if (!value) {
                        $loader.removeClass('active');
                        return;
                    }
                    Tracker.GetSearchResults();
                }, delay);
            }

            $input.on('keydown', triggerSearch);
            $input.on('paste', function () {
                setTimeout(triggerSearch, 0);
            });
            $input.on('input', function () {
                var hasValue = $input.val().trim();
                if (!hasValue) {
                    $loader.removeClass('active');
                    $clearBtn.removeClass('visible');
                }
            });

            function clearSearch() {
                $('section.search-results').addClass('closed').removeClass('open');
                $('body').removeClass('search-active');
                $('.content').css('margin-top', '');
                $input.val('');
                $clearBtn.removeClass('visible');
            }

            $clearBtn.on('click', clearSearch);

            $('.content').on('click', function () {
                if ($('body').hasClass('search-active')) {
                    clearSearch();
                }
            });
        },

        GetSearchResults: function () {
            var name = $('#member-search').val();
            if (!name) return;

            $.ajax({
                url: window.Laravel.appPath + '/search/members/' + name,
                type: 'GET',
                success: function (response) {
                    window.scrollTo(0, 0);
                    $('.desktop-search-loader').removeClass('active');
                    $('#searchclear').addClass('visible');

                    var $results = $('section.search-results');
                    $results.html(response).addClass('open').removeClass('closed');
                    $('body').addClass('search-active');

                    setTimeout(function () {
                        $('.content').css('margin-top', '20px');
                    }, 50);
                }
            });
        },

        InitCollectionSearch: function () {
            var $input = $('#search-collection');
            if (!$input.length) return;

            $input.on('keyup', function () {
                var value = $(this).val();
                var exp = new RegExp('^' + value, 'i');

                $('.collection .collection-item').each(function () {
                    $(this).toggle(exp.test($(this).text()));
                });
            });
        },

        InitRepeater: function () {
            var $repeater = $('.repeater');
            if (!$repeater.length) return;

            $repeater.repeater({
                isFirstItemUndeletable: true
            });
        },

        InitTabActivate: function () {
            var $tabs = $('.nav-tabs');
            if (!$tabs.length) return;

            $tabs.stickyTabs();

            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                $.sparkline_display_visible();
            });
        },

        InitSettings: function () {
            var $toggle = $('.settings-toggle, .mobile-settings-toggle');
            var $overlay = $('.settings-overlay');
            var $slideover = $('.settings-slideover');
            var $close = $('.settings-close');

            if (!$toggle.length) return;

            function openSettings() {
                $overlay.addClass('active');
                $slideover.addClass('active');
                $toggle.addClass('active');
                $('body').addClass('settings-open');
            }

            function closeSettings() {
                $overlay.removeClass('active');
                $slideover.removeClass('active');
                $toggle.removeClass('active');
                $('body').removeClass('settings-open');
            }

            $toggle.on('click', function (e) {
                e.preventDefault();
                if ($slideover.hasClass('active')) {
                    closeSettings();
                } else {
                    openSettings();
                }
            });

            $overlay.on('click', closeSettings);
            $close.on('click', closeSettings);

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $slideover.hasClass('active')) {
                    closeSettings();
                }
            });

            var saveTimer = null;
            var $status = $('.settings-save-status');

            function saveSettings() {
                if (saveTimer) clearTimeout(saveTimer);

                $status.text('Saving...').removeClass('saved error').addClass('saving');

                var formData = {
                    _token: $('input[name="_token"]').val(),
                    disable_animations: $('#setting-disable-animations').is(':checked'),
                    mobile_nav_side: $('#setting-mobile-nav-side').val(),
                    snow: $('#setting-snow').val(),
                    ticket_notifications: $('#setting-ticket-notifications').is(':checked')
                };

                $.ajax({
                    url: window.Laravel.appPath + '/settings',
                    type: 'POST',
                    data: formData,
                    success: function () {
                        $status.text('Saved').removeClass('saving error').addClass('saved');
                        saveTimer = setTimeout(function () {
                            $status.text('').removeClass('saved');
                        }, 2000);

                        Tracker.ApplySettings(formData);
                    },
                    error: function () {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    }
                });
            }

            $('#setting-disable-animations, #setting-ticket-notifications').on('change', saveSettings);

            $('.settings-btn[data-setting]').on('click', function () {
                var $btn = $(this);
                var setting = $btn.data('setting');
                var value = $btn.data('value');

                $btn.siblings('.settings-btn').removeClass('active');
                $btn.addClass('active');
                $('#setting-' + setting.split('_').join('-')).val(value);

                saveSettings();
            });
        },

        ApplySettings: function (settings) {
            if (settings.disable_animations) {
                $('body').addClass('no-animations');
            } else {
                $('body').removeClass('no-animations');
            }

            if (settings.mobile_nav_side === 'left') {
                $('body').addClass('mobile-nav-left');
            } else {
                $('body').removeClass('mobile-nav-left');
            }

            if (typeof initSnowStorm === 'function') {
                var flakesMax = 0;
                if (settings.snow === 'some_snow') {
                    flakesMax = 32;
                } else if (settings.snow === 'all_the_snow') {
                    flakesMax = 160;
                }
                initSnowStorm(flakesMax);
            }
        },

        InitProfileModals: function () {
            var $partTimeModal = $('#part-time-divisions-modal');
            var $handlesModal = $('#ingame-handles-modal');

            if (!$partTimeModal.length && !$handlesModal.length) return;

            $partTimeModal.add($handlesModal).on('show.bs.modal', function () {
                $('.settings-overlay').removeClass('active');
                $('.settings-slideover').removeClass('active');
                $('.settings-toggle, .mobile-settings-toggle').removeClass('active');
                $('body').removeClass('settings-open');
            });

            $('#save-part-time-divisions').on('click', function () {
                var $btn = $(this);
                var $status = $partTimeModal.find('.modal-save-status');
                var divisions = [];

                $partTimeModal.find('input[name="divisions[]"]:checked').each(function () {
                    divisions.push($(this).val());
                });

                $btn.prop('disabled', true);
                $status.text('Saving...').removeClass('saved error').addClass('saving');

                $.ajax({
                    url: window.Laravel.appPath + '/settings/part-time-divisions',
                    type: 'POST',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        divisions: divisions
                    },
                    success: function (response) {
                        $status.text('Saved!').removeClass('saving error').addClass('saved');
                        $('.settings-link-btn[data-target="#part-time-divisions-modal"] .settings-link-count').text(response.count);
                        setTimeout(function () {
                            $partTimeModal.modal('hide');
                            $status.text('').removeClass('saved');
                        }, 1000);
                    },
                    error: function () {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });

            var handleIndex = $('#handles-container .handle-row').length;

            $('#add-handle').on('click', function () {
                var template = $('#handle-row-template').html();
                var newRow = template.replace(/__INDEX__/g, handleIndex);
                $('#handles-container').append(newRow);
                handleIndex++;
            });

            $(document).on('click', '.remove-handle', function () {
                $(this).closest('.handle-row').remove();
            });

            $('#save-ingame-handles').on('click', function () {
                var $btn = $(this);
                var $status = $handlesModal.find('.modal-save-status');
                var handles = [];

                $('#handles-container .handle-row').each(function (index) {
                    var $row = $(this);
                    var handleId = $row.find('.handle-select').val();
                    var value = $row.find('input[type="text"]').val();

                    if (handleId && value) {
                        handles.push({
                            id: $row.data('id') || '',
                            handle_id: handleId,
                            value: value,
                            primary: $row.find('.handle-primary input').is(':checked') ? 1 : 0
                        });
                    }
                });

                $btn.prop('disabled', true);
                $status.text('Saving...').removeClass('saved error').addClass('saving');

                $.ajax({
                    url: window.Laravel.appPath + '/settings/ingame-handles',
                    type: 'POST',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        handles: handles
                    },
                    success: function (response) {
                        $status.text('Saved!').removeClass('saving error').addClass('saved');
                        $('.settings-link-btn[data-target="#ingame-handles-modal"] .settings-link-count').text(response.count);
                        setTimeout(function () {
                            $handlesModal.modal('hide');
                            $status.text('').removeClass('saved');
                        }, 1000);
                    },
                    error: function () {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });
        },

        InitNoSquadModal: function () {
            var $modal = $('#no-squad-modal');
            if (!$modal.length) return;

            var loaded = false;

            $modal.on('show.bs.modal', function () {
                if (loaded) return;

                var url = $modal.data('url');

                $.get(url, function (response) {
                    var $list = $('#no-squad-list');
                    $list.empty();

                    if (response.members.length === 0) {
                        $list.html('<p class="text-muted">No members found.</p>');
                    } else {
                        var grouped = {};
                        response.members.forEach(function (member) {
                            if (!grouped[member.platoon]) {
                                grouped[member.platoon] = { members: [], manage_url: member.manage_url };
                            }
                            grouped[member.platoon].members.push(member);
                        });

                        Object.keys(grouped).sort().forEach(function (platoon) {
                            var group = grouped[platoon];
                            var $group = $('<div class="no-squad-group"></div>');
                            $group.append(
                                '<div class="no-squad-platoon-header">' +
                                '<span>' + platoon + '</span>' +
                                '<a href="' + group.manage_url + '" class="btn btn-sm btn-accent">' +
                                '<i class="fa fa-arrows-alt"></i> Assign</a>' +
                                '</div>'
                            );
                            var $members = $('<div class="no-squad-members"></div>');
                            group.members.forEach(function (member) {
                                $members.append('<span class="no-squad-member">' + member.name + '</span>');
                            });
                            $group.append($members);
                            $list.append($group);
                        });
                    }

                    $('#no-squad-loading').hide();
                    $list.show();
                    loaded = true;
                });
            });
        },

        InitLeaderboardTabs: function () {
            var $tabs = $('.leaderboard-tab');
            var $panels = $('.leaderboard-panel');
            if (!$tabs.length) return;

            $tabs.on('click', function () {
                var tabName = $(this).data('tab');

                $tabs.removeClass('active');
                $panels.removeClass('active');

                $(this).addClass('active');
                $('.leaderboard-panel[data-panel="' + tabName + '"]').addClass('active');
            });
        },

        InitInactiveTabs: function () {
            var $tabs = $('.inactive-tab');
            var $panels = $('.inactive-panel');
            var $searchInput = $('#inactive-search');

            if (!$tabs.length) return;

            $tabs.on('click', function () {
                var tabName = $(this).data('tab');

                $tabs.removeClass('active');
                $panels.removeClass('active');

                $(this).addClass('active');
                $('.inactive-panel[data-panel="' + tabName + '"]').addClass('active');

                if ($searchInput.val()) {
                    Tracker.FilterInactiveTable($searchInput.val());
                }
            });

            if ($searchInput.length) {
                $searchInput.on('input', function () {
                    Tracker.FilterInactiveTable($(this).val());
                });
            }
        },

        FilterInactiveTable: function (filter) {
            filter = filter.toLowerCase();
            $('.inactive-panel.active tbody tr').each(function () {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(filter) !== -1);
            });
        },

        InitParttimerSearch: function () {
            var $searchInput = $('#parttimer-search');
            if (!$searchInput.length) return;

            $searchInput.on('input', function () {
                var filter = $(this).val().toLowerCase();
                $('.inactive-panel.active tbody tr').each(function () {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(filter) !== -1);
                });
            });
        },

        InitAddParttimer: function () {
            var $modal = $('#add-parttimer-modal');
            if (!$modal.length) return;

            var $searchInput = $('#parttimer-member-search');
            var $memberIdField = $('#parttimer-member-id');
            var $selectedDisplay = $('#parttimer-selected-member');
            var $selectedName = $selectedDisplay.find('.selected-member-name');
            var $submitBtn = $('#add-parttimer-submit');

            $searchInput.bootcomplete({
                url: window.Laravel.appPath + '/search-member/',
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: { _token: $('meta[name=csrf-token]').attr('content') }
            });

            $searchInput.on('bootcomplete.selected', function (e, id, label) {
                $memberIdField.val(id);
                $selectedName.text(label);
                $selectedDisplay.show();
                $searchInput.hide();
                $submitBtn.prop('disabled', false);
            });

            $selectedDisplay.on('click', '.clear-selected-member', function () {
                $memberIdField.val('');
                $selectedName.text('');
                $selectedDisplay.hide();
                $searchInput.val('').show().focus();
                $submitBtn.prop('disabled', true);
            });

            $modal.on('hidden.bs.modal', function () {
                $memberIdField.val('');
                $selectedName.text('');
                $selectedDisplay.hide();
                $searchInput.val('').show();
                $submitBtn.prop('disabled', true);
                $('#parttimer-handle-value').val('');
            });
        }

    };

})(window.jQuery);

Tracker.Setup();
