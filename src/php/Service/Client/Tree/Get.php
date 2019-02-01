<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Tree;

use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get\Response as AResponse;
use Praxigento\Milc\Bonus\Api\Service\Data\Tree\Entry as DTreeEntry;
use Praxigento\Milc\Bonus\Service\Client\Tree\Get\A\Query as AQuery;

class Get
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Tree\Get
{

    /** @var \Praxigento\Milc\Bonus\Service\Client\Tree\Get\A\Query */
    private $aQuery;
    /** @var \Praxigento\Milc\Bonus\Api\Helper\Format */
    private $hlpFormat;

    public function __construct(
        \Praxigento\Milc\Bonus\Api\Helper\Format $hlpFormat,
        \Praxigento\Milc\Bonus\Service\Client\Tree\Get\A\Query $aQuery
    ) {
        $this->hlpFormat = $hlpFormat;
        $this->aQuery = $aQuery;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $clientId = $req->clientId;
        $date = $req->date;
        if (!$date) {
            $now = $this->hlpFormat->getDateNowUtc();
            $date = $now->format('Y-m-d');
        }
        $qb = $this->aQuery->build();
        $qb->setParameters([AQuery::BND_DATE => $date]);
        $stmt = $qb->execute();
        $entries = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::CUSTOM_OBJECT, DTreeEntry::class);

        $result = new AResponse();
        $result->entries = $entries;
        return $result;
    }

}