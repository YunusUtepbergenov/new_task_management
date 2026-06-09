<?php
// One-off helper: insert a single turnstile entry for id=121 at 2026-06-02 09:10:00.
// Idempotent — re-running will NOT create a duplicate. Delete this file after use.

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$c  = DB::connection('turnstile');
$dt = '2026-06-02 09:10:00';

$exists = $c->selectOne('SELECT COUNT(*) c FROM user_logs WHERE id = ? AND auth_datetime = ?', [121, $dt])->c;
if ($exists) {
    echo "already exists ({$exists}) — nothing inserted\n";
} else {
    // Copy device/serial/name byte-exact from a real morning id=121 entry (no Cyrillic literals here).
    $s = $c->selectOne(
        'SELECT device_name, serial_num, empl_name FROM user_logs WHERE id = ? AND serial_num = ? ORDER BY auth_datetime DESC LIMIT 1',
        [121, 'DS-K1T642EF20200117V030000ENE24816231']
    );
    $c->insert(
        'INSERT INTO user_logs (id, auth_datetime, auth_date, auth_time, direction, device_name, serial_num, empl_name, card_num) VALUES (?,?,?,?,?,?,?,?,?)',
        ['121', $dt, '2026-06-02', '09:10:00', 'IN', $s->device_name, $s->serial_num, $s->empl_name, '121']
    );
    echo "inserted one row\n";
}

$row = $c->selectOne('SELECT * FROM user_logs WHERE id = ? AND auth_datetime = ?', ['121', $dt]);
echo "id=121 today total: " . $c->selectOne('SELECT COUNT(*) c FROM user_logs WHERE id = ? AND auth_date = ?', [121, '2026-06-02'])->c . "\n";
echo "row: id={$row->id} dt={$row->auth_datetime} dir={$row->direction} dev={$row->device_name}\n";
