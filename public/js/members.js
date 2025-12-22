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

eval("var Platoon = Platoon || {};\n\n(function ($) {\n\n  Platoon = {\n\n    setup: function () {\n      this.handleMemberList();\n      this.handleForumActivityChart();\n    },\n\n\n    handleForumActivityChart: function () {\n\n      var ctx = $('.forum-activity-chart');\n\n      var myDoughnutChart = new Chart(ctx, {\n        type: 'doughnut',\n        data: {\n          datasets: [\n            {\n              data: ctx.data('values'),\n              backgroundColor: ctx.data('colors'),\n              borderWidth: 0,\n            }],\n          labels: ctx.data('labels'),\n        },\n        options: {\n          rotation: 1 * Math.PI,\n          circumference: 1 * Math.PI,\n          legend: {\n            position: 'bottom',\n            labels: {\n              boxWidth: 5,\n              fontColor: '#949ba2'\n            },\n            label: {\n              fullWidth: false\n            }\n          }\n        }\n      });\n    },\n    handleMemberList: function () {\n\n      var platoonNum = parseInt($('.platoon-number').text()),\n        formattedDate = new Date(),\n        d = formattedDate.getDate(),\n        m = (formattedDate.getMonth() + 1),\n        y = formattedDate.getFullYear(),\n        nowDate = y + '-' + m + '-' + d,\n        selected = new Array();\n\n      /**\n       * Handle platoons, squads\n       */\n      $('table.members').DataTable({\n        bInfo: false, autoWidth: true,\n        columnDefs: [{\n          targets: 'no-search', searchable: false\n        }, {\n          targets: 'col-hidden', visible: false, searchable: false\n        }, {\n          // sort rank by rank id\n          'iDataSort': 2, 'aTargets': [1]\n        }\n        ],\n        stateSave: false, paging: false,\n      });\n\n      $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm');\n\n      $('#playerFilter input').attr({\n        'placeholder': 'Search Players',\n        'class': 'form-control'\n      });\n\n      $('.dataTables_filter label').remove();\n\n      $('.no-sort').removeClass('sorting');\n\n      // omit leader field if using TBA\n      $('#is_tba').click(function () {\n        toggleTBA();\n      });\n\n      toggleTBA();\n\n      function toggleTBA () {\n        if ($('#is_tba').is(':checked')) {\n          $('#leader_id, #leader').prop('disabled', true).val('');\n        } else {\n          $('#leader_id, #leader').prop('disabled', false);\n        }\n      }\n\n    },\n  };\n})(jQuery);\n\nPlatoon.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL21lbWJlcnMuanM/MzAyNCJdLCJzb3VyY2VzQ29udGVudCI6WyJsZXQgUGxhdG9vbiA9IFBsYXRvb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIFBsYXRvb24gPSB7XG5cbiAgICBzZXR1cDogZnVuY3Rpb24gKCkge1xuICAgICAgdGhpcy5oYW5kbGVNZW1iZXJMaXN0KCk7XG4gICAgICB0aGlzLmhhbmRsZUZvcnVtQWN0aXZpdHlDaGFydCgpO1xuICAgIH0sXG5cblxuICAgIGhhbmRsZUZvcnVtQWN0aXZpdHlDaGFydDogZnVuY3Rpb24gKCkge1xuXG4gICAgICB2YXIgY3R4ID0gJCgnLmZvcnVtLWFjdGl2aXR5LWNoYXJ0Jyk7XG5cbiAgICAgIHZhciBteURvdWdobnV0Q2hhcnQgPSBuZXcgQ2hhcnQoY3R4LCB7XG4gICAgICAgIHR5cGU6ICdkb3VnaG51dCcsXG4gICAgICAgIGRhdGE6IHtcbiAgICAgICAgICBkYXRhc2V0czogW1xuICAgICAgICAgICAge1xuICAgICAgICAgICAgICBkYXRhOiBjdHguZGF0YSgndmFsdWVzJyksXG4gICAgICAgICAgICAgIGJhY2tncm91bmRDb2xvcjogY3R4LmRhdGEoJ2NvbG9ycycpLFxuICAgICAgICAgICAgICBib3JkZXJXaWR0aDogMCxcbiAgICAgICAgICAgIH1dLFxuICAgICAgICAgIGxhYmVsczogY3R4LmRhdGEoJ2xhYmVscycpLFxuICAgICAgICB9LFxuICAgICAgICBvcHRpb25zOiB7XG4gICAgICAgICAgcm90YXRpb246IDEgKiBNYXRoLlBJLFxuICAgICAgICAgIGNpcmN1bWZlcmVuY2U6IDEgKiBNYXRoLlBJLFxuICAgICAgICAgIGxlZ2VuZDoge1xuICAgICAgICAgICAgcG9zaXRpb246ICdib3R0b20nLFxuICAgICAgICAgICAgbGFiZWxzOiB7XG4gICAgICAgICAgICAgIGJveFdpZHRoOiA1LFxuICAgICAgICAgICAgICBmb250Q29sb3I6ICcjOTQ5YmEyJ1xuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGxhYmVsOiB7XG4gICAgICAgICAgICAgIGZ1bGxXaWR0aDogZmFsc2VcbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH0pO1xuICAgIH0sXG4gICAgaGFuZGxlTWVtYmVyTGlzdDogZnVuY3Rpb24gKCkge1xuXG4gICAgICB2YXIgcGxhdG9vbk51bSA9IHBhcnNlSW50KCQoJy5wbGF0b29uLW51bWJlcicpLnRleHQoKSksXG4gICAgICAgIGZvcm1hdHRlZERhdGUgPSBuZXcgRGF0ZSgpLFxuICAgICAgICBkID0gZm9ybWF0dGVkRGF0ZS5nZXREYXRlKCksXG4gICAgICAgIG0gPSAoZm9ybWF0dGVkRGF0ZS5nZXRNb250aCgpICsgMSksXG4gICAgICAgIHkgPSBmb3JtYXR0ZWREYXRlLmdldEZ1bGxZZWFyKCksXG4gICAgICAgIG5vd0RhdGUgPSB5ICsgJy0nICsgbSArICctJyArIGQsXG4gICAgICAgIHNlbGVjdGVkID0gbmV3IEFycmF5KCk7XG5cbiAgICAgIC8qKlxuICAgICAgICogSGFuZGxlIHBsYXRvb25zLCBzcXVhZHNcbiAgICAgICAqL1xuICAgICAgJCgndGFibGUubWVtYmVycycpLkRhdGFUYWJsZSh7XG4gICAgICAgIGJJbmZvOiBmYWxzZSwgYXV0b1dpZHRoOiB0cnVlLFxuICAgICAgICBjb2x1bW5EZWZzOiBbe1xuICAgICAgICAgIHRhcmdldHM6ICduby1zZWFyY2gnLCBzZWFyY2hhYmxlOiBmYWxzZVxuICAgICAgICB9LCB7XG4gICAgICAgICAgdGFyZ2V0czogJ2NvbC1oaWRkZW4nLCB2aXNpYmxlOiBmYWxzZSwgc2VhcmNoYWJsZTogZmFsc2VcbiAgICAgICAgfSwge1xuICAgICAgICAgIC8vIHNvcnQgcmFuayBieSByYW5rIGlkXG4gICAgICAgICAgJ2lEYXRhU29ydCc6IDIsICdhVGFyZ2V0cyc6IFsxXVxuICAgICAgICB9XG4gICAgICAgIF0sXG4gICAgICAgIHN0YXRlU2F2ZTogZmFsc2UsIHBhZ2luZzogZmFsc2UsXG4gICAgICB9KTtcblxuICAgICAgJCgnLmRhdGFUYWJsZXNfZmlsdGVyIGlucHV0JykuYXBwZW5kVG8oJyNwbGF5ZXJGaWx0ZXInKS5yZW1vdmVDbGFzcygnaW5wdXQtc20nKTtcblxuICAgICAgJCgnI3BsYXllckZpbHRlciBpbnB1dCcpLmF0dHIoe1xuICAgICAgICAncGxhY2Vob2xkZXInOiAnU2VhcmNoIFBsYXllcnMnLFxuICAgICAgICAnY2xhc3MnOiAnZm9ybS1jb250cm9sJ1xuICAgICAgfSk7XG5cbiAgICAgICQoJy5kYXRhVGFibGVzX2ZpbHRlciBsYWJlbCcpLnJlbW92ZSgpO1xuXG4gICAgICAkKCcubm8tc29ydCcpLnJlbW92ZUNsYXNzKCdzb3J0aW5nJyk7XG5cbiAgICAgIC8vIG9taXQgbGVhZGVyIGZpZWxkIGlmIHVzaW5nIFRCQVxuICAgICAgJCgnI2lzX3RiYScpLmNsaWNrKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgdG9nZ2xlVEJBKCk7XG4gICAgICB9KTtcblxuICAgICAgdG9nZ2xlVEJBKCk7XG5cbiAgICAgIGZ1bmN0aW9uIHRvZ2dsZVRCQSAoKSB7XG4gICAgICAgIGlmICgkKCcjaXNfdGJhJykuaXMoJzpjaGVja2VkJykpIHtcbiAgICAgICAgICAkKCcjbGVhZGVyX2lkLCAjbGVhZGVyJykucHJvcCgnZGlzYWJsZWQnLCB0cnVlKS52YWwoJycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICQoJyNsZWFkZXJfaWQsICNsZWFkZXInKS5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgfSxcbiAgfTtcbn0pKGpRdWVyeSk7XG5cblBsYXRvb24uc2V0dXAoKTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9tZW1iZXJzLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOzs7O0FBSUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7Iiwic291cmNlUm9vdCI6IiJ9");

/***/ }
/******/ ]);