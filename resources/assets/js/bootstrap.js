import _ from 'lodash';
import jQuery from 'jquery';
import 'bootstrap-sass';
import Vue from 'vue';
import VueResource from 'vue-resource';
import toastr from 'toastr';
import axios from 'axios';
import VeeValidate from 'vee-validate';

window._ = _;
window.$ = window.jQuery = jQuery;
window.Vue = Vue;
window.VueResource = VueResource;
window.toastr = toastr;
window.axios = axios;

Vue.use(VeeValidate);
Vue.use(VueResource);

Vue.prototype.authorize = function (handler) {};

if (axios.defaults && axios.defaults.headers && axios.defaults.headers.common) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
} else {
    axios.defaults = axios.defaults || {};
    axios.defaults.headers = axios.defaults.headers || {};
    axios.defaults.headers.common = {
        'X-CSRF-TOKEN': window.Laravel.csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
    };
}

toastr.options = {
    'preventDuplicates': true,
    'progressBar': true,
    'positionClass': 'toast-top-right',
};

