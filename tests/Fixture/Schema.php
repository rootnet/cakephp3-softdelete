<?php

return [
    'users' => [
        'table' => 'users',
        'columns' => [
            'id' => ['type' => 'integer'],
            'posts_count' => ['type' => 'integer', 'default' => '0', 'null' => false],
            'deleted' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'posts' => [
        'table' => 'posts',
        'columns' => [
            'id' => ['type' => 'integer'],
            'user_id' => ['type' => 'integer', 'default' => '0', 'null' => false],
            'deleted' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'tags' => [
        'table' => 'tags',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
            'deleted_date' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'posts_tags' => [
        'table' => 'posts_tags',
        'columns' => [
            'id' => ['type' => 'integer'],
            'post_id' => ['type' => 'integer'],
            'tag_id' => ['type' => 'integer'],
            'deleted' => ['type' => 'datetime', 'default' => null, 'null' => true],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ]
    ]
];
