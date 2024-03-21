import {  Select, initMDB } from "mdb-ui-kit";
initMDB({ Select });

// Funktion, um die Liste zu aktualisieren
function updateRisks() {
    //API aufrufen
    fetch('/get-risks')
        .then(response => response.json())
        .then(data => {
            const statusIndicator = document.getElementById('status-indicator');
            statusIndicator.style.borderColor = data['colorCode'];
            statusIndicator.innerHTML = data['messageCode'];
            document.getElementById('systemstatus').innerHTML = '';
            data['risks'].forEach(risks => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-start text-white bg-' + risks.badge;
                listItem.innerHTML = '<div class="ms-2 me-auto">' + risks.name + '</div><span> ' + risks.importance + '</span>';

                //Listelement zur Liste hinzufügen
                document.getElementById('systemstatus').appendChild(listItem);
            });
        })
        .catch(error => console.error('Fehler beim Abrufen der Daten:', error));
}

// Funktion, um die Liste zu aktualisieren
function updateScheduleList() {
    // API aufrufen
    fetch('/get-schedules')
        .then(response => response.json())
        .then(data => {
            document.getElementById('services').innerHTML = '';
            // Daten verarbeiten und Liste aufbauen
            data.forEach(schedule => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.innerHTML = '<span class="badge badge-primary">' + schedule.service + '</span> ' + '<br>' + schedule.description + '<br>' + schedule.updated_at;

                //Listelement zur Liste hinzufügen
                document.getElementById('services').appendChild(listItem);
            });
        })
        .catch(error => console.error('Fehler beim Abrufen der Daten:', error));
}

// Funktion, um die Liste der Logs zu aktualisieren
function updateLastLogs() {
    // API aufrufen
    fetch('/log/getLastEntries')
        .then(response => response.json())
        .then(data => {
            document.getElementById('logs').innerHTML = '';
            // Daten verarbeiten und Liste aufbauen
            data.forEach(log => {
                console.log(log);
                const listItem = document.createElement('div');
                listItem.className = 'note mb-1 note-' + log.importanceColor;
                listItem.innerHTML = '<strong>' + log.system + '</strong><br><span class="d-inline-block text-truncate col-12"> ' + log.message + '</span><br>' + log.updated_at;

                listItem.setAttribute('data-mdb-popover-init', '');
                listItem.setAttribute('data-mdb-placement', 'top');
                listItem.setAttribute('data-mdb-content', log.message);

                //Listelement zur Liste hinzufügen
                document.getElementById('logs').appendChild(listItem);
            });
        })
        .catch(error => console.error('Fehler beim Abrufen der Daten:', error));
}

//Funktion, um die ID des geklickten tr-Elements zu bekommen
function getId(id) {
    console.log('ID des geklickten tr-Elements:', id);
}


function selectLogsOnChange(event) {
    window.logs = event.target.value;
    let logClearButton = document.getElementById("logClearButton");
    if (logClearButton) {
        logClearButton.href = "/log/storage/clear/" + window.logs;
    }
    document.getElementById('log-entries').innerHTML = '<tr id="placeholder" class="placeholder-glow"><td><span class="placeholder col-5"></span></td> <td><span class="placeholder w-25"></span></td> <td><span class="placeholder w-75"></span></td></tr>';
}

function updateLog() {
    fetch('/log/storage/' + window.logs) // Passe die URL entsprechend an
        .then(response => response.json())
        .then(data => {
            const logEntries = data;

            const logTable = document.getElementById('log-entries');
            logTable.innerHTML = ''; // Leere die bisherigen Log-Einträge

            // Füge die aktualisierten Log-Einträge hinzu
            logEntries.forEach(entry => {
                const newRow = document.createElement('tr');

                newRow.id = entry.id;

                // Füge das onclick-Event hinzu, um getId mit entry.id aufzurufen
                newRow.onclick = function () {
                    getId(entry.id);
                };

                const timestampCell = document.createElement('td');
                timestampCell.textContent = entry.timestamp;
                newRow.appendChild(timestampCell);

                const levelCell = document.createElement('td');
                levelCell.innerHTML = entry.level;
                newRow.appendChild(levelCell);

                const messageCell = document.createElement('td');
                messageCell.textContent = entry.message;
                newRow.appendChild(messageCell);


                logTable.appendChild(newRow);
            });

            // Scrolle zum Ende der Tabelle, um die neuesten Einträge anzuzeigen
            logTable.scrollTop = logTable.scrollHeight;
        });
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

if (document.getElementById('dashboard')) {
    // Seite initial laden und dann regelmäßig aktualisieren alle 10 Sekunden
    document.addEventListener('DOMContentLoaded', function () {
        updateScheduleList();
        updateRisks();
        updateLastLogs();
        setInterval(updateScheduleList, 10000); // 10000 Millisekunden = 10 Sekunden
        setInterval(updateRisks, 10000);
        setInterval(updateLastLogs, 10000);
    });

    window.logs = 'laravel';

    const logsSelectBox = document.getElementById('selectLog');
    logsSelectBox.addEventListener('change', selectLogsOnChange);

    sleep(3000);
    sleep(2000).then(() => {
        updateLog()
    });

// Rufe die Funktion alle paar Sekunden auf, um die Log-Zeilen zu aktualisieren
    setInterval(updateLog, 1000); // Zum Beispiel alle 3 Sekunden
}
