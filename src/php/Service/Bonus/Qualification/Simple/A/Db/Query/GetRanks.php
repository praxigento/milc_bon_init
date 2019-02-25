<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Calc\Qual\Rank as EPlanQual;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Rank as EPlanRank;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Result\Pool\Calc as EPeriodCalc;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Data\RankEntry as DRankEntry;

/**
 * Get qualification ranks bound to this calculation instance.
 */
class GetRanks
{
    /* ALIASES for TABLES */
    const AS_CALC = 'calc';
    const AS_QUAL = 'qual';
    const AS_RANK = 'rank';
    /**
     * Attributes see in \Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\A\Data\RankEntry
     */
    /* BINDING */
    const BND_CALC_ID = 'calcId';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn
    ) {
        $this->conn = $conn;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function build()
    {
        $asCalc = self::AS_CALC;
        $asQual = self::AS_QUAL;
        $asRank = self::AS_RANK;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $result */
        $result = $this->conn->createQueryBuilder();
        $result->from(Cfg::DB_TBL_BON_RESULT_POOL_CALC, $asCalc);
        $result->select([
            "$asRank." . EPlanRank::ID . " as " . DRankEntry::RANK_ID,
            "$asRank." . EPlanRank::CODE . " as " . DRankEntry::RANK_CODE,
            "$asRank." . EPlanRank::SEQUENCE . " as " . DRankEntry::SEQUENCE,
            "$asQual." . EPlanQual::RULE_REF . " as " . DRankEntry::RULE_ID
        ]);
        /* LEFT JOIN bon_calc_qual_rank */
        $on = "$asQual." . EPlanQual::CALC_REF . "=$asCalc." . EPeriodCalc::CALC_REF;
        $result->leftJoin($asCalc, Cfg::DB_TBL_BON_CALC_QUAL_RANK, $asQual, $on);
        /* LEFT JOIN bon_plan_rank */
        $on = "$asRank." . EPlanRank::ID . "=$asQual." . EPlanQual::RANK_REF;
        $result->leftJoin($asQual, Cfg::DB_TBL_BON_PLAN_RANK, $asRank, $on);
        /* WHERE */
        $byCalcId = "$asCalc." . EPeriodCalc::ID . "=:" . self::BND_CALC_ID;
        $result->andWhere($byCalcId);

        return $result;
    }
}