<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Repo\Data\Bonus\Base\Qualification\Rule\Group;


/**
 * Grouping rules.
 *
 * @Entity
 * @Table(name="bon_base_qual_rule_group_ref")
 */
class Ref
    extends \TeqFw\Lib\Data
{
    const GROUPED_REF = 'grouped_ref';
    const GROUPING_REF = 'grouping_ref';

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $grouped_ref;
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     */
    public $grouping_ref;
}