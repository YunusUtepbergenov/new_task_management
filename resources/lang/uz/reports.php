<?php

return [
    // Weekly
    'weekly_title' => 'Секторлар бўйича ҳафталик режа',
    'weekly_subtitle' => 'Ҳафталик вазифалар, секторлар бўйича гуруҳланган',

    // Protocol
    'protocol_title' => 'Баённома учун вазифалар',
    'protocol_subtitle' => 'Баённомага киритиш учун белгиланган вазифалар',
    'no_protocol_tasks' => 'Танланган ҳафта учун баённома вазифалари йўқ.',
    'remove_from_protocol' => 'Вазифани баённомадан олиб ташлаш?',

    // Common table
    'task_name' => 'Номи',
    'deadline' => 'Муддат',
    'responsible' => 'Масъул',
    'category' => 'Тоифа',
    'status' => 'Ҳолат',
    'score' => 'Балл',
    'creator' => 'Топшириқ берувчи',
    'state' => 'Вазият',
    'created_at' => 'Яратилган сана',
    'original_deadline' => 'Аслий муддат:',
    'deadline_extended' => 'Муддат узайтирилди',
    'recurring_task' => 'Такрорланувчи вазифа',
    'no_tasks_week' => 'Танланган ҳафта учун вазифалар йўқ.',

    // KPI
    'kpi_select_month' => 'Ойни танланг:',
    'kpi_norm' => 'KPI (меъёр)',
    'kpi_total' => 'KPI (жами)',

    // Filter
    'from' => 'Дан',
    'to' => 'Гача',
    'download_table' => 'Жадвални юклаб олиш',
    'sector_report' => 'Секторлар бўйича ҳисобот',
    'done_tasks_report' => 'Бажарилган вазифалар',
    'efficiency' => 'Самарадорлик:',
    'all_tasks' => 'Барча вазифалар',
    'overdue' => 'Муддати ўтган',

    // Sections
    'sectors' => 'Секторлар',
    'tasks' => 'Вазифалар',
    'employees' => 'Ходимлар',

    // Actions
    'edit' => 'Таҳрирлаш',
    'delete' => 'Ўчириш',
    'delete_task_confirm' => 'Вазифани ўчириш?',
    'save' => 'Сақлаш',
    'export_excel' => 'Excel га экспорт',
    'protocol' => 'Баённома',
    'for_protocol' => 'Баённома учун',

    // Statuses
    'status_unread' => 'Ўқилмаган',
    'status_in_progress' => 'Бажарилмоқда',
    'status_awaiting_confirmation' => 'Тасдиқ кутилмоқда',
    'status_done' => 'Бажарилди',

    // Filter table headers
    'full_name' => 'Ф.И.О',
    'sector' => 'Сектор',
    'all' => 'Барчаси',
    'deadline_col' => 'Муддат',

    // Workload
    'workload_title' => 'Ходимлар бандлиги',
    'active_tasks' => 'Фаол вазифалар',
    'workload' => [
        'subtitle' => 'Ички топшириқлар ва edo.ijro.uz ҳужжатлари бўйича жорий бандлик',
        'as_of' => ':date ҳолатига',
        'variants' => ['a' => 'Жадвал', 'b' => 'Секторлар', 'c' => 'Муддатлар'],
        'stats' => [
            'in_work' => 'Ишдаги топшириқлар',
            'split' => 'Топшириқлар :tasks · edo.ijro.uz :mails',
            'overdue' => 'Муддати ўтган',
            'people_with_overdue' => ':n ходимда',
            'review' => 'Тасдиқ кутмоқда',
            'levels' => 'Юклама даражаси',
            'thresholds' => 'Паст — 1–2 иш, ўрта — 3–5, юқори — 6 ва ундан кўп',
        ],
        'sources' => ['task' => 'Топшириқ', 'mail' => 'edo.ijro.uz', 'tasks' => 'Топшириқлар', 'mails' => 'edo.ijro.uz'],
        'levels' => ['high' => 'Юқори', 'medium' => 'Ўрта', 'low' => 'Паст', 'idle' => 'Бўш'],
        'filters' => [
            'all' => 'Барчаси',
            'high' => 'Юқори',
            'medium' => 'Ўрта',
            'low' => 'Паст',
            'overdue' => 'Кечиккан иши бор',
            'search' => 'Ходимни қидириш',
        ],
        'sort' => [
            'role' => 'Лавозим бўйича',
            'load' => 'Юклама бўйича',
            'overdue' => 'Кечикиш бўйича',
            'name' => 'Исм бўйича',
        ],
        'columns' => [
            'employee' => 'Ходим',
            'load' => 'Юклама',
            'tasks' => 'Топшириқлар',
            'mails' => 'edo.ijro.uz',
            'overdue' => 'Кечиккан',
            'review' => 'Тасдиқда',
        ],
        'buckets' => [
            'overdue' => 'Муддати ўтган',
            'week' => '7 кун ичида',
            'next_week' => '8–14 кун',
            'month' => '15–30 кун',
            'later' => 'Кейинроқ',
        ],
        'states' => [
            'new' => 'Ўқилмаган',
            'doing' => 'Бажарилмоқда',
            'rework' => 'Қайта ишланмоқда',
            'review' => 'Тасдиқ кутмоқда',
        ],
        'days' => [
            'overdue' => ':n кун кечикди',
            'today' => 'Бугун',
            'tomorrow' => 'Эртага',
            'left' => ':n кун қолди',
            'none' => 'Муддатсиз',
        ],
        'main' => 'Асосий ижрочи',
        'co' => 'Ҳамижрочи',
        'deadlines_total' => 'Яқин муддат, жами :n та',
        'head' => 'Мудир',
        'people' => 'ходим',
        'empty' => 'Очиқ иш йўқ',
        'nothing_found' => 'Ҳеч ким топилмади',
        'attention' => 'Энг кўп кечиккан',
        'worst' => 'энг эскиси :n кун',
        'close' => 'Ёпиш',
        'expand_all' => 'Барчасини очиш',
        'collapse_all' => 'Барчасини йиғиш',
        'in_work' => 'ишда',
        'all_items' => 'Барча ишлар',
    ],
];
