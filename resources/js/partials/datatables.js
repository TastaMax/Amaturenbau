import { Select, Modal, Datatable, Ripple, initMDB } from "mdb-ui-kit";

export default class CustomeDatatables {
    constructor(columns, searchInputId, datatableId, dataMappingFunction, apiUrl) {
        // Initialisierung von MDB-UI-Kit
        initMDB({ Select, Modal, Datatable, Ripple });

        // Definieren der Tabelle
        this.columns = columns;
        this.asyncTable = this.createTable(datatableId);
        this.searchInput = document.getElementById(searchInputId);
        this.dataMappingFunction = dataMappingFunction;
        this.apiUrl = apiUrl;

        // Event-Listener für die Suche hinzufügen
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => this.asyncTable.search(e.target.value));
        }

        // Daten abrufen und Tabelle aktualisieren
        this.updateDataTable();
    }

    createTable(datatableId) {
        const datatableElement = document.getElementById(datatableId);

        // Überprüfen, ob das Element existiert, bevor die Tabelle initialisiert wird
        if (datatableElement) {
            return new Datatable(
                datatableElement,
                { columns: this.columns },
                {
                    loading: true,
                    loadingMessage: 'Lade Daten...',
                    noFoundMessage: 'Keine Einträge gefunden.',
                    rowsText: 'Spalten pro Seite',
                    ofText: 'von',
                    hover: true,
                    sortField: 'id',
                    sortOrder: 'desc'
                }
            );
        }

        // Falls das Element nicht existiert, gib null zurück oder handle es auf andere Weise
        return null;
    }

    handleButtonClick(event, cssClass, dataAttribute, fetchDataFunction) {
        if (event.target.classList.contains(cssClass)) {
            let id = event.target.getAttribute(dataAttribute);
            fetchDataFunction(id);
        }
    }

    fetchData(url, callback) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                callback(data);
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    }

    updateDataTable() {
        if (this.asyncTable) {
            this.fetchData(this.apiUrl, (data) => {
                this.asyncTable.update({
                        rows: data.map(this.dataMappingFunction),
                    },
                    { loading: false });
            });
        }
    }

    setApiUrl(newApiUrl) {
        this.apiUrl = newApiUrl;
    }
}
