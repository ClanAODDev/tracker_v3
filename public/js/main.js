/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************************!*\
  !*** ./resources/assets/js/main.js ***!
  \*************************************/
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
var Tracker = Tracker || {};
(function ($) {
  Tracker = {
    Setup: function Setup() {
      Tracker.GeneralInit();
      Tracker.SearchMembers();
      Tracker.SearchCollection();

      // misc functionality, visual
      Tracker.InitRepeater();
      Tracker.InitTabActivate();
      Tracker.ResetLocality();
    },
    /**
     * Handle member search
     * @constructor
     */
    SearchMembers: function SearchMembers() {
      this.TriggerFilter(document.getElementById('member-search'), this.GetSearchResults, 1000);
      $('#searchclear').click(function () {
        $('section.search-results').addClass('closed').removeClass('open');
        $('#member-search').val('');
        $('#searchclear').css('display', 'none');
      });
    },
    /**
     * Handle repeater fields
     *
     * @constructor
     */
    InitRepeater: function InitRepeater() {
      $('.repeater').repeater({
        isFirstItemUndeletable: true
      });
    },
    /**
     * Handle tab activation on URL navigation
     *
     * @constructor
     */
    InitTabActivate: function InitTabActivate() {
      $('.nav-tabs').stickyTabs();

      // handle sparklines that aren't visible on the dom initially
      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.sparkline_display_visible();
      });
    },
    /**
     * Textarea event listener
     *
     * @param textArea
     * @param callback
     * @param delay
     * @constructor
     */
    TriggerFilter: function TriggerFilter(textArea, callback, delay) {
      var timer = null;
      if ($('#member-search').length) {
        textArea.onkeypress = function () {
          $('.results-loader').removeClass('hidden');
          if (timer) {
            window.clearTimeout(timer);
          }
          timer = window.setTimeout(function () {
            timer = null;
            callback();
          }, delay);
        };
        textArea = null;
      }
    },
    /**
     * Search members handle
     *
     * @constructor
     */
    GetSearchResults: function GetSearchResults() {
      if ($('#member-search').val()) {
        var name = $('input#member-search').val(),
          base_url = window.Laravel.appPath;
        $.ajax({
          url: base_url + '/search/members/' + name,
          type: 'GET',
          success: function success(response) {
            window.scrollTo(0, 0);
            $('.results-loader').addClass('hidden');
            $('#searchclear').css('display', 'block');
            $('section.search-results').html(response);
            $('section.search-results').addClass('open').removeClass('closed');
          }
        });
      }
    },
    /**
     * Format a human readable number
     *
     * @param num
     * @returns {string}
     * @constructor
     */
    FormatNumber: function FormatNumber(num) {
      return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    },
    /**
     * Filter a collection of items
     *
     * @constructor
     */
    SearchCollection: function SearchCollection() {
      $('#search-collection').keyup(function () {
        var value = $(this).val(),
          exp = new RegExp('^' + value, 'i'),
          items = '.collection .collection-item';
        $(items).each(function () {
          // toggle items that don't meet criteria
          var isMatch = exp.test($(this).text());
          $(this).toggle(isMatch);
        });
      });
    },
    ResetLocality: function ResetLocality() {
      $('[data-reset-locality]').click(function () {
        $('[data-locality-entry]').each(function () {
          var new_string = $(this).find('[data-new-string]'),
            old_string = $(this).find('[data-old-string]');
          if (new_string.val() !== old_string.val()) {
            new_string.effect('highlight', 1000);
            new_string.val(old_string.val());
          }
        });
      });
    },
    smoothScroll: function smoothScroll() {
      $('.smooth-scroll').click(function (e) {
        e.preventDefault();
        var targetId = $(this).attr('href');
        var top = $(targetId).offset().top - 90;
        $('html, body').stop().animate({
          scrollTop: top
        }, 750);
        window.location.hash = $.attr(this, 'href').substr(1);
      });
    },
    /**
     * Window opener
     *
     * @param url
     * @param name
     * @param args
     */
    windowOpener: function windowOpener(url, name, args) {
      if ((typeof popupWin === "undefined" ? "undefined" : _typeof(popupWin)) != 'object' || popupWin.closed) {
        popupWin = window.open(url, name, args);
      } else {
        popupWin.location.href = url;
      }
      popupWin.focus();
    },
    GeneralInit: function GeneralInit() {
      $('.approve-request').click(function (e) {
        var settings = 'width=900,height=600,scrollbars=yes';
        Tracker.windowOpener($(this).data('path'), 'Tracker | Approve Member', settings);
      });
      $('.remove-member').click(function (e) {
        var member = $(this).data('member-id'),
          removeUrl = 'https://www.clanaod.net/forums/modcp/aodmember.php?do=remaod&u=' + member,
          windowName = 'Tracker | Remove Member',
          settings = 'width=900,height=600,scrollbars=yes';
        Tracker.windowOpener(removeUrl, windowName, settings);
      });

      // handle primary nav collapse
      $('.left-nav-toggle a').click(function () {
        if ($('body').hasClass('nav-toggle')) {
          $.get(window.Laravel.appPath + '/primary-nav/decollapse');
        } else {
          $.get(window.Laravel.appPath + '/primary-nav/collapse');
        }
      });

      // Only enable if the document has a long scroll bar
      // Note the window height + offset
      if ($(window).height() + 100 < $(document).height()) {
        $('#top-link-block').removeClass('hidden').affix({
          // how far to scroll down before link "slides" into view
          offset: {
            top: 100
          }
        });
      }
      this.smoothScroll();
      var clipboard = new Clipboard('.copy-to-clipboard');
      clipboard.on('success', function (e) {
        toastr.success('Copied!');
        e.clearSelection();
      });
      var basicDatatable = $('table.basic-datatable').DataTable({
        paging: false,
        bFilter: false,
        stateSave: true,
        bInfo: false,
        order: [],
        columnDefs: [{
          targets: 'no-sort',
          orderable: false
        }],
        select: {
          style: 'os',
          selector: 'td:first-child'
        }
      });
      var advDataTable = $('table.adv-datatable').DataTable({
        order: [],
        columnDefs: [{
          targets: 'no-sort',
          orderable: false
        }]
      });
      if ($('.for-pm-selection').length) {
        // handle PM selection
        basicDatatable.on("select", function (e, t, a, d) {
          var l = basicDatatable.rows($(".selected")).data().toArray().map(function (e) {
            return e[4];
          });
          if (l.length >= 2) {
            $("#selected-data").show(), $("#selected-data .status-text").text("With selected (" + l.length + ")"), $("#pm-member-data").val(l);
          }
        });
      }
      var sparklineCharts = function sparklineCharts() {
        $('[census-data]').sparkline($('[census-data]').data('counts'), {
          type: 'line',
          lineColor: '#fff',
          lineWidth: 3,
          fillColor: '#404652',
          height: 50,
          width: '100%'
        });
        $('.census-pie').each(function () {
          $(this).sparkline($(this).data('counts'), {
            type: 'pie',
            sliceColors: $(this).data('colors')
          });
        });
        $('[census-data]').bind('sparklineClick', function (ev) {
          var sparkline = ev.sparklines[0],
            region = sparkline.getCurrentRegionFields();
          console.log('Clicked on x=' + region.x + ' y=' + region.y);
        });
      };
      var sparkResize;
      $(window).resize(function () {
        clearTimeout(sparkResize);
        sparkResize = setTimeout(sparklineCharts, 100);
      });
      sparklineCharts();

      // Handle minimalize left menu
      $('.left-nav-toggle a').on('click', function (event) {
        event.preventDefault();
        $('body').toggleClass('nav-toggle');
        clearTimeout(sparkResize);
        sparkResize = setTimeout(sparklineCharts, 100);
      });

      // Hide all open sub nav menu list
      $('.nav-second').on('show.bs.collapse', function () {
        $('.nav-second.in').collapse('hide');
      });

      // Handle panel toggle
      $('.panel-toggle').on('click', function (event) {
        event.preventDefault();
        var hpanel = $(event.target).closest('div.panel');
        var icon = $(event.target).closest('i.toggle-icon');
        var iconNotLinked = $(event.target).find('i.toggle-icon');
        var body = hpanel.find('div.panel-body');
        var footer = hpanel.find('div.panel-footer');
        body.slideToggle(300);
        footer.slideToggle(200);

        // Toggle icon from up to down
        icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        iconNotLinked.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        hpanel.toggleClass('').toggleClass('panel-collapse');
        setTimeout(function () {
          hpanel.resize();
          hpanel.find('[id^=map-]').resize();
        }, 50);
      });

      // Handle panel close
      $('.panel-close').on('click', function (event) {
        event.preventDefault();
        var hpanel = $(event.target).closest('div.panel');
        hpanel.remove();
      });
      $('.search-member').bootcomplete({
        url: window.Laravel.appPath + '/search-member/',
        minLength: 3,
        idField: true,
        method: 'POST',
        dataParams: {
          _token: $('meta[name=csrf-token]').attr('content')
        }
      });
    }
  };
})(jQuery);
Tracker.Setup();
/******/ })()
;