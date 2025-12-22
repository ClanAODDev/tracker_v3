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

eval("// Flot charts data and options\nvar populationData = $(\"#flot-line-chart\").data(\"populations\"),\n    discordData = $(\"#flot-line-chart\").data(\"weekly-discord\");\n// data1 = $('#flot-line-chart').data('weekly-active'),\n// comments = $('#flot-line-chart').data('comments');\n\nvar chartUsersOptions = {\n\n    series: {\n\n        points: {\n            show: true,\n            radius: 2,\n            symbol: \"circle\"\n        },\n\n        splines: {\n            show: true,\n            tension: 0.4,\n            lineWidth: 1,\n            fill: 0.10\n        }\n    },\n\n    grid: {\n        tickColor: \"#404652\",\n        borderWidth: 1,\n        color: \"#000\",\n        borderColor: \"#404652\"\n    },\n\n    tooltip: false,\n\n    tooltippage: {\n        show: true,\n        content: \"%x - %y members\"\n    },\n\n    xaxis: {\n        mode: \"time\",\n        timeformat: \"%m/%d/%y\"\n    },\n\n    colors: [\"#1bbf89\", \"#0F83C9\", \"#f7af3e\"]\n};\n\n$.plot($(\"#flot-line-chart\"), [populationData, discordData], chartUsersOptions);\n\n$(window).resize(function () {\n    $.plot($(\"#flot-line-chart\"), [populationData, discordData], chartUsersOptions);\n});\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2NlbnN1cy1ncmFwaC5qcz83NDIzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIEZsb3QgY2hhcnRzIGRhdGEgYW5kIG9wdGlvbnNcbnZhciBwb3B1bGF0aW9uRGF0YSA9ICQoXCIjZmxvdC1saW5lLWNoYXJ0XCIpLmRhdGEoXCJwb3B1bGF0aW9uc1wiKSxcbiAgICBkaXNjb3JkRGF0YSA9ICQoXCIjZmxvdC1saW5lLWNoYXJ0XCIpLmRhdGEoXCJ3ZWVrbHktZGlzY29yZFwiKTtcbi8vIGRhdGExID0gJCgnI2Zsb3QtbGluZS1jaGFydCcpLmRhdGEoJ3dlZWtseS1hY3RpdmUnKSxcbi8vIGNvbW1lbnRzID0gJCgnI2Zsb3QtbGluZS1jaGFydCcpLmRhdGEoJ2NvbW1lbnRzJyk7XG5cbnZhciBjaGFydFVzZXJzT3B0aW9ucyA9IHtcblxuICAgIHNlcmllczoge1xuXG4gICAgICAgIHBvaW50czoge1xuICAgICAgICAgICAgc2hvdzogdHJ1ZSxcbiAgICAgICAgICAgIHJhZGl1czogMixcbiAgICAgICAgICAgIHN5bWJvbDogXCJjaXJjbGVcIlxuICAgICAgICB9LFxuXG4gICAgICAgIHNwbGluZXM6IHtcbiAgICAgICAgICAgIHNob3c6IHRydWUsXG4gICAgICAgICAgICB0ZW5zaW9uOiAwLjQsXG4gICAgICAgICAgICBsaW5lV2lkdGg6IDEsXG4gICAgICAgICAgICBmaWxsOiAwLjEwXG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgZ3JpZDoge1xuICAgICAgICB0aWNrQ29sb3I6IFwiIzQwNDY1MlwiLFxuICAgICAgICBib3JkZXJXaWR0aDogMSxcbiAgICAgICAgY29sb3I6IFwiIzAwMFwiLFxuICAgICAgICBib3JkZXJDb2xvcjogXCIjNDA0NjUyXCJcbiAgICB9LFxuXG4gICAgdG9vbHRpcDogZmFsc2UsXG5cbiAgICB0b29sdGlwcGFnZToge1xuICAgICAgICBzaG93OiB0cnVlLFxuICAgICAgICBjb250ZW50OiBcIiV4IC0gJXkgbWVtYmVyc1wiXG4gICAgfSxcblxuICAgIHhheGlzOiB7XG4gICAgICAgIG1vZGU6IFwidGltZVwiLFxuICAgICAgICB0aW1lZm9ybWF0OiBcIiVtLyVkLyV5XCJcbiAgICB9LFxuXG4gICAgY29sb3JzOiBbXCIjMWJiZjg5XCIsIFwiIzBGODNDOVwiLCBcIiNmN2FmM2VcIl1cbn07XG5cbiQucGxvdCgkKFwiI2Zsb3QtbGluZS1jaGFydFwiKSwgW3BvcHVsYXRpb25EYXRhLCBkaXNjb3JkRGF0YV0sIGNoYXJ0VXNlcnNPcHRpb25zKTtcblxuJCh3aW5kb3cpLnJlc2l6ZShmdW5jdGlvbiAoKSB7XG4gICAgJC5wbG90KCQoXCIjZmxvdC1saW5lLWNoYXJ0XCIpLCBbcG9wdWxhdGlvbkRhdGEsIGRpc2NvcmREYXRhXSwgY2hhcnRVc2Vyc09wdGlvbnMpO1xufSk7XG5cblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9jZW5zdXMtZ3JhcGguanMiXSwibWFwcGluZ3MiOiJBQUFBO0FBQ0E7QUFDQTs7OztBQUlBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTsiLCJzb3VyY2VSb290IjoiIn0=");

/***/ }
/******/ ]);