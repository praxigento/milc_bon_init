<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\A\Data;


/**
 * Data object to structure CV movements data.
 */
class Item
    extends \TeqFw\Lib\Data
{
    const CLIENT_ID = 'client_id';
    const IS_AUTOSHIP = 'is_autoship';
    const VOLUME = 'volume';

    public $client_id;
    public $is_autoship;
    public $volume;
}