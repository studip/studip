(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["tablesorter"],{

/***/ "c/R+":
/*!************************************************************!*\
  !*** ./resources/assets/javascripts/chunks/tablesorter.js ***!
  \************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var tablesorter_dist_js_jquery_tablesorter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tablesorter/dist/js/jquery.tablesorter */ \"nQco\");\n/* harmony import */ var tablesorter_dist_js_jquery_tablesorter__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tablesorter_dist_js_jquery_tablesorter__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tablesorter_dist_js_extras_jquery_tablesorter_pager_min_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js */ \"3zzX\");\n/* harmony import */ var tablesorter_dist_js_extras_jquery_tablesorter_pager_min_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tablesorter_dist_js_extras_jquery_tablesorter_pager_min_js__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tablesorter_dist_js_jquery_tablesorter_widgets_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tablesorter/dist/js/jquery.tablesorter.widgets.js */ \"WPsp\");\n/* harmony import */ var tablesorter_dist_js_jquery_tablesorter_widgets_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tablesorter_dist_js_jquery_tablesorter_widgets_js__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\njQuery.tablesorter.addParser({\n  id: 'htmldata',\n  is: function is(s, table, cell, $cell) {\n    var c = table.config,\n        p = c.parserMetadataName || 'sortValue';\n    return $(cell).data(p) !== undefined;\n  },\n  format: function format(s, table, cell) {\n    var c = table.config,\n        p = c.parserMetadataName || 'sortValue';\n    return $(cell).data(p);\n  },\n  type: 'numeric'\n});\n\n//# sourceURL=webpack:///./resources/assets/javascripts/chunks/tablesorter.js?");

/***/ })

}]);