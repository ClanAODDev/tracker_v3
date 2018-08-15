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

eval("// Flot charts data and options\nvar data2 = $('#flot-line-chart').data('populations'),\n  data3 = $('#flot-line-chart').data('weekly-ts'),\n  data1 = $('#flot-line-chart').data('weekly-active'),\n  comments = $('#flot-line-chart').data('comments');\n\nvar chartUsersOptions = {\n\n  series: {\n\n    points: {\n      show: true,\n      radius: 2,\n      symbol: 'circle'\n    },\n\n    splines: {\n      show: true,\n      tension: 0.4,\n      lineWidth: 1,\n      fill: .10,\n    }\n  },\n\n  grid: {\n    tickColor: '#404652',\n    borderWidth: 1,\n    color: '#000',\n    borderColor: '#404652',\n  },\n\n  comment: {\n    show: true,\n  },\n\n  tooltip: false,\n\n  tooltippage: {\n    show: true,\n    content: '%x - %y members'\n  },\n\n  xaxis: {\n    mode: 'time',\n    timeformat: '%m/%d/%y',\n  },\n\n  colors: ['#1bbf89', '#0F83C9', '#f7af3e']\n};\n\n$.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);\n\n$(window).resize(function () {\n  $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);\n});\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBkYXRhMiA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdwb3B1bGF0aW9ucycpLFxuICBkYXRhMyA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCd3ZWVrbHktdHMnKSxcbiAgZGF0YTEgPSAkKCcjZmxvdC1saW5lLWNoYXJ0JykuZGF0YSgnd2Vla2x5LWFjdGl2ZScpLFxuICBjb21tZW50cyA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdjb21tZW50cycpO1xuXG52YXIgY2hhcnRVc2Vyc09wdGlvbnMgPSB7XG5cbiAgc2VyaWVzOiB7XG5cbiAgICBwb2ludHM6IHtcbiAgICAgIHNob3c6IHRydWUsXG4gICAgICByYWRpdXM6IDIsXG4gICAgICBzeW1ib2w6ICdjaXJjbGUnXG4gICAgfSxcblxuICAgIHNwbGluZXM6IHtcbiAgICAgIHNob3c6IHRydWUsXG4gICAgICB0ZW5zaW9uOiAwLjQsXG4gICAgICBsaW5lV2lkdGg6IDEsXG4gICAgICBmaWxsOiAuMTAsXG4gICAgfVxuICB9LFxuXG4gIGdyaWQ6IHtcbiAgICB0aWNrQ29sb3I6ICcjNDA0NjUyJyxcbiAgICBib3JkZXJXaWR0aDogMSxcbiAgICBjb2xvcjogJyMwMDAnLFxuICAgIGJvcmRlckNvbG9yOiAnIzQwNDY1MicsXG4gIH0sXG5cbiAgY29tbWVudDoge1xuICAgIHNob3c6IHRydWUsXG4gIH0sXG5cbiAgdG9vbHRpcDogZmFsc2UsXG5cbiAgdG9vbHRpcHBhZ2U6IHtcbiAgICBzaG93OiB0cnVlLFxuICAgIGNvbnRlbnQ6ICcleCAtICV5IG1lbWJlcnMnXG4gIH0sXG5cbiAgeGF4aXM6IHtcbiAgICBtb2RlOiAndGltZScsXG4gICAgdGltZWZvcm1hdDogJyVtLyVkLyV5JyxcbiAgfSxcblxuICBjb2xvcnM6IFsnIzFiYmY4OScsICcjMEY4M0M5JywgJyNmN2FmM2UnXVxufTtcblxuJC5wbG90KCQoJyNmbG90LWxpbmUtY2hhcnQnKSwgW2RhdGExLCBkYXRhMiwgZGF0YTNdLCBjaGFydFVzZXJzT3B0aW9ucyk7XG5cbiQod2luZG93KS5yZXNpemUoZnVuY3Rpb24gKCkge1xuICAkLnBsb3QoJCgnI2Zsb3QtbGluZS1jaGFydCcpLCBbZGF0YTEsIGRhdGEyLCBkYXRhM10sIGNoYXJ0VXNlcnNPcHRpb25zKTtcbn0pO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIHJlc291cmNlcy9hc3NldHMvanMvY2Vuc3VzLWdyYXBoLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTsiLCJzb3VyY2VSb290IjoiIn0=");

/***/ }
/******/ ]);