<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Dwnl\Tree\Log as ETreeLog;
use Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Response as AResponse;

class ChangeParent
    implements \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent
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
            $parentId = $found->parent_ref;
            $result->parentIdOld = $parentId;
            if ($parentId != $parentIdNew) {
                /* save data into downline tree trace */
                $log = new ETreeLog();
                $log->client_ref = $clientId;
                $log->parent_ref = $parentIdNew;
                $log->date = $date;
                $this->manEntity->persist($log);
                /* update current downline tree */
                $found->parent_ref = $parentIdNew;
                /* TODO: change depths & paths for customer itself & for it's downline */
                $this->manEntity->persist($found);
                $this->manEntity->flush();
            }
        }

        return $result;
    }
}