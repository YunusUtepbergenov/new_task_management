<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #444; padding: 6px; text-align: center; }
        th { background-color: #e2e3e5; font-weight: bold; }
        tr:nth-child(even) td { background-color: #f8f9fa; }
        h3 { text-align: center; margin-bottom: 14px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="4">Октябрь ойида Марказда дам олиш кунлари <i>(шанба/якшанба)</i> ишлаган ходимлар тўғрисида маълумот</th>
            </tr>
            <tr>
                <th>#</th>
                <th>Ф.И.О.</th>
                <th>Ишланган кунлар сони</th>
                <th>Ишланган соат</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:left">{{ $r['name'] }}</td>
                    <td>{{ $r['off_days'] }}</td>
                    <td>{{ $r['total_hhmm'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
