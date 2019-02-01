<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Tree;

/**
 * Get (sub)tree for given root id on given date.
 */
interface Get
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Response
     */
    public function exec($req);
}