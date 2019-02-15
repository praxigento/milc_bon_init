<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Api\Helper\Emulate;

/**
 * Emulate clients activity (downline composition & sales orders submission).
 */
interface Activity
{
    /**
     * Change parent for random selected client (except root client).
     *
     * @return array
     */
    public function clientChangeParent();

    /**
     * Change type for client (distr/cust).
     * @return array
     */
    public function clientChangeType();

    /**
     * Create new client and place it into the downline tree.
     *
     * @param int $percentIsCust 0..100 new client will not be a distributor.
     * @return array
     */
    public function clientCreate($percentIsCust);

    /**
     * Delete random client (except root).
     *
     * @return array
     */
    public function clientDelete();

    /**
     * Restore previously deleted client (except root).
     *
     * @return array
     */
    public function clientRestore();

    /**
     * Add sales batch and register related CV.
     *
     * @return array - [$saleId] => [$amount, $isAutoship, $clientId];
     */
    public function salesAdd();

    /**
     * Add batch with sales clawbacks.
     *
     * @return array
     */
    public function salesClawback();
}