import JSZip from 'jszip';
window.JSZip = JSZip;

import ClipboardJS from 'clipboard';
window.ClipboardJS = ClipboardJS;

import toastr from 'toastr';
window.toastr = toastr;

import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';
Chart.register(...registerables);
window.Chart = Chart;

import './vendor/bootcomplete.js';
import './vendor/stickytabs.js';
