<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Z\Data\Rule;

/**
 * Service level wrapper for DB data.
 */
class Group
    extends \TeqFw\Lib\Data
{
    const ID = 'id';
    const LOGIC = 'logic';
    const RULES = 'rules';

    /** @var int */
    public $id;
    /** @var string */
    public $logic;
    /** @var array */
    public $rules;
}