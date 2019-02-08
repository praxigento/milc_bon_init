<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Full as DTreeEntry;

class Tree
    implements \Praxigento\Milc\Bonus\Api\Helper\Tree
{

    /**
     * @param DTreeEntry[] $result
     * @param array $roots tree with nodes IDs
     * @param int|null $parentId
     */
    private function addDepth(&$result, $roots, $parentId = null)
    {
        foreach ($roots as $nodeId => $children) {
            $item = new DTreeEntry();
            $item->client_id = $nodeId;
            if (is_null($parentId)) {
                /* this is root node */
                $item->parent_id = $nodeId;
                $item->depth = Cfg::TREE_DEPTH_INIT;
                $item->path = Cfg::TREE_PS;
            } else {
                /** @var DTreeEntry $parentData */
                $parentData = $result[$parentId];
                $item->parent_id = $parentId;
                $item->depth = $parentData->depth + 1;
                $item->path = $parentData->path . $parentId . Cfg::TREE_PS;
            }
            $result[$nodeId] = $item;
            if (sizeof($children) > 0) {
                $this->addDepth($result, $children, $nodeId);
            }
        }
    }

    public function expandMinimal($tree, $keyNode = null, $keyParent = null)
    {
        /**
         * Convert source tree into "[node]=>parent" form.
         */
        $nodes = $this->extractNodes($tree, $keyNode, $keyParent);
        /**
         * Convert flat array into tree.
         */
        $registry = [];
        $roots = [];
        foreach ($nodes as $node => $parent) {
            /* map customers into tree */
            if (!isset($registry[$node])) {
                $registry[$node] = [];
            }
            if ($node != $parent) {
                $registry[$parent][$node] =& $registry[$node];
            } else {
                /* register root node */
                $roots[$node] =& $registry[$node];
            }
        }

        /* populate tree with depth/path/... and compose array to insert into DB  */
        $result = [];
        $this->addDepth($result, $roots);

        return $result;
    }

    private function extractNodes($tree, $keyNode = null, $keyParent = null)
    {
        $result = [];
        foreach ($tree as $item) {
            if (is_array($item)) {
                if (
                    isset($item[$keyNode]) &&
                    isset($item[$keyParent])
                ) {
                    $node = $item[$keyNode];
                    $parent = $item[$keyParent];
                    $result[$node] = $parent;
                }
            } elseif (is_object($item)) {
                if (
                    isset($item->$keyNode) &&
                    isset($item->$keyParent)
                ) {
                    $node = $item->$keyNode;
                    $parent = $item->$keyParent;
                    $result[$node] = $parent;
                }
            }
        }
        return $result;
    }

    public function mapByDepthAsc($tree)
    {
        $result = $this->mapByDepthDesc($tree);
        $result = array_reverse($result);
        return $result;
    }

    public function mapByDepthDesc($tree)
    {
        $result = [];
        foreach ($tree as $one) {
            /* this should be data object */
            $customerId = $one->client_id;
            $depth = (int)$one->depth;
            if (!isset($result[$depth])) {
                $result[$depth] = [];
            }
            $result[$depth][] = $customerId;
        }
        /* sort by depth desc */
        krsort($result);
        return $result;
    }
}