<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Downline\Tree;

/**
 * Delete customer from downline tree.
 */
interface Delete
{
    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Request $req
     * @return \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Response
     */
    public function exec($req);
}