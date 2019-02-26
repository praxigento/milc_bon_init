<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Data;


/**
 * Data object to structure CV movements data.
 */
class Movement
    extends \TeqFw\Lib\Data
{
    const BACK_ID = 'back_id';
    const REG_ID = 'reg_id';
    const SALE_ID = 'sale_id';

    public $back_id;
    public $reg_id;
    public $sale_id;
}