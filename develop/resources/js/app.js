/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
//autoKana
require('./jquery.autoKana');
// select2
require('./select2.full');
// Bootstrap4 Toggle
require('./bootstrap4-toggle.min');
// chart
require('chart.js');
//select2
$(document).ready(function () {
    $('.select2_search').select2({
        width: '100%',
        matcher: function (params, data) {
            return select2Matcher(params, data);
        },
        language: {"noResults": function(){ return "データがありません。";}},
        escapeMarkup: function (markup) { return markup; }
    });
    $('.select2_search_recipient').select2({
        width: '100%',
        matcher: function (params, data) {
            return select2Matcher(params, data);
        },
    });
    $('.select2_search_product').select2({
        width: '100%',
        matcher: function (params, data) {
            return select2Matcher(params, data);
        },
        templateSelection: function (data) {
            return $(data.element).data('code');
        },
    });

    // 表示の切り替え処理
    $(".toggle-event").on("click", function() {
        $("#"+$(this).data('id')).toggle();

        // 移動先を調整
        $("body,html").animate({ scrollTop: $(this).offset().top }, 300, "swing");
        return false;
    });
});

// ※Vue.js は、使用しない。
// window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

/*
const app = new Vue({
    el: '#app',
});
*/
