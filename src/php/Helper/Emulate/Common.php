<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Emulate;


use Praxigento\Milc\Bonus\Api\Config as Cfg;

class Common
    implements \Praxigento\Milc\Bonus\Api\Helper\Emulate\Common
{


    public function __construct()
    {
        /**/
    }

    public function dateModify(\DateTime $date, $maxSeconds = null): \DateTime
    {
        if (is_null($maxSeconds))
            $maxSeconds = Cfg::BEGINNING_OF_AGES_INC_MAX;
        $seconds = random_int(0, $maxSeconds);
        $result = clone $date;
        $result->modify("+$seconds seconds");
        return $result;
    }
}