<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Uzbek, Cyrillic)
    |--------------------------------------------------------------------------
    |
    | Covers the rules used across the application's forms (authentication,
    | password reset, settings). The "password" block maps the framework's
    | Password rule so users see readable messages instead of raw
    | "validation.password.*" keys.
    |
    */

    'accepted' => ':attribute ни қабул қилишингиз шарт.',
    'active_url' => ':attribute яроқли URL эмас.',
    'after' => ':attribute :date дан кейинги сана бўлиши керак.',
    'after_or_equal' => ':attribute :date дан олдин бўлмаган сана бўлиши керак.',
    'alpha' => ':attribute фақат ҳарфлардан иборат бўлиши керак.',
    'alpha_dash' => ':attribute фақат ҳарф, рақам, тире ва пастки чизиқдан иборат бўлиши керак.',
    'alpha_num' => ':attribute фақат ҳарф ва рақамлардан иборат бўлиши керак.',
    'array' => ':attribute массив бўлиши керак.',
    'before' => ':attribute :date дан олдинги сана бўлиши керак.',
    'before_or_equal' => ':attribute :date дан кейин бўлмаган сана бўлиши керак.',
    'between' => [
        'numeric' => ':attribute :min ва :max орасида бўлиши керак.',
        'file' => ':attribute ҳажми :min ва :max Кбайт орасида бўлиши керак.',
        'string' => ':attribute :min ва :max белги орасида бўлиши керак.',
        'array' => ':attribute :min ва :max элемент орасида бўлиши керак.',
    ],
    'boolean' => ':attribute майдони true ёки false бўлиши керак.',
    'confirmed' => ':attribute тасдиғи мос келмади.',
    'current_password' => 'Парол нотўғри.',
    'date' => ':attribute яроқли сана эмас.',
    'date_equals' => ':attribute :date га тенг сана бўлиши керак.',
    'date_format' => ':attribute :format форматига мос келмайди.',
    'different' => ':attribute ва :other фарқли бўлиши керак.',
    'digits' => ':attribute :digits та рақамдан иборат бўлиши керак.',
    'digits_between' => ':attribute :min ва :max та рақам орасида бўлиши керак.',
    'email' => ':attribute яроқли email манзил бўлиши керак.',
    'ends_with' => ':attribute қуйидаги қийматлардан бири билан тугаши керак: :values.',
    'exists' => 'Танланган :attribute нотўғри.',
    'file' => ':attribute файл бўлиши керак.',
    'filled' => ':attribute майдонини тўлдириш шарт.',
    'image' => ':attribute расм бўлиши керак.',
    'in' => 'Танланган :attribute нотўғри.',
    'integer' => ':attribute бутун сон бўлиши керак.',
    'ip' => ':attribute яроқли IP-манзил бўлиши керак.',
    'max' => [
        'numeric' => ':attribute :max дан катта бўлмаслиги керак.',
        'file' => ':attribute ҳажми :max Кбайтдан ошмаслиги керак.',
        'string' => ':attribute :max белгидан ошмаслиги керак.',
        'array' => ':attribute :max элементдан ошмаслиги керак.',
    ],
    'mimes' => ':attribute қуйидаги турдаги файл бўлиши керак: :values.',
    'mimetypes' => ':attribute қуйидаги турдаги файл бўлиши керак: :values.',
    'min' => [
        'numeric' => ':attribute камида :min бўлиши керак.',
        'file' => ':attribute ҳажми камида :min Кбайт бўлиши керак.',
        'string' => ':attribute камида :min белгидан иборат бўлиши керак.',
        'array' => ':attribute камида :min элементдан иборат бўлиши керак.',
    ],
    'not_in' => 'Танланган :attribute нотўғри.',
    'numeric' => ':attribute сон бўлиши керак.',
    'password' => [
        'letters' => 'Парол камида битта ҳарфдан иборат бўлиши керак.',
        'mixed' => 'Парол катта ва кичик ҳарфлардан иборат бўлиши керак.',
        'numbers' => 'Парол камида битта рақамдан иборат бўлиши керак.',
        'symbols' => 'Парол камида битта махсус белгидан иборат бўлиши керак.',
        'uncompromised' => 'Бу парол маълумотлар сизиб чиқишида аниқланди. Илтимос, бошқа парол танланг.',
    ],
    'present' => ':attribute мавжуд бўлиши керак.',
    'regex' => ':attribute формати нотўғри.',
    'required' => ':attribute майдонини тўлдириш шарт.',
    'required_if' => ':other :value бўлганда :attribute майдонини тўлдириш шарт.',
    'required_with' => ':values кўрсатилганда :attribute майдонини тўлдириш шарт.',
    'required_without' => ':values кўрсатилмаганда :attribute майдонини тўлдириш шарт.',
    'same' => ':attribute ва :other мос келиши керак.',
    'size' => [
        'numeric' => ':attribute :size бўлиши керак.',
        'file' => ':attribute ҳажми :size Кбайт бўлиши керак.',
        'string' => ':attribute :size белгидан иборат бўлиши керак.',
        'array' => ':attribute :size элементдан иборат бўлиши керак.',
    ],
    'starts_with' => ':attribute қуйидаги қийматлардан бири билан бошланиши керак: :values.',
    'string' => ':attribute сатр бўлиши керак.',
    'unique' => 'Бундай :attribute аллақачон мавжуд.',
    'uploaded' => ':attribute ни юклаб бўлмади.',
    'url' => ':attribute нотўғри URL.',

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
        'name' => 'исм',
        'email' => 'email',
        'password' => 'парол',
        'password_confirmation' => 'парол тасдиғи',
        'phone' => 'телефон',
        'internal' => 'ички рақам',
        'avatar' => 'расм',
    ],

];
