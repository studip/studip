/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
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
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "SFVy");
/******/ })
/************************************************************************/
/******/ ({

/***/ "SFVy":
/*!*********************************************************!*\
  !*** ./resources/assets/javascripts/entry-installer.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _stylesheets_scss_installer_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../stylesheets/scss/installer.scss */ \"iA2q\");\n/* harmony import */ var _stylesheets_scss_installer_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_stylesheets_scss_installer_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _bootstrap_installer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./bootstrap/installer.js */ \"xOJZ\");\n/* harmony import */ var _bootstrap_installer_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_bootstrap_installer_js__WEBPACK_IMPORTED_MODULE_1__);\n\n\n\n//# sourceURL=webpack:///./resources/assets/javascripts/entry-installer.js?");

/***/ }),

/***/ "iA2q":
/*!**********************************************************!*\
  !*** ./resources/assets/stylesheets/scss/installer.scss ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./resources/assets/stylesheets/scss/installer.scss?");

/***/ }),

/***/ "xOJZ":
/*!*************************************************************!*\
  !*** ./resources/assets/javascripts/bootstrap/installer.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("/*jslint esversion: 6*/\nfunction domReady(fn) {\n  if (document.readyState === 'complete' || document.readyState === 'interactive') {\n    setTimeout(fn, 1);\n  } else {\n    document.addEventListener('DOMContentLoaded', fn);\n  }\n}\n\ndomReady(function () {\n  if (!('fetch' in window) || !('Promise' in window)) {\n    var hidden_input = document.createElement('input');\n    hidden_input.setAttribute('type', 'hidden');\n    hidden_input.setAttribute('name', 'basic');\n    hidden_input.setAttribute('value', 1);\n    document.querySelector('form').append(hidden_input);\n    return;\n  }\n\n  var requests = [];\n  document.querySelectorAll('dl.requests > dt[data-request-url]').forEach(function (element) {\n    requests.push({\n      element: element,\n      url: element.dataset.requestUrl,\n      event_source: element.dataset.eventSource !== undefined\n    });\n  });\n\n  function next() {\n    if (requests.length === 0) {\n      return;\n    }\n\n    var current = requests.shift();\n    var promise;\n    current.element.classList.add('requesting');\n\n    if (current.event_source && 'EventSource' in window) {\n      var notifier = document.createElement('div');\n      notifier.setAttribute('data-percent', 0);\n      promise = new Promise(function (resolve, reject) {\n        current.element.classList.add('event-sourced');\n        var progress = current.element.nextElementSibling.nextElementSibling.nextElementSibling;\n        var total = 0;\n        progress.insertAdjacentElement('afterend', notifier);\n        notifier.setAttribute('style', \"left: \".concat(progress.offsetLeft, \"px; top: \").concat(progress.offsetTop, \"px\"));\n        var evtSource = new EventSource(current.url + '?evts=1', {\n          withCredentials: true\n        });\n        evtSource.addEventListener('total', function (event) {\n          total = parseInt(event.data, 10);\n          progress.setAttribute('max', total);\n        });\n        evtSource.addEventListener('file', function (event) {\n          notifier.setAttribute('data-file', event.data);\n        });\n        evtSource.addEventListener('current', function (event) {\n          var current = parseInt(event.data, 10);\n          progress.setAttribute('value', current);\n          notifier.setAttribute('data-percent', (100 * current / total).toFixed(2));\n        });\n        evtSource.addEventListener('error', function (event) {\n          evtSource.close();\n          reject(event.data || 'Fehler beim Installieren');\n        });\n        evtSource.addEventListener('close', function (event) {\n          evtSource.close();\n          resolve();\n        });\n      });\n      promise.finally(function () {\n        if (notifier.parentNode) {\n          notifier.parentNode.removeChild(notifier);\n        }\n\n        current.element.classList.remove('event-sourced');\n      });\n    } else {\n      promise = fetch(current.url, {\n        cache: 'no-cache',\n        credentials: 'same-origin'\n      }).then(function (response) {\n        if (!response.ok) {\n          return response.json().then(function (message) {\n            return Promise.reject(message);\n          });\n        }\n      });\n    }\n\n    promise.then(function (response) {\n      current.element.classList.add('succeeded');\n      next();\n    }).catch(function (error) {\n      current.element.classList.add('failed');\n\n      if (error !== null && error === Object(error)) {\n        current.element.nextElementSibling.nextElementSibling.querySelectorAll('.response').forEach(function (element) {\n          var key = element.dataset.key;\n          element.value = error[key];\n        });\n      } else {\n        current.element.nextElementSibling.nextElementSibling.querySelector('.response').innerText = error;\n      }\n    }).finally(function () {\n      current.element.classList.remove('requesting');\n    });\n  }\n\n  next();\n});\n\n//# sourceURL=webpack:///./resources/assets/javascripts/bootstrap/installer.js?");

/***/ })

/******/ });