<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Db\Data\Bonus\Cv\Registry\Sale;


/**
 * Registry links between CV/ACV movements & sales orders.
 *
 * @Entity
 * @Table(name="bon_cv_reg_sale_back")
 */
class Back
    extends \TeqFw\Lib\Data
{
    const REGISTRY_REF = 'registry_ref';
    const SOURCE_REF = 'source_ref';

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $registry_ref;
    /**
     * @var int
     * @Column(type="integer")
     */
    public $source_ref;

}