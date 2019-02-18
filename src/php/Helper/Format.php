<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;

use Praxigento\Milc\Bonus\Api\Config as Cfg;

class Format
    implements \Praxigento\Milc\Bonus\Api\Helper\Format
{

    public function getDateNowUtc(): \DateTime
    {
        $result = new \DateTime('now', new \DateTimeZone('UTC'));
        return $result;
    }

    public function parseDateTime($data = null): \DateTime
    {
        if (is_int($data)) {
            /* create DateTie from unix time */
            $dt = new \DateTime();
            $dt->setTimestamp($data);
            $result = $dt;
        } elseif ($data instanceof \DateTime) {
            $result = $data;
        } elseif (is_null($data)) {
            $result = new \DateTime();
        } else {
            $result = \DateTime::createFromFormat(Cfg::FORMAT_DATETIME, trim($data));
        }
        return $result;
    }
}