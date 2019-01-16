<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\App\Config;

/**
 * Configuration data structure (see `./cfg/local.init.json` & `./bin/config.sh`).
 */
class Data
    extends \TeqFw\Lib\Data
{
    /**
     * @var string
     */
    public $db_driver;
    /**
     * @var string
     */
    public $db_host;
    /**
     * @var string
     */
    public $db_name;
    /**
     * @var string
     */
    public $db_pass;
    /**
     * @var string
     */
    public $db_user;
}