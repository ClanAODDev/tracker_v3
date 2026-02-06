var Tracker = Tracker || {};

(function ($) {

    const csrfToken = $('meta[name=csrf-token]').attr('content');

    Tracker = {

        Setup() {
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
            Tracker.InitWelcomeModal();
            Tracker.InitLeaderboardTabs();
            Tracker.InitInactiveTabs();
            Tracker.InitParttimerSearch();
            Tracker.InitAddParttimer();
            Tracker.InitNoteReminderDetection();
            Tracker.InitActivityReminderToggle();
            Tracker.InitInactiveBulkMode();
            Tracker.InitTrashedNotes();
            Tracker.InitActivityFeedToggle();
            Tracker.InitPopulationMeter();
        },

        InitNavToggle() {
            const $toggle = $('.left-nav-toggle a');
            if (!$toggle.length) return;

            $toggle.on('click', (e) => {
                e.preventDefault();
                $('body').toggleClass('nav-toggle');

                if ($('body').hasClass('nav-toggle')) {
                    $.get(`${window.Laravel.appPath}/primary-nav/collapse`);
                } else {
                    $.get(`${window.Laravel.appPath}/primary-nav/decollapse`);
                }

                Tracker.RefreshSparklines();
            });
        },

        InitBackToTop() {
            const $btn = $('#top-link-block');
            if (!$btn.length) return;

            $(window).on('scroll', () => {
                if ($(window).scrollTop() > 100) {
                    $btn.addClass('visible');
                } else {
                    $btn.removeClass('visible');
                }
            });
        },

        InitSmoothScroll() {
            $('.smooth-scroll').on('click', function (e) {
                const targetId = $(this).attr('href');
                if (!targetId || !targetId.startsWith('#')) return;

                e.preventDefault();
                const $target = $(targetId);
                if (!$target.length) return;

                const top = $target.offset().top - 90;
                $('html, body').stop().animate({ scrollTop: top }, 750, () => {
                    history.replaceState(null, null, targetId);
                    $(window).trigger('hashchange');
                });
            });
        },

        InitClipboard() {
            if (typeof Clipboard === 'undefined') return;
            if (!$('.copy-to-clipboard').length) return;

            const clipboard = new Clipboard('.copy-to-clipboard');
            clipboard.on('success', (e) => {
                toastr.success('Copied!');
                e.clearSelection();
            });
        },

        InitDataTables() {
            const $basicTable = $('table.basic-datatable');
            const $advTable = $('table.adv-datatable');

            if ($basicTable.length) {
                const basicDatatable = $basicTable.DataTable({
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
                    basicDatatable.on('select', () => {
                        const selected = basicDatatable.rows($('.selected')).data().toArray().map((row) => row[4]);
                        if (selected.length >= 1) {
                            $('#selected-data').show();
                            $('#selected-data .status-text').text(`${selected.length} member${selected.length === 1 ? '' : 's'} selected`);
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

        InitSparklines() {
            Tracker.RefreshSparklines();

            let resizeTimer;
            $(window).on('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(Tracker.RefreshSparklines, 100);
            });
        },

        RefreshSparklines() {
            $('[census-data]').each(function () {
                const $el = $(this);
                const inContainer = $el.closest('.census-sparkline-container').length > 0;
                const chartHeight = inContainer ? 80 : 50;

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
                const $el = $(this);
                $el.sparkline($el.data('counts'), {
                    type: 'pie',
                    sliceColors: $el.data('colors')
                });
            });
        },

        InitPanels() {
            $('.panel-toggle').on('click', (e) => {
                e.preventDefault();
                const $panel = $(e.target).closest('div.panel');
                const $icon = $(e.target).closest('i.toggle-icon');
                const $iconNotLinked = $(e.target).find('i.toggle-icon');

                $panel.find('div.panel-body').slideToggle(300);
                $panel.find('div.panel-footer').slideToggle(200);

                $icon.toggleClass('fa-chevron-up fa-chevron-down');
                $iconNotLinked.toggleClass('fa-chevron-up fa-chevron-down');
                $panel.toggleClass('panel-collapse');

                setTimeout(() => {
                    $panel.resize();
                    $panel.find('[id^=map-]').resize();
                }, 50);
            });

            $('.panel-close').on('click', (e) => {
                e.preventDefault();
                $(e.target).closest('div.panel').remove();
            });
        },

        InitSubNavCollapse() {
            $('.nav-second').on('show.bs.collapse', () => {
                $('.nav-second.in').collapse('hide');
            });
        },

        InitMemberAutocomplete() {
            const $search = $('.search-member');
            if (!$search.length) return;

            $search.bootcomplete({
                url: `${window.Laravel.appPath}/search-member/`,
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: { _token: csrfToken }
            });
        },

        InitMobileNav() {
            const $toggle = $('.mobile-nav-toggle');
            const $drawer = $('.mobile-nav-drawer');
            const $overlay = $('.mobile-nav-overlay');
            const $close = $('.mobile-nav-close');

            if (!$drawer.length) return;

            const openNav = () => {
                $drawer.addClass('active');
                $overlay.addClass('active');
                $('body').addClass('mobile-nav-open');
            };

            const closeNav = () => {
                $drawer.removeClass('active');
                $overlay.removeClass('active');
                $('body').removeClass('mobile-nav-open');
            };

            $toggle.on('click', (e) => {
                e.preventDefault();
                if ($drawer.hasClass('active')) {
                    closeNav();
                } else {
                    openNav();
                }
            });

            $close.on('click', (e) => {
                e.preventDefault();
                closeNav();
            });

            $overlay.on('click', closeNav);

            $drawer.find('a').on('click', function () {
                const $link = $(this);
                if ($link.attr('data-toggle') === 'collapse' || $link.attr('href') === '#') {
                    return;
                }
                closeNav();
            });
        },

        InitMobileSearch() {
            const $toggle = $('.mobile-search-toggle');
            const $modal = $('.mobile-search-modal');
            const $input = $('#mobile-member-search');
            const $close = $('.mobile-search-close');
            const $clear = $('.mobile-searchclear');
            const $results = $('.mobile-search-results');
            const $loader = $('.mobile-search-loader');

            if (!$modal.length) return;

            let timer = null;
            const delay = 500;

            const openSearch = () => {
                $modal.addClass('active');
                $('body').addClass('mobile-search-open');
                $input.focus();
            };

            const closeSearch = () => {
                $modal.removeClass('active');
                $('body').removeClass('mobile-search-open');
                $input.val('');
                $clear.removeClass('visible');
                $results.empty();
                $loader.removeClass('active');
            };

            const performSearch = () => {
                const query = $input.val().trim();
                if (!query) {
                    $loader.removeClass('active');
                    $results.empty();
                    return;
                }

                $.ajax({
                    url: `${window.Laravel.appPath}/search/members`,
                    type: 'GET',
                    data: { q: query },
                    success: (response) => {
                        $loader.removeClass('active');
                        $results.html(response);
                    }
                });
            };

            $toggle.on('click', (e) => {
                e.preventDefault();
                openSearch();
            });

            $close.on('click', closeSearch);

            $clear.on('click', () => {
                $input.val('').focus();
                $clear.removeClass('visible');
                $results.empty();
            });

            $input.on('input', () => {
                const value = $input.val().trim();
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

            $input.on('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeSearch();
                }
            });
        },

        InitRarityFilter() {
            const $rarityFilters = $('.rarity-filter');
            const $divisionSelect = $('#division-filter');
            if (!$rarityFilters.length && !$divisionSelect.length) return;

            const applyRarityFilter = () => {
                const activeRarities = $('.rarity-filter.active').map(function () {
                    return $(this).data('rarity');
                }).get();

                $('.award-card').each(function () {
                    const $card = $(this);
                    const $col = $card.closest('[class*="col-"]');
                    let cardRarity = null;

                    ['unclaimed', 'mythic', 'legendary', 'epic', 'rare', 'common'].forEach((r) => {
                        if ($card.hasClass(`award-card-${r}`)) {
                            cardRarity = r;
                        }
                    });

                    const show = activeRarities.length === 0 || activeRarities.indexOf(cardRarity) !== -1;
                    const wasHidden = $col.hasClass('filter-hidden');

                    if (show) {
                        $col.removeClass('filter-hidden filter-hiding');
                        if (wasHidden) {
                            $col.addClass('filter-entering');
                            setTimeout(() => {
                                $col.removeClass('filter-entering').addClass('filter-visible');
                            }, 300);
                        } else {
                            $col.addClass('filter-visible');
                        }
                    } else if (!$col.hasClass('filter-hidden') && !$col.hasClass('filter-hiding')) {
                        $col.removeClass('filter-visible filter-entering').addClass('filter-hiding');
                        setTimeout(() => {
                            $col.removeClass('filter-hiding').addClass('filter-hidden');
                        }, 250);
                    }
                });
            };

            $rarityFilters.on('click', function () {
                $(this).toggleClass('active');
                applyRarityFilter();
            });

            $divisionSelect.on('change', function () {
                const division = $(this).val();
                const url = new URL(window.location.href);
                if (division) {
                    url.searchParams.set('division', division);
                } else {
                    url.searchParams.delete('division');
                }
                window.location.href = url.toString();
            });

            const $raritySort = $('#rarity-sort');
            const rarityOrder = ['mythic', 'legendary', 'epic', 'rare', 'common', 'unclaimed'];

            const sortAwards = (sortType) => {
                $('.award-grid').each(function () {
                    const $grid = $(this);
                    const $items = $grid.children('[class*="col-"]').detach().toArray();

                    if (sortType === 'default') {
                        $items.sort((a, b) => {
                            const orderA = parseInt($(a).data('original-order')) || 0;
                            const orderB = parseInt($(b).data('original-order')) || 0;
                            return orderA - orderB;
                        });
                    } else {
                        $items.sort((a, b) => {
                            const $cardA = $(a).find('.award-card');
                            const $cardB = $(b).find('.award-card');
                            let rarityA = 5, rarityB = 5;

                            rarityOrder.forEach((r, idx) => {
                                if ($cardA.hasClass(`award-card-${r}`)) rarityA = idx;
                                if ($cardB.hasClass(`award-card-${r}`)) rarityB = idx;
                            });

                            return sortType === 'rarity-desc' ? rarityA - rarityB : rarityB - rarityA;
                        });
                    }

                    $items.forEach((item, idx) => {
                        const $item = $(item);
                        if (!$item.data('original-order')) {
                            $item.attr('data-original-order', idx);
                        }
                        $item.removeClass('filter-visible filter-entering filter-hiding filter-hidden');
                        $grid.append(item);
                    });

                    setTimeout(() => {
                        $grid.children('[class*="col-"]').each(function (idx) {
                            const $el = $(this);
                            setTimeout(() => {
                                $el.addClass('filter-entering');
                                setTimeout(() => {
                                    $el.removeClass('filter-entering').addClass('filter-visible');
                                }, 300);
                            }, idx * 30);
                        });
                    }, 10);
                });
            };

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

        InitPopulationMeter() {
            const $meters = $('.tier-population-meter');
            if (!$meters.length) return;

            const barDelay = 25;
            const tierDelay = 400;

            const calculateBarCount = ($meter) => {
                const width = $meter.width();
                const barWidth = 4;
                const gap = 4;
                let count = Math.floor(width / (barWidth + gap));
                if (count < 5) count = 5;
                if (count > 80) count = 80;
                return count;
            };

            const renderMeter = ($meter, animate, tierIndex) => {
                const pct = parseInt($meter.data('pct')) || 0;
                const barCount = calculateBarCount($meter);
                const activeBars = Math.round((pct / 100) * barCount);

                $meter.empty();

                for (let i = 0; i < barCount; i++) {
                    const $bar = $('<div class="tier-population-bar"></div>');
                    const isActive = i < activeBars;

                    if (isActive) {
                        if (animate) {
                            $bar.addClass('animate-pending');
                            const delay = (tierIndex * tierDelay) + (i * barDelay);
                            setTimeout(() => {
                                $bar.removeClass('animate-pending').addClass('active');
                            }, delay);
                        } else {
                            $bar.addClass('active');
                        }
                    }
                    $meter.append($bar);
                }
            };

            const renderAll = (animate) => {
                $meters.each(function (index) {
                    renderMeter($(this), animate, index);
                });
            };

            renderAll(true);

            let resizeTimer;
            $(window).on('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    renderAll(false);
                }, 150);
            });
        },

        InitMemberSearch() {
            const $input = $('#member-search');
            if (!$input.length) return;

            const $loader = $('.desktop-search-loader');
            const $clearBtn = $('#searchclear');
            let timer = null;
            const delay = 1000;

            const triggerSearch = () => {
                $loader.addClass('active');

                if (timer) {
                    clearTimeout(timer);
                }

                timer = setTimeout(() => {
                    timer = null;
                    const value = $input.val().trim();
                    if (!value) {
                        $loader.removeClass('active');
                        return;
                    }
                    Tracker.GetSearchResults();
                }, delay);
            };

            $input.on('keydown', triggerSearch);
            $input.on('paste', () => {
                setTimeout(triggerSearch, 0);
            });
            $input.on('input', () => {
                const hasValue = $input.val().trim();
                if (!hasValue) {
                    $loader.removeClass('active');
                    $clearBtn.removeClass('visible');
                }
            });

            const clearSearch = () => {
                $('section.search-results').addClass('closed').removeClass('open');
                $('body').removeClass('search-active');
                $('.content').css('margin-top', '');
                $input.val('');
                $clearBtn.removeClass('visible');
            };

            $clearBtn.on('click', clearSearch);

            $('.content').on('click', () => {
                if ($('body').hasClass('search-active')) {
                    clearSearch();
                }
            });
        },

        GetSearchResults() {
            const name = $('#member-search').val();
            if (!name) return;

            $.ajax({
                url: `${window.Laravel.appPath}/search/members`,
                type: 'GET',
                data: { q: name },
                success: (response) => {
                    window.scrollTo(0, 0);
                    $('.desktop-search-loader').removeClass('active');
                    $('#searchclear').addClass('visible');

                    const $results = $('section.search-results');
                    $results.html(response).addClass('open').removeClass('closed');
                    $('body').addClass('search-active');

                    setTimeout(() => {
                        $('.content').css('margin-top', '20px');
                    }, 50);
                }
            });
        },

        InitCollectionSearch() {
            const $input = $('#search-collection');
            if (!$input.length) return;

            $input.on('keyup', function () {
                const value = $(this).val();
                const exp = new RegExp(`^${value}`, 'i');

                $('.collection .collection-item').each(function () {
                    $(this).toggle(exp.test($(this).text()));
                });
            });
        },

        InitRepeater() {
            const $repeater = $('.repeater');
            if (!$repeater.length) return;

            $repeater.repeater({
                isFirstItemUndeletable: true
            });
        },

        InitTabActivate() {
            const $tabs = $('.nav-tabs');
            if (!$tabs.length) return;

            $tabs.stickyTabs();

            $('a[data-toggle="tab"]').on('shown.bs.tab', () => {
                $.sparkline_display_visible();
            });
        },

        InitSettings() {
            const $toggle = $('.settings-toggle, .mobile-settings-toggle');
            const $overlay = $('.settings-overlay');
            const $slideover = $('.settings-slideover');
            const $close = $('.settings-close');

            if (!$toggle.length) return;

            const openSettings = () => {
                $overlay.addClass('active');
                $slideover.addClass('active');
                $toggle.addClass('active');
                $('body').addClass('settings-open');
            };

            const closeSettings = () => {
                $overlay.removeClass('active');
                $slideover.removeClass('active');
                $toggle.removeClass('active');
                $('body').removeClass('settings-open');
            };

            $toggle.on('click', (e) => {
                e.preventDefault();
                if ($slideover.hasClass('active')) {
                    closeSettings();
                } else {
                    openSettings();
                }
            });

            $overlay.on('click', closeSettings);
            $close.on('click', closeSettings);

            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && $slideover.hasClass('active')) {
                    closeSettings();
                }
            });

            let saveTimer = null;
            const $status = $('.settings-save-status');

            const saveSettings = () => {
                if (saveTimer) clearTimeout(saveTimer);

                $status.text('Saving...').removeClass('saved error').addClass('saving');

                const formData = {
                    _token: $('input[name="_token"]').val(),
                    disable_animations: $('#setting-disable-animations').is(':checked'),
                    mobile_nav_side: $('#setting-mobile-nav-side').val(),
                    snow: $('#setting-snow').val(),
                    snow_ignore_mouse: $('#setting-snow-ignore-mouse').is(':checked'),
                    theme: $('#setting-theme').val()
                };

                $.ajax({
                    url: `${window.Laravel.appPath}/settings`,
                    type: 'POST',
                    data: formData,
                    success: () => {
                        $status.text('Saved').removeClass('saving error').addClass('saved');
                        saveTimer = setTimeout(() => {
                            $status.text('').removeClass('saved');
                        }, 2000);

                        Tracker.ApplySettings(formData);
                    },
                    error: () => {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    }
                });
            };

            $('#setting-disable-animations, #setting-snow-ignore-mouse').on('change', saveSettings);

            $('.settings-btn[data-setting]').on('click', function () {
                const $btn = $(this);
                const setting = $btn.data('setting');
                const value = $btn.data('value');

                $btn.siblings('.settings-btn').removeClass('active');
                $btn.addClass('active');
                $(`#setting-${setting.split('_').join('-')}`).val(value);

                if (setting === 'theme') {
                    updateThemeSettings(value);
                }

                if (setting === 'snow') {
                    updateSnowMouseSetting(value);
                }

                saveSettings();
            });

            const updateSnowMouseSetting = (snowValue) => {
                $('#snow-mouse-setting').toggle(snowValue !== 'no_snow');
            };

            const updateThemeSettings = (theme) => {
                const isShattrath = theme === 'shattrath';
                $('.settings-btn[data-value="motes"]').toggle(isShattrath);

                if (!isShattrath && $('#setting-snow').val() === 'motes') {
                    $('#setting-snow').val('no_snow');
                    $('.settings-btn[data-setting="snow"]').removeClass('active');
                    $('.settings-btn[data-value="no_snow"]').addClass('active');
                    updateSnowMouseSetting('no_snow');
                }

                const favicon = document.getElementById('favicon');
                if (favicon) {
                    const logoMap = {
                        'shattrath': '/images/logo-shattrath.svg',
                        'aod': '/images/logo-aod.svg'
                    };
                    favicon.href = logoMap[theme] || '/images/logo_v2.svg';
                }
            };

            updateThemeSettings($('#setting-theme').val());
        },

        ApplySettings(settings) {
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
                let flakesMax = 0;
                if (settings.snow === 'some_snow') {
                    flakesMax = 32;
                } else if (settings.snow === 'all_the_snow') {
                    flakesMax = 160;
                }
                initSnowStorm(flakesMax, settings.snow_ignore_mouse);
            }

            if (typeof initMotesOfLight === 'function') {
                let motesCount = 0;
                if (settings.snow === 'motes') {
                    motesCount = 35;
                }
                initMotesOfLight(motesCount, settings.snow_ignore_mouse);
            }
        },

        InitProfileModals() {
            const $partTimeModal = $('#part-time-divisions-modal');
            const $handlesModal = $('#ingame-handles-modal');
            const $transferModal = $('#transfer-request-modal');

            if (!$partTimeModal.length && !$handlesModal.length && !$transferModal.length) return;

            $partTimeModal.add($handlesModal).add($transferModal).on('show.bs.modal', () => {
                $('.settings-overlay').removeClass('active');
                $('.settings-slideover').removeClass('active');
                $('.settings-toggle, .mobile-settings-toggle').removeClass('active');
                $('body').removeClass('settings-open');
            });

            $('#save-part-time-divisions').on('click', function () {
                const $btn = $(this);
                const $status = $partTimeModal.find('.modal-save-status');
                const divisions = [];

                $partTimeModal.find('input[name="divisions[]"]:checked').each(function () {
                    divisions.push($(this).val());
                });

                $btn.prop('disabled', true);
                $status.text('Saving...').removeClass('saved error').addClass('saving');

                $.ajax({
                    url: `${window.Laravel.appPath}/settings/part-time-divisions`,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        divisions: divisions
                    },
                    success: (response) => {
                        $status.text('Saved!').removeClass('saving error').addClass('saved');
                        $('.settings-link-btn[data-target="#part-time-divisions-modal"] .settings-link-count').text(response.count);
                        setTimeout(() => {
                            $partTimeModal.modal('hide');
                            $status.text('').removeClass('saved');
                        }, 1000);
                    },
                    error: () => {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    },
                    complete: () => {
                        $btn.prop('disabled', false);
                    }
                });
            });

            let handleIndex = $('#handles-container .handle-row').length;

            $('#add-handle').on('click', () => {
                const template = $('#handle-row-template').html();
                const newRow = template.replace(/__INDEX__/g, handleIndex);
                $('#handles-container').append(newRow);
                handleIndex++;
            });

            $(document).on('click', '.remove-handle', function () {
                $(this).closest('.handle-row').remove();
            });

            $('#save-ingame-handles').on('click', function () {
                const $btn = $(this);
                const $status = $handlesModal.find('.modal-save-status');
                const handles = [];

                $('#handles-container .handle-row').each(function () {
                    const $row = $(this);
                    const handleId = $row.find('.handle-select').val();
                    const value = $row.find('input[type="text"]').val();

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
                    url: `${window.Laravel.appPath}/settings/ingame-handles`,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        handles: handles
                    },
                    success: (response) => {
                        $status.text('Saved!').removeClass('saving error').addClass('saved');
                        $('.settings-link-btn[data-target="#ingame-handles-modal"] .settings-link-count').text(response.count);
                        setTimeout(() => {
                            $handlesModal.modal('hide');
                            $status.text('').removeClass('saved');
                        }, 1000);
                    },
                    error: () => {
                        $status.text('Error saving').removeClass('saving saved').addClass('error');
                    },
                    complete: () => {
                        $btn.prop('disabled', false);
                    }
                });
            });

            $('#save-transfer-request').on('click', function () {
                const $btn = $(this);
                const divisionId = $('#transfer-division-select').val();

                if (!divisionId) {
                    toastr.warning('Please select a division');
                    return;
                }

                $btn.prop('disabled', true);

                $.ajax({
                    url: `${window.Laravel.appPath}/settings/transfer-request`,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        division_id: divisionId
                    },
                    success: (response) => {
                        $transferModal.modal('hide');
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.error || 'Error submitting request';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                    }
                });
            });

            $transferModal.on('hidden.bs.modal', () => {
                $('#transfer-division-select').val('');
                $('#save-transfer-request').prop('disabled', false);
            });
        },

        InitNoSquadModal() {
            const $modal = $('#no-squad-modal');
            if (!$modal.length) return;

            let loaded = false;

            $modal.on('show.bs.modal', () => {
                if (loaded) return;

                const url = $modal.data('url');

                $.get(url, (response) => {
                    const $list = $('#no-squad-list');
                    $list.empty();

                    if (response.members.length === 0) {
                        $list.html('<p class="text-muted">No members found.</p>');
                    } else {
                        const grouped = {};
                        response.members.forEach((member) => {
                            if (!grouped[member.platoon]) {
                                grouped[member.platoon] = { members: [], manage_url: member.manage_url };
                            }
                            grouped[member.platoon].members.push(member);
                        });

                        Object.keys(grouped).sort().forEach((platoon) => {
                            const group = grouped[platoon];
                            const $group = $('<div class="no-squad-group"></div>');
                            $group.append(
                                `<div class="no-squad-platoon-header">` +
                                `<span>${platoon}</span>` +
                                `<a href="${group.manage_url}" class="btn btn-sm btn-accent">` +
                                '<i class="fa fa-arrows-alt"></i> Assign</a>' +
                                '</div>'
                            );
                            const $members = $('<div class="no-squad-members"></div>');
                            group.members.forEach((member) => {
                                $members.append(`<span class="no-squad-member">${member.name}</span>`);
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

        InitWelcomeModal() {
            const $modal = $('#welcome-modal');
            if (!$modal.length) return;

            $modal.modal('show');

            $modal.on('hidden.bs.modal', () => {
                $.ajax({
                    url: `${window.Laravel.appPath}/settings`,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        welcomed: true
                    }
                });
            });
        },

        InitLeaderboardTabs() {
            const $tabs = $('.leaderboard-tab');
            const $panels = $('.leaderboard-panel');
            if (!$tabs.length) return;

            $tabs.on('click', function () {
                const tabName = $(this).data('tab');

                $tabs.removeClass('active');
                $panels.removeClass('active');

                $(this).addClass('active');
                $(`.leaderboard-panel[data-panel="${tabName}"]`).addClass('active');
            });

            $('.leaderboard-card.animate-fade-in-up').on('animationend', function () {
                $(this).removeClass('animate-fade-in-up').css('opacity', '');
            });
        },

        InitInactiveTabs() {
            const $tabs = $('.inactive-tab');
            const $panels = $('.inactive-panel');
            const $searchInput = $('#inactive-search');

            if (!$tabs.length) return;

            Tracker.pendingTabRefresh = { inactive: false, flagged: false };
            Tracker.inactiveTables = {};

            $('.inactive-table').each(function () {
                const $table = $(this);
                const tableId = $table.closest('.inactive-panel').data('panel') || 'default';

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

            const activateTab = (tabName, skipRefreshCheck) => {
                if (!skipRefreshCheck && Tracker.pendingTabRefresh[tabName]) {
                    window.location.href = `${window.location.pathname}#${tabName}`;
                    window.location.reload();
                    return;
                }

                $tabs.removeClass('active');
                $panels.removeClass('active');

                $(`.inactive-tab[data-tab="${tabName}"]`).addClass('active');
                $(`.inactive-panel[data-panel="${tabName}"]`).addClass('active');

                if ($searchInput.val()) {
                    Tracker.FilterInactiveTable($searchInput.val());
                }

                if (Tracker.inactiveTables[tabName]) {
                    Tracker.inactiveTables[tabName].columns.adjust();
                }
            };

            $tabs.on('click', function () {
                const tabName = $(this).data('tab');
                activateTab(tabName);
                history.replaceState(null, null, `#${tabName}`);
            });

            const hash = window.location.hash.replace('#', '');
            if (hash && $tabs.filter(`[data-tab="${hash}"]`).length) {
                activateTab(hash, true);
            }

            if ($searchInput.length) {
                $searchInput.on('input', function () {
                    Tracker.FilterInactiveTable($(this).val());
                });
            }
        },

        FilterInactiveTable(filter) {
            const activePanel = $('.inactive-panel.active').data('panel');
            const table = Tracker.inactiveTables && Tracker.inactiveTables[activePanel];
            if (table) {
                table.search(filter).draw();
            }
        },

        InitParttimerSearch() {
            const $searchInput = $('#parttimer-search');
            if (!$searchInput.length) return;

            $searchInput.on('input', function () {
                const filter = $(this).val().toLowerCase();
                $('.inactive-panel.active tbody tr').each(function () {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(filter) !== -1);
                });
            });
        },

        InitAddParttimer() {
            const $modal = $('#add-parttimer-modal');
            if (!$modal.length) return;

            const $searchInput = $('#parttimer-member-search');
            const $memberIdField = $('#parttimer-member-id');
            const $selectedDisplay = $('#parttimer-selected-member');
            const $selectedName = $selectedDisplay.find('.selected-member-name');
            const $submitBtn = $('#add-parttimer-submit');

            $searchInput.bootcomplete({
                url: `${window.Laravel.appPath}/search-member/`,
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: { _token: csrfToken }
            });

            $searchInput.on('bootcomplete.selected', (e, id, label) => {
                $memberIdField.val(id);
                $selectedName.text(label);
                $selectedDisplay.show();
                $searchInput.hide();
                $submitBtn.prop('disabled', false);
            });

            $selectedDisplay.on('click', '.clear-selected-member', () => {
                $memberIdField.val('');
                $selectedName.text('');
                $selectedDisplay.hide();
                $searchInput.val('').show().focus();
                $submitBtn.prop('disabled', true);
            });

            $modal.on('hidden.bs.modal', () => {
                $memberIdField.val('');
                $selectedName.text('');
                $selectedDisplay.hide();
                $searchInput.val('').show();
                $submitBtn.prop('disabled', true);
                $('#parttimer-handle-value').val('');
            });
        },

        InitNoteReminderDetection() {
            const reminderPatterns = [
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

            const excludePatterns = [
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
                const $input = $(this);
                const $suggestion = $input.siblings('.reminder-note-suggestion');
                const text = $input.val();

                if ($suggestion.data('dismissed')) return;

                const matchesReminder = text.length < 100 && reminderPatterns.some((pattern) => pattern.test(text));

                const matchesExclude = excludePatterns.some((pattern) => pattern.test(text));

                $suggestion.toggle(matchesReminder && !matchesExclude);
            });

            $(document).on('click', '.dismiss-suggestion', function () {
                const $suggestion = $(this).closest('.reminder-note-suggestion');
                $suggestion.data('dismissed', true).hide();
            });

            $('#create-member-note').on('hidden.bs.modal', function () {
                $(this).find('.reminder-note-suggestion').removeData('dismissed').hide();
            });
        },

        InitActivityReminderToggle() {
            $(document).on('click', '.activity-reminder-toggle', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const memberId = $btn.data('member-id');

                if ($btn.prop('disabled')) {
                    return;
                }

                $btn.prop('disabled', true);

                $.ajax({
                    url: `${window.Laravel.appPath}/members/${memberId}/set-activity-reminder`,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: (response) => {
                        $btn.removeClass('btn-success').addClass('btn-default');
                        $btn.html(`<i class="fa fa-bell"></i> <span class="reminded-date">${response.date}</span>`);
                        $btn.attr('title', response.title);
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.message || 'Failed to set reminder';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.set-activity-reminder-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const url = $btn.data('url');

                if ($btn.prop('disabled') || $btn.hasClass('reminder-sent')) {
                    return;
                }

                $btn.prop('disabled', true);
                const originalHtml = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: (response) => {
                        $btn.addClass('reminder-sent');
                        $btn.html(`<i class="fa fa-check"></i> Reminded ${response.date}`);
                        toastr.success('Activity reminder marked');
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.message || 'Failed to set reminder';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });

            $(document).on('click', '.clear-reminders-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const url = $btn.data('url');

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
                    success: (response) => {
                        toastr.success(`${response.count} reminder(s) cleared`);
                        $('#member-reminder-history-modal').modal('hide');
                        $('.stat-reminder-badge').fadeOut();
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.message || 'Failed to clear reminders';
                        toastr.error(message);
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fa fa-trash"></i> Clear Reminders');
                    }
                });
            });
        },

        InitTrashedNotes() {
            const $toggleBtn = $('.toggle-trashed-notes');
            if (!$toggleBtn.length) return;

            const trashedCount = $toggleBtn.data('count');

            $toggleBtn.on('click', function() {
                const $btn = $(this);
                const $active = $('.notes-active-list');
                const $trashed = $('.notes-trashed-list');

                if ($trashed.is(':visible')) {
                    $trashed.hide();
                    $active.show();
                    $btn.removeClass('btn-warning').addClass('btn-default');
                    $btn.html(`<i class="fa fa-trash"></i> Deleted (${trashedCount})`);
                } else {
                    $active.hide();
                    $trashed.show();
                    $btn.removeClass('btn-default').addClass('btn-warning');
                    $btn.html('<i class="fa fa-sticky-note"></i> Active Notes');
                }
            });

            $(document).on('click', '.restore-note-btn', function() {
                const $btn = $(this);
                const url = $btn.data('url');
                const $card = $btn.closest('.note-card');

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: () => {
                        $card.fadeOut(300, function() { $(this).remove(); });
                        toastr.success('Note restored');
                    },
                    error: (xhr) => {
                        toastr.error(xhr.responseJSON?.message || 'Failed to restore note');
                        $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Restore');
                    }
                });
            });

            $(document).on('click', '.force-delete-note-btn', function() {
                const $btn = $(this);
                const url = $btn.data('url');
                const $card = $btn.closest('.note-card');

                if (!confirm('Permanently delete this note? This cannot be undone.')) {
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: () => {
                        $card.fadeOut(300, function() { $(this).remove(); });
                        toastr.success('Note permanently deleted');
                    },
                    error: (xhr) => {
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete note');
                        $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete Forever');
                    }
                });
            });
        },

        InitInactiveBulkMode() {
            const $bulkToggle = $('.inactive-bulk-toggle');
            if (!$bulkToggle.length) return;

            let bulkMode = false;
            let isDragging = false;
            let dragSelectState = true;
            let dragStartRow = null;

            const tables = {
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

            const updateTabCount = (tab, delta) => {
                const $count = $(`.inactive-tab[data-tab="${tab}"] .inactive-tab-count`);
                const current = parseInt($count.text()) || 0;
                $count.text(Math.max(0, current + delta));
            };

            const createSelectionUpdater = (config) => {
                return () => {
                    const selected = $(`.${config.checkboxClass}:checked`).map(function() {
                        return $(this).val();
                    }).get();

                    config.data.val(selected.join(','));

                    if (selected.length > 0) {
                        config.bar.find('.status-text').text(`${selected.length} selected`);
                        config.bar.slideDown(200);
                    } else {
                        config.bar.slideUp(200);
                    }

                    const total = $(`.${config.checkboxClass}`).length;
                    const checked = selected.length;
                    $(`.${config.selectAllClass}`).prop('checked', checked > 0 && checked === total);
                    $(`.${config.selectAllClass}`).prop('indeterminate', checked > 0 && checked < total);
                };
            };

            const updateSelection = createSelectionUpdater(tables.inactive);
            const updateFlaggedSelection = createSelectionUpdater(tables.flagged);

            const clearSelection = (config, updateFn) => {
                $(`.${config.checkboxClass}, .${config.selectAllClass}`).prop('checked', false);
                updateFn();
            };

            const bulkAction = (options) => {
                const memberIds = options.data.val();
                if (!memberIds) {
                    toastr.warning('No members selected');
                    return;
                }

                const $btn = options.btn;
                $btn.prop('disabled', true).html('<span class="themed-spinner spinner-sm"></span>');

                $.ajax({
                    url: options.url,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        member_ids: memberIds.split(',')
                    },
                    success: options.onSuccess,
                    error: (xhr) => {
                        toastr.error(xhr.responseJSON?.message || options.errorMessage);
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html(options.buttonHtml);
                    }
                });
            };

            $bulkToggle.on('click', function() {
                bulkMode = !bulkMode;
                const $btn = $(this);
                $btn.toggleClass('active', bulkMode);
                $('.inactive-table').toggleClass('bulk-mode', bulkMode);

                if (Tracker.inactiveTables) {
                    Object.values(Tracker.inactiveTables).forEach((table) => {
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

            $(document).on('change', `.${tables.inactive.checkboxClass}`, updateSelection);
            $(document).on('change', `.${tables.flagged.checkboxClass}`, updateFlaggedSelection);

            $(document).on('mousedown', '.inactive-table tbody tr', function(e) {
                if (!bulkMode) return;
                if ($(e.target).is('a, button, input, .fa')) return;
                if (e.which !== 1) return;

                e.preventDefault();
                isDragging = true;
                dragStartRow = this;

                const isFlagged = $(this).closest('.flagged-table').length > 0;
                const checkboxClass = isFlagged ? tables.flagged.checkboxClass : tables.inactive.checkboxClass;
                const $checkbox = $(this).find(`.${checkboxClass}`);

                if ($checkbox.length) {
                    dragSelectState = !$checkbox.prop('checked');
                    $checkbox.prop('checked', dragSelectState);
                    $(this).toggleClass('drag-selected', dragSelectState);
                }
            });

            $(document).on('mouseenter', '.inactive-table tbody tr', function() {
                if (!isDragging || !bulkMode) return;

                const isFlagged = $(this).closest('.flagged-table').length > 0;
                const checkboxClass = isFlagged ? tables.flagged.checkboxClass : tables.inactive.checkboxClass;
                const $checkbox = $(this).find(`.${checkboxClass}`);

                if ($checkbox.length) {
                    $checkbox.prop('checked', dragSelectState);
                    $(this).toggleClass('drag-selected', dragSelectState);
                }
            });

            $(document).on('mouseup', () => {
                if (isDragging) {
                    const wasFlagged = dragStartRow && $(dragStartRow).closest('.flagged-table').length > 0;
                    isDragging = false;
                    dragStartRow = null;
                    $('.inactive-table tbody tr').removeClass('drag-selected');
                    (wasFlagged ? updateFlaggedSelection : updateSelection)();
                }
            });

            $(document).on('change', '.inactive-select-all, .flagged-select-all', function() {
                const isFlagged = $(this).hasClass('flagged-select-all');
                const config = isFlagged ? tables.flagged : tables.inactive;
                const updateFn = isFlagged ? updateFlaggedSelection : updateSelection;

                $(this).closest('.inactive-panel').find(`.${config.checkboxClass}`).prop('checked', $(this).prop('checked'));
                updateFn();
            });

            $(document).on('click', '.inactive-bulk-close', function() {
                const isFlagged = $(this).closest('.bulk-action-bar').attr('id') === 'flagged-bulk-bar';
                clearSelection(isFlagged ? tables.flagged : tables.inactive, isFlagged ? updateFlaggedSelection : updateSelection);
            });

            $('#inactive-bulk-reminder-btn').on('click', function() {
                bulkAction({
                    btn: $(this),
                    data: tables.inactive.data,
                    url: $(this).data('url'),
                    errorMessage: 'Failed to set reminders',
                    buttonHtml: '<i class="fa fa-bell text-accent"></i> <span class="hidden-xs hidden-sm">Reminder</span>',
                    onSuccess: (response) => {
                        let message = `${response.count} member${response.count !== 1 ? 's' : ''} marked as reminded`;
                        if (response.skipped > 0) {
                            message += ` (${response.skipped} skipped - already reminded today)`;
                        }
                        toastr.success(message);

                        response.updatedIds.forEach((memberId) => {
                            const $toggleBtn = $(`.activity-reminder-toggle[data-member-id="${memberId}"]`);
                            if ($toggleBtn.length) {
                                $toggleBtn.removeClass('btn-success').addClass('btn-default')
                                    .html(`<i class="fa fa-bell"></i> <span class="reminded-date">${response.date}</span>`)
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
                    onSuccess: (response) => {
                        toastr.success(response.message);

                        response.flaggedIds.forEach((memberId) => {
                            $(`.inactive-member-checkbox[value="${memberId}"]`).closest('tr').fadeOut(300, function() {
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
                    onSuccess: (response) => {
                        toastr.success(response.message);

                        response.unflaggedIds.forEach((memberId) => {
                            $(`.flagged-member-checkbox[value="${memberId}"]`).closest('tr').fadeOut(300, function() {
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

        InitActivityFeedToggle() {
            $(document).on('show.bs.collapse', '.activity-group-members', function() {
                $('.activity-group-members.in').not(this).collapse('hide');
            });

            $(document).on('click', '.activity-feed-item', function(e) {
                if ($(e.target).closest('a').length) {
                    return;
                }

                const $toggle = $(this).find('.activity-group-toggle');
                if ($toggle.length) {
                    const $target = $($toggle.data('target'));
                    $target.collapse('toggle');
                }
            });
        }

    };

})(window.jQuery);

function initTracker() {
    const $ = window.jQuery;
    if (!$ || typeof $.fn.DataTable !== 'function' || typeof $.fn.bootcomplete !== 'function') {
        setTimeout(initTracker, 50);
        return;
    }
    Tracker.Setup();
}

initTracker();
