<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Downline;

use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Deactivate\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\Downline\Deactivate\Response as AResponse;

class Deactivate
    implements \Praxigento\Milc\Bonus\Api\Service\Client\Downline\Deactivate
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
        $customerId = $req->customerId;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $customerId);
        if ($found) {

        }

        $result = new AResponse();
        return $result;
    }
}