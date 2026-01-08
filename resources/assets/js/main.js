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
            Tracker.InitNoteReminderDetection();
            Tracker.InitActivityReminderToggle();
            Tracker.InitInactiveBulkMode();
            Tracker.InitTrashedNotes();
            Tracker.InitActivityFeedToggle();
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
            var $btn = $('#top-link-block');
            if (!$btn.length) return;

            $(window).on('scroll', function () {
                if ($(this).scrollTop() > 100) {
                    $btn.addClass('visible');
                } else {
                    $btn.removeClass('visible');
                }
            });
        },

        InitSmoothScroll: function () {
            $('.smooth-scroll').on('click', function (e) {
                var targetId = $(this).attr('href');
                if (!targetId || !targetId.startsWith('#')) return;

                e.preventDefault();
                var $target = $(targetId);
                if (!$target.length) return;

                var top = $target.offset().top - 90;
                $('html, body').stop().animate({ scrollTop: top }, 750, function () {
                    history.replaceState(null, null, targetId);
                    $(window).trigger('hashchange');
                });
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
                        if (selected.length >= 1) {
                            $('#selected-data').show();
                            $('#selected-data .status-text').text(selected.length + ' member' + (selected.length === 1 ? '' : 's') + ' selected');
                            $('#pm-member-data').val(selected);
                        } else {
                            $('#selected-data').hide();
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
                    url: window.Laravel.appPath + '/search/members',
                    type: 'GET',
                    data: { q: query },
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
                url: window.Laravel.appPath + '/search/members',
                type: 'GET',
                data: { q: name },
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
                    snow_ignore_mouse: $('#setting-snow-ignore-mouse').is(':checked'),
                    ticket_notifications: $('#setting-ticket-notifications').is(':checked'),
                    theme: $('#setting-theme').val()
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

            $('#setting-disable-animations, #setting-ticket-notifications, #setting-snow-ignore-mouse').on('change', saveSettings);

            $('.settings-btn[data-setting]').on('click', function () {
                var $btn = $(this);
                var setting = $btn.data('setting');
                var value = $btn.data('value');

                $btn.siblings('.settings-btn').removeClass('active');
                $btn.addClass('active');
                $('#setting-' + setting.split('_').join('-')).val(value);

                if (setting === 'theme') {
                    updateThemeSettings(value);
                }

                saveSettings();
            });

            function updateThemeSettings(theme) {
                var isShattrath = theme === 'shattrath';
                $('.settings-btn[data-value="motes"]').toggle(isShattrath);

                if (!isShattrath && $('#setting-snow').val() === 'motes') {
                    $('#setting-snow').val('no_snow');
                    $('.settings-btn[data-setting="snow"]').removeClass('active');
                    $('.settings-btn[data-value="no_snow"]').addClass('active');
                }

                var favicon = document.getElementById('favicon');
                if (favicon) {
                    favicon.href = theme === 'shattrath' ? '/images/logo-shattrath.svg' : '/images/logo_v2.svg';
                }
            }

            updateThemeSettings($('#setting-theme').val());
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

            if (settings.theme) {
                document.documentElement.setAttribute('data-theme', settings.theme);
            }

            if (typeof initSnowStorm === 'function') {
                var flakesMax = 0;
                if (settings.snow === 'some_snow') {
                    flakesMax = 32;
                } else if (settings.snow === 'all_the_snow') {
                    flakesMax = 160;
                }
                initSnowStorm(flakesMax, settings.snow_ignore_mouse);
            }

            if (typeof initMotesOfLight === 'function') {
                var motesCount = 0;
                if (settings.snow === 'motes') {
                    motesCount = 35;
                }
                initMotesOfLight(motesCount, settings.snow_ignore_mouse);
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

            $('.leaderboard-card.animate-fade-in-up').on('animationend', function () {
                $(this).removeClass('animate-fade-in-up').css('opacity', '');
            });
        },

        InitInactiveTabs: function () {
            var $tabs = $('.inactive-tab');
            var $panels = $('.inactive-panel');
            var $searchInput = $('#inactive-search');

            if (!$tabs.length) return;

            Tracker.pendingTabRefresh = { inactive: false, flagged: false };
            Tracker.inactiveTables = {};

            $('.inactive-table').each(function () {
                var $table = $(this);
                var tableId = $table.closest('.inactive-panel').data('panel') || 'default';

                Tracker.inactiveTables[tableId] = $table.DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    order: [[2, 'asc']],
                    columnDefs: [
                        { targets: 0, orderable: false, visible: false },
                        { targets: -1, orderable: false }
                    ],
                    dom: 't'
                });
            });

            function activateTab(tabName, skipRefreshCheck) {
                if (!skipRefreshCheck && Tracker.pendingTabRefresh[tabName]) {
                    window.location.href = window.location.pathname + '#' + tabName;
                    window.location.reload();
                    return;
                }

                $tabs.removeClass('active');
                $panels.removeClass('active');

                $('.inactive-tab[data-tab="' + tabName + '"]').addClass('active');
                $('.inactive-panel[data-panel="' + tabName + '"]').addClass('active');

                if ($searchInput.val()) {
                    Tracker.FilterInactiveTable($searchInput.val());
                }

                if (Tracker.inactiveTables[tabName]) {
                    Tracker.inactiveTables[tabName].columns.adjust();
                }
            }

            $tabs.on('click', function () {
                var tabName = $(this).data('tab');
                activateTab(tabName);
                history.replaceState(null, null, '#' + tabName);
            });

            var hash = window.location.hash.replace('#', '');
            if (hash && $tabs.filter('[data-tab="' + hash + '"]').length) {
                activateTab(hash, true);
            }

            if ($searchInput.length) {
                $searchInput.on('input', function () {
                    Tracker.FilterInactiveTable($(this).val());
                });
            }
        },

        FilterInactiveTable: function (filter) {
            var activePanel = $('.inactive-panel.active').data('panel');
            var table = Tracker.inactiveTables && Tracker.inactiveTables[activePanel];
            if (table) {
                table.search(filter).draw();
            }
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
        },

        InitNoteReminderDetection: function () {
            var reminderPatterns = [
                /\bpm\s*sent\b/i,
                /\bdm\s*sent\b/i,
                /\bmsg\s*sent\b/i,
                /\bpm'?e?d\b/i,
                /\bsent\s*(a\s+)?(pm|dm|msg|message|notice|reminder)\b/i,
                /\binactiv(ity|e)?\s+(notice|reminder|warning|msg|message|pm|dm|check)\b/i,
                /\bactivity\s+(reminder|notice|pm|dm|msg|message)\b/i,
                /\b(reminder|notice)\s+sent\b/i,
                /\b(wellness|welfare)\s+(check|message)\b/i,
                /\bforum\s+(notice|reminder|pm|dm|inactiv)/i,
                /\bdiscord\s+(message|dm|pm|notice|reminder)/i,
                /\bfinal\s+(notice|reminder|warning)\b/i,
                /\b(1[04]|2[18]|30|35|4[05]|50|60|70|80|85|90)\s*[\-\+]*\s*day/i,
                /\b[1234]\s*week/i,
                /\b[123]\s*month/i,
                /\bmessaged\s+(regarding|about|for|on|via|through|re:?)/i,
                /\bcontacted\s+(regarding|about|for)/i,
                /\breached\s+out\s+(about|regarding|for|in|to)/i,
                /\b(days?|weeks?)\s+(inactive|inactiv|reminder|over|behind)\b/i,
                /\bno\s+(activity|response|reply)\b/i,
                /\binactive\s+(for|on|in|warning)\b/i,
                /\breminder\s+(to|about|for|sent|pm)\b/i,
                /\bpinged\s+for\s+inactiv/i
            ];

            var excludePatterns = [
                /\bremoved\s+(for|due\s+to)\s+inactiv/i,
                /\bflagged\s+(for|member|notice)\b/i,
                /\bloa\b/i,
                /\bleave\s+(of\s+absence|request|expired)\b/i,
                /\bpromot(ed|ion)\b/i,
                /\brecruit/i,
                /\bwelcome\b/i,
                /\bremoval\b/i,
                /\bresign/i,
                /\bviolation\b/i
            ];

            $(document).on('input', '.note-body-input', function () {
                var $input = $(this);
                var $suggestion = $input.siblings('.reminder-note-suggestion');
                var text = $input.val();

                if ($suggestion.data('dismissed')) return;

                var matchesReminder = text.length < 100 && reminderPatterns.some(function (pattern) {
                    return pattern.test(text);
                });

                var matchesExclude = excludePatterns.some(function (pattern) {
                    return pattern.test(text);
                });

                $suggestion.toggle(matchesReminder && !matchesExclude);
            });

            $(document).on('click', '.dismiss-suggestion', function () {
                var $suggestion = $(this).closest('.reminder-note-suggestion');
                $suggestion.data('dismissed', true).hide();
            });

            $('#create-member-note').on('hidden.bs.modal', function () {
                $(this).find('.reminder-note-suggestion').removeData('dismissed').hide();
            });
        },

        InitActivityReminderToggle: function () {
            var csrfToken = $('meta[name=csrf-token]').attr('content');

            $(document).on('click', '.activity-reminder-toggle', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var memberId = $btn.data('member-id');

                if ($btn.prop('disabled')) {
                    return;
                }

                $btn.prop('disabled', true);

                $.ajax({
                    url: window.Laravel.appPath + '/members/' + memberId + '/set-activity-reminder',
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: function(response) {
                        $btn.removeClass('btn-success').addClass('btn-default');
                        $btn.html('<i class="fa fa-bell"></i> <span class="reminded-date">' + response.date + '</span>');
                        $btn.attr('title', response.title);
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Failed to set reminder';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.set-activity-reminder-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var url = $btn.data('url');

                if ($btn.prop('disabled') || $btn.hasClass('reminder-sent')) {
                    return;
                }

                $btn.prop('disabled', true);
                var originalHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: function(response) {
                        $btn.addClass('reminder-sent');
                        $btn.html('<i class="fa fa-check"></i> Reminded ' + response.date);
                        toastr.success('Activity reminder marked');
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Failed to set reminder';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });

            $(document).on('click', '.clear-reminders-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var url = $btn.data('url');

                if ($btn.prop('disabled')) {
                    return;
                }

                if (!confirm('Clear all activity reminders for this member?')) {
                    return;
                }

                $btn.prop('disabled', true);
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Clearing...');

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function(response) {
                        toastr.success(response.count + ' reminder(s) cleared');
                        $('#member-reminder-history-modal').modal('hide');
                        $('.stat-reminder-badge').fadeOut();
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Failed to clear reminders';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fa fa-trash"></i> Clear Reminders');
                    }
                });
            });
        },

        InitTrashedNotes: function () {
            var $toggleBtn = $('.toggle-trashed-notes');
            if (!$toggleBtn.length) return;

            var csrfToken = $('meta[name=csrf-token]').attr('content');
            var trashedCount = $toggleBtn.data('count');

            $toggleBtn.on('click', function() {
                var $btn = $(this);
                var $active = $('.notes-active-list');
                var $trashed = $('.notes-trashed-list');

                if ($trashed.is(':visible')) {
                    $trashed.hide();
                    $active.show();
                    $btn.removeClass('btn-warning').addClass('btn-default');
                    $btn.html('<i class="fa fa-trash"></i> Deleted (' + trashedCount + ')');
                } else {
                    $active.hide();
                    $trashed.show();
                    $btn.removeClass('btn-default').addClass('btn-warning');
                    $btn.html('<i class="fa fa-sticky-note"></i> Active Notes');
                }
            });

            $(document).on('click', '.restore-note-btn', function() {
                var $btn = $(this);
                var url = $btn.data('url');
                var $card = $btn.closest('.note-card');

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: function() {
                        $card.fadeOut(300, function() { $(this).remove(); });
                        toastr.success('Note restored');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to restore note');
                        $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Restore');
                    }
                });
            });

            $(document).on('click', '.force-delete-note-btn', function() {
                var $btn = $(this);
                var url = $btn.data('url');
                var $card = $btn.closest('.note-card');

                if (!confirm('Permanently delete this note? This cannot be undone.')) {
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function() {
                        $card.fadeOut(300, function() { $(this).remove(); });
                        toastr.success('Note permanently deleted');
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete note');
                        $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete Forever');
                    }
                });
            });
        },

        InitInactiveBulkMode: function () {
            var $bulkToggle = $('.inactive-bulk-toggle');
            if (!$bulkToggle.length) return;

            var csrfToken = $('meta[name=csrf-token]').attr('content');
            var bulkMode = false;
            var isDragging = false;
            var dragSelectState = true;
            var dragStartRow = null;

            var tables = {
                inactive: {
                    bar: $('#inactive-bulk-bar'),
                    data: $('#inactive-pm-member-data'),
                    checkboxClass: 'inactive-member-checkbox',
                    selectAllClass: 'inactive-select-all',
                    tableClass: 'inactive-table:not(.flagged-table)'
                },
                flagged: {
                    bar: $('#flagged-bulk-bar'),
                    data: $('<input type="hidden" id="flagged-pm-member-data">'),
                    checkboxClass: 'flagged-member-checkbox',
                    selectAllClass: 'flagged-select-all',
                    tableClass: 'flagged-table'
                }
            };
            tables.flagged.bar.append(tables.flagged.data);

            function updateTabCount(tab, delta) {
                var $count = $('.inactive-tab[data-tab="' + tab + '"] .inactive-tab-count');
                var current = parseInt($count.text()) || 0;
                $count.text(Math.max(0, current + delta));
            }

            function createSelectionUpdater(config) {
                return function() {
                    var selected = $('.' + config.checkboxClass + ':checked').map(function() {
                        return $(this).val();
                    }).get();

                    config.data.val(selected.join(','));

                    if (selected.length > 0) {
                        config.bar.find('.status-text').text(selected.length + ' selected');
                        config.bar.slideDown(200);
                    } else {
                        config.bar.slideUp(200);
                    }

                    var total = $('.' + config.checkboxClass).length;
                    var checked = selected.length;
                    $('.' + config.selectAllClass).prop('checked', checked > 0 && checked === total);
                    $('.' + config.selectAllClass).prop('indeterminate', checked > 0 && checked < total);
                };
            }

            var updateSelection = createSelectionUpdater(tables.inactive);
            var updateFlaggedSelection = createSelectionUpdater(tables.flagged);

            function clearSelection(config, updateFn) {
                $('.' + config.checkboxClass + ', .' + config.selectAllClass).prop('checked', false);
                updateFn();
            }

            function bulkAction(options) {
                var memberIds = options.data.val();
                if (!memberIds) {
                    toastr.warning('No members selected');
                    return;
                }

                var $btn = options.btn;
                $btn.prop('disabled', true).html('<span class="themed-spinner spinner-sm"></span>');

                $.ajax({
                    url: options.url,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        member_ids: memberIds.split(',')
                    },
                    success: options.onSuccess,
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || options.errorMessage);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(options.buttonHtml);
                    }
                });
            }

            $bulkToggle.on('click', function() {
                bulkMode = !bulkMode;
                var $btn = $(this);
                $btn.toggleClass('active', bulkMode);
                $('.inactive-table').toggleClass('bulk-mode', bulkMode);

                if (Tracker.inactiveTables) {
                    Object.values(Tracker.inactiveTables).forEach(function(table) {
                        table.column(0).visible(bulkMode);
                    });
                }

                if (bulkMode) {
                    $btn.html('<i class="fa fa-times"></i> Exit Bulk Mode');
                } else {
                    $btn.html('Bulk Mode');
                    clearSelection(tables.inactive, updateSelection);
                    clearSelection(tables.flagged, updateFlaggedSelection);
                }
            });

            $(document).on('change', '.' + tables.inactive.checkboxClass, updateSelection);
            $(document).on('change', '.' + tables.flagged.checkboxClass, updateFlaggedSelection);

            $(document).on('mousedown', '.inactive-table tbody tr', function(e) {
                if (!bulkMode) return;
                if ($(e.target).is('a, button, input, .fa')) return;
                if (e.which !== 1) return;

                e.preventDefault();
                isDragging = true;
                dragStartRow = this;

                var isFlagged = $(this).closest('.flagged-table').length > 0;
                var checkboxClass = isFlagged ? tables.flagged.checkboxClass : tables.inactive.checkboxClass;
                var $checkbox = $(this).find('.' + checkboxClass);

                if ($checkbox.length) {
                    dragSelectState = !$checkbox.prop('checked');
                    $checkbox.prop('checked', dragSelectState);
                    $(this).toggleClass('drag-selected', dragSelectState);
                }
            });

            $(document).on('mouseenter', '.inactive-table tbody tr', function() {
                if (!isDragging || !bulkMode) return;

                var isFlagged = $(this).closest('.flagged-table').length > 0;
                var checkboxClass = isFlagged ? tables.flagged.checkboxClass : tables.inactive.checkboxClass;
                var $checkbox = $(this).find('.' + checkboxClass);

                if ($checkbox.length) {
                    $checkbox.prop('checked', dragSelectState);
                    $(this).toggleClass('drag-selected', dragSelectState);
                }
            });

            $(document).on('mouseup', function() {
                if (isDragging) {
                    var wasFlagged = dragStartRow && $(dragStartRow).closest('.flagged-table').length > 0;
                    isDragging = false;
                    dragStartRow = null;
                    $('.inactive-table tbody tr').removeClass('drag-selected');
                    (wasFlagged ? updateFlaggedSelection : updateSelection)();
                }
            });

            $(document).on('change', '.inactive-select-all, .flagged-select-all', function() {
                var isFlagged = $(this).hasClass('flagged-select-all');
                var config = isFlagged ? tables.flagged : tables.inactive;
                var updateFn = isFlagged ? updateFlaggedSelection : updateSelection;

                $(this).closest('.inactive-panel').find('.' + config.checkboxClass).prop('checked', $(this).prop('checked'));
                updateFn();
            });

            $(document).on('click', '.inactive-bulk-close', function() {
                var isFlagged = $(this).closest('.bulk-action-bar').attr('id') === 'flagged-bulk-bar';
                clearSelection(isFlagged ? tables.flagged : tables.inactive, isFlagged ? updateFlaggedSelection : updateSelection);
            });

            $('#inactive-bulk-reminder-btn').on('click', function() {
                bulkAction({
                    btn: $(this),
                    data: tables.inactive.data,
                    url: $(this).data('url'),
                    errorMessage: 'Failed to set reminders',
                    buttonHtml: '<i class="fa fa-bell text-accent"></i> <span class="hidden-xs hidden-sm">Reminder</span>',
                    onSuccess: function(response) {
                        var message = response.count + ' member' + (response.count !== 1 ? 's' : '') + ' marked as reminded';
                        if (response.skipped > 0) {
                            message += ' (' + response.skipped + ' skipped - already reminded today)';
                        }
                        toastr.success(message);

                        response.updatedIds.forEach(function(memberId) {
                            var $toggleBtn = $('.activity-reminder-toggle[data-member-id="' + memberId + '"]');
                            if ($toggleBtn.length) {
                                $toggleBtn.removeClass('btn-success').addClass('btn-default')
                                    .html('<i class="fa fa-bell"></i> <span class="reminded-date">' + response.date + '</span>')
                                    .attr('title', 'Reminded just now')
                                    .prop('disabled', true);
                            }
                        });

                        clearSelection(tables.inactive, updateSelection);
                    }
                });
            });

            $('#inactive-bulk-flag-btn').on('click', function() {
                bulkAction({
                    btn: $(this),
                    data: tables.inactive.data,
                    url: $(this).data('url'),
                    errorMessage: 'Failed to flag members',
                    buttonHtml: '<i class="fa fa-flag"></i> <span class="hidden-xs hidden-sm">Flag</span>',
                    onSuccess: function(response) {
                        toastr.success(response.message);

                        response.flaggedIds.forEach(function(memberId) {
                            $('.inactive-member-checkbox[value="' + memberId + '"]').closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                        });

                        clearSelection(tables.inactive, updateSelection);
                        updateTabCount('inactive', -response.count);
                        updateTabCount('flagged', response.count);

                        if (Tracker.pendingTabRefresh) {
                            Tracker.pendingTabRefresh.flagged = true;
                        }
                    }
                });
            });

            $('#flagged-bulk-unflag-btn').on('click', function() {
                bulkAction({
                    btn: $(this),
                    data: tables.flagged.data,
                    url: $(this).data('url'),
                    errorMessage: 'Failed to unflag members',
                    buttonHtml: '<i class="fa fa-flag"></i> <span class="hidden-xs hidden-sm">Unflag</span>',
                    onSuccess: function(response) {
                        toastr.success(response.message);

                        response.unflaggedIds.forEach(function(memberId) {
                            $('.flagged-member-checkbox[value="' + memberId + '"]').closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                if ($('.flagged-member-checkbox').length === 0) {
                                    $('.flagged-table').closest('.table-responsive').replaceWith(
                                        '<div class="inactive-empty">' +
                                        '<i class="fa fa-flag-o"></i>' +
                                        '<h4>No Flagged Members</h4>' +
                                        '<p>There are currently no members flagged for removal.</p>' +
                                        '</div>'
                                    );
                                }
                            });
                        });

                        clearSelection(tables.flagged, updateFlaggedSelection);
                        updateTabCount('flagged', -response.count);
                        updateTabCount('inactive', response.count);

                        if (Tracker.pendingTabRefresh) {
                            Tracker.pendingTabRefresh.inactive = true;
                        }
                    }
                });
            });
        },

        InitActivityFeedToggle: function () {
            $(document).on('show.bs.collapse', '.activity-group-members', function() {
                $('.activity-group-members.in').not(this).collapse('hide');
            });

            $(document).on('click', '.activity-feed-item', function(e) {
                if ($(e.target).closest('a').length) {
                    return;
                }

                var $toggle = $(this).find('.activity-group-toggle');
                if ($toggle.length) {
                    var $target = $($toggle.data('target'));
                    $target.collapse('toggle');
                }
            });
        }

    };

})(window.jQuery);

function initTracker() {
    var $ = window.jQuery;
    if (!$ || typeof $.fn.DataTable !== 'function' || typeof $.fn.bootcomplete !== 'function') {
        setTimeout(initTracker, 50);
        return;
    }
    Tracker.Setup();
}

initTracker();
