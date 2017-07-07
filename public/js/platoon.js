/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmory imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmory exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		Object.defineProperty(exports, name, {
/******/ 			configurable: false,
/******/ 			enumerable: true,
/******/ 			get: getter
/******/ 		});
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports) {

eval("var Platoon = Platoon || {};\n\n(function ($) {\n\n  Platoon = {\n\n    handleSquadMembers: function () {\n      $('.sortable-squad').sortable({\n        revert: 'invalid',\n        placeholder: 'ui-state-highlight',\n        forcePlaceholderSize: true,\n      })\n\n      $('.draggable').draggable({\n        connectToSortable: 'ul',\n        revert: 'invalid',\n        scroll: true,\n        scrollSensitivity: 100\n      })\n\n      var itemMoved, targetSquad, senderLength, receiverLength\n      $('.mod-plt .sortable').sortable({\n        connectWith: 'ul',\n        placeholder: 'ui-state-highlight',\n        forcePlaceholderSize: true,\n        revert: 'invalid',\n        receive: function (event, ui) {\n          itemMoved = $(ui.item).attr('data-member-id')\n          targetSquad = $(this).attr('data-squad-id')\n          senderLength = $(ui.sender).find('li').length\n          receiverLength = $(this).find('li').length\n          if (undefined === targetSquad) {\n            alert('You cannot move players to this list')\n            $('.mod-plt .sortable').sortable('cancel')\n          } else {\n            // is genpop empty?\n            if ($('.genpop').find('li').length < 1) {\n              $('.genpop').fadeOut()\n            }\n            // update squad counts\n            $(ui.sender).parent().find('.count').text(senderLength)\n            $(this).parent().find('.count').text(receiverLength).effect('highlight')\n            $.ajax({\n              type: 'POST',\n              url: window.Laravel.appPath + '/members/assign-squad',\n              data: {\n                member_id: itemMoved,\n                squad_id: targetSquad,\n                _token: $('meta[name=csrf-token]').attr('content')\n              },\n              dataType: 'json',\n              success: function () {\n                toastr.success('Member reassigned!')\n              },\n              error: function () {\n                toastr.error('Something bad happened...')\n              }\n            })\n          }\n        }\n      })\n    },\n    setup: function () {\n      this.handleMembers()\n      this.handleSquadMembers()\n    },\n\n    handleMembers: function () {\n\n      var platoonNum = parseInt($('.platoon-number').text()),\n        formattedDate = new Date(),\n        d = formattedDate.getDate(),\n        m = (formattedDate.getMonth() + 1),\n        y = formattedDate.getFullYear(),\n        nowDate = y + '-' + m + '-' + d,\n        selected = new Array()\n\n      /**\n       * Handle platoons, squads\n       */\n      $('table.members-table').DataTable({\n        autoWidth: true, bInfo: false,\n        columnDefs: [{\n          targets: 'no-search', searchable: false\n        }, {\n          targets: 'col-hidden', visible: false, searchable: false\n        }, {\n          // sort rank by rank id\n          'iDataSort': 0, 'aTargets': [3]\n        }, {\n          // sort activity by last login date\n          'iDataSort': 1, 'aTargets': [5]\n        }],\n        stateSave: true, paging: false,\n      })\n\n      $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm')\n\n      $('#playerFilter input').attr({\n        'placeholder': 'Search Players',\n        'class': 'form-control'\n      })\n\n      $('.dataTables_filter label').remove()\n\n      $('.no-sort').removeClass('sorting')\n\n      // omit leader field if using TBA\n      $('#is_tba').click(function () {\n        toggleTBA()\n      })\n\n      toggleTBA()\n\n      function toggleTBA () {\n        if ($('#is_tba').is(':checked')) {\n          $('#leader_id, #leader').prop('disabled', true).val('')\n        } else {\n          $('#leader_id, #leader').prop('disabled', false)\n        }\n      }\n\n    },\n  }\n})(jQuery)\n\nPlatoon.setup()//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL3BsYXRvb24uanM/ZDAzMSJdLCJzb3VyY2VzQ29udGVudCI6WyJsZXQgUGxhdG9vbiA9IFBsYXRvb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIFBsYXRvb24gPSB7XG5cbiAgICBoYW5kbGVTcXVhZE1lbWJlcnM6IGZ1bmN0aW9uICgpIHtcbiAgICAgICQoJy5zb3J0YWJsZS1zcXVhZCcpLnNvcnRhYmxlKHtcbiAgICAgICAgcmV2ZXJ0OiAnaW52YWxpZCcsXG4gICAgICAgIHBsYWNlaG9sZGVyOiAndWktc3RhdGUtaGlnaGxpZ2h0JyxcbiAgICAgICAgZm9yY2VQbGFjZWhvbGRlclNpemU6IHRydWUsXG4gICAgICB9KVxuXG4gICAgICAkKCcuZHJhZ2dhYmxlJykuZHJhZ2dhYmxlKHtcbiAgICAgICAgY29ubmVjdFRvU29ydGFibGU6ICd1bCcsXG4gICAgICAgIHJldmVydDogJ2ludmFsaWQnLFxuICAgICAgICBzY3JvbGw6IHRydWUsXG4gICAgICAgIHNjcm9sbFNlbnNpdGl2aXR5OiAxMDBcbiAgICAgIH0pXG5cbiAgICAgIGxldCBpdGVtTW92ZWQsIHRhcmdldFNxdWFkLCBzZW5kZXJMZW5ndGgsIHJlY2VpdmVyTGVuZ3RoXG4gICAgICAkKCcubW9kLXBsdCAuc29ydGFibGUnKS5zb3J0YWJsZSh7XG4gICAgICAgIGNvbm5lY3RXaXRoOiAndWwnLFxuICAgICAgICBwbGFjZWhvbGRlcjogJ3VpLXN0YXRlLWhpZ2hsaWdodCcsXG4gICAgICAgIGZvcmNlUGxhY2Vob2xkZXJTaXplOiB0cnVlLFxuICAgICAgICByZXZlcnQ6ICdpbnZhbGlkJyxcbiAgICAgICAgcmVjZWl2ZTogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAgIGl0ZW1Nb3ZlZCA9ICQodWkuaXRlbSkuYXR0cignZGF0YS1tZW1iZXItaWQnKVxuICAgICAgICAgIHRhcmdldFNxdWFkID0gJCh0aGlzKS5hdHRyKCdkYXRhLXNxdWFkLWlkJylcbiAgICAgICAgICBzZW5kZXJMZW5ndGggPSAkKHVpLnNlbmRlcikuZmluZCgnbGknKS5sZW5ndGhcbiAgICAgICAgICByZWNlaXZlckxlbmd0aCA9ICQodGhpcykuZmluZCgnbGknKS5sZW5ndGhcbiAgICAgICAgICBpZiAodW5kZWZpbmVkID09PSB0YXJnZXRTcXVhZCkge1xuICAgICAgICAgICAgYWxlcnQoJ1lvdSBjYW5ub3QgbW92ZSBwbGF5ZXJzIHRvIHRoaXMgbGlzdCcpXG4gICAgICAgICAgICAkKCcubW9kLXBsdCAuc29ydGFibGUnKS5zb3J0YWJsZSgnY2FuY2VsJylcbiAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgLy8gaXMgZ2VucG9wIGVtcHR5P1xuICAgICAgICAgICAgaWYgKCQoJy5nZW5wb3AnKS5maW5kKCdsaScpLmxlbmd0aCA8IDEpIHtcbiAgICAgICAgICAgICAgJCgnLmdlbnBvcCcpLmZhZGVPdXQoKVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgLy8gdXBkYXRlIHNxdWFkIGNvdW50c1xuICAgICAgICAgICAgJCh1aS5zZW5kZXIpLnBhcmVudCgpLmZpbmQoJy5jb3VudCcpLnRleHQoc2VuZGVyTGVuZ3RoKVxuICAgICAgICAgICAgJCh0aGlzKS5wYXJlbnQoKS5maW5kKCcuY291bnQnKS50ZXh0KHJlY2VpdmVyTGVuZ3RoKS5lZmZlY3QoJ2hpZ2hsaWdodCcpXG4gICAgICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgICB0eXBlOiAnUE9TVCcsXG4gICAgICAgICAgICAgIHVybDogd2luZG93LkxhcmF2ZWwuYXBwUGF0aCArICcvbWVtYmVycy9hc3NpZ24tc3F1YWQnLFxuICAgICAgICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICAgICAgbWVtYmVyX2lkOiBpdGVtTW92ZWQsXG4gICAgICAgICAgICAgICAgc3F1YWRfaWQ6IHRhcmdldFNxdWFkLFxuICAgICAgICAgICAgICAgIF90b2tlbjogJCgnbWV0YVtuYW1lPWNzcmYtdG9rZW5dJykuYXR0cignY29udGVudCcpXG4gICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgIGRhdGFUeXBlOiAnanNvbicsXG4gICAgICAgICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICB0b2FzdHIuc3VjY2VzcygnTWVtYmVyIHJlYXNzaWduZWQhJylcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgZXJyb3I6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICB0b2FzdHIuZXJyb3IoJ1NvbWV0aGluZyBiYWQgaGFwcGVuZWQuLi4nKVxuICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KVxuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfSlcbiAgICB9LFxuICAgIHNldHVwOiBmdW5jdGlvbiAoKSB7XG4gICAgICB0aGlzLmhhbmRsZU1lbWJlcnMoKVxuICAgICAgdGhpcy5oYW5kbGVTcXVhZE1lbWJlcnMoKVxuICAgIH0sXG5cbiAgICBoYW5kbGVNZW1iZXJzOiBmdW5jdGlvbiAoKSB7XG5cbiAgICAgIHZhciBwbGF0b29uTnVtID0gcGFyc2VJbnQoJCgnLnBsYXRvb24tbnVtYmVyJykudGV4dCgpKSxcbiAgICAgICAgZm9ybWF0dGVkRGF0ZSA9IG5ldyBEYXRlKCksXG4gICAgICAgIGQgPSBmb3JtYXR0ZWREYXRlLmdldERhdGUoKSxcbiAgICAgICAgbSA9IChmb3JtYXR0ZWREYXRlLmdldE1vbnRoKCkgKyAxKSxcbiAgICAgICAgeSA9IGZvcm1hdHRlZERhdGUuZ2V0RnVsbFllYXIoKSxcbiAgICAgICAgbm93RGF0ZSA9IHkgKyAnLScgKyBtICsgJy0nICsgZCxcbiAgICAgICAgc2VsZWN0ZWQgPSBuZXcgQXJyYXkoKVxuXG4gICAgICAvKipcbiAgICAgICAqIEhhbmRsZSBwbGF0b29ucywgc3F1YWRzXG4gICAgICAgKi9cbiAgICAgICQoJ3RhYmxlLm1lbWJlcnMtdGFibGUnKS5EYXRhVGFibGUoe1xuICAgICAgICBhdXRvV2lkdGg6IHRydWUsIGJJbmZvOiBmYWxzZSxcbiAgICAgICAgY29sdW1uRGVmczogW3tcbiAgICAgICAgICB0YXJnZXRzOiAnbm8tc2VhcmNoJywgc2VhcmNoYWJsZTogZmFsc2VcbiAgICAgICAgfSwge1xuICAgICAgICAgIHRhcmdldHM6ICdjb2wtaGlkZGVuJywgdmlzaWJsZTogZmFsc2UsIHNlYXJjaGFibGU6IGZhbHNlXG4gICAgICAgIH0sIHtcbiAgICAgICAgICAvLyBzb3J0IHJhbmsgYnkgcmFuayBpZFxuICAgICAgICAgICdpRGF0YVNvcnQnOiAwLCAnYVRhcmdldHMnOiBbM11cbiAgICAgICAgfSwge1xuICAgICAgICAgIC8vIHNvcnQgYWN0aXZpdHkgYnkgbGFzdCBsb2dpbiBkYXRlXG4gICAgICAgICAgJ2lEYXRhU29ydCc6IDEsICdhVGFyZ2V0cyc6IFs1XVxuICAgICAgICB9XSxcbiAgICAgICAgc3RhdGVTYXZlOiB0cnVlLCBwYWdpbmc6IGZhbHNlLFxuICAgICAgfSlcblxuICAgICAgJCgnLmRhdGFUYWJsZXNfZmlsdGVyIGlucHV0JykuYXBwZW5kVG8oJyNwbGF5ZXJGaWx0ZXInKS5yZW1vdmVDbGFzcygnaW5wdXQtc20nKVxuXG4gICAgICAkKCcjcGxheWVyRmlsdGVyIGlucHV0JykuYXR0cih7XG4gICAgICAgICdwbGFjZWhvbGRlcic6ICdTZWFyY2ggUGxheWVycycsXG4gICAgICAgICdjbGFzcyc6ICdmb3JtLWNvbnRyb2wnXG4gICAgICB9KVxuXG4gICAgICAkKCcuZGF0YVRhYmxlc19maWx0ZXIgbGFiZWwnKS5yZW1vdmUoKVxuXG4gICAgICAkKCcubm8tc29ydCcpLnJlbW92ZUNsYXNzKCdzb3J0aW5nJylcblxuICAgICAgLy8gb21pdCBsZWFkZXIgZmllbGQgaWYgdXNpbmcgVEJBXG4gICAgICAkKCcjaXNfdGJhJykuY2xpY2soZnVuY3Rpb24gKCkge1xuICAgICAgICB0b2dnbGVUQkEoKVxuICAgICAgfSlcblxuICAgICAgdG9nZ2xlVEJBKClcblxuICAgICAgZnVuY3Rpb24gdG9nZ2xlVEJBICgpIHtcbiAgICAgICAgaWYgKCQoJyNpc190YmEnKS5pcygnOmNoZWNrZWQnKSkge1xuICAgICAgICAgICQoJyNsZWFkZXJfaWQsICNsZWFkZXInKS5wcm9wKCdkaXNhYmxlZCcsIHRydWUpLnZhbCgnJylcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAkKCcjbGVhZGVyX2lkLCAjbGVhZGVyJykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSlcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgfSxcbiAgfVxufSkoalF1ZXJ5KVxuXG5QbGF0b29uLnNldHVwKClcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9wbGF0b29uLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7OztBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOyIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);