<?php

return [
    // Task actions
    'add_task' => 'Вазифа қўшиш',
    'create_task' => 'Вазифа яратиш',
    'create_modal_title' => 'Янги вазифа',
    'edit_modal_title' => 'Вазифани ўзгартириш',
    'create_button' => 'Вазифа қўйиш',
    'creating' => 'Яратилмоқда...',
    'saving' => 'Сақланмоқда...',
    'search' => 'Қидириш...',

    // Form labels
    'category' => 'Категория',
    'name' => 'Номи',
    'name_placeholder' => 'Вазифа номини киритинг',
    'deadline' => 'Муддат',
    'responsible' => 'Масъул',
    'creator' => 'Топшириқ берувчи',
    'select' => 'Танланг',
    'files' => 'Файллар (Макс: 5 МБ)',
    'drag_drop' => 'Босинг ёки файлларни ташланг',
    'drag_drop_single' => 'Босинг ёки файлни ташланг',
    'file_formats' => 'PDF, DOC, XLS, JPG 5 МБ гача',
    'uploading' => 'Файллар юкланмоқда...',
    'uploading_single' => 'Файл юкланмоқда...',
    'remove_file' => 'Файлни ўчириш',
    'auto_determined' => 'Автоматик аниқланади',
    'max' => 'Макс:',

    // Table headers
    'employee' => 'Ходим',
    'status' => 'Ҳолат',
    'score' => 'Балл',
    'for_protocol' => 'Баённома учун',
    'created_at' => 'Яратилган сана',

    // View modal labels
    'start_label' => 'БОШЛАНИШИ',
    'deadline_label' => 'МУДДАТ',
    'completion_time' => 'БАЖАРИШ ВАҚТИ',
    'extension' => 'УЗАЙТИРИШ',
    'creator_label' => 'ТОПШИРИҚ БЕРУВЧИ',
    'state' => 'ҲОЛАТ',
    'category_label' => 'КАТЕГОРИЯ',
    'score_label' => 'БАЛЛ',
    'attached_files' => 'Бириктирилган файллар',
    'complete_task' => 'Вазифани якунлаш',
    'completed_task' => 'Якунланган вазифа',
    'participants_responses' => 'Иштирокчилар жавоблари',
    'responsible_users' => 'Масъуллар',
    'score_input' => 'Баҳо (Макс:',
    'history' => 'Тарих',
    'comments' => 'Изоҳлар',
    'comment_placeholder' => 'Изоҳ ёзинг...',
    'work_description_placeholder' => 'Бажарилган ишни тавсифланг...',

    // Status values
    'status_unread' => 'Ўқилмаган',
    'status_in_progress' => 'Бажарилмоқда',
    'status_waiting' => 'Тасдиқлаш кутилмоқда',
    'status_completed' => 'Бажарилди',
    'status_revision' => 'Қайта ишланмоқда',
    'overdue' => 'Муддати ўтган',

    // Actions
    'confirm' => 'Тасдиқлаш',
    'reject' => 'Рад этиш',
    'cancel_submission' => 'Юборишни бекор қилиш',
    'edit' => 'Ўзгартириш',
    'delete' => 'Ўчириш',
    'delete_task' => 'Жорий вазифани ўчириш',
    'stop_cycle' => 'Циклни тўхтатиш',
    'original_deadline' => 'Аслий муддат:',
    'delete_confirmation' => 'Вазифа ўчирилсинми?',
    'delete_current_confirmation' => 'Жорий вазифа ўчирилсинми?',
    'stop_cycle_confirmation' => 'Такрорланувчи вазифа тўхтатилсинми?',
    'deadline_extended_tooltip' => 'Муддат узайтирилди',
    'repeating_task_tooltip' => 'Такрорланувчи вазифа',

    // Filters
    'all_employees' => 'Барча ходимлар',
    'all_categories' => 'Барча категориялар',
    'clear_filters' => 'Фильтрларни тозалаш',
    'search_placeholder' => 'Вазифа қидириш...',

    // Section headers
    'weekly_tasks' => 'Ҳафталик вазифалар',
    'all_tasks' => 'Барча вазифалар',

    // Empty states
    'no_weekly_tasks' => 'Ҳафталик вазифалар йўқ',
    'no_unplanned_tasks' => 'Режадан ташқари вазифалар йўқ',
    'no_completed_tasks' => 'Якунланган вазифалар йўқ',
    'no_tasks_period' => 'Танланган давр учун якунланган вазифалар йўқ',

    // Task categories (used in PageController)
    'categories' => [
        'researchers' => 'Илмий ходимлар',
        'hr' => 'Кадрлар бўйича мутахассис',
        'accountant' => 'Бош бухгалтер',
        'lawyer' => 'Юристконсульт',
        'steward' => 'Хўжалик мудири',
        'ict' => 'АКТ мутахассиси',
        'default' => 'Категориялар',
    ],
];
