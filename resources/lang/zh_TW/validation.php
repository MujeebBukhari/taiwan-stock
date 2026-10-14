<?php

return [
    'required' => ':attribute 欄位為必填。',
    'numeric' => ':attribute 必須為數值。',
    'min' => [
        'numeric' => ':attribute 至少須為 :min。',
        'string' => ':attribute 長度至少須為 :min 個字元。',
    ],
    'max' => [
        'numeric' => ':attribute 不得大於 :max。',
        'string' => ':attribute 長度不得超過 :max 個字元。',
    ],
    'unique' => ':attribute 已經被使用。',
    'email' => ':attribute 必須是有效的電子郵件地址。',
    'confirmed' => ':attribute 確認不一致。',
];
