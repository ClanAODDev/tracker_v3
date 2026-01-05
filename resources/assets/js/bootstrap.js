import _ from 'lodash';
import axios from 'axios';

window._ = _;
window.axios = axios;

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

if (window.toastr) {
    window.toastr.options = {
        'preventDuplicates': true,
        'progressBar': true,
        'positionClass': 'toast-top-right',
    };
}
