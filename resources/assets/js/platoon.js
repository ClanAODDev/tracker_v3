let Platoon = Platoon || {};

(function ($) {

  Platoon = {

    handleSquadMembers: function () {
      $('.sortable-squad').sortable({
        revert: 'invalid',
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
      })

      $('.draggable').draggable({
        connectToSortable: 'ul',
        revert: 'invalid',
        scroll: true,
        scrollSensitivity: 100
      })

      let itemMoved, targetSquad, senderLength, receiverLength
      $('.mod-plt .sortable').sortable({
        connectWith: 'ul',
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        revert: 'invalid',
        receive: function (event, ui) {
          itemMoved = $(ui.item).attr('data-member-id')
          targetSquad = $(this).attr('data-squad-id')
          senderLength = $(ui.sender).find('li').length
          receiverLength = $(this).find('li').length
          if (undefined === targetSquad) {
            alert('You cannot move players to this list')
            $('.mod-plt .sortable').sortable('cancel')
          } else {
            // is genpop empty?
            if ($('.genpop').find('li').length < 1) {
              $('.genpop').fadeOut()
            }
            // update squad counts
            $(ui.sender).parent().find('.count').text(senderLength)
            $(this).parent().find('.count').text(receiverLength).effect('highlight')
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
                toastr.success('Member reassigned!')
              },
              error: function () {
                toastr.error('Something bad happened...')
              }
            })
          }
        }
      })
    },
    setup: function () {
      this.handleMembers()
      this.handleSquadMembers()
    },

    handleMembers: function () {

      var platoonNum = parseInt($('.platoon-number').text()),
        formattedDate = new Date(),
        d = formattedDate.getDate(),
        m = (formattedDate.getMonth() + 1),
        y = formattedDate.getFullYear(),
        nowDate = y + '-' + m + '-' + d,
        selected = new Array()

      /**
       * Handle platoons, squads
       */
      $('table.members-table').DataTable({
        autoWidth: true, bInfo: false,
        columnDefs: [{
          targets: 'no-search', searchable: false
        }, {
          targets: 'col-hidden', visible: false, searchable: false
        }, {
          // sort rank by rank id
          'iDataSort': 0, 'aTargets': [3]
        }, {
          // sort activity by last login date
          'iDataSort': 1, 'aTargets': [5]
        }],
        stateSave: true, paging: false,
      })

      $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm')

      $('#playerFilter input').attr({
        'placeholder': 'Search Players',
        'class': 'form-control'
      })

      $('.dataTables_filter label').remove()

      $('.no-sort').removeClass('sorting')

      // omit leader field if using TBA
      $('#is_tba').click(function () {
        toggleTBA()
      })

      toggleTBA()

      function toggleTBA () {
        if ($('#is_tba').is(':checked')) {
          $('#leader_id, #leader').prop('disabled', true).val('')
        } else {
          $('#leader_id, #leader').prop('disabled', false)
        }
      }

    },
  }
})(jQuery)

Platoon.setup()