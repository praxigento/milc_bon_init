<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Downline;

/**
 * Deactivate customer in downline tree (Distributor => Customer).
 */
interface Deactivate
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Deactivate\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Deactivate\Response
     */
    public function exec($req);
}