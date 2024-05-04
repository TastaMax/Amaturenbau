import * as mdb from "mdb-ui-kit/js/mdb.es.min.js";

window.mdb = mdb;
import WYSIWYG from 'mdb-wysiwyg-editor/js/wysiwyg.min.js';
import 'mdb-wysiwyg-editor/css/wysiwyg.min.css';

function ready() {
    if (document.getElementById('wysiwygGerman') || document.getElementById('wysiwygEnglish')) {
        let wysiwygConfig = {
            wysiwygTranslations: {
                paragraph: 'Absatz',
                textStyle: 'Textstil',
                heading: 'Überschrift',
                preformatted: 'Vorformatiert',
                bold: 'Fett',
                italic: 'Kursiv',
                strikethrough: 'Durchgestrichen',
                underline: 'Unterstrichen',
                textcolor: 'Textfarbe',
                textBackgroundColor: 'Hintergrundfarbe des Textes',
                alignLeft: 'Linksbündig ausrichten',
                alignCenter: 'Zentriert ausrichten',
                alignRight: 'Rechtsbündig ausrichten',
                alignJustify: 'Blocksatz',
                insertLink: 'Link einfügen',
                insertPicture: 'Bild einfügen',
                unorderedList: 'Ungeordnete Liste',
                orderedList: 'Geordnete Liste',
                increaseIndent: 'Einzug vergrößern',
                decreaseIndent: 'Einzug verkleinern',
                insertHorizontalRule: 'Horizontale Linie einfügen',
                showHTML: 'HTML-Code anzeigen',
                undo: 'Rückgängig machen',
                redo: 'Wiederherstellen',
                addLinkHead: 'Link hinzufügen',
                addImageHead: 'Bild hinzufügen',
                linkUrlLabel: 'URL eingeben:',
                linkDescription: 'Beschreibung eingeben',
                imageUrlLabel: 'Bild-URL eingeben:',
                okButton: 'OK',
                cancelButton: 'Abbrechen',
                moreOptions: 'Mehr Optionen anzeigen',
            },
            'wysiwygLinksSection': false,
            'wysiwygJustifySection': false,
        };

        const wysiwygElementGerman = document.getElementsByClassName('wysiwygGerman')[0];
        const instanceWYSIWYGGerman = new WYSIWYG(wysiwygElementGerman, wysiwygConfig);

        const wysiwygElementEnglish = document.getElementsByClassName('wysiwygEnglish')[0];
        const instanceWYSIWYGEnglish = new WYSIWYG(wysiwygElementEnglish, wysiwygConfig);
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
