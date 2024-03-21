const notificationJsonFormatter = document.getElementById('notificationJsonFormatter');

if (notificationJsonFormatter) {
    notificationJsonFormatter.addEventListener('click', (e) => {
        let postUrl = 'https://jsonformatter.curiousconcept.com/';

        let newWindow = window.open();

        // Erstelle ein HTML-Formular dynamisch
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = postUrl;

        // Füge ein verstecktes Input-Feld für die Daten hinzu
        let dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'data'; // Der Name muss der sein, den der Server erwartet
        dataInput.value = document.getElementById('logDetails').innerHTML;
        form.appendChild(dataInput);

        // Füge das Formular zum neuen Fenster hinzu und sende es
        newWindow.document.body.appendChild(form);
        form.submit();
    });
}
