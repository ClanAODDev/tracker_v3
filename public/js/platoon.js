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

eval("var Platoon = Platoon || {};\n\n(function ($) {\n\n  Platoon = {\n\n    handleSquadMembers: function () {\n      $('.sortable-squad').sortable({\n        revert: 'invalid',\n      });\n\n      $('.draggable').draggable({\n        connectToSortable: 'ul',\n        revert: 'invalid',\n        scroll: true,\n        scrollSensitivity: 100\n      });\n\n      var itemMoved, targetSquad, senderLength, receiverLength;\n      $('.mod-plt .sortable').sortable({\n        connectWith: 'ul',\n        placeholder: 'ui-state-highlight',\n        forcePlaceholderSize: true,\n        revert: 'invalid',\n        receive: function (event, ui) {\n          itemMoved = $(ui.item).attr('data-member-id');\n          targetSquad = $(this).attr('data-squad-id');\n          senderLength = $(ui.sender).find('li').length;\n          receiverLength = $(this).find('li').length;\n          if (undefined === targetSquad) {\n            toastr.error('You cannot move members to the unassigned list');\n            $('.mod-plt .sortable').sortable('cancel');\n          } else {\n            // is genpop empty?\n            if ($('.genpop').find('li').length < 1) {\n              $('.genpop').fadeOut();\n            }\n            // update squad counts\n            $(ui.sender).parent().find('.count').text(senderLength);\n            $(this).parent().find('.count').text(receiverLength).effect('highlight');\n            $.ajax({\n              type: 'POST',\n              url: window.Laravel.appPath + '/members/assign-squad',\n              data: {\n                member_id: itemMoved,\n                squad_id: targetSquad,\n                _token: $('meta[name=csrf-token]').attr('content')\n              },\n              dataType: 'json',\n              success: function () {\n                toastr.success('Member reassigned!');\n              },\n              error: function () {\n                toastr.error('Something bad happened...');\n              }\n            });\n          }\n        }\n      });\n    },\n    setup: function () {\n      this.handleMembers();\n      this.handleSquadMembers();\n    },\n\n    handleMembers: function () {\n\n      var platoonNum = parseInt($('.platoon-number').text()),\n        formattedDate = new Date(),\n        d = formattedDate.getDate(),\n        m = (formattedDate.getMonth() + 1),\n        y = formattedDate.getFullYear(),\n        nowDate = y + '-' + m + '-' + d,\n        selected = new Array();\n\n      /**\n       * Handle platoons, squads\n       */\n      $('table.members-table').DataTable({\n        autoWidth: true, bInfo: false,\n        columnDefs: [{\n          targets: 'no-search', searchable: false\n        }, {\n          targets: 'col-hidden', visible: false, searchable: false\n        }, {\n          // sort rank by rank id\n          'iDataSort': 0, 'aTargets': [3]\n        }, {\n          // sort activity by last login date\n          'iDataSort': 1, 'aTargets': [5]\n        }],\n        stateSave: true, paging: false,\n      });\n\n      $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm');\n\n      $('#playerFilter input').attr({\n        'placeholder': 'Search Players',\n        'class': 'form-control'\n      });\n\n      $('.dataTables_filter label').remove();\n\n      $('.no-sort').removeClass('sorting');\n\n      // omit leader field if using TBA\n      $('#is_tba').click(function () {\n        toggleTBA();\n      });\n\n      toggleTBA();\n\n      function toggleTBA () {\n        if ($('#is_tba').is(':checked')) {\n          $('#leader_id, #leader').prop('disabled', true).val('');\n        } else {\n          $('#leader_id, #leader').prop('disabled', false);\n        }\n      }\n\n    },\n  };\n})(jQuery);\n\nPlatoon.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL3BsYXRvb24uanM/ZDAzMSJdLCJzb3VyY2VzQ29udGVudCI6WyJsZXQgUGxhdG9vbiA9IFBsYXRvb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIFBsYXRvb24gPSB7XG5cbiAgICBoYW5kbGVTcXVhZE1lbWJlcnM6IGZ1bmN0aW9uICgpIHtcbiAgICAgICQoJy5zb3J0YWJsZS1zcXVhZCcpLnNvcnRhYmxlKHtcbiAgICAgICAgcmV2ZXJ0OiAnaW52YWxpZCcsXG4gICAgICB9KTtcblxuICAgICAgJCgnLmRyYWdnYWJsZScpLmRyYWdnYWJsZSh7XG4gICAgICAgIGNvbm5lY3RUb1NvcnRhYmxlOiAndWwnLFxuICAgICAgICByZXZlcnQ6ICdpbnZhbGlkJyxcbiAgICAgICAgc2Nyb2xsOiB0cnVlLFxuICAgICAgICBzY3JvbGxTZW5zaXRpdml0eTogMTAwXG4gICAgICB9KTtcblxuICAgICAgbGV0IGl0ZW1Nb3ZlZCwgdGFyZ2V0U3F1YWQsIHNlbmRlckxlbmd0aCwgcmVjZWl2ZXJMZW5ndGg7XG4gICAgICAkKCcubW9kLXBsdCAuc29ydGFibGUnKS5zb3J0YWJsZSh7XG4gICAgICAgIGNvbm5lY3RXaXRoOiAndWwnLFxuICAgICAgICBwbGFjZWhvbGRlcjogJ3VpLXN0YXRlLWhpZ2hsaWdodCcsXG4gICAgICAgIGZvcmNlUGxhY2Vob2xkZXJTaXplOiB0cnVlLFxuICAgICAgICByZXZlcnQ6ICdpbnZhbGlkJyxcbiAgICAgICAgcmVjZWl2ZTogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAgIGl0ZW1Nb3ZlZCA9ICQodWkuaXRlbSkuYXR0cignZGF0YS1tZW1iZXItaWQnKTtcbiAgICAgICAgICB0YXJnZXRTcXVhZCA9ICQodGhpcykuYXR0cignZGF0YS1zcXVhZC1pZCcpO1xuICAgICAgICAgIHNlbmRlckxlbmd0aCA9ICQodWkuc2VuZGVyKS5maW5kKCdsaScpLmxlbmd0aDtcbiAgICAgICAgICByZWNlaXZlckxlbmd0aCA9ICQodGhpcykuZmluZCgnbGknKS5sZW5ndGg7XG4gICAgICAgICAgaWYgKHVuZGVmaW5lZCA9PT0gdGFyZ2V0U3F1YWQpIHtcbiAgICAgICAgICAgIHRvYXN0ci5lcnJvcignWW91IGNhbm5vdCBtb3ZlIG1lbWJlcnMgdG8gdGhlIHVuYXNzaWduZWQgbGlzdCcpO1xuICAgICAgICAgICAgJCgnLm1vZC1wbHQgLnNvcnRhYmxlJykuc29ydGFibGUoJ2NhbmNlbCcpO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAvLyBpcyBnZW5wb3AgZW1wdHk/XG4gICAgICAgICAgICBpZiAoJCgnLmdlbnBvcCcpLmZpbmQoJ2xpJykubGVuZ3RoIDwgMSkge1xuICAgICAgICAgICAgICAkKCcuZ2VucG9wJykuZmFkZU91dCgpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgLy8gdXBkYXRlIHNxdWFkIGNvdW50c1xuICAgICAgICAgICAgJCh1aS5zZW5kZXIpLnBhcmVudCgpLmZpbmQoJy5jb3VudCcpLnRleHQoc2VuZGVyTGVuZ3RoKTtcbiAgICAgICAgICAgICQodGhpcykucGFyZW50KCkuZmluZCgnLmNvdW50JykudGV4dChyZWNlaXZlckxlbmd0aCkuZWZmZWN0KCdoaWdobGlnaHQnKTtcbiAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgIHR5cGU6ICdQT1NUJyxcbiAgICAgICAgICAgICAgdXJsOiB3aW5kb3cuTGFyYXZlbC5hcHBQYXRoICsgJy9tZW1iZXJzL2Fzc2lnbi1zcXVhZCcsXG4gICAgICAgICAgICAgIGRhdGE6IHtcbiAgICAgICAgICAgICAgICBtZW1iZXJfaWQ6IGl0ZW1Nb3ZlZCxcbiAgICAgICAgICAgICAgICBzcXVhZF9pZDogdGFyZ2V0U3F1YWQsXG4gICAgICAgICAgICAgICAgX3Rva2VuOiAkKCdtZXRhW25hbWU9Y3NyZi10b2tlbl0nKS5hdHRyKCdjb250ZW50JylcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgZGF0YVR5cGU6ICdqc29uJyxcbiAgICAgICAgICAgICAgc3VjY2VzczogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgIHRvYXN0ci5zdWNjZXNzKCdNZW1iZXIgcmVhc3NpZ25lZCEnKTtcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgZXJyb3I6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICB0b2FzdHIuZXJyb3IoJ1NvbWV0aGluZyBiYWQgaGFwcGVuZWQuLi4nKTtcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSk7XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9LFxuICAgIHNldHVwOiBmdW5jdGlvbiAoKSB7XG4gICAgICB0aGlzLmhhbmRsZU1lbWJlcnMoKTtcbiAgICAgIHRoaXMuaGFuZGxlU3F1YWRNZW1iZXJzKCk7XG4gICAgfSxcblxuICAgIGhhbmRsZU1lbWJlcnM6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgdmFyIHBsYXRvb25OdW0gPSBwYXJzZUludCgkKCcucGxhdG9vbi1udW1iZXInKS50ZXh0KCkpLFxuICAgICAgICBmb3JtYXR0ZWREYXRlID0gbmV3IERhdGUoKSxcbiAgICAgICAgZCA9IGZvcm1hdHRlZERhdGUuZ2V0RGF0ZSgpLFxuICAgICAgICBtID0gKGZvcm1hdHRlZERhdGUuZ2V0TW9udGgoKSArIDEpLFxuICAgICAgICB5ID0gZm9ybWF0dGVkRGF0ZS5nZXRGdWxsWWVhcigpLFxuICAgICAgICBub3dEYXRlID0geSArICctJyArIG0gKyAnLScgKyBkLFxuICAgICAgICBzZWxlY3RlZCA9IG5ldyBBcnJheSgpO1xuXG4gICAgICAvKipcbiAgICAgICAqIEhhbmRsZSBwbGF0b29ucywgc3F1YWRzXG4gICAgICAgKi9cbiAgICAgICQoJ3RhYmxlLm1lbWJlcnMtdGFibGUnKS5EYXRhVGFibGUoe1xuICAgICAgICBhdXRvV2lkdGg6IHRydWUsIGJJbmZvOiBmYWxzZSxcbiAgICAgICAgY29sdW1uRGVmczogW3tcbiAgICAgICAgICB0YXJnZXRzOiAnbm8tc2VhcmNoJywgc2VhcmNoYWJsZTogZmFsc2VcbiAgICAgICAgfSwge1xuICAgICAgICAgIHRhcmdldHM6ICdjb2wtaGlkZGVuJywgdmlzaWJsZTogZmFsc2UsIHNlYXJjaGFibGU6IGZhbHNlXG4gICAgICAgIH0sIHtcbiAgICAgICAgICAvLyBzb3J0IHJhbmsgYnkgcmFuayBpZFxuICAgICAgICAgICdpRGF0YVNvcnQnOiAwLCAnYVRhcmdldHMnOiBbM11cbiAgICAgICAgfSwge1xuICAgICAgICAgIC8vIHNvcnQgYWN0aXZpdHkgYnkgbGFzdCBsb2dpbiBkYXRlXG4gICAgICAgICAgJ2lEYXRhU29ydCc6IDEsICdhVGFyZ2V0cyc6IFs1XVxuICAgICAgICB9XSxcbiAgICAgICAgc3RhdGVTYXZlOiB0cnVlLCBwYWdpbmc6IGZhbHNlLFxuICAgICAgfSk7XG5cbiAgICAgICQoJy5kYXRhVGFibGVzX2ZpbHRlciBpbnB1dCcpLmFwcGVuZFRvKCcjcGxheWVyRmlsdGVyJykucmVtb3ZlQ2xhc3MoJ2lucHV0LXNtJyk7XG5cbiAgICAgICQoJyNwbGF5ZXJGaWx0ZXIgaW5wdXQnKS5hdHRyKHtcbiAgICAgICAgJ3BsYWNlaG9sZGVyJzogJ1NlYXJjaCBQbGF5ZXJzJyxcbiAgICAgICAgJ2NsYXNzJzogJ2Zvcm0tY29udHJvbCdcbiAgICAgIH0pO1xuXG4gICAgICAkKCcuZGF0YVRhYmxlc19maWx0ZXIgbGFiZWwnKS5yZW1vdmUoKTtcblxuICAgICAgJCgnLm5vLXNvcnQnKS5yZW1vdmVDbGFzcygnc29ydGluZycpO1xuXG4gICAgICAvLyBvbWl0IGxlYWRlciBmaWVsZCBpZiB1c2luZyBUQkFcbiAgICAgICQoJyNpc190YmEnKS5jbGljayhmdW5jdGlvbiAoKSB7XG4gICAgICAgIHRvZ2dsZVRCQSgpO1xuICAgICAgfSk7XG5cbiAgICAgIHRvZ2dsZVRCQSgpO1xuXG4gICAgICBmdW5jdGlvbiB0b2dnbGVUQkEgKCkge1xuICAgICAgICBpZiAoJCgnI2lzX3RiYScpLmlzKCc6Y2hlY2tlZCcpKSB7XG4gICAgICAgICAgJCgnI2xlYWRlcl9pZCwgI2xlYWRlcicpLnByb3AoJ2Rpc2FibGVkJywgdHJ1ZSkudmFsKCcnKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAkKCcjbGVhZGVyX2lkLCAjbGVhZGVyJykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgIH0sXG4gIH07XG59KShqUXVlcnkpO1xuXG5QbGF0b29uLnNldHVwKCk7XG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIHJlc291cmNlcy9hc3NldHMvanMvcGxhdG9vbi5qcyJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7OztBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOyIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);