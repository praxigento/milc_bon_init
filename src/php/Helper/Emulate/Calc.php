<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Emulate;


use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Calc\Type as EPlanCalcType;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as EPlanSuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite\Calc as EPlanSuiteCalc;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Period as EResPeriod;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race as EResRace;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Race\Calc as EResRaceCalc;

class Calc
    implements \Praxigento\Milc\Bonus\Api\Helper\Emulate\Calc
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;
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
        \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect $srvCv,
        \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple $srvTree,
        \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple $srvQual,
        \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased $srvComm
    ) {
        $this->dao = $dao;
        $this->srvCv = $srvCv;
        $this->srvTree = $srvTree;
        $this->srvQual = $srvQual;
        $this->srvComm = $srvComm;
        /**/
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
        $entity->race_ref = $raceId;
        $entity->calc_ref = $calcId;
        $id = $this->dao->create($entity);
        $result = $this->dao->getOne(EResRaceCalc::class, $id);
        return $result;
    }

    public function step01Cv($raceCalcId, $dateFrom, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\Request();
        $req->raceCalcId = $raceCalcId;
        $req->dateFrom = $dateFrom;
        $req->dateTo = $dateTo;
        $resp = $this->srvCv->exec($req);
    }

    public function step02Tree($raceCalcId, $raceCalcIdCvCollect, $dateTo)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Tree\Simple\Request();
        $req->raceCalcId = $raceCalcId;
        $req->raceCalcIdCvCollect = $raceCalcIdCvCollect;
        $req->dateTo = $dateTo;
        $resp = $this->srvTree->exec($req);
    }

    public function step03Qual($raceCalcId, $raceCalcIdTree)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request();
        $req->raceCalcIdQual = $raceCalcId;
        $req->raceCalcIdTree = $raceCalcIdTree;
        $this->srvQual->exec($req);
    }

    public function step04Comm($raceCalcId, $raceCalcIdTree, $raceCalcIdQual)
    {
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request();
        $req->thisCalcInstId = $raceCalcId;
        $req->treeCalcInstId = $raceCalcIdTree;
        $req->ranksCalcInstId = $raceCalcIdQual;
        /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response $resp */
        $resp = $this->srvComm->exec($req);
    }
}