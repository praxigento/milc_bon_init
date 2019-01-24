<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Pv;

use Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Pv\Registry as EPvReg;
use Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register\Response as AResponse;

class Register
    implements \Praxigento\Milc\Bonus\Api\Service\Bonus\Pv\Register
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
        $pvReg = new EPvReg();
        $pvReg->client_ref = $clientId;
        $pvReg->volume = $volume;
        $pvReg->is_autoship = $isAutoship;
        $pvReg->date = $date;
        $this->manEntity->persist($pvReg);
        $this->manEntity->flush();

        $result = new AResponse();
        return $result;
    }
}