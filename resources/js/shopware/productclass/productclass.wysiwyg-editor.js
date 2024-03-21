import * as mdb from "mdb-ui-kit/js/mdb.es.min.js";
window.mdb = mdb;
import WYSIWYG from 'mdb-wysiwyg-editor/js/wysiwyg.min.js';

function ready () {
    if(document.getElementById('onboarding')) {

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
