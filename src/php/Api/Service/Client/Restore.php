<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client;

/**
 * Restore previously deleted customer in natural downline tree.
 */
interface Restore
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Restore\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Restore\Response
     */
    public function exec($req);
}