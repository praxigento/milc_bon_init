<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper;

/**
 * Utilities to map array data.
 */
interface Map
{
    /**
     * Convert array of array or data objects ([ 0 => [ 'id' => 321, ... ], ...])
     * to mapped array ([ 321 => [ 'id'=>321, ... ], ... ]).
     *
     * @param array|\TeqFw\Lib\Data[] $data nested array or array of data objects.
     * @param string $key name of the 'id' attribute.
     *
     * @return array|\TeqFw\Lib\Data[]
     */
    public function byId($data, $key);
}