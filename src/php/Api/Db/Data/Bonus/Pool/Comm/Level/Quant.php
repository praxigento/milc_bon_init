<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Pool\Comm\Level;


/**
 * Links to CV movements related to commission been paid.
 *
 * @Entity
 * @Table(name="bon_pool_comm_level_quant")
 */
class Quant
    extends \TeqFw\Lib\Data
{
    public const COMM_REF = 'comm_ref';
    public const CV_REG_REF = 'cv_reg_ref';
    public const VALUE = 'value';
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $comm_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $cv_reg_ref;
    /**
     * @var float
     * @Column(type="decimal", precision=10, scale=2)
     */
    public $value;
}