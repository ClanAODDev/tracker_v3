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

eval("var Division = Division || {};\n\n(function ($) {\n\n  Division = {\n\n    initUnassigned: function () {\n\n      $(function () {\n\n        $('.unassigned').draggable({\n          revert: true,\n        });\n\n        // $('.squad').droppable({\n        //   hoverClass: 'panel-c-success',\n        //   greedy: true,\n        //   drop: function (event, ui) {\n        //     alert('asdf');\n        //   }\n        // });\n\n        $('.platoon').droppable({\n          hoverClass: 'panel-c-success',\n          drop: function (event, ui) {\n\n            var platoon = $(this),\n              base_url = window.Laravel.appPath,\n              draggableId = ui.draggable.attr('data-member-id'),\n              droppableId = platoon.attr('data-platoon-id');\n\n            $.ajax({\n              type: 'POST',\n              url: base_url + '/members/' + draggableId + '/assign-platoon',\n              data: {\n                platoon_id: droppableId,\n                _token: $('meta[name=csrf-token]').attr('content')\n              },\n              success: function (response) {\n                toastr.success('Member was assigned to platoon #' + droppableId);\n                $(ui.draggable).remove();\n                if ($('.unassigned').length < 1) {\n                  $('.unassigned-container').slideUp();\n                }\n              },\n            });\n          }\n        });\n      });\n    },\n    setup: function () {\n      this.initAutocomplete();\n      this.initSetup();\n      this.initUnassigned();\n    },\n\n    initSetup: function () {\n      var ctx = $('.promotions-chart');\n\n      if (ctx.length) {\n        var myDoughnutChart = new Chart(ctx, {\n          type: 'doughnut',\n          data: {\n            datasets: [\n              {\n                data: ctx.data('values'),\n                borderWidth: 0,\n                backgroundColor: [\n                  '#949ba2', '#0f83c9', '#1bbf89', '#f7af3e', '#56c0e0', '#db524b'\n                ]\n              }],\n            labels: ctx.data('labels'),\n          },\n          options: {\n\n            legend: {\n              position: 'bottom',\n              labels: {\n                boxWidth: 5,\n                fontColor: '#949ba2'\n              },\n              label: {\n                fullWidth: true\n              }\n            }\n          }\n        });\n      }\n    },\n\n    initAutocomplete: function () {\n\n      $('#leader').bootcomplete({\n        url: window.Laravel.appPath + '/search-member/',\n        minLength: 3,\n        idField: true,\n        method: 'POST',\n        dataParams: {_token: $('meta[name=csrf-token]').attr('content')}\n      });\n\n    },\n  };\n})(jQuery);\n\nDivision.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzPzVhZTUiXSwic291cmNlc0NvbnRlbnQiOlsidmFyIERpdmlzaW9uID0gRGl2aXNpb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIERpdmlzaW9uID0ge1xuXG4gICAgaW5pdFVuYXNzaWduZWQ6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgJChmdW5jdGlvbiAoKSB7XG5cbiAgICAgICAgJCgnLnVuYXNzaWduZWQnKS5kcmFnZ2FibGUoe1xuICAgICAgICAgIHJldmVydDogdHJ1ZSxcbiAgICAgICAgfSk7XG5cbiAgICAgICAgLy8gJCgnLnNxdWFkJykuZHJvcHBhYmxlKHtcbiAgICAgICAgLy8gICBob3ZlckNsYXNzOiAncGFuZWwtYy1zdWNjZXNzJyxcbiAgICAgICAgLy8gICBncmVlZHk6IHRydWUsXG4gICAgICAgIC8vICAgZHJvcDogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAvLyAgICAgYWxlcnQoJ2FzZGYnKTtcbiAgICAgICAgLy8gICB9XG4gICAgICAgIC8vIH0pO1xuXG4gICAgICAgICQoJy5wbGF0b29uJykuZHJvcHBhYmxlKHtcbiAgICAgICAgICBob3ZlckNsYXNzOiAncGFuZWwtYy1zdWNjZXNzJyxcbiAgICAgICAgICBkcm9wOiBmdW5jdGlvbiAoZXZlbnQsIHVpKSB7XG5cbiAgICAgICAgICAgIGxldCBwbGF0b29uID0gJCh0aGlzKSxcbiAgICAgICAgICAgICAgYmFzZV91cmwgPSB3aW5kb3cuTGFyYXZlbC5hcHBQYXRoLFxuICAgICAgICAgICAgICBkcmFnZ2FibGVJZCA9IHVpLmRyYWdnYWJsZS5hdHRyKCdkYXRhLW1lbWJlci1pZCcpLFxuICAgICAgICAgICAgICBkcm9wcGFibGVJZCA9IHBsYXRvb24uYXR0cignZGF0YS1wbGF0b29uLWlkJyk7XG5cbiAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgIHR5cGU6ICdQT1NUJyxcbiAgICAgICAgICAgICAgdXJsOiBiYXNlX3VybCArICcvbWVtYmVycy8nICsgZHJhZ2dhYmxlSWQgKyAnL2Fzc2lnbi1wbGF0b29uJyxcbiAgICAgICAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgICAgIHBsYXRvb25faWQ6IGRyb3BwYWJsZUlkLFxuICAgICAgICAgICAgICAgIF90b2tlbjogJCgnbWV0YVtuYW1lPWNzcmYtdG9rZW5dJykuYXR0cignY29udGVudCcpXG4gICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgIHRvYXN0ci5zdWNjZXNzKCdNZW1iZXIgd2FzIGFzc2lnbmVkIHRvIHBsYXRvb24gIycgKyBkcm9wcGFibGVJZCk7XG4gICAgICAgICAgICAgICAgJCh1aS5kcmFnZ2FibGUpLnJlbW92ZSgpO1xuICAgICAgICAgICAgICAgIGlmICgkKCcudW5hc3NpZ25lZCcpLmxlbmd0aCA8IDEpIHtcbiAgICAgICAgICAgICAgICAgICQoJy51bmFzc2lnbmVkLWNvbnRhaW5lcicpLnNsaWRlVXAoKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgfSk7XG4gICAgfSxcbiAgICBzZXR1cDogZnVuY3Rpb24gKCkge1xuICAgICAgdGhpcy5pbml0QXV0b2NvbXBsZXRlKCk7XG4gICAgICB0aGlzLmluaXRTZXR1cCgpO1xuICAgICAgdGhpcy5pbml0VW5hc3NpZ25lZCgpO1xuICAgIH0sXG5cbiAgICBpbml0U2V0dXA6IGZ1bmN0aW9uICgpIHtcbiAgICAgIHZhciBjdHggPSAkKCcucHJvbW90aW9ucy1jaGFydCcpO1xuXG4gICAgICBpZiAoY3R4Lmxlbmd0aCkge1xuICAgICAgICB2YXIgbXlEb3VnaG51dENoYXJ0ID0gbmV3IENoYXJ0KGN0eCwge1xuICAgICAgICAgIHR5cGU6ICdkb3VnaG51dCcsXG4gICAgICAgICAgZGF0YToge1xuICAgICAgICAgICAgZGF0YXNldHM6IFtcbiAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIGRhdGE6IGN0eC5kYXRhKCd2YWx1ZXMnKSxcbiAgICAgICAgICAgICAgICBib3JkZXJXaWR0aDogMCxcbiAgICAgICAgICAgICAgICBiYWNrZ3JvdW5kQ29sb3I6IFtcbiAgICAgICAgICAgICAgICAgICcjOTQ5YmEyJywgJyMwZjgzYzknLCAnIzFiYmY4OScsICcjZjdhZjNlJywgJyM1NmMwZTAnLCAnI2RiNTI0YidcbiAgICAgICAgICAgICAgICBdXG4gICAgICAgICAgICAgIH1dLFxuICAgICAgICAgICAgbGFiZWxzOiBjdHguZGF0YSgnbGFiZWxzJyksXG4gICAgICAgICAgfSxcbiAgICAgICAgICBvcHRpb25zOiB7XG5cbiAgICAgICAgICAgIGxlZ2VuZDoge1xuICAgICAgICAgICAgICBwb3NpdGlvbjogJ2JvdHRvbScsXG4gICAgICAgICAgICAgIGxhYmVsczoge1xuICAgICAgICAgICAgICAgIGJveFdpZHRoOiA1LFxuICAgICAgICAgICAgICAgIGZvbnRDb2xvcjogJyM5NDliYTInXG4gICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgIGxhYmVsOiB7XG4gICAgICAgICAgICAgICAgZnVsbFdpZHRoOiB0cnVlXG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgfVxuICAgIH0sXG5cbiAgICBpbml0QXV0b2NvbXBsZXRlOiBmdW5jdGlvbiAoKSB7XG5cbiAgICAgICQoJyNsZWFkZXInKS5ib290Y29tcGxldGUoe1xuICAgICAgICB1cmw6IHdpbmRvdy5MYXJhdmVsLmFwcFBhdGggKyAnL3NlYXJjaC1tZW1iZXIvJyxcbiAgICAgICAgbWluTGVuZ3RoOiAzLFxuICAgICAgICBpZEZpZWxkOiB0cnVlLFxuICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgZGF0YVBhcmFtczoge190b2tlbjogJCgnbWV0YVtuYW1lPWNzcmYtdG9rZW5dJykuYXR0cignY29udGVudCcpfVxuICAgICAgfSk7XG5cbiAgICB9LFxuICB9O1xufSkoalF1ZXJ5KTtcblxuRGl2aXNpb24uc2V0dXAoKTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9kaXZpc2lvbi5qcyJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7Ozs7Ozs7O0FBU0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7Iiwic291cmNlUm9vdCI6IiJ9");

/***/ }
/******/ ]);