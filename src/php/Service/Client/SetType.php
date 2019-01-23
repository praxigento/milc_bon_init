<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Client\Downline;

use Praxigento\Milc\Bonus\Api\Repo\Data\Client\Registry as ECustReg;
use Praxigento\Milc\Bonus\Api\Service\Client\SetType\Request as ARequest;
use Praxigento\Milc\Bonus\Api\Service\Client\SetType\Response as AResponse;

class SetType
    implements \Praxigento\Milc\Bonus\Api\Service\Client\SetType
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
        $isCust = $req->isCustomer;
        $date = $req->date;
        if (!$date)
            $date = $this->hlpFormat->getDateNowUtc();

        /* find data in customer registry */
        /** @var ECustReg $found */
        $found = $this->manEntity->find(ECustReg::class, $customerId);
        if ($found) {
            if ($found->is_customer) {
            }
        }

        $result = new AResponse();
        return $result;
    }
}