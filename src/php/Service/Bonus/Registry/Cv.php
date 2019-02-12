<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Registry;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry as ECvReg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry\Sale as ECvRegSale;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry\Sale\Back as ECvRegSaleBack;
use Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Response as AResponse;

class Cv
    implements \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv
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
        $clientId = $req->clientId;
        $volume = $req->volume;
        $isAutoship = (bool)$req->isAutoship;
        $sourceId = $req->sourceId;
        $sourceType = $req->sourceType;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();


        /* save data into registry */
        $cvReg = new ECvReg();
        $cvReg->client_ref = $clientId;
        $cvReg->volume = $volume;
        $cvReg->is_autoship = $isAutoship;
        $cvReg->date = $date;
        $cvReg->type = $sourceType;
        $this->manEntity->persist($cvReg);
        $this->manEntity->flush();
        $id = $cvReg->id;

        /* save link to the CV source */
        switch ($sourceType) {
            case Cfg::CV_REG_SOURCE_SALE:
                $this->saveLinkSale($id, $sourceId);
                break;
            case Cfg::CV_REG_SOURCE_SALE_BACK:
                $this->saveLinkSaleBack($id, $sourceId);
                break;
            default:
                throw new \Exception("Unknown CV source type: '$sourceType'.");
        }

        $result = new AResponse();
        $result->registryId = $id;
        $result->sourceId = $sourceId;
        $result->sourceType = $sourceType;
        return $result;
    }

    private function saveLinkSale($id, $sourceId)
    {
        $link = new ECvRegSale();
        $link->registry_ref = $id;
        $link->source_ref = $sourceId;
        $this->manEntity->persist($link);
        $this->manEntity->flush();
    }

    private function saveLinkSaleBack($id, $sourceId)
    {
        $link = new ECvRegSaleBack();
        $link->registry_ref = $id;
        $link->source_ref = $sourceId;
        $this->manEntity->persist($link);
        $this->manEntity->flush();
    }
}