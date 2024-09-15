<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Полето :attribute мора да биде прифатено.',
    'active_url'           => 'Полето :attribute не е валиден URL.',
    'after'                => 'Полето :attribute мора да биде датум после :date.',
    'after_or_equal'       => 'Полето :attribute мора да е датум еднаков или после :date.',
    'alpha'                => 'Полето :attribute може да содржи само букви.',
    'alpha_dash'           => 'Полето :attribute може да содржи само букви, цифри, долна црта и тире.',
    'alpha_num'            => 'Полето :attribute може да содржи само букви и цифри.',
    'array'                => 'Полето :attribute мора да биде низа.',
    'before'               => 'Полето :attribute мора да биде датум пред :date.',
    'before_or_equal'      => 'Полето :attribute мора да е датум еднаков или пред :date.',
    'between'              => [
        'numeric' => 'Полето :attribute мора да биде помеѓу :min и :max.',
        'file'    => 'Полето :attribute мора да биде помеѓу :min и :max килобајти.',
        'string'  => 'Полето :attribute мора да биде помеѓу :min и :max карактери.',
        'array'   => 'Полето :attribute мора да има помеѓу :min - :max карактери.',
    ],
    'boolean'              => 'Полето :attribute мора да е true или false.',
    'confirmed'            => 'Полето :attribute не е потврдено.',
    'date'                 => 'Полето :attribute не е валиден датум.',
    'date_equals'          => 'Полето :attribute мора да е датум еднаков со :date.',
    'date_format'          => 'Полето :attribute не е во формат :format.',
    'different'            => 'Полињата :attribute и :other треба да се различни.',
    'digits'               => 'Полето :attribute треба да има :digits цифри.',
    'digits_between'       => 'Полето :attribute треба да има помеѓу :min и :max цифри.',
    'dimensions'           => 'Полето :attribute има неправилни димензии на слика.',
    'distinct'             => 'Полето :attribute има дупликат вредност.',
    'email'                => 'Полето :attribute не е во валиден формат.',
    'exists'               => 'Избранато поле :attribute веќе постои.',
    'file'                 => 'Полето :attribute мора да е датотека.',
    'filled'               => 'Полето :attribute е задолжително.',
    'gt'                   => [
        'numeric' => 'Полето :attribute мора да биде поголемо од :value.',
        'file'    => 'Полето :attribute мора да биде поголемо од :value килобајти.',
        'string'  => 'Полето :attribute мора да биде поголемо од :value карактери.',
        'array'   => 'Полето :attribute мора да има повеќе од :value ставки.',
    ],
    'gte'                  => [
        'numeric' => 'Полето :attribute мора да биде поголемо или еднакво на :value.',
        'file'    => 'Полето :attribute мора да биде поголемо или еднакво на :value килобајти.',
        'string'  => 'Полето :attribute мора да биде поголемо или еднакво на :value карактери.',
        'array'   => 'Полето :attribute мора да има :value ставки или повеќе.',
    ],
    'image'                => 'Полето :attribute мора да биде слика.',
    'in'                   => 'Избраното поле :attribute е невалидно.',
    'in_array'             => 'Полето :attribute не постои во :other.',
    'integer'              => 'Полето :attribute мора да биде цел број.',
    'ip'                   => 'Полето :attribute мора да биде IP адреса.',
    'ipv4'                 => 'Полето :attribute мора да биде валидна IPv4 адреса.',
    'ipv6'                 => 'Полето :attribute мора да биде валидна IPv6 адреса.',
    'json'                 => 'Полето :attribute мора да биде валидна JSON низа.',
    'lt'                   => [
        'numeric' => 'Полето :attribute мора да биде помало од :value.',
        'file'    => 'Полето :attribute мора да биде помало од :value килобајти.',
        'string'  => 'Полето :attribute мора да биде помало од :value карактери.',
        'array'   => 'Полето :attribute мора да има помалку од :value ставки.',
    ],
    'lte'                  => [
        'numeric' => 'Полето :attribute мора да биде помало или еднакво на :value.',
        'file'    => 'Полето :attribute мора да биде помало или еднакво на :value килобајти.',
        'string'  => 'Полето :attribute мора да биде помало или еднакво на :value карактери.',
        'array'   => 'Полето :attribute не може да има повеќе од :value ставки.',
    ],
    'max'                  => [
        'numeric' => 'Полето :attribute мора да биде помало од :max.',
        'file'    => 'Полето :attribute мора да биде помало од :max килобајти.',
        'string'  => 'Полето :attribute мора да има помалку од :max карактери.',
        'array'   => 'Полето :attribute не може да има повеќе од :max карактери.',
    ],
    'mimes'                => 'Полето :attribute мора да биде фајл од типот: :values.',
    'mimetypes'            => 'Полето :attribute мора да биде фајл од типот: :values.',
    'min'                  => [
        'numeric' => 'Полето :attribute мора да биде минимум :min.',
        'file'    => 'Полето :attribute мора да биде минимум :min килобајти.',
        'string'  => 'Полето :attribute мора да има минимум :min карактери.',
        'array'   => 'Полето :attribute мора да има минимум :min карактери.',
    ],
    'not_in'               => 'Избраното поле :attribute е невалидно.',
    'not_regex'            => 'Форматот на полето :attribute е невалиден.',
    'numeric'              => 'Полето :attribute мора да биде број.',
    'present'              => 'Полето :attribute мора да биде присутно.',
    'regex'                => 'Полето :attribute е во невалиден формат.',
    'required'             => 'Полето :attribute е задолжително.',
    'required_if'          => 'Полето :attribute е задолжително, кога :other е :value.',
    'required_unless'      => 'Полето :attribute е задолжително, освен ако :other не е во :values.',
    'required_with'        => 'Полето :attribute е задолжително, кога е внесено :values.',
    'required_with_all'    => 'Полето :attribute е задолжително, кога сите :values се присутни.',
    'required_without'     => 'Полето :attribute е задолжително, кога не е внесено :values.',
    'required_without_all' => 'Полето :attribute е задолжително, кога ниту едно од :values не е присутно.',
    'same'                 => 'Полињата :attribute и :other треба да совпаѓаат.',
    'size'                 => [
        'numeric' => 'Полето :attribute мора да биде :size.',
        'file'    => 'Полето :attribute мора да биде :size килобајти.',
        'string'  => 'Полето :attribute мора да има :size карактери.',
        'array'   => 'Полето :attribute мора да има :size ставки.',
    ],
    'starts_with'          => 'Полето :attribute мора да започнува со една од следните вредности: :values.',
    'string'               => 'Полето :attribute мора да биде низа.',
    'timezone'             => 'Полето :attribute мора да биде валидна временска зона.',
    'unique'               => 'Полето :attribute веќе постои.',
    'uploaded'             => 'Полето :attribute не успеа да се прикачи.',
    'url'                  => 'Полето :attribute не е во валиден формат.',
    'uuid'                 => 'Полето :attribute мора да биде валиден UUID.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
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
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
