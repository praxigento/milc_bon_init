<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Delete as ELogDelete;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Log\Tree as ETreeLog;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Service\Client\Restore\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Restore\Response as AResponse;

class Restore
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Restore
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

    private function addToDeleteLog($clientId, $date)
    {
        $log = new  ELogDelete();
        $log->client_ref = $clientId;
        $log->is_deleted = false;
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add\Request();
        $req->date = $date;
        $req->details = $log;
        $this->srvEventLogAdd->exec($req);
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
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $clientId);
        if ($found) {
            /* add customer to the tree */
            $this->addToTree($clientId, $parentId);
            /* save into tree log */
            $this->addToTreeLog($clientId, $parentId, $date);
            /* update customer registry */
            $found->is_deleted = false;
            $this->manEntity->persist($found);
            /* save into delete/restore log */
            $this->addToDeleteLog($clientId, $date);

            $this->manEntity->flush();
        }

        $result = new AResponse();
        return $result;
    }

}