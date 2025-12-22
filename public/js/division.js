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

eval("var Division = Division || {};\n\n(function ($) {\n\n  Division = {\n\n    initUnassigned: function () {\n\n      $(function () {\n\n        $('.unassigned').draggable({\n          revert: true,\n        });\n\n        $('.platoon').droppable({\n          hoverClass: 'panel-c-success',\n          drop: function (event, ui) {\n\n            var platoon = $(this),\n              base_url = window.Laravel.appPath,\n              draggableId = ui.draggable.attr('data-member-id'),\n              droppableId = platoon.attr('data-platoon-id');\n\n            $.ajax({\n              type: 'POST',\n              url: base_url + '/members/' + draggableId + '/assign-platoon',\n              data: {\n                platoon_id: droppableId,\n                _token: $('meta[name=csrf-token]').attr('content')\n              },\n              success: function (response) {\n                toastr.success('Member was assigned to platoon #' + droppableId);\n                $(ui.draggable).remove();\n                if ($('.unassigned').length < 1) {\n                  $('.unassigned-container').slideUp();\n                }\n              },\n            });\n          }\n        });\n      });\n    },\n    setup: function () {\n      this.initAutocomplete();\n      this.initSetup();\n      this.initUnassigned();\n    },\n\n    initSetup: function () {\n      var ctx = $('.promotions-chart');\n\n      if (ctx.length) {\n        var myDoughnutChart = new Chart(ctx, {\n          type: 'doughnut',\n          data: {\n            datasets: [\n              {\n                data: ctx.data('values'),\n                borderWidth: 0,\n                backgroundColor: [\n                  '#949ba2', '#0f83c9', '#1bbf89', '#f7af3e', '#56c0e0', '#db524b'\n                ]\n              }],\n            labels: ctx.data('labels'),\n          },\n          options: {\n\n            legend: {\n              position: 'bottom',\n              labels: {\n                boxWidth: 5,\n                fontColor: '#949ba2'\n              },\n              label: {\n                fullWidth: true\n              }\n            }\n          }\n        });\n      }\n    },\n\n    initAutocomplete: function () {\n\n      $('#leader').bootcomplete({\n        url: window.Laravel.appPath + '/search-member/',\n        minLength: 3,\n        idField: true,\n        method: 'POST',\n        dataParams: {_token: $('meta[name=csrf-token]').attr('content')}\n      });\n\n    },\n  };\n})(jQuery);\n\nDivision.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzPzVhZTUiXSwic291cmNlc0NvbnRlbnQiOlsidmFyIERpdmlzaW9uID0gRGl2aXNpb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIERpdmlzaW9uID0ge1xuXG4gICAgaW5pdFVuYXNzaWduZWQ6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgJChmdW5jdGlvbiAoKSB7XG5cbiAgICAgICAgJCgnLnVuYXNzaWduZWQnKS5kcmFnZ2FibGUoe1xuICAgICAgICAgIHJldmVydDogdHJ1ZSxcbiAgICAgICAgfSk7XG5cbiAgICAgICAgJCgnLnBsYXRvb24nKS5kcm9wcGFibGUoe1xuICAgICAgICAgIGhvdmVyQ2xhc3M6ICdwYW5lbC1jLXN1Y2Nlc3MnLFxuICAgICAgICAgIGRyb3A6IGZ1bmN0aW9uIChldmVudCwgdWkpIHtcblxuICAgICAgICAgICAgbGV0IHBsYXRvb24gPSAkKHRoaXMpLFxuICAgICAgICAgICAgICBiYXNlX3VybCA9IHdpbmRvdy5MYXJhdmVsLmFwcFBhdGgsXG4gICAgICAgICAgICAgIGRyYWdnYWJsZUlkID0gdWkuZHJhZ2dhYmxlLmF0dHIoJ2RhdGEtbWVtYmVyLWlkJyksXG4gICAgICAgICAgICAgIGRyb3BwYWJsZUlkID0gcGxhdG9vbi5hdHRyKCdkYXRhLXBsYXRvb24taWQnKTtcblxuICAgICAgICAgICAgJC5hamF4KHtcbiAgICAgICAgICAgICAgdHlwZTogJ1BPU1QnLFxuICAgICAgICAgICAgICB1cmw6IGJhc2VfdXJsICsgJy9tZW1iZXJzLycgKyBkcmFnZ2FibGVJZCArICcvYXNzaWduLXBsYXRvb24nLFxuICAgICAgICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICAgICAgcGxhdG9vbl9pZDogZHJvcHBhYmxlSWQsXG4gICAgICAgICAgICAgICAgX3Rva2VuOiAkKCdtZXRhW25hbWU9Y3NyZi10b2tlbl0nKS5hdHRyKCdjb250ZW50JylcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgc3VjY2VzczogZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgdG9hc3RyLnN1Y2Nlc3MoJ01lbWJlciB3YXMgYXNzaWduZWQgdG8gcGxhdG9vbiAjJyArIGRyb3BwYWJsZUlkKTtcbiAgICAgICAgICAgICAgICAkKHVpLmRyYWdnYWJsZSkucmVtb3ZlKCk7XG4gICAgICAgICAgICAgICAgaWYgKCQoJy51bmFzc2lnbmVkJykubGVuZ3RoIDwgMSkge1xuICAgICAgICAgICAgICAgICAgJCgnLnVuYXNzaWduZWQtY29udGFpbmVyJykuc2xpZGVVcCgpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgICB9KTtcbiAgICB9LFxuICAgIHNldHVwOiBmdW5jdGlvbiAoKSB7XG4gICAgICB0aGlzLmluaXRBdXRvY29tcGxldGUoKTtcbiAgICAgIHRoaXMuaW5pdFNldHVwKCk7XG4gICAgICB0aGlzLmluaXRVbmFzc2lnbmVkKCk7XG4gICAgfSxcblxuICAgIGluaXRTZXR1cDogZnVuY3Rpb24gKCkge1xuICAgICAgdmFyIGN0eCA9ICQoJy5wcm9tb3Rpb25zLWNoYXJ0Jyk7XG5cbiAgICAgIGlmIChjdHgubGVuZ3RoKSB7XG4gICAgICAgIHZhciBteURvdWdobnV0Q2hhcnQgPSBuZXcgQ2hhcnQoY3R4LCB7XG4gICAgICAgICAgdHlwZTogJ2RvdWdobnV0JyxcbiAgICAgICAgICBkYXRhOiB7XG4gICAgICAgICAgICBkYXRhc2V0czogW1xuICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgZGF0YTogY3R4LmRhdGEoJ3ZhbHVlcycpLFxuICAgICAgICAgICAgICAgIGJvcmRlcldpZHRoOiAwLFxuICAgICAgICAgICAgICAgIGJhY2tncm91bmRDb2xvcjogW1xuICAgICAgICAgICAgICAgICAgJyM5NDliYTInLCAnIzBmODNjOScsICcjMWJiZjg5JywgJyNmN2FmM2UnLCAnIzU2YzBlMCcsICcjZGI1MjRiJ1xuICAgICAgICAgICAgICAgIF1cbiAgICAgICAgICAgICAgfV0sXG4gICAgICAgICAgICBsYWJlbHM6IGN0eC5kYXRhKCdsYWJlbHMnKSxcbiAgICAgICAgICB9LFxuICAgICAgICAgIG9wdGlvbnM6IHtcblxuICAgICAgICAgICAgbGVnZW5kOiB7XG4gICAgICAgICAgICAgIHBvc2l0aW9uOiAnYm90dG9tJyxcbiAgICAgICAgICAgICAgbGFiZWxzOiB7XG4gICAgICAgICAgICAgICAgYm94V2lkdGg6IDUsXG4gICAgICAgICAgICAgICAgZm9udENvbG9yOiAnIzk0OWJhMidcbiAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgbGFiZWw6IHtcbiAgICAgICAgICAgICAgICBmdWxsV2lkdGg6IHRydWVcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgICB9XG4gICAgfSxcblxuICAgIGluaXRBdXRvY29tcGxldGU6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgJCgnI2xlYWRlcicpLmJvb3Rjb21wbGV0ZSh7XG4gICAgICAgIHVybDogd2luZG93LkxhcmF2ZWwuYXBwUGF0aCArICcvc2VhcmNoLW1lbWJlci8nLFxuICAgICAgICBtaW5MZW5ndGg6IDMsXG4gICAgICAgIGlkRmllbGQ6IHRydWUsXG4gICAgICAgIG1ldGhvZDogJ1BPU1QnLFxuICAgICAgICBkYXRhUGFyYW1zOiB7X3Rva2VuOiAkKCdtZXRhW25hbWU9Y3NyZi10b2tlbl0nKS5hdHRyKCdjb250ZW50Jyl9XG4gICAgICB9KTtcblxuICAgIH0sXG4gIH07XG59KShqUXVlcnkpO1xuXG5EaXZpc2lvbi5zZXR1cCgpO1xuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyByZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);