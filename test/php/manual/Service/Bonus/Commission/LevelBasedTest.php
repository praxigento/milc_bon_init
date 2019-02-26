<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Test\Praxigento\Milc\Bonus\Service\Bonus\Commission;


class LevelBasedTest
    extends \PHPUnit\Framework\TestCase
{
    public function test_me()
    {
        $app = \Praxigento\Milc\Bonus\App::getInstance();
        /** @var \Psr\Container\ContainerInterface $container */
        $container = $app->getContainer();
        /** @var \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased $srv */
        $srv = $container->get(\Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased::class);
        $req = new \Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Request();
        $req->poolCalcIdOwn = 4;
        $req->poolCalcIdRanks = 3;
        $req->poolCalcIdTree = 2;
        $resp = $srv->exec($req);
        $this->assertInstanceOf(\Praxigento\Milc\Bonus\Service\Bonus\Commission\LevelBased\Response::class, $resp);
    }
}