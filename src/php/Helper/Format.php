<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;


class Format
    implements \Praxigento\Milc\Bonus\Api\Helper\Format
{

    public function getDateNowUtc(): \DateTime
    {
        $result = new \DateTime('now', new \DateTimeZone('UTC'));
        return $result;
    }
}