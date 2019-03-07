<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Emulate;


use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as ECalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as ESuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as ESuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool as EPool;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EPoolCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Period as EPoolPeriod;

class Calc
    implements \Praxigento\Milc\Bonus\Api\Helper\Emulate\Calc
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased */
    private $srvComm;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect */
    private $srvCv;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv */
    private $srvGroupPv;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple */
    private $srvQual;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple */
    private $srvTree;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased $srvComm,
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect $srvCv,
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv $srvGroupPv,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple $srvQual,
        \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple $srvTree
    ) {
        $this->dao = $dao;
        $this->hlpFormat = $hlpFormat;
        $this->srvComm = $srvComm;
        $this->srvCv = $srvCv;
        $this->srvGroupPv = $srvGroupPv;
        $this->srvQual = $srvQual;
        $this->srvTree = $srvTree;
    }

    public function getDateMax(): \DateTime
    {
        $order = [ECvReg::DATE => 'DESC'];
        $limit = 1;
        $all = $this->dao->getSet(ECvReg::class, null, null, $order, $limit);
        /** @var ECvReg $one */
        $one = reset($all);
        $result = $this->hlpFormat->parseDateTime($one->date);
        return $result;
    }

    public function getSuite(): ESuite
    {
        /** @var ESuite[] $all */
        $all = $this->dao->getSet(ESuite::class);
        $result = reset($all);
        return $result;
    }

    public function getSuiteCalc($suiteId, $code): ESuiteCalc
    {
        /* get calc type ID by code */
        $key = [ECalcType::CODE => $code];
        /** @var ECalcType $foundType */
        $foundType = $this->dao->getOne(ECalcType::class, $key);
        /* get suite calc */
        $key = [
            ESuiteCalc::SUITE_REF => $suiteId,
            ESuiteCalc::TYPE_REF => $foundType->id
        ];
        $result = $this->dao->getOne(ESuiteCalc::class, $key);
        return $result;

    }

    public function registerPeriod($dateBegin, $suiteId): EPoolPeriod
    {
        $key = [
            EPoolPeriod::DATE_BEGIN => $dateBegin,
            EPoolPeriod::SUITE_REF => $suiteId
        ];
        /** @var EPoolPeriod $result */
        $result = $this->dao->getOne(EPoolPeriod::class, $key);
        if (!$result) {
            $entity = new EPoolPeriod();
            $entity->suite_ref = $suiteId;
            $entity->date_begin = $dateBegin;
            $entity->state = Cfg::BONUS_PERIOD_STATE_OPEN;
            $id = $this->dao->create($entity);
            $result = $this->dao->getOne(EPoolPeriod::class, $id);
        }
        return $result;
    }

    public function registerPool($periodId, $dateStarted): EPool
    {
        $entity = new EPool();
        $entity->period_ref = $periodId;
        $entity->date_started = $dateStarted;
        $id = $this->dao->create($entity);
        $result = $this->dao->getOne(EPool::class, $id);
        return $result;
    }

    public function registerPoolCalc($poolId, $suiteCalcId): EPoolCalc
    {
        $entity = new EPoolCalc();
        $entity->pool_ref = $poolId;
        $entity->calc_ref = $suiteCalcId;
        $id = $this->dao->create($entity);
        $result = $this->dao->getOne(EPoolCalc::class, $id);
        return $result;
    }

    public function step01CvCollect($poolCalcId, $dateFrom, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\Request();
        $req->poolCalcIdOwn = $poolCalcId;
        $req->dateFrom = $dateFrom;
        $req->dateTo = $dateTo;
        $resp = $this->srvCv->exec($req);
    }

    public function step02Tree($poolCalcId, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Request();
        $req->poolCalcIdOwn = $poolCalcId;
        $req->dateTo = $dateTo;
        $resp = $this->srvTree->exec($req);
    }

    public function step03GroupPv($poolCalcId, $poolCalcIdCvCollect, $poolCalcIdTree)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Cv\Group\Pv\Request();
        $req->poolCalcIdOwn = $poolCalcId;
        $req->poolCalcIdCollect = $poolCalcIdCvCollect;
        $req->poolCalcIdTree = $poolCalcIdTree;
        $this->srvGroupPv->exec($req);
    }

    public function step04Rank($poolCalcId, $poolCalcIdTree)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request();
        $req->poolCalcIdRank = $poolCalcId;
        $req->poolCalcIdTree = $poolCalcIdTree;
        $this->srvQual->exec($req);
    }

    public function step05Comm($poolCalcId, $poolCalcIdTree, $poolCalcIdQual)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request();
        $req->poolCalcIdOwn = $poolCalcId;
        $req->poolCalcIdTree = $poolCalcIdTree;
        $req->poolCalcIdRanks = $poolCalcIdQual;
        /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response $resp */
        $resp = $this->srvComm->exec($req);
    }
}