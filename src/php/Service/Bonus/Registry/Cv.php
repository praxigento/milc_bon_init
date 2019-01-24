<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Registry;

use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Registry\Cv as ECvReg;
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
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* save data into registry */
        $cvReg = new ECvReg();
        $cvReg->client_ref = $clientId;
        $cvReg->volume = $volume;
        $cvReg->is_autoship = $isAutoship;
        $cvReg->date = $date;
        $this->manEntity->persist($cvReg);
        $this->manEntity->flush();

        $result = new AResponse();
        return $result;
    }
}