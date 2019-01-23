<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Downline;

/**
 * Activate previously deactivated customer in downline tree (Customer => Distributor).
 */
interface Activate
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Activate\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Activate\Response
     */
    public function exec($req);
}