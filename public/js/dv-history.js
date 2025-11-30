/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/dv-history.js":
/*!************************************!*\
  !*** ./resources/js/dv-history.js ***!
  \************************************/
/***/ (function() {

eval("/**\n * Page Documents List\n */\n\n\n\n// Datatable (jquery)\n$(function () {\n  // Variable declaration for table\n  var historyUrl = baseUrl + 'history/';\n\n  // ajax setup\n  $.ajaxSetup({\n    headers: {\n      'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')\n    }\n  });\n\n  // //Load Timeline\n  // window.loadHistory = function loadHistory(vat_reg_id)\n  // {\n  //   $.ajax({      \n  //     url: `${baseUrl}history/${vat_reg_id}`,\n  //     type: 'GET',\n  //     success: function (result) { \n  //       $(\"#navs-vatreturns-timeline-\"+vat_reg_id).html(\"\");  \n\n  //       if(result['view'] != \"\")    \n  //       {                   \n  //         $(\"#navs-vatreturns-timeline-\"+vat_reg_id).append(result['view']);\n  //         importVatCommentEditor(vat_reg_id);  \n  //       }\n  //     },\n  //     error: function (err) {\n\n  //     }\n  //   });\n  // }\n\n  //Download Files\n  $(document).on('click', '.btn-download-files', function () {\n    var btn_download_files = $(this);\n    var data = btn_download_files.data();\n    var file_id = data['fileid'];\n    var loadertext = '<!-- Bounce -->' + '<div class=\"sk-bounce sk-primary sk-center position-absolute\" style=\"left: 0; right: 0; top: 0; bottom: 0;\">' + '<div class=\"sk-bounce-dot\"></div>' + '<div class=\"sk-bounce-dot\"></div>' + '</div>';\n    btn_download_files.parent(\".card-body\").append(loadertext);\n    $.ajax({\n      url: \"\".concat(historyUrl).concat(file_id, \"/download\"),\n      type: 'GET',\n      success: function success(data) {\n        btn_download_files.parent(\".card-body\").find(\".sk-bounce\").remove();\n        window.open(data, '_blank');\n      },\n      error: function error(err) {\n        console.log(err);\n      }\n    });\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZHYtaGlzdG9yeS5qcy5qcyIsIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7O0FBRWE7O0FBQ2I7QUFDQUEsQ0FBQyxDQUFDLFlBQVk7RUFFWjtFQUNBLElBQUlDLFVBQVUsR0FBR0MsT0FBTyxHQUFHLFVBQVU7O0VBR3JDO0VBQ0FGLENBQUMsQ0FBQ0csU0FBUyxDQUFDO0lBQ1ZDLE9BQU8sRUFBRTtNQUNQLGNBQWMsRUFBRUosQ0FBQyxDQUFDLHlCQUF5QixDQUFDLENBQUNLLElBQUksQ0FBQyxTQUFTO0lBQzdEO0VBQ0YsQ0FBQyxDQUFDOztFQUVGO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7O0VBRUE7RUFDQTtFQUNBO0VBQ0E7RUFDQTtFQUNBO0VBQ0E7O0VBRUE7RUFDQTtFQUNBOztFQUVBO0VBQ0FMLENBQUMsQ0FBQ00sUUFBUSxDQUFDLENBQUNDLEVBQUUsQ0FBQyxPQUFPLEVBQUUscUJBQXFCLEVBQUUsWUFBWTtJQUN2RCxJQUFJQyxrQkFBa0IsR0FBR1IsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUNoQyxJQUFJUyxJQUFJLEdBQUdELGtCQUFrQixDQUFDQyxJQUFJLEVBQUU7SUFFcEMsSUFBSUMsT0FBTyxHQUFHRCxJQUFJLENBQUMsUUFBUSxDQUFDO0lBRTVCLElBQUlFLFVBQVUsR0FBRyxpQkFBaUIsR0FDNUIsOEdBQThHLEdBQzVHLG1DQUFtQyxHQUNuQyxtQ0FBbUMsR0FDckMsUUFBUTtJQUVkSCxrQkFBa0IsQ0FBQ0ksTUFBTSxDQUFDLFlBQVksQ0FBQyxDQUFDQyxNQUFNLENBQUNGLFVBQVUsQ0FBQztJQUUxRFgsQ0FBQyxDQUFDYyxJQUFJLENBQUM7TUFDTEMsR0FBRyxLQUFBQyxNQUFBLENBQUtmLFVBQVUsRUFBQWUsTUFBQSxDQUFHTixPQUFPLGNBQVc7TUFDdkNPLElBQUksRUFBRSxLQUFLO01BQ1hDLE9BQU8sRUFBRSxTQUFBQSxRQUFVVCxJQUFJLEVBQUU7UUFFdkJELGtCQUFrQixDQUFDSSxNQUFNLENBQUMsWUFBWSxDQUFDLENBQUNPLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQ0MsTUFBTSxFQUFFO1FBRW5FQyxNQUFNLENBQUNDLElBQUksQ0FBQ2IsSUFBSSxFQUFFLFFBQVEsQ0FBQztNQUM3QixDQUFDO01BQ0RjLEtBQUssRUFBRSxTQUFBQSxNQUFVQyxHQUFHLEVBQUU7UUFDcEJDLE9BQU8sQ0FBQ0MsR0FBRyxDQUFDRixHQUFHLENBQUM7TUFDbEI7SUFDRixDQUFDLENBQUM7RUFDTixDQUFDLENBQUM7QUFFSixDQUFDLENBQUMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZHYtaGlzdG9yeS5qcz9lNjFiIl0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogUGFnZSBEb2N1bWVudHMgTGlzdFxuICovXG5cbid1c2Ugc3RyaWN0Jztcbi8vIERhdGF0YWJsZSAoanF1ZXJ5KVxuJChmdW5jdGlvbiAoKSB7XG4gIFxuICAvLyBWYXJpYWJsZSBkZWNsYXJhdGlvbiBmb3IgdGFibGVcbiAgdmFyIGhpc3RvcnlVcmwgPSBiYXNlVXJsICsgJ2hpc3RvcnkvJ1xuICAgIDtcbiAgIFxuICAvLyBhamF4IHNldHVwXG4gICQuYWpheFNldHVwKHtcbiAgICBoZWFkZXJzOiB7XG4gICAgICAnWC1DU1JGLVRPS0VOJzogJCgnbWV0YVtuYW1lPVwiY3NyZi10b2tlblwiXScpLmF0dHIoJ2NvbnRlbnQnKVxuICAgIH1cbiAgfSk7ICAgXG5cbiAgLy8gLy9Mb2FkIFRpbWVsaW5lXG4gIC8vIHdpbmRvdy5sb2FkSGlzdG9yeSA9IGZ1bmN0aW9uIGxvYWRIaXN0b3J5KHZhdF9yZWdfaWQpXG4gIC8vIHtcbiAgLy8gICAkLmFqYXgoeyAgICAgIFxuICAvLyAgICAgdXJsOiBgJHtiYXNlVXJsfWhpc3RvcnkvJHt2YXRfcmVnX2lkfWAsXG4gIC8vICAgICB0eXBlOiAnR0VUJyxcbiAgLy8gICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uIChyZXN1bHQpIHsgXG4gIC8vICAgICAgICQoXCIjbmF2cy12YXRyZXR1cm5zLXRpbWVsaW5lLVwiK3ZhdF9yZWdfaWQpLmh0bWwoXCJcIik7ICBcblxuICAvLyAgICAgICBpZihyZXN1bHRbJ3ZpZXcnXSAhPSBcIlwiKSAgICBcbiAgLy8gICAgICAgeyAgICAgICAgICAgICAgICAgICBcbiAgLy8gICAgICAgICAkKFwiI25hdnMtdmF0cmV0dXJucy10aW1lbGluZS1cIit2YXRfcmVnX2lkKS5hcHBlbmQocmVzdWx0Wyd2aWV3J10pO1xuICAvLyAgICAgICAgIGltcG9ydFZhdENvbW1lbnRFZGl0b3IodmF0X3JlZ19pZCk7ICBcbiAgLy8gICAgICAgfVxuICAvLyAgICAgfSxcbiAgLy8gICAgIGVycm9yOiBmdW5jdGlvbiAoZXJyKSB7XG4gICAgICAgIFxuICAvLyAgICAgfVxuICAvLyAgIH0pO1xuICAvLyB9XG5cbiAgLy9Eb3dubG9hZCBGaWxlc1xuICAkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLmJ0bi1kb3dubG9hZC1maWxlcycsIGZ1bmN0aW9uICgpIHtcbiAgICAgIHZhciBidG5fZG93bmxvYWRfZmlsZXMgPSAkKHRoaXMpO1xuICAgICAgdmFyIGRhdGEgPSBidG5fZG93bmxvYWRfZmlsZXMuZGF0YSgpO1xuXG4gICAgICB2YXIgZmlsZV9pZCA9IGRhdGFbJ2ZpbGVpZCddO1xuICAgICAgICAgIFxuICAgICAgdmFyIGxvYWRlcnRleHQgPSAnPCEtLSBCb3VuY2UgLS0+JyArXG4gICAgICAgICAgICAnPGRpdiBjbGFzcz1cInNrLWJvdW5jZSBzay1wcmltYXJ5IHNrLWNlbnRlciBwb3NpdGlvbi1hYnNvbHV0ZVwiIHN0eWxlPVwibGVmdDogMDsgcmlnaHQ6IDA7IHRvcDogMDsgYm90dG9tOiAwO1wiPicgK1xuICAgICAgICAgICAgICAnPGRpdiBjbGFzcz1cInNrLWJvdW5jZS1kb3RcIj48L2Rpdj4nICtcbiAgICAgICAgICAgICAgJzxkaXYgY2xhc3M9XCJzay1ib3VuY2UtZG90XCI+PC9kaXY+JyArXG4gICAgICAgICAgICAnPC9kaXY+JzsgXG4gICAgICBcbiAgICAgIGJ0bl9kb3dubG9hZF9maWxlcy5wYXJlbnQoXCIuY2FyZC1ib2R5XCIpLmFwcGVuZChsb2FkZXJ0ZXh0KTtcbiAgICAgIFxuICAgICAgJC5hamF4KHsgICAgICBcbiAgICAgICAgdXJsOiBgJHtoaXN0b3J5VXJsfSR7ZmlsZV9pZH0vZG93bmxvYWRgLFxuICAgICAgICB0eXBlOiAnR0VUJywgICAgICAgXG4gICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uIChkYXRhKSB7XG4gICAgICAgICAgICAgICAgICAgIFxuICAgICAgICAgIGJ0bl9kb3dubG9hZF9maWxlcy5wYXJlbnQoXCIuY2FyZC1ib2R5XCIpLmZpbmQoXCIuc2stYm91bmNlXCIpLnJlbW92ZSgpO1xuICAgICAgICAgIFxuICAgICAgICAgIHdpbmRvdy5vcGVuKGRhdGEsICdfYmxhbmsnKTsgICAgICAgICAgXG4gICAgICAgIH0sXG4gICAgICAgIGVycm9yOiBmdW5jdGlvbiAoZXJyKSB7XG4gICAgICAgICAgY29uc29sZS5sb2coZXJyKTsgICAgIFxuICAgICAgICB9XG4gICAgICB9KTtcbiAgfSk7XG4gIFxufSk7Il0sIm5hbWVzIjpbIiQiLCJoaXN0b3J5VXJsIiwiYmFzZVVybCIsImFqYXhTZXR1cCIsImhlYWRlcnMiLCJhdHRyIiwiZG9jdW1lbnQiLCJvbiIsImJ0bl9kb3dubG9hZF9maWxlcyIsImRhdGEiLCJmaWxlX2lkIiwibG9hZGVydGV4dCIsInBhcmVudCIsImFwcGVuZCIsImFqYXgiLCJ1cmwiLCJjb25jYXQiLCJ0eXBlIiwic3VjY2VzcyIsImZpbmQiLCJyZW1vdmUiLCJ3aW5kb3ciLCJvcGVuIiwiZXJyb3IiLCJlcnIiLCJjb25zb2xlIiwibG9nIl0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/dv-history.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/dv-history.js"]();
/******/ 	
/******/ 	return __webpack_exports__;
/******/ })()
;
});