<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Service\Bonus\Event\Log;

use Praxigento\Milc\Bonus\Api\Config as Cfg;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log as ELog;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Delete as ELogDwnlDelete;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Tree as ELogDwnlTree;
use Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Event\Log\Dwnl\Type as ELogDwnlType;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Request as ARequest;
use Praxigento\Milc\Bonus\Service\Bonus\Qualification\Simple\Response as AResponse;

class Add
{
    /** @var \TeqFw\Lib\Db\Api\Dao\Entity\Anno */
    private $dao;

    public function __construct(
        \TeqFw\Lib\Db\Api\Dao\Entity\Anno $dao
    ) {
        $this->dao = $dao;
    }

    /**
     * Create new log event entry (w/o details).
     *
     * @param string $type
     * @param \DateTime|string $date
     * @return int
     */
    private function createLog($type, $date)
    {
        $entity = new ELog();
        $entity->date = $date;
        $entity->type = $type;
        $result = $this->dao->create($entity);
        return $result;
    }

    public function exec($req)
    {
        assert($req instanceof ARequest);
        $date = $req->date;
        $details = $req->details;

        $type = $this->getDetailsType($details);
        $id = $this->createLog($type, $date);
        /* all log details should have a 'log_ref' attribute */
        $details->log_ref = $id;
        $this->dao->create($details);

        $result = new AResponse();
        return $result;
    }

    private function getDetailsType($details)
    {
        $result = 'n/a';
        $class = get_class($details);
        switch ($class) {
            case ELogDwnlDelete::class:
                $result = Cfg::EVENT_LOG_TYPE_DWNL_DELETE;
                break;
            case ELogDwnlTree::class:
                $result = Cfg::EVENT_LOG_TYPE_DWNL_TREE;
                break;
            case ELogDwnlType::class:
                $result = Cfg::EVENT_LOG_TYPE_DWNL_TYPE;
                break;
        }
        return $result;
    }
}