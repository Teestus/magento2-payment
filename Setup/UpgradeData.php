<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@dotpay.pl so we can send you a copy immediately.
 *
 * @author    Dotpay Team <tech@dotpay.pl>
 * @copyright Dotpay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Dotpay\Payment\Setup;

use Dotpay\Payment\Api\Data\OrderInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Installation of required data during installing of the payment module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Installs DB data for a module.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface   $context
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.14', '<')) {
            // Intallation of required statuses
            $availableStatuses = [
                OrderInterface::STATUS_DUPLICATE => __('Dotpay payment possible duplicate'),
                OrderInterface::STATUS_REFUND_NEW => __('Dotpay payment refund initialized'),
                OrderInterface::STATUS_REFUNDED => __('Dotpay payment refunded'),
                OrderInterface::STATUS_REFUND_FAILED => __('Dotpay payment refund failed'),
            ];
            $statusData = [];
            foreach ($availableStatuses as $code => $label) {
                $statusData[] = [
                    'status' => $code,
                    'label' => $label,
                ];
            }
            try {
                $setup->getConnection()->insertArray(
                    $setup->getTable('sales_order_status'),
                    ['status', 'label'],
                    $statusData
                );
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            // Installation of required order states
            $stateData = [
                [
                    'status' => OrderInterface::STATUS_DUPLICATE,
                    'state' => \Magento\Sales\Model\Order::STATE_PROCESSING,
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ], [
                    'status' => OrderInterface::STATUS_REFUND_NEW,
                    'state' => \Magento\Sales\Model\Order::STATE_CLOSED,
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ], [
                    'status' => OrderInterface::STATUS_REFUNDED,
                    'state' => \Magento\Sales\Model\Order::STATE_CLOSED,
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ], [
                    'status' => OrderInterface::STATUS_REFUND_FAILED,
                    'state' => \Magento\Sales\Model\Order::STATE_CLOSED,
                    'is_default' => 1,
                    'visible_on_front' => 1,
                ],
            ];

            try {
                $setup->getConnection()->insertArray(
                    $setup->getTable('sales_order_status_state'),
                    ['status', 'state', 'is_default', 'visible_on_front'],
                    $stateData
                );
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $setup->endSetup();
    }
}
