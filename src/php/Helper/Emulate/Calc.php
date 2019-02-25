<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Emulate;


use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as EPlanCalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as EPlanSuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as EPlanSuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool as EResRace;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Calc as EResRaceCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Period as EResPeriod;

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
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple */
    private $srvQual;
    /** @var \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple */
    private $srvTree;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao,
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat,
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect $srvCv,
        \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple $srvTree,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple $srvQual,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased $srvComm
    ) {
        $this->dao = $dao;
        $this->hlpFormat = $hlpFormat;
        $this->srvCv = $srvCv;
        $this->srvTree = $srvTree;
        $this->srvQual = $srvQual;
        $this->srvComm = $srvComm;
        /**/
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

    public function getSuite(): EPlanSuite
    {
        /** @var EPlanSuite[] $all */
        $all = $this->dao->getSet(EPlanSuite::class);
        $result = reset($all);
        return $result;
    }

    public function getSuiteCalc($suiteId, $code): EPlanSuiteCalc
    {
        /* get calc type ID by code */
        $key = [EPlanCalcType::CODE => $code];
        /** @var EPlanCalcType $foundType */
        $foundType = $this->dao->getOne(EPlanCalcType::class, $key);
        /* get suite calc */
        $key = [
            EPlanSuiteCalc::SUITE_REF => $suiteId,
            EPlanSuiteCalc::TYPE_REF => $foundType->id
        ];
        $result = $this->dao->getOne(EPlanSuiteCalc::class, $key);
        return $result;

    }

    public function registerPeriod($dateBegin, $suiteId): EResPeriod
    {
        $key = [
            EResPeriod::DATE_BEGIN => $dateBegin,
            EResPeriod::SUITE_REF => $suiteId
        ];
        /** @var EResPeriod $result */
        $result = $this->dao->getOne(EResPeriod::class, $key);
        if (!$result) {
            $entity = new EResPeriod();
            $entity->suite_ref = $suiteId;
            $entity->date_begin = $dateBegin;
            $entity->state = Cfg::BONUS_PERIOD_STATE_OPEN;
            $id = $this->dao->create($entity);
            $result = $this->dao->getOne(EResPeriod::class, $id);
        }
        return $result;
    }

    public function registerRace($periodId, $dateStarted): EResRace
    {
        $entity = new EResRace();
        $entity->period_ref = $periodId;
        $entity->date_started = $dateStarted;
        $id = $this->dao->create($entity);
        $result = $this->dao->getOne(EResRace::class, $id);
        return $result;
    }

    public function registerRaceCalc($raceId, $calcId): EResRaceCalc
    {
        $entity = new EResRaceCalc();
        $entity->pool_ref = $raceId;
        $entity->calc_ref = $calcId;
        $id = $this->dao->create($entity);
        $result = $this->dao->getOne(EResRaceCalc::class, $id);
        return $result;
    }

    public function step01Cv($poolCalcId, $dateFrom, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\Request();
        $req->poolCalcId = $poolCalcId;
        $req->dateFrom = $dateFrom;
        $req->dateTo = $dateTo;
        $resp = $this->srvCv->exec($req);
    }

    public function step02Tree($poolCalcId, $poolCalcIdCvCollect, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Request();
        $req->poolCalcId = $poolCalcId;
        $req->poolCalcIdCvCollect = $poolCalcIdCvCollect;
        $req->dateTo = $dateTo;
        $resp = $this->srvTree->exec($req);
    }

    public function step03Qual($poolCalcId, $poolCalcIdTree)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request();
        $req->poolCalcIdQual = $poolCalcId;
        $req->poolCalcIdTree = $poolCalcIdTree;
        $this->srvQual->exec($req);
    }

    public function step04Comm($poolCalcId, $poolCalcIdTree, $poolCalcIdQual)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request();
        $req->thisCalcInstId = $poolCalcId;
        $req->treeCalcInstId = $poolCalcIdTree;
        $req->ranksCalcInstId = $poolCalcIdQual;
        /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response $resp */
        $resp = $this->srvComm->exec($req);
    }
}