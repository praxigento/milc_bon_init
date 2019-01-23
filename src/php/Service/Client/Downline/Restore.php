<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Downline;

use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry\Log\Delete as EDeleteLog;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree\Log as ETreeLog;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore\Response as AResponse;

class Restore
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Restore
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
        $log->is_deleted = false;
        $this->manEntity->persist($log);
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
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $customerId);
        if ($found) {
            /* add customer to the tree */
            $this->addToTree($customerId, $parentId);
            /* save into tree log */
            $this->addToTreeLog($customerId, $parentId, $date);
            /* update customer registry */
            $found->is_deleted = false;
            $this->manEntity->persist($found);
            /* save into delete/restore log */
            $this->addToDeleteLog($customerId, $date);

            $this->manEntity->flush();
        }

        $result = new AResponse();
        return $result;
    }

}