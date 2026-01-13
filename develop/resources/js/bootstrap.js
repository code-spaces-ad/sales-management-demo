import axios from 'axios';
import Echo from "laravel-echo";

window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
    require('jquery-ui/ui/widgets/sortable.js');
    require('jquery-ui/ui/widgets/autocomplete.js');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    }
});

window.Echo.channel('charge_closing_channel')
    .listen('.charge_closing.sent', (e) => {
        $('#customer_' + e.customer_id).replaceWith(e.redraw_tag);
    })
    .listen('.completed', (e) => {
        console.log(e.flg);

        revertClosingButtons();
        revertClosingLoading();
    });

window.Echo.channel('purchase_closing_channel')
    .listen('.purchase_closing.sent', (e) => {
        $('#supplier_' + e.supplier_id).replaceWith(e.redraw_tag);
    })
    .listen('.completed', (e) => {
        console.log(e.flg);

        revertClosingButtons();
        revertClosingLoading();
    });
