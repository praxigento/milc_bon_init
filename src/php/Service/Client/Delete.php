<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry\Log\Delete as EDeleteLog;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree\Log as ETreeLog;
use Praxigento\Milc\Bonus\Api\Service\Client\Delete\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Delete\Response as AResponse;

class Delete
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Delete
{
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $manEntity;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $manEntity,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat
    ) {
        $this->manEntity = $manEntity;
        $this->hlpFormat = $hlpFormat;
    }

    private function addToDeleteLog($customerId, $date)
    {
        $log = new EDeleteLog();
        $log->client_ref = $customerId;
        $log->date = $date;
        $log->is_deleted = true;
        $this->manEntity->persist($log);
    }

    private function addToTreeLog($customerId, $date)
    {
        $log = new ETreeLog();
        $log->client_ref = $customerId;
        $log->parent_ref = null;
        $log->date = $date;
        $this->manEntity->persist($log);
        $this->manEntity->flush();
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $customerId = $req->customerId;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $customerId);
        if ($found) {
            /* find data in downline tree */
            /** @var ETree $foundInTree */
            $foundInTree = $this->manEntity->find(ETree::class, $customerId);
            if ($foundInTree) {
                /* UPDATE query does not change data in Entity Manager */
                $this->manEntity->refresh($foundInTree);
                /* update parents for frontline customers in tree */
                $parentId = $foundInTree->parent_ref;
                $this->updateDescendants($customerId, $parentId);
                /* remove tree entry */
                $removed = $this->manEntity->remove($foundInTree);
                $this->manEntity->flush();
                /* save data into downline tree trace */
                $this->addToTreeLog($customerId, $date);
            }
            /* update customer registry */
            $found->is_deleted = true;
            $this->manEntity->persist($found);
            /* save event in delete log */
            $this->addToDeleteLog($customerId, $date);

            $this->manEntity->flush();
        }

        $result = new AResponse();
        return $result;
    }

    private function updateDescendants($customerId, $parentId)
    {
        $qb = $this->manEntity->createQueryBuilder();
        $as = 'tree';
        $update = $qb->update(ETree::class, $as);
        $update->set("$as." . ETree::PARENT_REF, ':parentId');
        $update->where("$as." . ETree::PARENT_REF . '=:customerId');
        $query = $update->getQuery();
        $params = [
            'customerId' => $customerId,
            'parentId' => $parentId
        ];
        $updated = $query->execute($params);
        $this->manEntity->flush();
    }
}