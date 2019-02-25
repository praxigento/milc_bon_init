<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Log\Tree as ETreeLog;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Service\Client\Add\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Add\Response as AResponse;

class Add
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Add
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

    private function addToRegistry($clientId, $mlmId, $isNotDistr)
    {
        $custReg = new ECustReg();
        $custReg->client_ref = $clientId;
        $custReg->mlm_id = $mlmId;
        $custReg->is_deleted = false;
        $custReg->is_customer = $isNotDistr;
        $this->manEntity->persist($custReg);
        $this->manEntity->flush();
    }

    private function addToTree($clientId, $parentId)
    {
        $tree = new ETree();
        $tree->client_ref = $clientId;
        $tree->parent_ref = $parentId;
        /* TODO: init depths & paths for customer */
        $tree->depth = 1;
        $tree->path = '::';
        $this->manEntity->persist($tree);
    }

    private function addToTreeLog($clientId, $parentId, $date)
    {
        $log = new ETreeLog();
        $log->client_ref = $clientId;
        $log->parent_ref = $parentId;
        $log->date = $date;
        $this->manEntity->persist($log);
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $clientId = $req->clientId;
        $parentId = $req->parentId;
        $mlmId = $req->mlmId;
        $isNotDistr = $req->isNotDistributor;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* save data into registry */
        $this->addToRegistry($clientId, $mlmId, $isNotDistr);

        /* save data into current downline tree */
        $this->addToTree($clientId, $parentId);

        /* save data into downline tree log */
        $this->addToTreeLog($clientId, $parentId, $date);

        $this->manEntity->flush();

        $result = new AResponse();
        return $result;
    }
}