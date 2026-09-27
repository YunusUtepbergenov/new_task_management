<?php

return [
    // Weekly
    'weekly_title' => 'Еженедельный план по секторам',
    'weekly_subtitle' => 'Задачи на неделю, сгруппированные по секторам',

    // Protocol
    'protocol_title' => 'Задачи для протокола',
    'protocol_subtitle' => 'Задачи, отмеченные для включения в протокол',
    'no_protocol_tasks' => 'Нет задач для протокола на выбранную неделю.',
    'remove_from_protocol' => 'Убрать задачу из протокола?',

    // Common table
    'task_name' => 'Название',
    'deadline' => 'Срок',
    'responsible' => 'Ответственный',
    'category' => 'Категория',
    'status' => 'Статус',
    'score' => 'Балл',
    'creator' => 'Постановщик',
    'state' => 'Состояние',
    'created_at' => 'Дата создания',
    'original_deadline' => 'Оригинальный срок:',
    'deadline_extended' => 'Срок продлен',
    'recurring_task' => 'Повторяющаяся задача',
    'no_tasks_week' => 'Нет задач на выбранную неделю.',

    // KPI
    'kpi_select_month' => 'Выберите месяц:',
    'kpi_norm' => 'KPI (норма)',
    'kpi_total' => 'KPI (итого)',

    // Filter
    'from' => 'От',
    'to' => 'До',
    'download_table' => 'Скачать таблицу',
    'sector_report' => 'Отчёт по секторам',
    'done_tasks_report' => 'Выполненные задачи',
    'efficiency' => 'Эффективность:',
    'all_tasks' => 'Все задачи',
    'overdue' => 'Просроченный',

    // Sections
    'sectors' => 'Секторы',
    'tasks' => 'Задачи',
    'employees' => 'Сотрудники',

    // Actions
    'edit' => 'Изменить',
    'delete' => 'Удалить',
    'delete_task_confirm' => 'Удалить задачу?',
    'save' => 'Сохранить',
    'export_excel' => 'Экспорт в Excel',
    'protocol' => 'Протокол',
    'for_protocol' => 'Для протокола',

    // Statuses
    'status_unread' => 'Не прочитано',
    'status_in_progress' => 'Выполняется',
    'status_awaiting_confirmation' => 'Ждет подтверждения',
    'status_done' => 'Выполнено',

    // Filter table headers
    'full_name' => 'Ф.И.О',
    'sector' => 'Сектор',
    'all' => 'Все',
    'deadline_col' => 'Крайний срок',

    // Workload
    'workload_title' => 'Загруженность сотрудников',
    'active_tasks' => 'Активных задач',
    'workload' => [
        'subtitle' => 'Текущая загрузка по внутренним поручениям и документам edo.ijro.uz',
        'as_of' => 'на :date',
        'variants' => ['a' => 'Таблица', 'b' => 'Секторы', 'c' => 'Сроки'],
        'stats' => [
            'in_work' => 'Поручений в работе',
            'split' => 'Поручения :tasks · edo.ijro.uz :mails',
            'overdue' => 'Просрочено',
            'people_with_overdue' => 'у :n сотрудников',
            'review' => 'Ждут подтверждения',
            'levels' => 'Уровень загрузки',
            'thresholds' => 'Низкая — 1–2 поручения, средняя — 3–5, высокая — 6 и более',
        ],
        'sources' => ['task' => 'Поручение', 'mail' => 'edo.ijro.uz', 'tasks' => 'Поручения', 'mails' => 'edo.ijro.uz'],
        'levels' => ['high' => 'Высокая', 'medium' => 'Средняя', 'low' => 'Низкая', 'idle' => 'Свободен'],
        'filters' => [
            'all' => 'Все',
            'high' => 'Высокая',
            'medium' => 'Средняя',
            'low' => 'Низкая',
            'overdue' => 'С просрочкой',
            'search' => 'Поиск сотрудника',
        ],
        'sort' => [
            'role' => 'По должности',
            'load' => 'По загрузке',
            'overdue' => 'По просрочке',
            'name' => 'По имени',
        ],
        'columns' => [
            'employee' => 'Сотрудник',
            'load' => 'Загрузка',
            'tasks' => 'Поручения',
            'mails' => 'edo.ijro.uz',
            'overdue' => 'Просрочено',
            'review' => 'На проверке',
        ],
        'buckets' => [
            'overdue' => 'Просрочено',
            'week' => 'В течение 7 дней',
            'next_week' => '8–14 дней',
            'month' => '15–30 дней',
            'later' => 'Позже',
        ],
        'states' => [
            'new' => 'Не прочитано',
            'doing' => 'Выполняется',
            'rework' => 'На доработке',
            'review' => 'Ждет подтверждения',
        ],
        'days' => [
            'overdue' => 'просрочено на :n дн.',
            'today' => 'Сегодня',
            'tomorrow' => 'Завтра',
            'left' => 'осталось :n дн.',
            'none' => 'Без срока',
        ],
        'main' => 'Основной исполнитель',
        'co' => 'Соисполнитель',
        'deadlines_total' => 'Ближайший срок, всего :n',
        'head' => 'Заведующий',
        'people' => 'сотр.',
        'empty' => 'Нет открытых поручений',
        'nothing_found' => 'Никто не найден',
        'attention' => 'Наибольшая просрочка',
        'worst' => 'самое старое — :n дн.',
        'close' => 'Закрыть',
        'expand_all' => 'Развернуть все',
        'collapse_all' => 'Свернуть все',
        'in_work' => 'в работе',
        'all_items' => 'Все поручения',
    ],
];
