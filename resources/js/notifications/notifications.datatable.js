import CustomeDatatables from '/resources/js/partials/datatables';
import { initializeSelect } from '/resources/js/partials/initHelper';
import {Modal, initMDB} from "mdb-ui-kit";

if(document.getElementById('notificationsDatatable')) {
    const columns = [
        { label: 'Nummer', field: 'id', width: 35},
        { label: 'System', field: 'system' },
        { label: 'Meldung', field: 'message' },
        { label: 'Schweregrad', field: 'importance', sort: false, width: 35 },
        { label: 'Datum', field: 'created_at', width: 300 },
        { label: 'Aktion', field: 'action', sort: false, width: 250 }
    ];

    const NotificationTable = new CustomeDatatables(
        columns,
        'datatable-search-input',
        'notificationsDatatable',
        (notification) => ({
            ...notification,
            id: parseInt(notification.id),
            system: notification.system,
            message: notification.message,
            importance: notification.importance,
            created_date: Date.parse(notification.created_at),
            action: `<button data-log="${notification.id}" class="btn btn-primary btn-sm eventlistener-click-log">Details</button>`
        }),
        '/notifications/json/getData/'
    );

    document.addEventListener('click', (event) => {
        initMDB({ Modal });
        NotificationTable.handleButtonClick(
            event,
            'eventlistener-click-log',
            'data-log',
            (id) => NotificationTable.fetchData(
                '/log/' + id,
                (data) => {
                    console.log(data);
                    let logDetails = data.log.debug;
                    try {
                        // Versuche das JSON zu parsen
                        logDetails = JSON.stringify(JSON.parse(logDetails), null, 2);
                    } catch (error) {
                        // Falls ein Fehler auftritt, handhabe ihn hier
                        console.log('Fehler beim Parsen des JSON:', error);
                        // Du kannst hier optional eine alternative Aktion durchführen oder den Fehler weiterreichen.
                    }

                    document.getElementById('system').innerHTML = data.log.system;
                    document.getElementById('importance').classList.remove('badge-info', 'badge-danger', 'badge-warning', 'badge');
                    document.getElementById('importance').classList.add('badge', 'badge-' + data.importanceColor);
                    document.getElementById('importance').innerHTML = data.importanceText;
                    document.getElementById('importance').setAttribute('data-mdb-original-title', 'Level ' + data.importanceLevel);
                    document.getElementById('importance').title = 'Level ' +  data.importanceLevel;
                    document.getElementById('LogDate').innerHTML = data.date;
                    document.getElementById('notification').innerHTML = data.log.message;
                    document.getElementById('logDetails').innerHTML = logDetails;
                    document.getElementById('LogNumber').innerText = event.target.getAttribute('data-log');

                    const modal = new Modal(document.getElementById('logModal'), []);
                    modal.show();
                }
            )
        );
    });

    const selectFilter = document.getElementById('addSelectGroup');
    initializeSelect('addSelectGroup');

    selectFilter.addEventListener('change', (e) => {
        const url = e.target.value
            ? '/notifications/json/getData/' + e.target.value
            : '/notifications/json/getData';

        NotificationTable.setApiUrl(url);
        NotificationTable.updateDataTable();
    });
}
