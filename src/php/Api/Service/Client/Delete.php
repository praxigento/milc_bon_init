<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client;

/**
 * Delete customer from natural downline tree.
 */
interface Delete
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\Delete\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\Delete\Response
     */
    public function exec($req);
}