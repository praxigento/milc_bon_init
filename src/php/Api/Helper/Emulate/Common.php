<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper\Emulate;

/**
 * Common emulation related functionality.
 */
interface Common
{
    /**
     *
     * @param \DateTime $date
     * @param int $maxSeconds max number of seconds to increment
     * @return mixed
     */
    public function dateModify(\DateTime $date, $maxSeconds = null): \DateTime;
}