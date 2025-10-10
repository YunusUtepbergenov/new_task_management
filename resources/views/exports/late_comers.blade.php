<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <td colspan="3" height="60" style="vertical-align:middle;text-align: center; font-weight:bold; font-family:Cambria;font-size:14px;border:1px solid #000">
                    {{date('01:m:Y')}} - {{date('t:m:Y')}} оралиғида Марказ ходимларининг ишга вақтида келмаслик кўрсаткичлари тўғрисида маълумот<br>
                </td>
            </tr>
            <tr>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Ф.И.О.</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Кечикишлар сони</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td width="5" style="font-family:Cambria; border:1px solid #000">{{ $index + 1 }}</td>
                    <td width="50" style="border:1px solid #000; font-family:Cambria">{{ $item['name'] }}</td>
                    <td width="30" style="font-family:Cambria; border:1px solid #000; text-align:center">{{ $item['late'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
