<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\ResourceModel;

use Amasty\Sorting\Api\ProductReviewLinkProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DuplicateException;

class ProductReviewLinkProcessor implements ProductReviewLinkProcessorInterface
{
    const TABLE_NAME = 'amasty_sorting_reviews_link';
    const PRODUCT_ID = 'product_id';
    const REVIEW_ID = 'review_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function create(int $productId, int $reviewId): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $connection->insert(
                $this->getTableName(),
                [
                    self::PRODUCT_ID => $productId,
                    self::REVIEW_ID => $reviewId,
                ]
            );
        } catch (DuplicateException $e) {
            $this->remove($productId, $reviewId);
        }
    }

    public function getTableName(): string
    {
        return $this->resourceConnection->getTableName(self::TABLE_NAME);
    }

    public function remove(int $productId, int $reviewId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->getTableName(),
            [
                $connection->prepareSqlCondition(self::REVIEW_ID, $reviewId),
                $connection->prepareSqlCondition(self::PRODUCT_ID, $productId)
            ]
        );
    }
}
