<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Downline;

/**
 * Restore previously deleted customer in downline tree.
 */
interface Restore
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore\Response
     */
    public function exec($req);
}