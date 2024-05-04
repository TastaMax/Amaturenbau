import * as mdb from "mdb-ui-kit/js/mdb.es.min.js";
window.mdb = mdb;
import EcommerceGallery from 'mdb-ecommerce-gallery/js/ecommerce-gallery.min.js';
import 'mdb-ecommerce-gallery/css/ecommerce-gallery.min.css';

function ready () {
    if(document.getElementById('ecommerce-gallery')) {
        const elementGallery = document.querySelector('.ecommerce-gallery');
        const instanceEcomerceGallery = new EcommerceGallery(elementGallery);
        //instanceEcomerceGallery.init();
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
