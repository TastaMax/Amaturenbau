import './bootstrap';

import { LazyLoad, Tab, Autocomplete, Select, Datepicker, Input, Animate, Carousel, Collapse, Ripple, Modal, SmoothScroll, Loading, Dropdown, Popover, Alert, Tooltip, Lightbox, initMDB } from "mdb-ui-kit";
initMDB({ LazyLoad, Tab, Autocomplete, Select, Datepicker, Input, Animate, Carousel, Collapse, Ripple, Modal, SmoothScroll, Loading, Dropdown, Popover, Alert, Tooltip, Lightbox });

// CSS
import '~mdb-ui-kit/css/mdb.min.css';
import '../css/app.css';

// Assets
import.meta.glob([
    '../images/**',
    '../videos/**',
]);

// Dashboard JS Logic
import './dashboard/dashboard.js';

// theme.js
import './theme.js';

// notifications.js
import './notifications/notifications.datatable.js';
import './notifications/notifications.jsonformatter.js';

// ShopWare JS
// Category
import './shopware/category/categorys.datatable.js';
import './shopware/category/subcategorys.datatable.js';
// Productclass
import './shopware/productclass/productclass.datatable.js';
import './shopware/productclass/productclass.ecommerce-gallery.js';
import './shopware/productclass/productclass.wysiwyg-editor.js';

function ready () {
    if (document.getElementById('alert')) {
        new Alert(document.getElementById('alert')).show();
    }
}

//Listeners
if (window.addEventListener) {
    window.addEventListener("load", ready, false);
} else if (window.attachEvent) {
    window.attachEvent("onload", ready);
} else {
    window.onload = ready;
}
