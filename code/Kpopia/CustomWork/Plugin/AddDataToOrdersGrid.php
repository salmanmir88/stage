<?php

namespace Kpopia\CustomWork\Plugin;

use Magento\Framework\Data\Collection\AbstractDb;
use Psr\Log\LoggerInterface;

class AddDataToOrdersGrid
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param LoggerInterface $customLogger
     */
    public function __construct(LoggerInterface $customLogger)
    {
        $this->logger = $customLogger;
    }

    /**
     * Add products' names column to orders grid.
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param AbstractDb $collection
     * @param string $requestName
     * @return AbstractDb
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }

        if (
            !$collection instanceof AbstractDb ||
            $collection->getMainTable() !== $collection->getResource()->getTable('sales_order_grid')
        ) {
            return $collection;
        }

        try {
            $this->addManufacturerColumn($collection);
        } catch (\Zend_Db_Select_Exception $selectException) {
            $this->logger->error($selectException->getMessage());
        }

        return $collection;
    }

    /**
     * Adds products name column to the orders grid collection.
     *
     * @param AbstractDb $collection
     * @return void
     */
    private function addManufacturerColumn(AbstractDb $collection): void
    {
        $connection = $collection->getConnection();
        $resource = $collection->getResource();

        $orderItemsTable = $resource->getTable('sales_order_item');
        $productEntityTable = $resource->getTable('catalog_product_entity');
        $eavAttributeTable = $resource->getTable('eav_attribute');
        $eavOptionTable = $resource->getTable('eav_attribute_option');
        $eavOptionValueTable = $resource->getTable('eav_attribute_option_value');

        // Get Manufacturer Attribute ID
        $attributeData = $connection->fetchRow(
            $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', 'manufacturer')
                ->where('entity_type_id = ?', 4) // Entity type 4 = products
        );

        if (!$attributeData) {
            $this->logger->warning('Manufacturer attribute not found.');
            return;
        }

        $attributeId = $attributeData['attribute_id'];

        // Subquery to get manufacturer option labels
        $itemsTableSelectGrouped = $connection->select()
            ->from(
                ['soi' => $orderItemsTable],
                ['order_id']
            )
            ->joinLeft(
                ['cpe' => $productEntityTable],
                'soi.product_id = cpe.entity_id',
                []
            )
            ->joinLeft(
                ['cpei' => $resource->getTable('catalog_product_entity_int')],
                "cpe.entity_id = cpei.entity_id AND cpei.attribute_id = $attributeId",
                []
            )
            ->joinLeft(
                ['eaopt' => $eavOptionTable],
                'cpei.value = eaopt.option_id',
                []
            )
            ->joinLeft(
                ['eaopv' => $eavOptionValueTable],
                'eaopt.option_id = eaopv.option_id AND eaopv.store_id = 0', // Default store scope
                ['manufacturer' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT eaopv.value SEPARATOR ", ")')]
            )
            ->group('soi.order_id');

        // Join the subquery with the main grid collection
        $collection->getSelect()->joinLeft(
            ['soi' => $itemsTableSelectGrouped],
            'soi.order_id = main_table.entity_id',
            ['manufacturer']
        );
    }
}
