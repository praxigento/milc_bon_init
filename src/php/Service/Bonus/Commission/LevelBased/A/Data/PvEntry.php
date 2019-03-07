<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\A\Data;


/**
 * Data object to structure CV movements data related to tree PV.
 */
class PvEntry
    extends \TeqFw\Lib\Data
{
    const APV = 'apv';
    const CLIENT_ID = 'client_id';
    const PV = 'pv';

    public $apv;
    public $client_id;
    public $pv;
}