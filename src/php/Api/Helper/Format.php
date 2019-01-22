<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper;

/**
 * Utilities to format data.
 */
interface Format
{
    /**
     * 'now' date (UTC) to be stored in DB.
     *
     * @return \DateTime
     */
    public function getDateNowUtc(): \DateTime;
}