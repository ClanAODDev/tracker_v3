window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

window.$ = window.jQuery = require('jquery');

require('bootstrap-sass');

/**
 * Vue is a modern JavaScript library for building interactive web interfaces
 * using reactive data binding and reusable components. Vue's API is clean
 * and simple, leaving you to focus on building your next great project.
 */

window.Vue = require('vue');

// window.Vue.config.devtools = false;
// window.Vue.config.debug = false;
// window.Vue.config.silent = true;
// window.Vue.config.productionTip = false;


window.VueResource = require('vue-resource');
window.toastr = require('toastr');

import VeeValidate from 'vee-validate';
window.Vue.use(VeeValidate);
window.Vue.use(VueResource);

window.Vue.prototype.authorize = function (handler) {

};

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

var axiosLib = require('axios');
window.axios = axiosLib.default || axiosLib;

if (window.axios.defaults && window.axios.defaults.headers && window.axios.defaults.headers.common) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
  window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
} else {
  window.axios.defaults = window.axios.defaults || {};
  window.axios.defaults.headers = window.axios.defaults.headers || {};
  window.axios.defaults.headers.common = {
    'X-CSRF-TOKEN': window.Laravel.csrfToken,
    'X-Requested-With': 'XMLHttpRequest'
  };
}

window.toastr.options = {
  'preventDuplicates': true,
  'progressBar': true,
  'positionClass': 'toast-top-right',
};

