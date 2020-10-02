<?php

namespace SoftDelete\Model\Table;

use SoftDelete\ORM\Query;

interface WrapSoftDeleteQueryInterface
{
    public function wrapSoftDeleteQuery(): Query;
}
