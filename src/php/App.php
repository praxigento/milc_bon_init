<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus;

use Doctrine\ORM\EntityManager as DoctrineEntityMgr;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;

/**
 * Entry point for application (configuration, container, db connection, ...).
 */
class App
{
    /** @var  \TeqFw\Lib\Di\Api\Container */
    private $container;
    /** @var  \Praxigento\Milc\Bonus\App */
    private static $instance;

    public function __construct()
    {
        /* create DI container for the application */
        $this->initDiContainer();
        /* add application objects to container */
        $this->initConfig();
        $this->initDbConnection();
    }

    public function getContainer(): \Psr\Container\ContainerInterface
    {
        return $this->container;
    }

    public static function getInstance(): \Praxigento\Milc\Bonus\App
    {
        if (is_null(self::$instance)) {
            self::$instance = new \Praxigento\Milc\Bonus\App();
        }
        return self::$instance;
    }

    /**
     * Load application configuration.
     */
    private function initConfig()
    {
        $this->container->share(\Praxigento\Milc\Bonus\App\Config::class);
        /** @var \Praxigento\Milc\Bonus\App\Config $config */
        $config = $this->container->get(\Praxigento\Milc\Bonus\App\Config::class);
        $appCfg = $config->load();
        $this->container->share(\Praxigento\Milc\Bonus\App\Config\Data::class, $appCfg);
    }

    /**
     * Create DB connection and place it into DI container.
     */
    private function initDbConnection()
    {
        /** @var \Praxigento\Milc\Bonus\App\Config\Data $appCfg */
        $appCfg = $this->container->get(\Praxigento\Milc\Bonus\App\Config\Data::class);
        /* compose DB connection config for "teqfw/back-db-php" module */
        $dbCfg = new \TeqFw\Lib\Db\Api\Data\Cfg\Db();
        $dbCfg->driver = $appCfg->db_driver;
        $dbCfg->host = $appCfg->db_host;
        $dbCfg->dbname = $appCfg->db_name;
        $dbCfg->user = $appCfg->db_user;
        $dbCfg->password = $appCfg->db_pass;
        $this->container->share(\TeqFw\Lib\Db\Api\Data\Cfg\Db::class, $dbCfg);
        /* add module's IoC objects to container */
        \TeqFw\Lib\Db\Api\ContainerBuilder::populate($this->container);
        \TeqFw\Lib\Db\Repo3\Api\ContainerBuilder::populate($this->container);
        /* Configure Doctrine Entity Manager */
        $isDevMode = true;
        $config = DoctrineSetup::createAnnotationMetadataConfiguration(array(__DIR__ . '/Api/Repo/Data'), $isDevMode);
        $conn = $this->container->get(\Doctrine\DBAL\Connection::class);
        $em = DoctrineEntityMgr::create($conn, $config);
        $this->container->share(\Doctrine\ORM\EntityManagerInterface::class, $em);
    }

    /** Create application level container. */
    private function initDiContainer()
    {
        $this->container = \TeqFw\Lib\Di\Api\ContainerFactory::getContainer();
    }
}