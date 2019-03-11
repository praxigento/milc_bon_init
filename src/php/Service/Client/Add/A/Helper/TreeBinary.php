<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Add\A\Helper;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree\Bin as ETreeBin;
use Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Data\Entry as DEntry;
use Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Db\Query\GetChildren as QGetChildren;

class TreeBinary
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Db\Query\GetChildren */
    private $qGetChildren;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary\A\Db\Query\GetChildren $qGetChildren
    ) {
        $this->dao = $dao;
        $this->qGetChildren = $qGetChildren;
    }

    /**
     * @param int $clientId
     * @param int $parentId
     * @param bool $placeToLeft
     * @return int
     * @throws \Exception
     */
    public function add($clientId, $parentId, $placeToLeft)
    {
        if ($clientId == $parentId) {
            /* this is a root node */
            $result = $this->createNode($clientId, $parentId);
        } else {
            /* get enroller's children */
            $children = $this->getChildren($parentId);
            $count = count($children);
            if ($count == 0) {
                /* this is the first child for this client */
                $result = $this->createNode($clientId, $parentId);
                $this->createNodePlacement($clientId, $placeToLeft);
            } elseif ($count == 1) {
                /* there is one child only */
                /** @var DEntry $child */
                $child = reset($children);
                $childId = $child->client_id;
                $childIsLeft = (bool)$child->is_left;
                if ($childIsLeft != $placeToLeft) {
                    /* place new client under $parentId */
                    $result = $this->createNode($clientId, $parentId);
                    $this->createNodePlacement($clientId, $placeToLeft);
                } else {
                    /* this place is occupied, dive one level down */
                    $result = $this->add($clientId, $childId, $placeToLeft);
                }
            } elseif ($count == 2) {
                /* there are 2 children for the parent, we need to dive down into correct leg (left or right)*/
                foreach ($children as $child) {
                    $childId = $child->client_id;
                    $childIsLeft = (bool)$child->is_left;
                    if ($childIsLeft == $placeToLeft) {
                        $result = $this->add($clientId, $childId, $placeToLeft);
                    }
                }
            } else {
                throw new \Exception("Binary tree must contain 2 children only. Check children for client #$clientId.");
            }
        }
        return $result;
    }

    /**
     * @param int $clientId
     * @param int $parentId
     * @return int
     */
    private function createNode($clientId, $parentId)
    {
        $tree = new ETree();
        $tree->client_ref = $clientId;
        $tree->parent_ref = $parentId;
        /* TODO: init depths & paths for customer */
        $tree->depth = 1;
        $tree->path = '::';
        $this->dao->create($tree);
        return $parentId;
    }

    private function createNodePlacement($clientId, $placeToLeft)
    {
        $entity = new ETreeBin();
        $entity->client_ref = $clientId;
        $entity->is_on_left = $placeToLeft;
        $this->dao->create($entity);
    }

    /**
     * Load children for $customerId.
     *
     * @param int $clientId
     * @return DEntry[]
     */
    private function getChildren($clientId)
    {
        $query = $this->qGetChildren->build();
        $bind = [QGetChildren::BND_CLIENT_ID => $clientId];
        $query->setParameters($bind);
        $stmt = $query->execute();
        $result = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, QGetChildren::RESULT_CLASS);
        return $result;
    }

    private function getNode($clientId)
    {
        $result = $this->dao->getOne(ETree::class, $clientId);
        return $result;
    }
}