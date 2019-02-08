<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper;

/**
 * Utilities to manipulate tree data.
 */
interface Tree
{
    /**
     * @param array $tree
     * @param string|null $keyNode
     * @param string|null $keyParent
     * @return \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Full[]
     */
    public function expandMinimal($tree, $keyNode = null, $keyParent = null);

    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Full[] $tree
     * @return array
     */
    public function mapByDepthAsc($tree);

    /**
     * @param \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Full[] $tree
     * @return array
     */
    public function mapByDepthDesc($tree);
}