<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Downline\Tree;

use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree\Trace as ETreeTrace;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent\Response as AResponse;

class ChangeParent
    implements \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\ChangeParent
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
        $parentIdNew = $req->parentIdNew;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /**
         * Get customer from registry.
         */
        /** @var ETree $found */
        $found = $this->manEntity->find(ETree::class, $customerId);
        if ($found) {
            $parentId = $found->parent_ref;
            if ($parentId != $parentIdNew) {
                /* save data into downline tree trace */
                $trace = new ETreeTrace();
                $trace->member_ref = $customerId;
                $trace->parent_ref = $parentIdNew;
                $trace->date = $date;
                $this->manEntity->persist($trace);
                /* update current downline tree */
                $found->parent_ref = $parentIdNew;
                /* TODO: change depths & paths for customer itself & for it's downline */
                $this->manEntity->persist($found);
                $this->manEntity->flush();
            }
        }


        $result = new AResponse();
        return $result;
    }
}