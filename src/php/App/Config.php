<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\App;

/**
 * Configuration loader.
 *
 *
 */
class Config
{
    const DEF_CFG_FILE = __DIR__ . '/../../../cfg/local.json';

    /** @var  \Praxigento\Milc\Bonus\App\Config\Data */
    private $data;

    public function get(): \Praxigento\Milc\Bonus\App\Config\Data
    {
        return $this->data;
    }

    /**
     * Load configuration from given filename or from default.
     *
     * @param string|null $filename
     * @return \Praxigento\Milc\Bonus\App\Config\Data
     */
    public function load($filename = null): \Praxigento\Milc\Bonus\App\Config\Data
    {
        if (!file_exists($filename)) {
            if (!is_null($filename)) {
                echo "Cannot load configuration from given file: $filename. Use default config file.";
            }
            $filename = self::DEF_CFG_FILE;
        }
        if (!file_exists($filename)) {
            echo "Cannot load configuration from default file: $filename";
            exit(255);
        }
        /* read content and convert to JSON/DataObject */
        $filename = realpath($filename);
        $content = file_get_contents($filename);
        $std = json_decode($content);
        $this->data = new \Praxigento\Milc\Bonus\App\Config\Data($std);
        // echo "Configuration is loaded from file: $filename";
        return $this->data;
    }
}