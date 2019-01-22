<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Downline\Tree;

/**
 * Add new customer to downline tree.
 */
interface Add
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Response
     */
    public function exec($req);
}