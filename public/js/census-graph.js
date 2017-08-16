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

eval("// Flot charts data and options\nvar data2 = $('#flot-line-chart').data('populations'),\n  data3 = $('#flot-line-chart').data('weekly-ts'),\n  data1 = $('#flot-line-chart').data('weekly-active'),\n  comments = $('#flot-line-chart').data('comments');\n\nvar chartUsersOptions = {\n\n  series: {\n\n    points: {\n      show: true,\n      radius: 2,\n      symbol: 'circle'\n    },\n\n    splines: {\n      show: true,\n      tension: 0.4,\n      lineWidth: 1,\n      fill: .10,\n    }\n  },\n\n  grid: {\n    tickColor: '#404652',\n    borderWidth: 1,\n    hoverable: true,\n    color: '#000',\n    borderColor: '#404652',\n  },\n\n  comment: {\n    show: true,\n\n    hoverable: false,\n  },\n\n  tooltip: {\n    show: false\n  },\n\n  sidenote: {\n    show: false\n  },\n\n  xaxis: {\n    axisLabel: 'Weeks'\n  },\n\n  comments: comments,\n\n  colors: ['#0F83C9', '#1bbf89', '#f7af3e']\n};\n\n$.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);\n\n$(window).resize(function () {\n  $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);\n});\n\n$('input[type=checkbox]').change(function (event) {\n  var option = {};\n  option['comment'] = {show: $(this).is(':checked')};\n  $.extend(true, chartUsersOptions, option);\n  $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);\n});//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBkYXRhMiA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdwb3B1bGF0aW9ucycpLFxuICBkYXRhMyA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCd3ZWVrbHktdHMnKSxcbiAgZGF0YTEgPSAkKCcjZmxvdC1saW5lLWNoYXJ0JykuZGF0YSgnd2Vla2x5LWFjdGl2ZScpLFxuICBjb21tZW50cyA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdjb21tZW50cycpO1xuXG52YXIgY2hhcnRVc2Vyc09wdGlvbnMgPSB7XG5cbiAgc2VyaWVzOiB7XG5cbiAgICBwb2ludHM6IHtcbiAgICAgIHNob3c6IHRydWUsXG4gICAgICByYWRpdXM6IDIsXG4gICAgICBzeW1ib2w6ICdjaXJjbGUnXG4gICAgfSxcblxuICAgIHNwbGluZXM6IHtcbiAgICAgIHNob3c6IHRydWUsXG4gICAgICB0ZW5zaW9uOiAwLjQsXG4gICAgICBsaW5lV2lkdGg6IDEsXG4gICAgICBmaWxsOiAuMTAsXG4gICAgfVxuICB9LFxuXG4gIGdyaWQ6IHtcbiAgICB0aWNrQ29sb3I6ICcjNDA0NjUyJyxcbiAgICBib3JkZXJXaWR0aDogMSxcbiAgICBob3ZlcmFibGU6IHRydWUsXG4gICAgY29sb3I6ICcjMDAwJyxcbiAgICBib3JkZXJDb2xvcjogJyM0MDQ2NTInLFxuICB9LFxuXG4gIGNvbW1lbnQ6IHtcbiAgICBzaG93OiB0cnVlLFxuXG4gICAgaG92ZXJhYmxlOiBmYWxzZSxcbiAgfSxcblxuICB0b29sdGlwOiB7XG4gICAgc2hvdzogZmFsc2VcbiAgfSxcblxuICBzaWRlbm90ZToge1xuICAgIHNob3c6IGZhbHNlXG4gIH0sXG5cbiAgeGF4aXM6IHtcbiAgICBheGlzTGFiZWw6ICdXZWVrcydcbiAgfSxcblxuICBjb21tZW50czogY29tbWVudHMsXG5cbiAgY29sb3JzOiBbJyMwRjgzQzknLCAnIzFiYmY4OScsICcjZjdhZjNlJ11cbn07XG5cbiQucGxvdCgkKCcjZmxvdC1saW5lLWNoYXJ0JyksIFtkYXRhMSwgZGF0YTIsIGRhdGEzXSwgY2hhcnRVc2Vyc09wdGlvbnMpO1xuXG4kKHdpbmRvdykucmVzaXplKGZ1bmN0aW9uICgpIHtcbiAgJC5wbG90KCQoJyNmbG90LWxpbmUtY2hhcnQnKSwgW2RhdGExLCBkYXRhMiwgZGF0YTNdLCBjaGFydFVzZXJzT3B0aW9ucyk7XG59KTtcblxuJCgnaW5wdXRbdHlwZT1jaGVja2JveF0nKS5jaGFuZ2UoZnVuY3Rpb24gKGV2ZW50KSB7XG4gIHZhciBvcHRpb24gPSB7fTtcbiAgb3B0aW9uWydjb21tZW50J10gPSB7c2hvdzogJCh0aGlzKS5pcygnOmNoZWNrZWQnKX07XG4gICQuZXh0ZW5kKHRydWUsIGNoYXJ0VXNlcnNPcHRpb25zLCBvcHRpb24pO1xuICAkLnBsb3QoJCgnI2Zsb3QtbGluZS1jaGFydCcpLCBbZGF0YTEsIGRhdGEyLCBkYXRhM10sIGNoYXJ0VXNlcnNPcHRpb25zKTtcbn0pO1xuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyByZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcyJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=");

/***/ }
/******/ ]);