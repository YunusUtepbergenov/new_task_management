<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Executor aliases for the edo.ijro.uz import
    |--------------------------------------------------------------------------
    |
    | The Excel export usually names executors as "И.Фамилия". When it uses a
    | nickname instead, map it here to the start of the employee's full name,
    | e.g. 'Диля опа' => 'Закирова Дилафруз'. The --alias option of
    | `php artisan mails:import` does the same for a single run.
    |
    */

    'executor_aliases' => [
        'Диля опа' => 'Закирова Дилафруз',
    ],

];
