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
    const CLIENT_ID = 'client_id';
    const DATE = 'date';
    const IS_AUTOSHIP = 'is_autoship';
    const REG_ID = 'reg_id';
    const SALE_ID = 'sale_id';
    const TYPE = 'type';
    const VOLUME = 'volume';

    public $back_id;
    public $client_id;
    public $date;
    public $is_autoship;
    public $reg_id;
    public $sale_id;
    public $type;
    public $volume;
}