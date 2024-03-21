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

    @if($data['data']['entry']->repeatContract == 1)
        <p>Sie erhalten diese E-Mail, da einer Ihrer Verträge bald @if(isset($data['data']['renewDays'])) um {{ $data['data']['renewDays'] }} @endif verlängert wird.</p>
    @else
        @if($data['data']['expired'] === true)
            <p>Einer Ihrer Verträge ist ausgelaufen!</p>
            <p>Bitte beachten Sie folgenden Hinweis: Nach <u>30 Tagen</u> werden die Verträge automatisch
                archiviert.</p>
        @else
            <p>Einer Ihrer Verträge wird bald auslaufen. Bitte beachten Sie das Laufzeitende.</p>
        @endif
    @endif
    <table>
        <thead>
        <tr>
            <th>Nummer</th>
            <th>Name</th>
            <th>Beschreibung</th>
            @if($data['data']['entry']->repeatContract == 1)
                <th>Aktuelles Kündigungsdatum</th>
                <th>Aktuelles Laufzeitende</th>
                <th>Neues Kündigungsdatum</th>
                <th>Neues Laufzeitende</th>
            @else
                <th>Laufzeitende</th>
            @endif
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $data['data']['entry']->idContractManagement }}</td>
            <td>{{ $data['data']['entry']->name }}</td>
            <td>{{ $data['data']['entry']->description }}</td>
            @if($data['data']['entry']->repeatContract == 1)
                <td>{{ $data['data']['noticePeriod']->format('d.m.Y') }}</td>
                <td>{{ $data['data']['endDateContract']->format('d.m.Y') }}</td>
                <td>{{ $data['data']['dates']['noticePeriod']->format('d.m.Y') }}</td>
                <td>{{ $data['data']['dates']['newEndDateContract']->format('d.m.Y') }}</td>
            @else
                <td>{{ $data['data']['endDateContract']->format('d.m.Y') }}</td>
            @endif
        </tr>
        </tbody>
    </table>
    @if($data['data']['entry']->repeatContract == 1)
        <p>
            Der nächste Erinnerungstermin ist am: <b>@if(is_null($data['data']['dates']['reminderDateTime'])) {{ $data['data']['dates']['noticePeriod']->format('d.m.Y') }} @else {{ $data['data']['dates']['reminderDateTime']->format('d.m.Y') }} @endif</b>
        </p>
    @endif
</div>
</body>
</html>
