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

eval("var Division = Division || {};\n\n(function ($) {\n\n    Division = {\n\n        setup: function () {\n            this.initAutocomplete();\n        },\n\n        initAutocomplete: function () {\n\n            $('#leader').bootcomplete({\n                url: window.Laravel.appPath + '/search-leader/',\n                minLength: 3,\n                idField: true,\n                method: 'POST',\n                dataParams: {_token: $('meta[name=csrf-token]').attr('content')}\n            });\n\n        },\n    }\n})(jQuery);\n\nDivision.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzPzVhZTUiXSwic291cmNlc0NvbnRlbnQiOlsidmFyIERpdmlzaW9uID0gRGl2aXNpb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gICAgRGl2aXNpb24gPSB7XG5cbiAgICAgICAgc2V0dXA6IGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHRoaXMuaW5pdEF1dG9jb21wbGV0ZSgpO1xuICAgICAgICB9LFxuXG4gICAgICAgIGluaXRBdXRvY29tcGxldGU6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgICAgICAgJCgnI2xlYWRlcicpLmJvb3Rjb21wbGV0ZSh7XG4gICAgICAgICAgICAgICAgdXJsOiB3aW5kb3cuTGFyYXZlbC5hcHBQYXRoICsgJy9zZWFyY2gtbGVhZGVyLycsXG4gICAgICAgICAgICAgICAgbWluTGVuZ3RoOiAzLFxuICAgICAgICAgICAgICAgIGlkRmllbGQ6IHRydWUsXG4gICAgICAgICAgICAgICAgbWV0aG9kOiAnUE9TVCcsXG4gICAgICAgICAgICAgICAgZGF0YVBhcmFtczoge190b2tlbjogJCgnbWV0YVtuYW1lPWNzcmYtdG9rZW5dJykuYXR0cignY29udGVudCcpfVxuICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgfSxcbiAgICB9XG59KShqUXVlcnkpO1xuXG5EaXZpc2lvbi5zZXR1cCgpO1xuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyByZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzIl0sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZVJvb3QiOiIifQ==");

/***/ }
/******/ ]);