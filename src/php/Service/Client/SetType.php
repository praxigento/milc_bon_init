<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Tree as ELogTree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Type as ELogType;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Service\Client\SetType\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\SetType\Response as AResponse;

class SetType
    implements \Praxigento\Milc\Bonus\Api\Service\Client\SetType
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

    private function addToTypeLog($clientId, $date, $isCustomer)
    {
        $log = new ELogType();
        $log->client_ref = $clientId;
        $log->is_customer = $isCustomer;
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add\Request();
        $req->date = $date;
        $req->details = $log;
        $this->srvEventLogAdd->exec($req);
    }

    private function changeParentForFrontLine($clientId, $parentId)
    {
        $qb = $this->manEntity->createQueryBuilder();
        $as = 'tree';
        $update = $qb->update(ETree::class, $as);
        $update->set("$as." . ETree::PARENT_REF, ':parentId');
        $update->where("$as." . ETree::PARENT_REF . '=:clientId');
        $query = $update->getQuery();
        $params = [
            'clientId' => $clientId,
            'parentId' => $parentId
        ];
        $updated = $query->execute($params);
        $this->manEntity->flush();
    }

    private function changeParents($clientId, $date)
    {
        /* find client data in downline tree */
        /** @var ETree $found */
        $found = $this->manEntity->find(ETree::class, $clientId);
        if ($found) {
            /* UPDATE query does not change data in Entity Manager */
            $this->manEntity->refresh($found);
            /* update parents for front line customers in the tree */
            $parentId = $found->parent_ref;
            $this->updateDescendants($clientId, $parentId, $date);
        }
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $clientId = $req->clientId;
        $isCust = $req->isCustomer;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $clientId);
        if ($found) {
            if ($found->is_customer != $isCust) {
                /* update data in registry */
                $found->is_customer = $isCust;
                $this->manEntity->persist($found);
                /* save event into type log */
                $this->addToTypeLog($clientId, $date, $isCust);
                /* update parents for front line customers in tree */
                if ($isCust) {
                    $this->changeParents($clientId, $date);
                }
            }
        }

        $result = new AResponse();
        return $result;
    }

    private function selectByParentId($id)
    {
        $result = [];
        $as = 'tree';
        $pParentId = 'parentId';
        $qb = $this->manEntity->createQueryBuilder();
        $qb->select($as);
        $qb->from(ETree::class, $as);
        $qb->andWhere("$as." . ETree::PARENT_REF . "=:$pParentId");
        $qb->setParameters([$pParentId => $id]);
        $query = $qb->getQuery();
        $all = $query->getArrayResult();
        foreach ($all as $one) {
            $entity = new ETree($one);
            $result[] = $entity;
        }
        return $result;
    }

    private function updateDescendants($clientId, $parentId, $date)
    {
        $frontLine = $this->selectByParentId($clientId);
        if (count($frontLine)) {
            $this->changeParentForFrontLine($clientId, $parentId);
            foreach ($frontLine as $one) {
                $childId = $one->client_ref;
                $this->addToTreeLog($childId, $parentId, $date);
            }
        }
    }
}