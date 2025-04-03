<?php

declare(strict_types=1);

namespace Amasty\Sorting\Api;

/**
 * @api
 */
interface ProductReviewLinkProcessorInterface
{
    /**
     * @param int $productId
     * @param int $reviewId
     */
    public function create(int $productId, int $reviewId): void;

    /**
     * @param int $productId
     * @param int $reviewId
     */
    public function remove(int $productId, int $reviewId): void;
}
