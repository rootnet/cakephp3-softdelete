<?php

namespace SoftDelete\Test\Fixture;

use Cake\ORM\Table;
use Cake\TestSuite\Fixture\TestFixture;

use SoftDelete\Model\Table\SoftDeleteTrait;

class PostsTagsTable extends Table
{
    use SoftDeleteTrait;

    public function initialize(array $config): void
    {
        $this->belongsTo('Tags');
        $this->belongsTo('Posts');
        $this->setPrimaryKey('id');
    }
}


class PostsTagsFixture extends TestFixture
{
    public $records = [
        [
            'id' => 1,
            'post_id' => 1,
            'tag_id' => 1,
            'deleted' => null,
        ],
        [
            'id' => 2,
            'post_id' => 1,
            'tag_id' => 2,
            'deleted' => '2015-05-18 15:04:00',
        ],
        [
            'id' => 3,
            'post_id' => 2,
            'tag_id' => 3,
            'deleted' => '2018-11-30 15:04:00',
        ],
    ];
}


