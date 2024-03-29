<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory;

/**
 * Updates the coupon usages
 */
class UpdateCouponUsages
{
    /**
     * @var CouponUsageProcessor
     */
    private $couponUsageProcessor;

    /**
     * @var UpdateInfoFactory
     */
    private $updateInfoFactory;

    /**
     * @param CouponUsageProcessor $couponUsageProcessor
     * @param UpdateInfoFactory $updateInfoFactory
     */
    public function __construct(
        CouponUsageProcessor $couponUsageProcessor,
        UpdateInfoFactory $updateInfoFactory
    ) {
        $this->couponUsageProcessor = $couponUsageProcessor;
        $this->updateInfoFactory = $updateInfoFactory;
    }

    /**
     * Executes the current command
     *
     * @param OrderInterface $subject
     * @param bool $increment
     * @return OrderInterface
     */
    public function execute(OrderInterface $subject, bool $increment): OrderInterface
    {
        if (!$subject || !$subject->getAppliedRuleIds()) {
            return $subject;
        }

        /** @var UpdateInfo $updateInfo */
        $updateInfo = $this->updateInfoFactory->create();
        $appliedRuleIds = explode(',', $subject->getAppliedRuleIds());
        $appliedRuleIds = array_filter(array_map('intval', array_unique($appliedRuleIds)));
        $updateInfo->setAppliedRuleIds($appliedRuleIds);
        $updateInfo->setCouponCode((string)$subject->getCouponCode());
        $updateInfo->setCustomerId((int)$subject->getCustomerId());
        $updateInfo->setIsIncrement($increment);

        if ($subject->getOrigData('coupon_code') !== null && $subject->getStatus() !== Order::STATE_CANCELED) {
            $updateInfo->setCouponAlreadyApplied(true);
        }

        $this->couponUsageProcessor->process($updateInfo);

        return $subject;
    }
}
