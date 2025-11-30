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

/***/ "./resources/js/dv-preview-report.js":
/*!*******************************************!*\
  !*** ./resources/js/dv-preview-report.js ***!
  \*******************************************/
/***/ (function() {

eval("/**\n * Page Preview Report\n */\n\n\n\n// Datatable (jquery)\n$(function () {\n  // Variable declaration for table\n  var previewReportUrl = baseUrl + 'preview-report/';\n\n  // ajax setup\n  $.ajaxSetup({\n    headers: {\n      'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')\n    }\n  });\n\n  //Export to PDF  \n  $(document).on('click', '.btn-export-pdf-previewreport', function () {\n    var btn_export_pdf_previewreport = $(this);\n    btn_export_pdf_previewreport.attr('disabled', 'disabled');\n    btn_export_pdf_previewreport.html('<span class=\"spinner-border me-1\" role=\"status\" aria-hidden=\"true\"></span>' + 'Exporting...');\n    var vat_reg_id = btn_export_pdf_previewreport.data('vat_reg_id');\n    $.ajax({\n      //data: {box1: box1, box2: box2, box3: box3, box4: box4, box5: box5, box6: box6, box7: box7, box8: box8, box9: box9},  \n      url: \"\".concat(previewReportUrl).concat(vat_reg_id, \"/export\"),\n      type: 'POST',\n      xhrFields: {\n        responseType: 'blob'\n      },\n      success: function success(data) {\n        btn_export_pdf_previewreport.removeAttr('disabled');\n        btn_export_pdf_previewreport.html('<i class=\"bx bx-up-arrow-circle me-1\"></i>' + '<span class=\"align-middle\">Export to PDF</span>');\n        var blob = new Blob([data]);\n        var link = document.createElement('a');\n        link.href = window.URL.createObjectURL(blob);\n        link.download = \"previewreport.pdf\";\n        link.click();\n      },\n      error: function error(err) {\n        console.log(err);\n      }\n    });\n  });\n  $(\"#confirm-vatreturns-footer div.card\").clone().appendTo('#load-previewreport-vatreturns-footer');\n  $(\"#load-previewreport-vatreturns-footer div.card\").addClass('w-75 float-end');\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZHYtcHJldmlldy1yZXBvcnQuanMuanMiLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBOztBQUVhOztBQUNiO0FBQ0FBLENBQUMsQ0FBQyxZQUFZO0VBRVo7RUFDQSxJQUFJQyxnQkFBZ0IsR0FBR0MsT0FBTyxHQUFHLGlCQUFpQjs7RUFFbEQ7RUFDQUYsQ0FBQyxDQUFDRyxTQUFTLENBQUM7SUFDVkMsT0FBTyxFQUFFO01BQ1AsY0FBYyxFQUFFSixDQUFDLENBQUMseUJBQXlCLENBQUMsQ0FBQ0ssSUFBSSxDQUFDLFNBQVM7SUFDN0Q7RUFDRixDQUFDLENBQUM7O0VBRUY7RUFDQUwsQ0FBQyxDQUFDTSxRQUFRLENBQUMsQ0FBQ0MsRUFBRSxDQUFDLE9BQU8sRUFBRSwrQkFBK0IsRUFBRSxZQUFZO0lBQ2pFLElBQUlDLDRCQUE0QixHQUFHUixDQUFDLENBQUMsSUFBSSxDQUFDO0lBQzFDUSw0QkFBNEIsQ0FBQ0gsSUFBSSxDQUFDLFVBQVUsRUFBRSxVQUFVLENBQUM7SUFDekRHLDRCQUE0QixDQUFDQyxJQUFJLENBQUMsNEVBQTRFLEdBQ3RHLGNBQWMsQ0FBQztJQUV2QixJQUFJQyxVQUFVLEdBQUdGLDRCQUE0QixDQUFDRyxJQUFJLENBQUMsWUFBWSxDQUFDO0lBRWhFWCxDQUFDLENBQUNZLElBQUksQ0FBQztNQUNMO01BQ0FDLEdBQUcsS0FBQUMsTUFBQSxDQUFLYixnQkFBZ0IsRUFBQWEsTUFBQSxDQUFHSixVQUFVLFlBQVM7TUFDOUNLLElBQUksRUFBRSxNQUFNO01BQ1pDLFNBQVMsRUFBRTtRQUNUQyxZQUFZLEVBQUU7TUFDaEIsQ0FBQztNQUNEQyxPQUFPLEVBQUUsU0FBQUEsUUFBVVAsSUFBSSxFQUFFO1FBQ3ZCSCw0QkFBNEIsQ0FBQ1csVUFBVSxDQUFDLFVBQVUsQ0FBQztRQUNuRFgsNEJBQTRCLENBQUNDLElBQUksQ0FBQyw0Q0FBNEMsR0FDdEQsaURBQWlELENBQUM7UUFFMUUsSUFBSVcsSUFBSSxHQUFDLElBQUlDLElBQUksQ0FBQyxDQUFDVixJQUFJLENBQUMsQ0FBQztRQUN6QixJQUFJVyxJQUFJLEdBQUNoQixRQUFRLENBQUNpQixhQUFhLENBQUMsR0FBRyxDQUFDO1FBQ3BDRCxJQUFJLENBQUNFLElBQUksR0FBQ0MsTUFBTSxDQUFDQyxHQUFHLENBQUNDLGVBQWUsQ0FBQ1AsSUFBSSxDQUFDO1FBQzFDRSxJQUFJLENBQUNNLFFBQVEsR0FBQyxtQkFBbUI7UUFDakNOLElBQUksQ0FBQ08sS0FBSyxFQUFFO01BQ2QsQ0FBQztNQUNEQyxLQUFLLEVBQUUsU0FBQUEsTUFBVUMsR0FBRyxFQUFFO1FBQ3BCQyxPQUFPLENBQUNDLEdBQUcsQ0FBQ0YsR0FBRyxDQUFDO01BQ2xCO0lBQ0YsQ0FBQyxDQUFDO0VBQ04sQ0FBQyxDQUFDO0VBRUYvQixDQUFDLENBQUMscUNBQXFDLENBQUMsQ0FBQ2tDLEtBQUssRUFBRSxDQUFDQyxRQUFRLENBQUMsdUNBQXVDLENBQUM7RUFDbEduQyxDQUFDLENBQUMsZ0RBQWdELENBQUMsQ0FBQ29DLFFBQVEsQ0FBQyxnQkFBZ0IsQ0FBQztBQUNoRixDQUFDLENBQUMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9yZXNvdXJjZXMvanMvZHYtcHJldmlldy1yZXBvcnQuanM/NDQ5YSJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFBhZ2UgUHJldmlldyBSZXBvcnRcbiAqL1xuXG4ndXNlIHN0cmljdCc7XG4vLyBEYXRhdGFibGUgKGpxdWVyeSlcbiQoZnVuY3Rpb24gKCkge1xuICBcbiAgLy8gVmFyaWFibGUgZGVjbGFyYXRpb24gZm9yIHRhYmxlXG4gIHZhciBwcmV2aWV3UmVwb3J0VXJsID0gYmFzZVVybCArICdwcmV2aWV3LXJlcG9ydC8nO1xuICAgXG4gIC8vIGFqYXggc2V0dXBcbiAgJC5hamF4U2V0dXAoe1xuICAgIGhlYWRlcnM6IHtcbiAgICAgICdYLUNTUkYtVE9LRU4nOiAkKCdtZXRhW25hbWU9XCJjc3JmLXRva2VuXCJdJykuYXR0cignY29udGVudCcpXG4gICAgfVxuICB9KTsgXG4gIFxuICAvL0V4cG9ydCB0byBQREYgIFxuICAkKGRvY3VtZW50KS5vbignY2xpY2snLCAnLmJ0bi1leHBvcnQtcGRmLXByZXZpZXdyZXBvcnQnLCBmdW5jdGlvbiAoKSB7XG4gICAgICB2YXIgYnRuX2V4cG9ydF9wZGZfcHJldmlld3JlcG9ydCA9ICQodGhpcyk7XG4gICAgICBidG5fZXhwb3J0X3BkZl9wcmV2aWV3cmVwb3J0LmF0dHIoJ2Rpc2FibGVkJywgJ2Rpc2FibGVkJyk7XG4gICAgICBidG5fZXhwb3J0X3BkZl9wcmV2aWV3cmVwb3J0Lmh0bWwoJzxzcGFuIGNsYXNzPVwic3Bpbm5lci1ib3JkZXIgbWUtMVwiIHJvbGU9XCJzdGF0dXNcIiBhcmlhLWhpZGRlbj1cInRydWVcIj48L3NwYW4+JyArXG4gICAgICAgICAgICAgICdFeHBvcnRpbmcuLi4nKTtcblxuICAgICAgdmFyIHZhdF9yZWdfaWQgPSBidG5fZXhwb3J0X3BkZl9wcmV2aWV3cmVwb3J0LmRhdGEoJ3ZhdF9yZWdfaWQnKTsgXG4gICAgIFxuICAgICAgJC5hamF4KHsgICAgICAgIFxuICAgICAgICAvL2RhdGE6IHtib3gxOiBib3gxLCBib3gyOiBib3gyLCBib3gzOiBib3gzLCBib3g0OiBib3g0LCBib3g1OiBib3g1LCBib3g2OiBib3g2LCBib3g3OiBib3g3LCBib3g4OiBib3g4LCBib3g5OiBib3g5fSwgIFxuICAgICAgICB1cmw6IGAke3ByZXZpZXdSZXBvcnRVcmx9JHt2YXRfcmVnX2lkfS9leHBvcnRgLFxuICAgICAgICB0eXBlOiAnUE9TVCcsXG4gICAgICAgIHhockZpZWxkczoge1xuICAgICAgICAgIHJlc3BvbnNlVHlwZTogJ2Jsb2InICAgICAgXG4gICAgICAgIH0sXG4gICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uIChkYXRhKSB7XG4gICAgICAgICAgYnRuX2V4cG9ydF9wZGZfcHJldmlld3JlcG9ydC5yZW1vdmVBdHRyKCdkaXNhYmxlZCcpOyBcbiAgICAgICAgICBidG5fZXhwb3J0X3BkZl9wcmV2aWV3cmVwb3J0Lmh0bWwoJzxpIGNsYXNzPVwiYnggYngtdXAtYXJyb3ctY2lyY2xlIG1lLTFcIj48L2k+JyArXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJzxzcGFuIGNsYXNzPVwiYWxpZ24tbWlkZGxlXCI+RXhwb3J0IHRvIFBERjwvc3Bhbj4nKTtcblxuICAgICAgICAgIHZhciBibG9iPW5ldyBCbG9iKFtkYXRhXSk7ICAgICAgXG4gICAgICAgICAgdmFyIGxpbms9ZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYScpO1xuICAgICAgICAgIGxpbmsuaHJlZj13aW5kb3cuVVJMLmNyZWF0ZU9iamVjdFVSTChibG9iKTtcbiAgICAgICAgICBsaW5rLmRvd25sb2FkPVwicHJldmlld3JlcG9ydC5wZGZcIjtcbiAgICAgICAgICBsaW5rLmNsaWNrKCk7XG4gICAgICAgIH0sXG4gICAgICAgIGVycm9yOiBmdW5jdGlvbiAoZXJyKSB7XG4gICAgICAgICAgY29uc29sZS5sb2coZXJyKTsgICAgIFxuICAgICAgICB9XG4gICAgICB9KTtcbiAgfSk7XG5cbiAgJChcIiNjb25maXJtLXZhdHJldHVybnMtZm9vdGVyIGRpdi5jYXJkXCIpLmNsb25lKCkuYXBwZW5kVG8oJyNsb2FkLXByZXZpZXdyZXBvcnQtdmF0cmV0dXJucy1mb290ZXInKTsgIFxuICAkKFwiI2xvYWQtcHJldmlld3JlcG9ydC12YXRyZXR1cm5zLWZvb3RlciBkaXYuY2FyZFwiKS5hZGRDbGFzcygndy03NSBmbG9hdC1lbmQnKTsgIFxufSk7Il0sIm5hbWVzIjpbIiQiLCJwcmV2aWV3UmVwb3J0VXJsIiwiYmFzZVVybCIsImFqYXhTZXR1cCIsImhlYWRlcnMiLCJhdHRyIiwiZG9jdW1lbnQiLCJvbiIsImJ0bl9leHBvcnRfcGRmX3ByZXZpZXdyZXBvcnQiLCJodG1sIiwidmF0X3JlZ19pZCIsImRhdGEiLCJhamF4IiwidXJsIiwiY29uY2F0IiwidHlwZSIsInhockZpZWxkcyIsInJlc3BvbnNlVHlwZSIsInN1Y2Nlc3MiLCJyZW1vdmVBdHRyIiwiYmxvYiIsIkJsb2IiLCJsaW5rIiwiY3JlYXRlRWxlbWVudCIsImhyZWYiLCJ3aW5kb3ciLCJVUkwiLCJjcmVhdGVPYmplY3RVUkwiLCJkb3dubG9hZCIsImNsaWNrIiwiZXJyb3IiLCJlcnIiLCJjb25zb2xlIiwibG9nIiwiY2xvbmUiLCJhcHBlbmRUbyIsImFkZENsYXNzIl0sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./resources/js/dv-preview-report.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./resources/js/dv-preview-report.js"]();
/******/ 	
/******/ 	return __webpack_exports__;
/******/ })()
;
});