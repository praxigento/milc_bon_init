<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Tree\Get\A;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log as ELog;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Tree as ETreeLog;

/**
 * Build query to get
 */
class Query
{
    /**/
    const AS_BY_CLIENT = 'by_client';
    const AS_BY_DATE = 'by_date';
    const AS_INIT = 'init';
    const AS_INIT_LOG = 'ilog';
    const AS_RESULT = 'result';
    /**/
    const A_BY_CLIENT_REF = 'by_client_ref';
    const A_BY_DATE_CLIENT_REF = 'by_date_client_ref';
    const A_BY_DATE_ID = 'by_date_id';
    const A_RES_CLIENT_ID = 'client_id'; // see \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min
    const A_RES_PARENT_ID = 'parent_id'; // see \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry\Min
    /**/
    const BND_DATE = 'date';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Db */
    private $hlpDb;


    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn,
        \Praxigento\Milc\Bonus\Api\Helper\Db $hlpDb
    ) {
        $this->conn = $conn;
        $this->hlpDb = $hlpDb;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function build()
    {
        $qbInit = $this->getQueryInit();
        $qbMiddle = $this->getQueryMiddle($qbInit);
        $result = $this->getQueryOuter($qbMiddle);
        $notDeleted = self::AS_RESULT . '.' . ETreeLog::PARENT_REF . ' IS NOT NULL';
        $result->andWhere($notDeleted);
        return $result;
    }

    /**
     * Create expression in MySQL or Postgres style.
     *
     * @return string
     */
    private function expForDate()
    {
        $as = self::AS_INIT_LOG;
        $isMySQL = $this->hlpDb->isConnectedToMySQL();
        if ($isMySQL) {
            $result = "date_format($as." . ELog::DATE . ", '%Y-%m-%d')";
        } else {
            /* postgres case */
            $result = "date_trunc('day', $as." . ELog::DATE . ")";
        }
        return $result;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryInit()
    {
        $asLogEvent = self::AS_INIT_LOG;
        $asLogTree = self::AS_INIT;
        $expForDate = $this->expForDate();
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();

        /* FROM bon_event_log_dwnl_tree */
        $result->from(Cfg::DB_TBL_BON_EVENT_LOG_DWNL_TREE, $asLogTree);
        $result->select([
            "$asLogTree." . ETreeLog::CLIENT_REF . ' as ' . self::A_BY_DATE_CLIENT_REF,
            "MAX($asLogTree." . ETreeLog::LOG_REF . ') as ' . self::A_BY_DATE_ID
        ]);

        /* LEFT JOIN bon_cv_reg */
        $on = "$asLogEvent." . ELog::ID . "=$asLogTree." . ETreeLog::LOG_REF;
        $result->leftJoin($asLogTree, Cfg::DB_TBL_BON_EVENT_LOG, $asLogEvent, $on);

        /* WHERE */
        $result->where($expForDate . '<=:' . self::BND_DATE);

        /* GROUP */
        $result->groupBy([
            "$asLogTree." . ETreeLog::CLIENT_REF,
            $expForDate
        ]);

        /* ORDER */
        $result->addOrderBy("$asLogTree." . ETreeLog::CLIENT_REF);
        $result->addOrderBy($expForDate, 'desc');
        return $result;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $qbInit
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryMiddle($qbInit)
    {
        $as = self::AS_BY_DATE;
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();
        $from = '(' . $qbInit->getSQL() . ')';
        $result->from($from, $as);
        $result->select("MAX($as." . self::A_BY_DATE_ID . ') as ' . self::A_BY_CLIENT_REF);
        $result->groupBy("$as." . self::A_BY_DATE_CLIENT_REF);
        return $result;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $qbMiddle
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryOuter($qbMiddle)
    {
        $asRes = self::AS_RESULT;
        $asClient = self::AS_BY_CLIENT;
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();
        $expr = $result->expr();
        $sqlMiddle = $qbMiddle->getSQL();
        $from = "($sqlMiddle)";
        $result->from($from, $asClient);
        $result->select([
            "$asRes." . ETreeLog::CLIENT_REF . ' as ' . self::A_RES_CLIENT_ID,
            "$asRes." . ETreeLog::PARENT_REF . ' as ' . self::A_RES_PARENT_ID
        ]);
        /* LEFT JOIN */
        $on = "$asClient." . self::A_BY_CLIENT_REF . "=$asRes." . ETreeLog::LOG_REF;
        $result->leftJoin($asClient, Cfg::DB_TBL_BON_EVENT_LOG_DWNL_TREE, $asRes, $on);
        return $result;
    }
}