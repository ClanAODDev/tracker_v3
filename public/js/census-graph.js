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

eval("// Flot charts data and options\nvar data2 = $(\"#flot-line-chart\").data('populations'),\n    data1 = $(\"#flot-line-chart\").data('weekly-active'),\n    comments = $(\"#flot-line-chart\").data('comments');\n\nvar chartUsersOptions = {\n\n    series: {\n\n        points: {\n            show: true,\n            radius: 2,\n            symbol: \"circle\"\n        },\n\n        splines: {\n            show: true,\n            tension: 0.4,\n            lineWidth: 1,\n            fill: 1,\n        }\n    },\n\n    grid: {\n        tickColor: \"#404652\",\n        borderWidth: 1,\n        hoverable: true,\n        color: '#000',\n        borderColor: '#404652',\n    },\n\n    comment: {\n        show: true,\n\n        hoverable: false,\n    },\n\n    tooltip: {\n        show: false\n    },\n\n    sidenote: {\n        show: false\n    },\n\n    xaxis: {\n        axisLabel: 'Weeks'\n    },\n\n    comments: comments,\n\n    colors: [\"#f7af3e\", \"#DE9536\"]\n};\n\n$.plot($(\"#flot-line-chart\"), [data2, data1], chartUsersOptions);\n\n$(window).resize(function () {\n    $.plot($(\"#flot-line-chart\"), [data2, data1], chartUsersOptions);\n});\n\n$(\"input[type=checkbox]\").change(function (event) {\n    var option = {};\n    option['comment'] = {show: $(this).is(':checked')};\n    $.extend(true, chartUsersOptions, option);\n    $.plot($(\"#flot-line-chart\"), [data2, data1], chartUsersOptions);\n});//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBkYXRhMiA9ICQoXCIjZmxvdC1saW5lLWNoYXJ0XCIpLmRhdGEoJ3BvcHVsYXRpb25zJyksXG4gICAgZGF0YTEgPSAkKFwiI2Zsb3QtbGluZS1jaGFydFwiKS5kYXRhKCd3ZWVrbHktYWN0aXZlJyksXG4gICAgY29tbWVudHMgPSAkKFwiI2Zsb3QtbGluZS1jaGFydFwiKS5kYXRhKCdjb21tZW50cycpO1xuXG52YXIgY2hhcnRVc2Vyc09wdGlvbnMgPSB7XG5cbiAgICBzZXJpZXM6IHtcblxuICAgICAgICBwb2ludHM6IHtcbiAgICAgICAgICAgIHNob3c6IHRydWUsXG4gICAgICAgICAgICByYWRpdXM6IDIsXG4gICAgICAgICAgICBzeW1ib2w6IFwiY2lyY2xlXCJcbiAgICAgICAgfSxcblxuICAgICAgICBzcGxpbmVzOiB7XG4gICAgICAgICAgICBzaG93OiB0cnVlLFxuICAgICAgICAgICAgdGVuc2lvbjogMC40LFxuICAgICAgICAgICAgbGluZVdpZHRoOiAxLFxuICAgICAgICAgICAgZmlsbDogMSxcbiAgICAgICAgfVxuICAgIH0sXG5cbiAgICBncmlkOiB7XG4gICAgICAgIHRpY2tDb2xvcjogXCIjNDA0NjUyXCIsXG4gICAgICAgIGJvcmRlcldpZHRoOiAxLFxuICAgICAgICBob3ZlcmFibGU6IHRydWUsXG4gICAgICAgIGNvbG9yOiAnIzAwMCcsXG4gICAgICAgIGJvcmRlckNvbG9yOiAnIzQwNDY1MicsXG4gICAgfSxcblxuICAgIGNvbW1lbnQ6IHtcbiAgICAgICAgc2hvdzogdHJ1ZSxcblxuICAgICAgICBob3ZlcmFibGU6IGZhbHNlLFxuICAgIH0sXG5cbiAgICB0b29sdGlwOiB7XG4gICAgICAgIHNob3c6IGZhbHNlXG4gICAgfSxcblxuICAgIHNpZGVub3RlOiB7XG4gICAgICAgIHNob3c6IGZhbHNlXG4gICAgfSxcblxuICAgIHhheGlzOiB7XG4gICAgICAgIGF4aXNMYWJlbDogJ1dlZWtzJ1xuICAgIH0sXG5cbiAgICBjb21tZW50czogY29tbWVudHMsXG5cbiAgICBjb2xvcnM6IFtcIiNmN2FmM2VcIiwgXCIjREU5NTM2XCJdXG59O1xuXG4kLnBsb3QoJChcIiNmbG90LWxpbmUtY2hhcnRcIiksIFtkYXRhMiwgZGF0YTFdLCBjaGFydFVzZXJzT3B0aW9ucyk7XG5cbiQod2luZG93KS5yZXNpemUoZnVuY3Rpb24gKCkge1xuICAgICQucGxvdCgkKFwiI2Zsb3QtbGluZS1jaGFydFwiKSwgW2RhdGEyLCBkYXRhMV0sIGNoYXJ0VXNlcnNPcHRpb25zKTtcbn0pO1xuXG4kKFwiaW5wdXRbdHlwZT1jaGVja2JveF1cIikuY2hhbmdlKGZ1bmN0aW9uIChldmVudCkge1xuICAgIHZhciBvcHRpb24gPSB7fTtcbiAgICBvcHRpb25bJ2NvbW1lbnQnXSA9IHtzaG93OiAkKHRoaXMpLmlzKCc6Y2hlY2tlZCcpfTtcbiAgICAkLmV4dGVuZCh0cnVlLCBjaGFydFVzZXJzT3B0aW9ucywgb3B0aW9uKTtcbiAgICAkLnBsb3QoJChcIiNmbG90LWxpbmUtY2hhcnRcIiksIFtkYXRhMiwgZGF0YTFdLCBjaGFydFVzZXJzT3B0aW9ucyk7XG59KTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9jZW5zdXMtZ3JhcGguanMiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);