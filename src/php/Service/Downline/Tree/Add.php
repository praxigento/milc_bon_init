<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Downline\Tree;

use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Customer\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Downline\Tree\Trace as ETreeTrace;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add\Response as AResponse;

class Add
    implements \Praxigento\Milc\Bonus\Api\Service\Downline\Tree\Add
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
        $parentId = $req->parentId;
        $mlmId = $req->mlmId;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* save data into registry */
        $custReg = new ECustReg();
        $custReg->customer_ref = $customerId;
        $custReg->mlm_id = $mlmId;
        $custReg->is_deleted = false;
        $custReg->is_inactive = false;
        $this->manEntity->persist($custReg);
        $this->manEntity->flush();

        /* save data into downline tree trace */
        $trace = new ETreeTrace();
        $trace->member_ref = $customerId;
        $trace->parent_ref = $parentId;
        $trace->date = $date;
        $this->manEntity->persist($trace);

        /* save data into current downline tree */
        $tree = new ETree();
        $tree->member_ref = $customerId;
        $tree->parent_ref = $parentId;
        /* TODO: init depths & paths for customer */
        $tree->depth = 1;
        $tree->path = '::';
        $this->manEntity->persist($tree);

        $this->manEntity->flush();

        $result = new AResponse();
        return $result;
    }
}