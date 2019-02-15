<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper\Emulate;


use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Res\Partner as EFlectraPartner;
use Praxigento\Milc\Bonus\Api\Db\Data\Sale\Order as EFlectraSale;

class Activity
    implements \Praxigento\Milc\Bonus\Api\Helper\Emulate\Activity
{
    /** @var \Psr\Container\ContainerInterface */
    private $container;
    /** @var \DateTime date trace for events (is incremented on every event) */
    private $date;
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;
    /* maps are registries to save entities IDs */
    private $mapClientAll = [];
    private $mapClientDeleted = [];
    private $mapClientTypeCust = [];
    private $mapClientTypeDistr = [];
    private $mapSale = [];
    private $mapSaleClawback = [];
    /**
     * @var int|null we have one only root in the test downline.
     */
    private $rootId = null;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Add */
    private $srvClientAdd;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent */
    private $srvClientChangeParent;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Delete */
    private $srvClientDelete;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\Restore */
    private $srvClientRestore;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Client\SetType */
    private $srvClientSetType;
    /** @var \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv */
    private $srvCvReg;

    public function __construct(
        \Psr\Container\ContainerInterface $container,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Praxigento\Milc\Bonus\Api\Service\Client\Add $srvClientAdd,
        \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent $srvClientChangeParent,
        \Praxigento\Milc\Bonus\Api\Service\Client\Delete $srvClientDelete,
        \Praxigento\Milc\Bonus\Api\Service\Client\Restore $srvClientRestore,
        \Praxigento\Milc\Bonus\Api\Service\Client\SetType $srvClientSetType,
        \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv $srvCvReg
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->srvClientChangeParent = $srvClientChangeParent;
        $this->srvClientAdd = $srvClientAdd;
        $this->srvClientDelete = $srvClientDelete;
        $this->srvClientRestore = $srvClientRestore;
        $this->srvClientSetType = $srvClientSetType;
        $this->srvCvReg = $srvCvReg;
        /**/
        $this->date = \DateTime::createFromFormat(Cfg::BEGINNING_OF_AGES_FORMAT, Cfg::BEGINNING_OF_AGES);
    }

    public function clientChangeParent()
    {
        $clientId = $parentIdNew = $parentIdOld = null;
        $count = count($this->mapClientTypeDistr) - 1;
        if ($count >= 1) {
            $keyDistr = random_int(0, $count);
            $clientId = $this->mapClientTypeDistr[$keyDistr];
            $keyParent = random_int(0, $count);
            $parentIdNew = $this->mapClientTypeDistr[$keyParent];
            if (
                ($parentIdNew != $clientId) &&
                ($clientId != $this->rootId)  // don't change parent for the root customer
            ) {
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\ChangeParent\Request();
                $req->clientId = $clientId;
                $req->parentIdNew = $parentIdNew;
                $req->date = $this->dateModify();
                $resp = $this->srvClientChangeParent->exec($req);
                if ($resp->success) {
                    $parentIdOld = $resp->parentIdOld;
                    if ($parentIdOld == $parentIdNew) {
                        /* reset result vars to eliminate output noise */
                        $clientId = $parentIdNew = $parentIdOld = null;
                    }
                    $this->em->flush();
                }
            }
        }
        return [$clientId, $parentIdNew, $parentIdOld];
    }

    public function clientChangeType()
    {
        $clientId = $typeOld = $typeNew = null;
        $changeDistr = $this->randomPercent(50);
        if ($changeDistr) {
            /* change type for distributor */
            $count = count($this->mapClientTypeDistr) - 1;
            if ($count > 1) {
                $key = random_int(0, $count);
                $clientId = $this->mapClientTypeDistr[$key];
                $typeOld = 'distr';
                $typeNew = 'cust';
            }
        } else {
            /* change type for customer */
            $count = count($this->mapClientTypeCust) - 1;
            if ($count > 1) {
                $key = random_int(0, $count);
                $clientId = $this->mapClientTypeCust[$key];
                $typeOld = 'cust';
                $typeNew = 'distr';
            }
        }
        /* there is data to change*/
        if (
            isset($clientId) &&
            ($clientId != $this->rootId) &&
            !in_array($clientId, $this->mapClientDeleted)
        ) {
            $req = new \Praxigento\Milc\Bonus\Api\Service\Client\SetType\Request();
            $req->clientId = $clientId;
            $req->isCustomer = $changeDistr;
            $req->date = $this->dateModify();
            $this->srvClientSetType->exec($req);
            /* fix maps */
            if ($changeDistr) {
                /* move distr to cust */
                unset($this->mapClientTypeDistr[$key]);
                $this->mapClientTypeDistr = array_values($this->mapClientTypeDistr);
                $this->mapClientTypeCust[] = $clientId;
            } else {
                /* move cust to distr */
                unset($this->mapClientTypeCust[$key]);
                $this->mapClientTypeCust = array_values($this->mapClientTypeCust);
                $this->mapClientTypeDistr[] = $clientId;
            }
        } else {
            /* reset result vars to eliminate output noise */
            $clientId = $typeOld = $typeNew = null;
        }
        return [$clientId, $typeOld, $typeNew];
    }

    public function clientCreate($percentIsCust)
    {
        $customerId = $this->clientPartnerCreate();
        $this->mapClientAll[] = $customerId;
        /* prepare working data */
        $count = count($this->mapClientTypeDistr) - 1;
        if (is_null($this->rootId)) {
            /* this is first client, do it a root */
            $this->rootId = $customerId;
            $parentId = $customerId;
            $isNotDistr = false;
        } else {
            /* parent ID should not be equal to customer ID - we have a failures when we delete customers */
            do {
                $key = random_int(0, $count);
                $parentId = $this->mapClientTypeDistr[$key];
            } while ($parentId == $customerId);

            /* 5% - new customer is not distributor */
            $isNotDistr = $this->randomPercent($percentIsCust);
        }
        $mlmId = $customerId; // simple rule: MLM ID equals to ID

        $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Add\Request();
        $req->clientId = $customerId;
        $req->parentId = $parentId;
        $req->mlmId = $mlmId;
        $req->isNotDistributor = $isNotDistr;
        $req->date = $this->dateModify();
        $this->srvClientAdd->exec($req);

        if ($isNotDistr) {
            $this->mapClientTypeCust[] = $customerId;
        } else {
            $this->mapClientTypeDistr[] = $customerId;
        }

        return [$this->date, $this->rootId, $customerId, $parentId, $isNotDistr];
    }

    public function clientDelete()
    {
        $clientId = null;
        $count = count($this->mapClientTypeDistr) - 1;
        if ($count > 1) {
            /* there are distributors to delete */
            $key = random_int(0, $count);
            $clientId = $this->mapClientTypeDistr[$key];
            if ($clientId != $this->rootId) {
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Delete\Request();
                $req->clientId = $clientId;
                $req->date = $this->dateModify();
                $this->srvClientDelete->exec($req);
                unset($this->mapClientTypeDistr[$key]);
                $this->mapClientTypeDistr = array_values($this->mapClientTypeDistr);
                $this->mapClientDeleted[] = $clientId;
                $this->em->flush();
            }
        }
        return [$clientId];
    }

    /**
     * Create new "res_partner" entry (Flectra's namespace).
     *
     * @return int
     */
    private function clientPartnerCreate(): int
    {
        $partner = new EFlectraPartner();
        $this->em->persist($partner);
        $this->em->flush($partner);
        $result = $partner->id;
        return $result;
    }

    public function clientRestore()
    {
        $clientId = null;
        $countDel = count($this->mapClientDeleted) - 1;
        $countDistr = count($this->mapClientTypeDistr) - 1;
        if ($countDel > 1) {
            /* there are distributors to restore */
            $keyCust = random_int(0, $countDel);
            $clientId = $this->mapClientDeleted[$keyCust];
            $keyParent = random_int(0, $countDistr);
            $parentId = $this->mapClientTypeDistr[$keyParent];
            if (
                ($clientId != $this->rootId) &&
                ($clientId != $parentId)
            ) {
                $req = new \Praxigento\Milc\Bonus\Api\Service\Client\Restore\Request();
                $req->clientId = $clientId;
                $req->parentId = $parentId;
                $req->date = $this->dateModify();
                $this->srvClientRestore->exec($req);
                unset($this->mapClientDeleted[$keyCust]);
                $this->mapClientDeleted = array_values($this->mapClientDeleted);
                $this->mapClientTypeDistr[] = $clientId;
                $this->em->flush();
            }
        }
        return [$clientId];
    }

    /**
     * Add random delta to given date.
     *
     * @return \DateTime
     * @throws \Exception
     */
    private function dateModify()
    {
        $seconds = random_int(0, Cfg::BEGINNING_OF_AGES_INC_MAX);
        $this->date->modify("+$seconds seconds");
        return $this->date;
    }

    /**
     * CV amount:
     *  * 15%: 30.00 - 100.00
     *  * 85%: 100.00 - 250.00
     *
     * @return float
     * @throws \Exception
     */
    private function randomCvAmount(): float
    {
        $needSmallAmnt = randomPercent(15);
        if ($needSmallAmnt) {
            /* 15%: 30.00 - 100.00 */
            $result = random_int(3000, 10000) / 100;
        } else {
            /* 85%: 100.00 - 250.00 */
            $result = random_int(10000, 25000) / 100;
        }
        return $result;
    }

    /**
     * Return 'true' with probability of $percent %.
     *
     * @param int $percent
     * @return bool
     * @throws \Exception
     */
    private function randomPercent(int $percent): bool
    {
        $result = (random_int(0, 99) < $percent);
        return $result;
    }

    /**
     * Create new sale order in Flectra related table.
     *
     * @return int
     */
    private function saleCreate(): int
    {
        $sale = new EFlectraSale();
        $this->em->persist($sale);
        $this->em->flush($sale);
        $result = $sale->id;
        return $result;
    }

    public function salesAdd()
    {
        $result = [];
        /* one sale order (CV movement) for every 10 clients */
        $countDistrs = count($this->mapClientTypeDistr);
        $countCusts = count($this->mapClientTypeCust);
        $total = $countDistrs + $countCusts;
        $movements = intdiv($total, 10);

        /* if total < 10 then 25% for one movement */
        if (($movements < 1) && $this->randomPercent(25))
            $movements = 1;

        /* make CV movements */
        /* 75% - should be a movement for distributor */
        $normDistr = $countDistrs * 75;
        $normCust = $countCusts * 25;
        $percentDistr = round(($normDistr / ($normDistr + $normCust)) * 100);
        for ($i = 0; $i < $movements; $i++) {
            /* add source sale order into Flectra table */
            $saleId = $this->saleCreate();

            /* register CV */
            $isDistr = $this->randomPercent($percentDistr);
            if ($isDistr) {
                $key = random_int(0, $countDistrs - 1);
                $clientId = $this->mapClientTypeDistr[$key];
            } else {
                $key = random_int(0, $countCusts - 1);
                $clientId = $this->mapClientTypeCust[$key];
            }
            /* random amount of CV (15%: 30.00 - 100.00; 85%: 100.00 - 250.00) */
            $cv = $this->randomCvAmount();
            $isAutoship = $this->randomPercent(70); // 70% - is autoship
            $req = new \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Request();
            $req->clientId = $clientId;
            $req->volume = $cv;
            $req->date = $this->dateModify();
            if ($isAutoship)
                $req->isAutoship = true;
            $req->sourceId = $saleId;
            $req->sourceType = Cfg::CV_REG_SOURCE_SALE;
            $this->srvCvReg->exec($req);
            $this->mapSale[$saleId] = [$cv, $isAutoship, $clientId];
            $result[$saleId] = [$cv, $isAutoship, $clientId];
        }
        return $result;
    }

    public function salesClawback()
    {
        $saleId = $cv = $isAutoship = $clientId = null;
        $countSales = count($this->mapSale) - 1;
        if ($countSales > 1) {
            /* there are sale orders to clawback */
            $keySale = random_int(0, $countSales);
            $keys = array_keys($this->mapSale);
            $saleId = $keys[$keySale];
            $saleData = $this->mapSale[$saleId];
            $cv = $saleData[0];
            $isAutoship = $saleData[1];
            $clientId = $saleData[2];

            $req = new \Praxigento\Milc\Bonus\Api\Service\Bonus\Registry\Cv\Request();
            $req->clientId = $clientId;
            $req->volume = $cv;
            $req->date = $this->dateModify();
            if ($isAutoship)
                $req->isAutoship = true;
            $req->sourceId = $saleId;
            $req->sourceType = Cfg::CV_REG_SOURCE_SALE_BACK;
            $this->srvCvReg->exec($req);
            unset($this->mapSale[$saleId]);
            $this->mapSaleClawback[$saleId] = [$cv, $isAutoship, $clientId];
        }
        return [$saleId, $cv, $isAutoship, $clientId];
    }
}