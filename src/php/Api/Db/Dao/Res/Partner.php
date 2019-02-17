<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Dao\Res;


interface Partner
{
    const ENTITY_CLASS = \Praxigento\Milc\Bonus\Api\Db\Data\Res\Partner::class;
    const ENTITY_NAME = 'vnd_mod_entity';   // table name
    const ENTITY_PK = ['key1', 'key2']; // array with primary key attributes
}