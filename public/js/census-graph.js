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

eval("// Flot charts data and options\nvar data2 = $('#flot-line-chart').data('populations'),\n    data3 = $('#flot-line-chart').data('weekly-ts'),\n    data4 = $('#flot-line-chart').data('weekly-discord');\n// data1 = $('#flot-line-chart').data('weekly-active'),\n// comments = $('#flot-line-chart').data('comments');\n\nvar chartUsersOptions = {\n\n    series: {\n\n        points: {\n            show: true,\n            radius: 2,\n            symbol: 'circle'\n        },\n\n        splines: {\n            show: true,\n            tension: 0.4,\n            lineWidth: 1,\n            fill: .10,\n        }\n    },\n\n    grid: {\n        tickColor: '#404652',\n        borderWidth: 1,\n        color: '#000',\n        borderColor: '#404652',\n    },\n\n    comment: {\n        show: true,\n    },\n\n    tooltip: false,\n\n    tooltippage: {\n        show: true,\n        content: '%x - %y members'\n    },\n\n    xaxis: {\n        mode: 'time',\n        timeformat: '%m/%d/%y',\n    },\n\n    colors: ['#1bbf89', '#0F83C9', '#f7af3e']\n};\n\n$.plot($('#flot-line-chart'), [data2, data3, data4], chartUsersOptions);\n\n$(window).resize(function () {\n    $.plot($('#flot-line-chart'), [data2, data3, data4], chartUsersOptions);\n});\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBkYXRhMiA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdwb3B1bGF0aW9ucycpLFxuICAgIGRhdGEzID0gJCgnI2Zsb3QtbGluZS1jaGFydCcpLmRhdGEoJ3dlZWtseS10cycpLFxuICAgIGRhdGE0ID0gJCgnI2Zsb3QtbGluZS1jaGFydCcpLmRhdGEoJ3dlZWtseS1kaXNjb3JkJyk7XG4vLyBkYXRhMSA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCd3ZWVrbHktYWN0aXZlJyksXG4vLyBjb21tZW50cyA9ICQoJyNmbG90LWxpbmUtY2hhcnQnKS5kYXRhKCdjb21tZW50cycpO1xuXG52YXIgY2hhcnRVc2Vyc09wdGlvbnMgPSB7XG5cbiAgICBzZXJpZXM6IHtcblxuICAgICAgICBwb2ludHM6IHtcbiAgICAgICAgICAgIHNob3c6IHRydWUsXG4gICAgICAgICAgICByYWRpdXM6IDIsXG4gICAgICAgICAgICBzeW1ib2w6ICdjaXJjbGUnXG4gICAgICAgIH0sXG5cbiAgICAgICAgc3BsaW5lczoge1xuICAgICAgICAgICAgc2hvdzogdHJ1ZSxcbiAgICAgICAgICAgIHRlbnNpb246IDAuNCxcbiAgICAgICAgICAgIGxpbmVXaWR0aDogMSxcbiAgICAgICAgICAgIGZpbGw6IC4xMCxcbiAgICAgICAgfVxuICAgIH0sXG5cbiAgICBncmlkOiB7XG4gICAgICAgIHRpY2tDb2xvcjogJyM0MDQ2NTInLFxuICAgICAgICBib3JkZXJXaWR0aDogMSxcbiAgICAgICAgY29sb3I6ICcjMDAwJyxcbiAgICAgICAgYm9yZGVyQ29sb3I6ICcjNDA0NjUyJyxcbiAgICB9LFxuXG4gICAgY29tbWVudDoge1xuICAgICAgICBzaG93OiB0cnVlLFxuICAgIH0sXG5cbiAgICB0b29sdGlwOiBmYWxzZSxcblxuICAgIHRvb2x0aXBwYWdlOiB7XG4gICAgICAgIHNob3c6IHRydWUsXG4gICAgICAgIGNvbnRlbnQ6ICcleCAtICV5IG1lbWJlcnMnXG4gICAgfSxcblxuICAgIHhheGlzOiB7XG4gICAgICAgIG1vZGU6ICd0aW1lJyxcbiAgICAgICAgdGltZWZvcm1hdDogJyVtLyVkLyV5JyxcbiAgICB9LFxuXG4gICAgY29sb3JzOiBbJyMxYmJmODknLCAnIzBGODNDOScsICcjZjdhZjNlJ11cbn07XG5cbiQucGxvdCgkKCcjZmxvdC1saW5lLWNoYXJ0JyksIFtkYXRhMiwgZGF0YTMsIGRhdGE0XSwgY2hhcnRVc2Vyc09wdGlvbnMpO1xuXG4kKHdpbmRvdykucmVzaXplKGZ1bmN0aW9uICgpIHtcbiAgICAkLnBsb3QoJCgnI2Zsb3QtbGluZS1jaGFydCcpLCBbZGF0YTIsIGRhdGEzLCBkYXRhNF0sIGNoYXJ0VXNlcnNPcHRpb25zKTtcbn0pO1xuXG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIHJlc291cmNlcy9hc3NldHMvanMvY2Vuc3VzLWdyYXBoLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTs7OztBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOyIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);