<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Bonus\Pv;

/**
 * Register PV movement for the customer.
 */
interface Register
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Response
     */
    public function exec($req);
}