<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Russian)
    |--------------------------------------------------------------------------
    |
    | Covers the rules used across the application's forms (authentication,
    | password reset, settings). The "password" block maps the framework's
    | Password rule so users see readable messages instead of raw
    | "validation.password.*" keys.
    |
    */

    'accepted' => 'Вы должны принять :attribute.',
    'active_url' => 'Поле :attribute содержит недействительный URL.',
    'after' => 'Поле :attribute должно содержать дату после :date.',
    'after_or_equal' => 'Поле :attribute должно содержать дату не ранее :date.',
    'alpha' => 'Поле :attribute может содержать только буквы.',
    'alpha_dash' => 'Поле :attribute может содержать только буквы, цифры, дефис и нижнее подчёркивание.',
    'alpha_num' => 'Поле :attribute может содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'Поле :attribute должно содержать дату до :date.',
    'before_or_equal' => 'Поле :attribute должно содержать дату не позднее :date.',
    'between' => [
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'file' => 'Размер файла в поле :attribute должен быть от :min до :max Кбайт.',
        'string' => 'Поле :attribute должно содержать от :min до :max символов.',
        'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
    ],
    'boolean' => 'Поле :attribute должно иметь значение логического типа.',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute не является датой.',
    'date_equals' => 'Поле :attribute должно содержать дату, равную :date.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'different' => 'Поля :attribute и :other должны различаться.',
    'digits' => 'Поле :attribute должно содержать :digits цифр(ы).',
    'digits_between' => 'Поле :attribute должно содержать от :min до :max цифр.',
    'email' => 'Поле :attribute должно содержать корректный email-адрес.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values.',
    'exists' => 'Выбранное значение для :attribute некорректно.',
    'file' => 'Поле :attribute должно содержать файл.',
    'filled' => 'Поле :attribute обязательно для заполнения.',
    'image' => 'Поле :attribute должно быть изображением.',
    'in' => 'Выбранное значение для :attribute некорректно.',
    'integer' => 'Поле :attribute должно содержать целое число.',
    'ip' => 'Поле :attribute должно содержать корректный IP-адрес.',
    'max' => [
        'numeric' => 'Поле :attribute не может быть больше :max.',
        'file' => 'Размер файла в поле :attribute не может быть больше :max Кбайт.',
        'string' => 'Поле :attribute не может содержать более :max символов.',
        'array' => 'Поле :attribute не может содержать более :max элементов.',
    ],
    'mimes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'mimetypes' => 'Поле :attribute должно содержать файл одного из типов: :values.',
    'min' => [
        'numeric' => 'Поле :attribute должно быть не меньше :min.',
        'file' => 'Размер файла в поле :attribute должен быть не меньше :min Кбайт.',
        'string' => 'Поле :attribute должно содержать не менее :min символов.',
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
    ],
    'not_in' => 'Выбранное значение для :attribute некорректно.',
    'numeric' => 'Поле :attribute должно содержать число.',
    'password' => [
        'letters' => 'Пароль должен содержать хотя бы одну букву.',
        'mixed' => 'Пароль должен содержать заглавные и строчные буквы.',
        'numbers' => 'Пароль должен содержать хотя бы одну цифру.',
        'symbols' => 'Пароль должен содержать хотя бы один спецсимвол.',
        'uncompromised' => 'Этот пароль найден в утечке данных. Пожалуйста, выберите другой.',
    ],
    'present' => 'Поле :attribute должно присутствовать.',
    'regex' => 'Поле :attribute имеет некорректный формат.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_if' => 'Поле :attribute обязательно для заполнения, когда :other равно :value.',
    'required_with' => 'Поле :attribute обязательно для заполнения, когда указано :values.',
    'required_without' => 'Поле :attribute обязательно для заполнения, когда не указано :values.',
    'same' => 'Поля :attribute и :other должны совпадать.',
    'size' => [
        'numeric' => 'Поле :attribute должно быть равно :size.',
        'file' => 'Размер файла в поле :attribute должен быть равен :size Кбайт.',
        'string' => 'Поле :attribute должно содержать :size символов.',
        'array' => 'Поле :attribute должно содержать :size элементов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с одного из следующих значений: :values.',
    'string' => 'Поле :attribute должно быть строкой.',
    'unique' => 'Такое значение поля :attribute уже существует.',
    'uploaded' => 'Не удалось загрузить файл :attribute.',
    'url' => 'Поле :attribute содержит некорректный URL.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'имя',
        'email' => 'email',
        'password' => 'пароль',
        'password_confirmation' => 'подтверждение пароля',
        'phone' => 'телефон',
        'internal' => 'внутренний номер',
        'avatar' => 'фото',
    ],

];
