<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>

</head>
<body>
<div>
    <p>Sehr geehrte Damen und Herren,</p>
    <p>Sie erhalten diese E-Mail, weil einer Ihrer Verträge nach 30 Tagen ausgelaufen ist.</p>
    <p>Der Vertrag wurde automatisch archiviert.</p>
    @if($data['contractmanager'])
        <p>Wenn Sie den Vertrag reaktivieren möchten, gehen Sie dazu in JobRouter in die Vertragsverwaltung.</p>
    @endif
    <table>
        <thead>
        <tr>
            <th>Nummer</th>
            <th>Name</th>
            <th>Beschreibung</th>
            <th>Laufzeitende</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $data['data']['entry']->idContractManagement }}</td>
            <td>{{ $data['data']['entry']->name }}</td>
            <td>{{ $data['data']['entry']->description }}</td>
            <td>{{ $data['data']['endDateContract']->format('d.m.Y') }}</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
