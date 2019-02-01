<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Tree\Get\A;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Tree\Log as ETreeLog;

/**
 * Build query to get
 */
class Query
{
    /**/
    const AS_BY_CLIENT = 'by_client';
    const AS_BY_DATE = 'by_date';
    const AS_INIT = 'init';
    const AS_RESULT = 'result';
    /**/
    const A_BY_CLIENT_REF = 'by_client_ref';
    const A_BY_DATE_CLIENT_REF = 'by_date_client_ref';
    const A_BY_DATE_ID = 'by_date_id';
    const A_RES_CLIENT_ID = 'client_id'; // see \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry
    const A_RES_PARENT_ID = 'parent_id'; // see \Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry
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
        $isMySQL = $this->hlpDb->isConnectedToMySQL();
        if ($isMySQL) {
            $result = "date_format(" . self::AS_INIT . "." . ETreeLog::DATE . ", '%Y-%m-%d')";
        } else {
            /* postgres case */
            $result = "date_trunc('day', " . self::AS_INIT . "." . ETreeLog::DATE . ")";
        }
        return $result;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryInit()
    {
        $expForDate = $this->expForDate();
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();
        $result->from(Cfg::DB_TBL_CLIENT_TREE_LOG, self::AS_INIT);
        $result->select([
            self::AS_INIT . '.' . ETreeLog::CLIENT_REF . ' as ' . self::A_BY_DATE_CLIENT_REF,
            'MAX(' . self::AS_INIT . '.' . ETreeLog::ID . ') as ' . self::A_BY_DATE_ID
        ]);
        $result->where($expForDate . '<=:' . self::BND_DATE);
        $result->groupBy([
            self::AS_INIT . '.' . ETreeLog::CLIENT_REF,
            $expForDate
        ]);
        $result->addOrderBy(self::AS_INIT . '.' . ETreeLog::CLIENT_REF);
        $result->addOrderBy($expForDate, 'desc');
        return $result;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $qbInit
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryMiddle($qbInit)
    {
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();
        $from = '(' . $qbInit->getSQL() . ')';
        $result->from($from, self::AS_BY_DATE);
        $result->select('MAX(' . self::AS_BY_DATE . '.' . self::A_BY_DATE_ID . ') as ' . self::A_BY_CLIENT_REF);
        $result->groupBy(self::AS_BY_DATE . '.' . self::A_BY_DATE_CLIENT_REF);
        return $result;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $qbMiddle
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQueryOuter($qbMiddle)
    {
        /* compose query using builder */
        $result = $this->conn->createQueryBuilder();
        $expr = $result->expr();
        $from = '(' . $qbMiddle->getSQL() . ')';
        $result->from($from, self::AS_BY_CLIENT);
        $result->select([
            self::AS_RESULT . '.' . ETreeLog::CLIENT_REF . ' as ' . self::A_RES_CLIENT_ID,
            self::AS_RESULT . '.' . ETreeLog::PARENT_REF . ' as ' . self::A_RES_PARENT_ID
        ]);
        $on = $expr->eq(self::AS_RESULT . '.' . ETreeLog::ID, self::AS_BY_CLIENT . '.' . self::A_BY_CLIENT_REF);
        $result->leftJoin(
            self::AS_BY_CLIENT,
            Cfg::DB_TBL_CLIENT_TREE_LOG,
            self::AS_RESULT,
            $on
        );
        return $result;
    }
}