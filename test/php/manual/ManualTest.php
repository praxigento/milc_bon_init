<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Test\Praxigento\Milc\Bonus;

use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Plan\Suite as EBonSuite;
use Praxigento\Milc\Bonus\Api\Db\Data\Res\Partner as EResPartner;

class ManualTest
    extends \PHPUnit\Framework\TestCase
{
    public function test_me()
    {
        $app = \Praxigento\Milc\Bonus\App::getInstance();
        /** @var \Psr\Container\ContainerInterface $container */
        $container = $app->getContainer();

        /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao */
        $dao = $container->get(\TeqFw\Lib\Db\Api\Dao\Entity\Anno::class);
        $partner = new EResPartner();
        $dao->create($partner);

        $key = [EBonSuite::ID => 1];
        $found = $dao->getOne(EBonSuite::class, $key);
        $this->assertTrue(true);
    }
}