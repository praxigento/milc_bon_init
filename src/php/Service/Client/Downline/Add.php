<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Downline;

use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree\Log as ETreeLog;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add\Response as AResponse;

class Add
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Add
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

    private function addToRegistry($customerId, $mlmId, $isNotDistr)
    {
        $custReg = new ECustReg();
        $custReg->client_ref = $customerId;
        $custReg->mlm_id = $mlmId;
        $custReg->is_deleted = false;
        $custReg->is_customer = $isNotDistr;
        $this->manEntity->persist($custReg);
        $this->manEntity->flush();
    }

    private function addToTree($customerId, $parentId)
    {
        $tree = new ETree();
        $tree->client_ref = $customerId;
        $tree->parent_ref = $parentId;
        /* TODO: init depths & paths for customer */
        $tree->depth = 1;
        $tree->path = '::';
        $this->manEntity->persist($tree);
    }

    private function addToTreeLog($customerId, $parentId, $date)
    {
        $log = new ETreeLog();
        $log->client_ref = $customerId;
        $log->parent_ref = $parentId;
        $log->date = $date;
        $this->manEntity->persist($log);
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $customerId = $req->customerId;
        $parentId = $req->parentId;
        $mlmId = $req->mlmId;
        $isNotDistr = $req->isNotDistributor;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* save data into registry */
        $this->addToRegistry($customerId, $mlmId, $isNotDistr);

        /* save data into current downline tree */
        $this->addToTree($customerId, $parentId);

        /* save data into downline tree log */
        $this->addToTreeLog($customerId, $parentId, $date);

        $this->manEntity->flush();

        $result = new AResponse();
        return $result;
    }
}