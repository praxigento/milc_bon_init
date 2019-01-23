<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client;

/**
 * Add new customer to natural downline tree.
 */
interface Add
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Add\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Add\Response
     */
    public function exec($req);
}