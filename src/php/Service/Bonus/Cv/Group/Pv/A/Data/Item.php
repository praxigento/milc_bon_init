<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\A\Data;


/**
 * Data object to structure CV movements data.
 */
class Item
    extends \TeqFw\Lib\Data
{
    const CLIENT_ID = 'client_id';
    const CV_REG_ID = 'cv_reg_id';
    const IS_AUTOSHIP = 'is_autoship';
    const VOLUME = 'volume';

    public $client_id;
    public $cv_reg_id;
    public $is_autoship;
    public $volume;
}