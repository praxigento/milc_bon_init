<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;


class Map
    implements \Praxigento\Milc\Bonus\Api\Helper\Map
{

    public function byId($data, $key)
    {
        $result = [];
        foreach ($data as $one) {
            /* $one should be an array ... */
            if (is_array($one)) {
                $id = $one[$key];
            } else {
                /* ... or stdClass with property  */
                $id = $one->{$key};
            }
            $result[$id] = $one;
        }
        return $result;
    }
}