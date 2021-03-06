<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Tree as ELogTree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Response as AResponse;

class ChangeParent
    implements \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent
{
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $manEntity;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add */
    private $srvEventLogAdd;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $manEntity,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat,
        \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add $srvEventLogAdd
    ) {
        $this->manEntity = $manEntity;
        $this->hlpFormat = $hlpFormat;
        $this->srvEventLogAdd = $srvEventLogAdd;
    }

    private function addToTreeLog($clientId, $parentId, $date)
    {
        $log = new ELogTree();
        $log->client_ref = $clientId;
        $log->parent_ref = $parentId;
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add\Request();
        $req->date = $date;
        $req->details = $log;
        $this->srvEventLogAdd->exec($req);
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $result = new AResponse();

        $clientId = $req->clientId;
        $parentIdNew = $req->parentIdNew;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /**
         * Get customer from registry.
         */
        /** @var ETree $found */
        $found = $this->manEntity->find(ETree::class, $clientId);
        if ($found) {
            $parentIdOld = $found->parent_ref;
            $result->parentIdOld = $parentIdOld;
            $isDiffer = $this->validateNewParentIsDiffer($parentIdOld, $parentIdNew);
            $isNotInDwnl = $this->validateNewParentIsNotInDownline($clientId, $parentIdOld, $parentIdNew);
            if ($isDiffer && $isNotInDwnl) {
                /* save data into downline tree trace */
                $this->addToTreeLog($clientId, $parentIdNew, $date);
                /* update current downline tree */
                $found->parent_ref = $parentIdNew;
                /* TODO: change depths & paths for customer itself & for it's downline */
                $this->manEntity->persist($found);
                $this->manEntity->flush();
            }
        }

        return $result;
    }

    private function validateNewParentIsDiffer($parentIdOld, $parentIdNew)
    {
        $result = $parentIdOld != $parentIdNew;
        return $result;
    }

    private function validateNewParentIsNotInDownline($clientId, $parentIdOld, $parentIdNew)
    {
        $result = false;
        $nodeId = $parentIdNew;
        $log = ":$nodeId";
        do {
            /** @var ETree $entity */
            $entity = $this->manEntity->find(ETree::class, $nodeId);
            $this->manEntity->refresh($entity);
            $nodeFatherId = $entity->parent_ref;
            if ($nodeFatherId == $clientId)
                break;
            if ($nodeFatherId == $nodeId) {
                /* this is root node*/
                $result = true;
                break;
            }
            $nodeId = $nodeFatherId;
            $log .= ":$nodeId";
        } while (true);
        return $result;
    }
}