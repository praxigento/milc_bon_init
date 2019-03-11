<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Dwnl\Tree as ETree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Tree as ELogTree;
use Praxigento\Milc\Bonus\Api\Service\Client\Add\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Add\Response as AResponse;

class Add
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Add
{
    const TMP_TREE_TYPE_BINARY = 'binary';
    const TMP_TREE_TYPE_NATURAL = 'natural';

    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /** @var \Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary */
    private $hlpTreeBin;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add */
    private $srvEventLogAdd;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat,
        \Praxigento\Milc\Bonus\Service\Bonus\Event\Log\Add $srvEventLogAdd,
        \Praxigento\Milc\Bonus\Service\Client\Add\A\Helper\TreeBinary $hlpTreeBin
    ) {
        $this->dao = $dao;
        $this->hlpFormat = $hlpFormat;
        $this->srvEventLogAdd = $srvEventLogAdd;
        $this->hlpTreeBin = $hlpTreeBin;
    }

    private function addToRegistry($clientId, $enrollerId, $mlmId, $isNotDistr)
    {
        $custReg = new ECustReg();
        $custReg->client_ref = $clientId;
        $custReg->enroller_ref = $enrollerId;
        $custReg->mlm_id = $mlmId;
        $custReg->is_deleted = false;
        $custReg->is_customer = $isNotDistr;
        $this->dao->create($custReg);
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

    /**
     * @param int $clientId
     * @param int $parentId
     * @return int
     */
    private function addToTreeNatural($clientId, $parentId)
    {
        $tree = new ETree();
        $tree->client_ref = $clientId;
        $tree->parent_ref = $parentId;
        /* TODO: init depths & paths for customer */
        $tree->depth = 1;
        $tree->path = '::';
        $this->dao->create($tree);
        return $parentId;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $clientId = $req->clientId;
        $enrollerId = $req->enrollerId;
        $mlmId = $req->mlmId;
        $isNotDistr = $req->isNotDistributor;
        $placeToLeft = $req->placeToLeft;
        $treeType = $req->treeType;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* save data into registry */
        $this->addToRegistry($clientId, $enrollerId, $mlmId, $isNotDistr);

        /* save data into current downline tree */
        if ($treeType == self::TMP_TREE_TYPE_BINARY) {
            $parentId = $this->hlpTreeBin->add($clientId, $enrollerId, $placeToLeft);
        } else {
            $parentId = $this->addToTreeNatural($clientId, $enrollerId);
        }
        /* save data into downline tree log */
        $this->addToTreeLog($clientId, $parentId, $date);

        $result = new AResponse();
        $result->enrollerId = $enrollerId;
        $result->parentId = $parentId;
        return $result;
    }
}