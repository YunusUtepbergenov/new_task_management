<?php

return [
    // Task actions
    'add_task' => 'Добавить Задачу',
    'create_task' => 'Создать Задачу',
    'create_modal_title' => 'Новая задача',
    'edit_modal_title' => 'Изменить задачу',
    'create_button' => 'Поставить Задачу',
    'creating' => 'Создание...',
    'saving' => 'Сохранение...',
    'search' => 'Поиск...',

    // Form labels
    'category' => 'Категория',
    'name' => 'Название',
    'name_placeholder' => 'Введите название задачи',
    'deadline' => 'Срок',
    'responsible' => 'Ответственный',
    'creator' => 'Постановщик',
    'select' => 'Выберите',
    'files' => 'Файлы (Макс: 5 МБ)',
    'drag_drop' => 'Нажмите или перетащите файлы',
    'drag_drop_single' => 'Нажмите или перетащите файл',
    'file_formats' => 'PDF, DOC, XLS, JPG до 5 МБ',
    'uploading' => 'Загрузка файлов...',
    'uploading_single' => 'Загрузка файла...',
    'remove_file' => 'Удалить файл',
    'auto_determined' => 'Определяется автоматически',
    'max' => 'Макс:',

    // Table headers
    'employee' => 'Сотрудник',
    'status' => 'Статус',
    'score' => 'Балл',
    'for_protocol' => 'Для протокола',
    'created_at' => 'Дата создания',

    // View modal labels
    'start_label' => 'НАЧАЛО',
    'deadline_label' => 'СРОК',
    'completion_time' => 'ВРЕМЯ ВЫПОЛНЕНИЯ',
    'extension' => 'ПРОДЛЕНИЕ',
    'creator_label' => 'ПОСТАНОВЩИК',
    'state' => 'СОСТОЯНИЕ',
    'category_label' => 'КАТЕГОРИЯ',
    'score_label' => 'БАЛЛ',
    'attached_files' => 'Прикрепленные файлы',
    'complete_task' => 'Завершить задачу',
    'completed_task' => 'Завершенная задача',
    'participants_responses' => 'Ответы участников',
    'responsible_users' => 'Ответственные',
    'score_input' => 'Оценка (Макс:',
    'history' => 'История',
    'comments' => 'Комментарии',
    'comment_placeholder' => 'Напишите комментарий...',
    'work_description_placeholder' => 'Опишите выполненную работу...',

    // Status values
    'status_unread' => 'Не прочитано',
    'status_in_progress' => 'Выполняется',
    'status_waiting' => 'Ждет подтверждения',
    'status_completed' => 'Выполнено',
    'status_revision' => 'Дорабатывается',
    'overdue' => 'Просроченный',

    // Actions
    'confirm' => 'Подтвердить',
    'reject' => 'Отклонить',
    'cancel_submission' => 'Отменить отправку',
    'edit' => 'Изменить',
    'delete' => 'Удалить',
    'delete_task' => 'Удалить текущую задачу',
    'stop_cycle' => 'Остановить цикл',
    'original_deadline' => 'Оригинальный срок:',
    'delete_confirmation' => 'Удалить задачу?',
    'delete_current_confirmation' => 'Удалить текущую задачу?',
    'stop_cycle_confirmation' => 'Остановить повторяющуюся задачу?',
    'deadline_extended_tooltip' => 'Срок продлен',
    'repeating_task_tooltip' => 'Повторяющаяся задача',

    // Filters
    'all_employees' => 'Все сотрудники',
    'all_categories' => 'Все категории',
    'clear_filters' => 'Сбросить фильтры',
    'search_placeholder' => 'Поиск задачи...',

    // Section headers
    'weekly_tasks' => 'Задачи на неделю',
    'all_tasks' => 'Все задачи',

    // Empty states
    'no_weekly_tasks' => 'Нет еженедельных задач',
    'no_unplanned_tasks' => 'Нет внеплановых задач',
    'no_completed_tasks' => 'Нет завершенных задач',
    'no_tasks_period' => 'Нет завершенных задач за выбранный период',

    // Task categories (used in PageController)
    'categories' => [
        'researchers' => 'Научные сотрудники',
        'hr' => 'Специалиста по работе с персоналом',
        'accountant' => 'Главный бухгалтер',
        'lawyer' => 'Юристконсульт',
        'steward' => 'Заведующий хозяйством',
        'ict' => 'Специалист ИКТ',
        'default' => 'Категории',
    ],
];
