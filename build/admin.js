/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/admin.scss":
/*!************************!*\
  !*** ./src/admin.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = window["jQuery"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/admin.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _admin_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./admin.scss */ "./src/admin.scss");
/**
 * This file is used on the transient page to power quick-editing.
 */

/* global inlineEditL10n, ajaxurl, inlineEditTransient */



window.wp = window.wp || {};
inlineEditL10n = {
  error: 'Error while saving the changes.',
  saved: 'Changes saved.',
  confirmDelete: 'Are you sure you want to delete this transient?'
};

/**
 * Consists of functions relevant to the inline taxonomy editor.
 *
 * @namespace inlineEditTransient
 *
 * @param    {Object} $    jQuery
 * @param    {Object} wp   The WordPress global object.
 *
 * @property {string} type The type of inline edit we are currently on.
 * @property {string} what The type property with a hash prefixed and a dash suffixed.
 */
(function ($, wp) {
  window.inlineEditTransient = {
    /**
     * Initializes the inline transient editor by adding event handlers to be able to quick-edit.
     *
     * @this inlineEditTransient
     * @memberof inlineEditTransient
     * @return {void}
     */
    init() {
      const t = this,
        row = $('#inline-edit'),
        theList = $('#the-list');
      t.type = theList.attr('data-wp-lists').substring(5);
      t.what = '#' + t.type + '-';
      theList.on('click', '.editinline', function () {
        $(this).attr('aria-expanded', 'true');
        inlineEditTransient.edit(this);
      });

      /**
       * Cancels inline editing when pressing escape inside the inline editor.
       *
       * @param {Object} e The keyup event that has been triggered.
       */
      row.keyup(function (e) {
        // 27 = [escape]
        if (e.which === 27) {
          return inlineEditTransient.revert();
        }
      });

      /**
       * Cancels inline editing when clicking the cancel button.
       */
      $('.cancel', row).click(function () {
        return inlineEditTransient.revert();
      });

      /**
       * Saves the inline edits when clicking the save button.
       */
      $('.save', row).click(function () {
        return inlineEditTransient.save(this);
      });

      /**
       * Saves the inline edits when pressing enter inside the inline editor.
       */
      $('input, select', row).keydown(function (e) {
        // 13 = [enter]
        if (e.which === 13) {
          return inlineEditTransient.save(this);
        }
      });

      /**
       * Saves the inline edits on submitting the inline edit form.
       */
      $('#posts-filter input[type="submit"]').mousedown(function () {
        t.revert();
      });

      /**
       * Change datetime fields to the current user time
       */
      inlineEditTransient.updateDates();

      /**
       * Confirm before deleting a transient
       */
      theList.on('click', '.row-actions .delete a.submitdelete', function () {
        return confirm(inlineEditL10n.confirmDelete);
      });

      /**
       * Handle rate us footer click
       */
      $('body').on('click', 'a.leira-transients-admin-rating-link', function () {
        $.post(ajaxurl, {
          action: 'leira-transients-footer-rated',
          _wpnonce: $(this).data('nonce')
        }, function () {
          //on success do nothing
        });
        $(this).parent().text($(this).data('rated'));
      });
    },
    /**
     * Toggles the quick edit based on if it is currently shown or hidden.
     *
     * @this inlineEditTransient
     * @memberof inlineEditTransient
     *
     * @param {HTMLElement} el An element within the table row or the table row itself that we want to quick edit.
     * @return {void}
     */
    toggle(el) {
      const t = this;
      $(t.what + t.getId(el)).css('display') === 'none' ? t.revert() : t.edit(el);
    },
    /**
     * Shows the quick editor
     *
     * @this inlineEditTransient
     * @memberof inlineEditTransient
     *
     * @param {string|HTMLElement} id The ID of the term we want to quickly edit or an element within the table row or the table row itself.
     * @return {boolean} Always returns false.
     */
    edit(id) {
      let editRow,
        rowData,
        val,
        t = this;
      t.revert();

      // Makes sure we can pass an HTMLElement as the ID.
      if (typeof id === 'object') {
        id = t.getId(id);
      }
      editRow = $('#inline-edit').clone(true), rowData = $('#inline_' + id);
      $('td', editRow).attr('colspan', $('th:visible, td:visible', '.wp-list-table.widefat:first thead').length);
      $(t.what + id).hide().after(editRow).after('<tr class="hidden"></tr>');
      $('> div', rowData).each(function (index, value) {
        value = $(value);
        const name = value.attr('class');
        value = value.text();
        if (name === 'expiration') {
          //Convert to local time string suitable for datetime-local input
          const time = new Date(value * 1000);
          //const timeString = time.toLocaleString();
          const pad = n => String(n).padStart(2, '0');
          let timeString = time.getFullYear() + '-' + pad(time.getMonth() + 1) + '-' + pad(time.getDate()) + 'T' + pad(time.getHours()) + ':' + pad(time.getMinutes());
          timeString += ':' + pad(time.getSeconds());
          $(':input[name=expiration]', editRow).val(timeString); //local time string
        } else if (name === 'name') {
          //in case the user updates the name
          let theName = value;
          if (value.toString().startsWith('_site_transient_')) {
            theName = value.toString().substring(16);
          }
          if (value.toString().startsWith('_transient_')) {
            theName = value.substring(11);
          }
          $(':input[name="name"]', editRow).val(theName);
          $(':input[name="original-name"]', editRow).val(value);
        } else {
          //Any other input field
          $(':input[name=' + name + ']', editRow).val(value);
        }
      });
      $(editRow).attr('id', 'edit-' + id).addClass('inline-editor').show();
      $('.ptitle', editRow).eq(0).focus();
      return false;
    },
    /**
     * Saves the quick-edit data to the server and replaces the table row with the HTML retrieved from the server.
     *
     * @this inlineEditTransient
     * @memberof inlineEditTransient
     *
     * @param {string|HTMLElement} id The ID of the term we want to quick-edit or an element within the table row or the table row itself.
     * @return {boolean} Always returns false.
     */
    save(id) {
      let params, fields;

      // Makes sure we can pass an HTMLElement as the ID.
      if (typeof id === 'object') {
        id = this.getId(id);
      }
      $('table.widefat .spinner').addClass('is-active');
      let expiration = $('#edit-' + id + ' input[name="expiration"]').val();
      expiration = new Date(expiration);
      expiration = expiration.toISOString(); //Convert tu UTC timestamp
      params = {
        expiration: expiration
      };
      fields = $('#edit-' + id).find(':input').not('input[name="expiration"]').serialize();
      params = fields + '&' + $.param(params);

      // Do the ajax request to save the data to the server.
      $.post(ajaxurl, params).done(
      /**
       * Handles the response from the server
       *
       * Handles the response from the server, replaces the table row with the response
       * from the server.
       *
       * @param {string} r The string with which to replace the table row.
       */
      function (r) {
        let row,
          new_id,
          $errorNotice = $('#edit-' + id + ' .inline-edit-save .notice-error'),
          $error = $errorNotice.find('.error');
        $('table.widefat .spinner').removeClass('is-active');
        if (r) {
          if (-1 !== r.indexOf('<tr')) {
            $(inlineEditTransient.what + id).siblings('tr.hidden').addBack().remove();
            new_id = $(r).attr('id');
            $('#edit-' + id).before(r).remove();
            if (new_id) {
              row = $('#' + new_id);
            } else {
              row = $(inlineEditTransient.what + id);
            }
            row.hide().fadeIn(400, function () {
              // Move focus back to the Quick Edit button.
              row.find('.editinline').attr('aria-expanded', 'false').focus();
              wp.a11y.speak(inlineEditL10n.saved);
            });
            inlineEditTransient.updateDates();
          } else {
            $errorNotice.removeClass('hidden');
            $error.html(r);
            /*
             * Some error strings may contain HTML entities (e.g. `&#8220`), let's use
             * the HTML element's text.
             */
            wp.a11y.speak($error.text());
          }
        } else {
          $errorNotice.removeClass('hidden');
          $error.html(inlineEditL10n.error);
          wp.a11y.speak(inlineEditL10n.error);
        }
      }).fail(function () {
        /**
         * Handles the case when the ajax request fails.
         */
        $('table.widefat .spinner').removeClass('is-active');
        const $errorNotice = $('#edit-' + id + ' .inline-edit-save .notice-error');
        $errorNotice.removeClass('hidden');
        $errorNotice.find('.error').html(inlineEditL10n.error);
        wp.a11y.speak(inlineEditL10n.error);
      });

      // Prevent submitting the form when pressing Enter on a focused field.
      return false;
    },
    /**
     * Closes the quick edit form.
     *
     * @this inlineEditTransient
     * @memberof inlineEditTransient
     * @return {void}
     */
    revert() {
      let id = $('table.widefat tr.inline-editor').attr('id');
      if (id) {
        $('table.widefat .spinner').removeClass('is-active');
        $('#' + id).siblings('tr.hidden').addBack().remove();
        id = id.substring(id.lastIndexOf('-') + 1);

        // Show the transient row and move focus back to the Quick Edit button.
        $(this.what + id).show().find('.editinline').attr('aria-expanded', 'false').focus();
      }
    },
    /**
     * Update the date fields in the table to the current user time.
     *
     * @return {void}
     */
    updateDates() {
      $('#the-list time').each(function (index, el) {
        el = $(el);
        const value = el.attr('datetime');
        const format = el.data('format');
        const date = new Date(value);
        el.text(inlineEditTransient.dateFormat(format, date));
      });
    },
    /**
     * Return a formatted string from a date Object mimicking PHP's date() functionality
     *
     * format string "Y-m-d H:i:s" or similar PHP-style date format string
     * date mixed Date Object, Datestring, or milliseconds
     *
     * @param {string} format
     * @param {Date} date
     */
    dateFormat(format, date) {
      if (!(date instanceof Date)) {
        date = new Date(date);
      }
      const map = {
        Y: date.getFullYear(),
        y: date.getFullYear().toString().slice(-2),
        m: String(date.getMonth() + 1).padStart(2, '0'),
        n: date.getMonth() + 1,
        d: String(date.getDate()).padStart(2, '0'),
        j: date.getDate(),
        H: String(date.getHours()).padStart(2, '0'),
        h: String((date.getHours() + 11) % 12 + 1).padStart(2, '0'),
        g: (date.getHours() + 11) % 12 + 1,
        i: String(date.getMinutes()).padStart(2, '0'),
        s: String(date.getSeconds()).padStart(2, '0'),
        a: date.getHours() < 12 ? 'am' : 'pm',
        A: date.getHours() < 12 ? 'AM' : 'PM',
        D: date.toLocaleString('en-US', {
          weekday: 'short'
        }),
        l: date.toLocaleString('en-US', {
          weekday: 'long'
        }),
        M: date.toLocaleString('en-US', {
          month: 'short'
        }),
        F: date.toLocaleString('en-US', {
          month: 'long'
        }),
        c: date.toISOString(),
        w: date.getDay()
      };
      return format.replace(/\\?([a-zA-Z])/g, (match, key) => {
        return map[key] !== undefined ? map[key] : key;
      });
    },
    /**
     * Retrieves the ID of the transient inside the table row.
     *
     * @memberof inlineEditTransient
     *
     * @param {HTMLElement} o An element within the table row or the table row itself.
     * @return {string} The ID of the term based on the element.
     */
    getId(o) {
      const id = o.tagName === 'TR' ? o.id : $(o).parents('tr').attr('id');
      return id.substring(id.indexOf('-') + 1);
    }
  };
  $(document).ready(function () {
    inlineEditTransient.init();
  });
})(jQuery, window.wp);
})();

/******/ })()
;
//# sourceMappingURL=admin.js.map