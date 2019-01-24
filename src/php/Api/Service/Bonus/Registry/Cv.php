<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Bonus\Registry;

/**
 * Register PV movement for the customer.
 */
interface Cv
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Response
     */
    public function exec($req);
}