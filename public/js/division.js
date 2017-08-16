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

eval("var Division = Division || {};\n\n(function ($) {\n\n  Division = {\n\n    setup: function () {\n      this.initAutocomplete();\n    },\n\n    initAutocomplete: function () {\n\n      $('#leader').bootcomplete({\n        url: window.Laravel.appPath + '/search-member/',\n        minLength: 3,\n        idField: true,\n        method: 'POST',\n        dataParams: {_token: $('meta[name=csrf-token]').attr('content')}\n      });\n\n    },\n  };\n})(jQuery);\n\nDivision.setup();//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy9yZXNvdXJjZXMvYXNzZXRzL2pzL2RpdmlzaW9uLmpzPzVhZTUiXSwic291cmNlc0NvbnRlbnQiOlsidmFyIERpdmlzaW9uID0gRGl2aXNpb24gfHwge307XG5cbihmdW5jdGlvbiAoJCkge1xuXG4gIERpdmlzaW9uID0ge1xuXG4gICAgc2V0dXA6IGZ1bmN0aW9uICgpIHtcbiAgICAgIHRoaXMuaW5pdEF1dG9jb21wbGV0ZSgpO1xuICAgIH0sXG5cbiAgICBpbml0QXV0b2NvbXBsZXRlOiBmdW5jdGlvbiAoKSB7XG5cbiAgICAgICQoJyNsZWFkZXInKS5ib290Y29tcGxldGUoe1xuICAgICAgICB1cmw6IHdpbmRvdy5MYXJhdmVsLmFwcFBhdGggKyAnL3NlYXJjaC1tZW1iZXIvJyxcbiAgICAgICAgbWluTGVuZ3RoOiAzLFxuICAgICAgICBpZEZpZWxkOiB0cnVlLFxuICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgZGF0YVBhcmFtczoge190b2tlbjogJCgnbWV0YVtuYW1lPWNzcmYtdG9rZW5dJykuYXR0cignY29udGVudCcpfVxuICAgICAgfSk7XG5cbiAgICB9LFxuICB9O1xufSkoalF1ZXJ5KTtcblxuRGl2aXNpb24uc2V0dXAoKTtcblxuXG4vLyBXRUJQQUNLIEZPT1RFUiAvL1xuLy8gcmVzb3VyY2VzL2Fzc2V0cy9qcy9kaXZpc2lvbi5qcyJdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=");

/***/ }
/******/ ]);