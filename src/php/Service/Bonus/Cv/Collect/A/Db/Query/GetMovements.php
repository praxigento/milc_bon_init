<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Cv\Collect\A\Db\Query;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Cv as EPoolCv;

class GetMovements
{
    private const AS_MAIN = 'main';
    private const BND_DATE_FROM = 'dateFrom';
    private const BND_DATE_TO = 'dateTo';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn
    ) {
        $this->conn = $conn;
    }

    public function exec($dateFrom, $dateTo)
    {

        $as = self::AS_MAIN;
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->conn->createQueryBuilder();
        $qb->from(Cfg::DB_TBL_BON_CV_REG, $as);
        $cols = [
            EPoolCv::CLIENT_REF => "$as." . ECvReg::CLIENT_REF,
            EPoolCv::IS_AUTOSHIP => "$as." . ECvReg::IS_AUTOSHIP,
            "SUM($as." . ECvReg::VOLUME . ") as " . EPoolCv::VOLUME
        ];
        $qb->select($cols);
        $byDateFrom = ECvReg::DATE . '>=:' . self::BND_DATE_FROM;
        $byDateTo = ECvReg::DATE . '<:' . self::BND_DATE_TO;
        $qb->where("($byDateFrom) AND ($byDateTo)");
        $qb->setParameters([
            self::BND_DATE_FROM => $dateFrom,
            self::BND_DATE_TO => $dateTo
        ]);
        $qb->groupBy("$as." . ECvReg::CLIENT_REF, "$as." . ECvReg::IS_AUTOSHIP);

        $stmt = $qb->execute();
        /** @var EPoolCv[] $all */
        $result = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, EPoolCv::class);
        return $result;
    }

}