import _ from 'lodash';
import toastr from 'toastr';
import axios from 'axios';

window._ = _;
window.toastr = toastr;
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

toastr.options = {
    'preventDuplicates': true,
    'progressBar': true,
    'positionClass': 'toast-top-right',
};
