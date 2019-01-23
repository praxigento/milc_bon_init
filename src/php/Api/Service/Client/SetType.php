<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client;

/**
 * Set type for client (Distributor or Customer).
 */
interface SetType
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\SetType\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\SetType\Response
     */
    public function exec($req);
}