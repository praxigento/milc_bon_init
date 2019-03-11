<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Service\Client\Add;


class Request
    extends \TeqFw\Lib\Data
{
    /** @var int */
    public $clientId;
    /** @var string */
    public $date;
    /** @var int */
    public $enrollerId;
    /** @var bool */
    public $isNotDistributor;
    /** @var string */
    public $mlmId;
    /**
     * Used for binary trees only. 'true' - place new client into the left leg in downline,
     * 'false' - into the right leg. Ignored if downline tree is not binary.
     *
     * @var bool
     */
    public $placeToLeft;
    /**
     * TODO: remove this tmp attribute, tree type should be configured from inside service.
     * @var string
     */
    public $treeType;
}