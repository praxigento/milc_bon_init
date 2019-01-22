<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Downline\Tree;

use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Customer\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree\Trace as ETreeTrace;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete\Response as AResponse;

class Delete
    implements \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Delete
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
            /** @var ETree $foundTree */
            $foundTree = $this->manEntity->find(ETree::class, $customerId);
            if ($foundTree) {
                /* UPDATE query does not change data in Entity Manager */
                $this->manEntity->refresh($foundTree);
                /* update parents for frontline customers in tree */
                $parentId = $foundTree->parent_ref;
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
                /* remove tree entry */
                $removed = $this->manEntity->remove($foundTree);
                $this->manEntity->flush();
                /* save data into downline tree trace */
                $trace = new ETreeTrace();
                $trace->member_ref = $customerId;
                $trace->parent_ref = null;
                $trace->date = $date;
                $this->manEntity->persist($trace);
                $this->manEntity->flush();
            }
            /* update customer registry */
            $found->is_deleted = true;
            $this->manEntity->persist($found);
            $this->manEntity->flush();
        }

        $result = new AResponse();
        return $result;
    }
}