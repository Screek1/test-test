console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

import Chartist from 'chartist'
window.Chartist = Chartist;

import Scrollbar from 'smooth-scrollbar';
window.Scrollbar = Scrollbar;

import SimpleBar from 'simplebar';
window.SimpleBar = SimpleBar;

require('jquery');
require('jquery-lazy');
require('objectFitPolyfill');
require('./plugin/jquery-ui.min.js');
require('./plugin/jquery.ui.touch-punch.min.js');
require('./plugin/slick.js');
require('./plugin/jquery.validate.min.js');
require('./modules/global.js');
require('./modules/disable-scroll.js');
require('./modules/select.js');
require('./modules/form.js');
require('./modules/popup.js');
require('./modules/mortgage-calc.js');
require('./modules/chart.js');
require('./modules/range.js');
require('./modules/autofill.js');
require('./modules/google-street-view.js');
require('./modules/common.js');

$(document).ready(() => {
    objectFitPolyfill($('.js-object-fit'));
});