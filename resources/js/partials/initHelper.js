import {Select} from "mdb-ui-kit";

// select.js
export const initializeSelect = (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
        new Select(element, []);
    }
};

