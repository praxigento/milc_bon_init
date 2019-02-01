<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper;

/**
 * Utilities for database operations.
 */
interface Db
{
    public function isConnectedToMySQL(): bool;

    public function isConnectedToPostgres(): bool;
}