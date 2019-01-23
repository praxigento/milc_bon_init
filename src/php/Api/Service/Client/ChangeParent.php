<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client;

/**
 * Change parent for existing node in downline tree.
 */
interface ChangeParent
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Response
     */
    public function exec($req);
}