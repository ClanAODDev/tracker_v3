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

eval("// Flot charts data and options\nvar data2 = $('#flot-line-chart').data('populations'),\n  data1 = $('#flot-line-chart').data('weekly-active'),\n  comments = $('#flot-line-chart').data('comments');\n\nvar chartUsersOptions = {\n\n  series: {\n\n    points: {\n      show: true,\n      radius: 2,\n      symbol: 'circle'\n    },\n\n    splines: {\n      show: true,\n      tension: 0.4,\n      lineWidth: 1,\n      fill: 1,\n    }\n  },\n\n  grid: {\n    tickColor: '#404652',\n    borderWidth: 1,\n    hoverable: true,\n    color: '#000',\n    borderColor: '#404652',\n  },\n\n  comment: {\n    show: true,\n\n    hoverable: false,\n  },\n\n  tooltip: {\n    show: false\n  },\n\n  sidenote: {\n    show: false\n  },\n\n  xaxis: {\n    axisLabel: 'Weeks'\n  },\n\n  comments: comments,\n\n  colors: ['#f7af3e', '#DE9536']\n};\n\n$.plot($('#flot-line-chart'), [data2, data1], chartUsersOptions);\n\n$(window).resize(function () {\n  $.plot($('#flot-line-chart'), [data2, data1], chartUsersOptions);\n});\n\n$('input[type=checkbox]').change(function (event) {\n  var option = {};\n  option['comment'] = {show: $(this).is(':checked')};\n  $.extend(true, chartUsersOptions, option);\n  $.plot($('#flot-line-chart'), [data2, data1], chartUsersOptions);\n});//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBkYXRhMiA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdwb3B1bGF0aW9ucycpLFxuICBkYXRhMSA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCd3ZWVrbHktYWN0aXZlJyksXG4gIGNvbW1lbnRzID0gJCgnI2Zsb3QtbGluZS1jaGFydCcpLmRhdGEoJ2NvbW1lbnRzJyk7XG5cbnZhciBjaGFydFVzZXJzT3B0aW9ucyA9IHtcblxuICBzZXJpZXM6IHtcblxuICAgIHBvaW50czoge1xuICAgICAgc2hvdzogdHJ1ZSxcbiAgICAgIHJhZGl1czogMixcbiAgICAgIHN5bWJvbDogJ2NpcmNsZSdcbiAgICB9LFxuXG4gICAgc3BsaW5lczoge1xuICAgICAgc2hvdzogdHJ1ZSxcbiAgICAgIHRlbnNpb246IDAuNCxcbiAgICAgIGxpbmVXaWR0aDogMSxcbiAgICAgIGZpbGw6IDEsXG4gICAgfVxuICB9LFxuXG4gIGdyaWQ6IHtcbiAgICB0aWNrQ29sb3I6ICcjNDA0NjUyJyxcbiAgICBib3JkZXJXaWR0aDogMSxcbiAgICBob3ZlcmFibGU6IHRydWUsXG4gICAgY29sb3I6ICcjMDAwJyxcbiAgICBib3JkZXJDb2xvcjogJyM0MDQ2NTInLFxuICB9LFxuXG4gIGNvbW1lbnQ6IHtcbiAgICBzaG93OiB0cnVlLFxuXG4gICAgaG92ZXJhYmxlOiBmYWxzZSxcbiAgfSxcblxuICB0b29sdGlwOiB7XG4gICAgc2hvdzogZmFsc2VcbiAgfSxcblxuICBzaWRlbm90ZToge1xuICAgIHNob3c6IGZhbHNlXG4gIH0sXG5cbiAgeGF4aXM6IHtcbiAgICBheGlzTGFiZWw6ICdXZWVrcydcbiAgfSxcblxuICBjb21tZW50czogY29tbWVudHMsXG5cbiAgY29sb3JzOiBbJyNmN2FmM2UnLCAnI0RFOTUzNiddXG59O1xuXG4kLnBsb3QoJCgnI2Zsb3QtbGluZS1jaGFydCcpLCBbZGF0YTIsIGRhdGExXSwgY2hhcnRVc2Vyc09wdGlvbnMpO1xuXG4kKHdpbmRvdykucmVzaXplKGZ1bmN0aW9uICgpIHtcbiAgJC5wbG90KCQoJyNmbG90LWxpbmUtY2hhcnQnKSwgW2RhdGEyLCBkYXRhMV0sIGNoYXJ0VXNlcnNPcHRpb25zKTtcbn0pO1xuXG4kKCdpbnB1dFt0eXBlPWNoZWNrYm94XScpLmNoYW5nZShmdW5jdGlvbiAoZXZlbnQpIHtcbiAgdmFyIG9wdGlvbiA9IHt9O1xuICBvcHRpb25bJ2NvbW1lbnQnXSA9IHtzaG93OiAkKHRoaXMpLmlzKCc6Y2hlY2tlZCcpfTtcbiAgJC5leHRlbmQodHJ1ZSwgY2hhcnRVc2Vyc09wdGlvbnMsIG9wdGlvbik7XG4gICQucGxvdCgkKCcjZmxvdC1saW5lLWNoYXJ0JyksIFtkYXRhMiwgZGF0YTFdLCBjaGFydFVzZXJzT3B0aW9ucyk7XG59KTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9jZW5zdXMtZ3JhcGguanMiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);