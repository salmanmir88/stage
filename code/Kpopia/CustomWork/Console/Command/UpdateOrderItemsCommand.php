<?php

namespace Kpopia\CustomWork\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Kpopia\CustomWork\Helper\Data as CustomWorkHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOrderItemsCommand extends Command
{
    protected $state;
    protected $orderItemCollectionFactory;
    protected $customWorkHelper;

    public function __construct(
        State $state,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        CustomWorkHelper $customWorkHelper
    ) {
        $this->state = $state;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->customWorkHelper = $customWorkHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kpopia:update-order-items');
        $this->setDescription('Update total quantity for all order items');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

            $orderItemCollection = $this->orderItemCollectionFactory->create();
            $orderItemCollection->addFieldToSelect(['item_id', 'sku']);

            foreach ($orderItemCollection as $orderItem) {
                $sku = $orderItem->getSku();
                $totalSoldQty = $this->customWorkHelper->getSoldProductCount($sku);

                // Update the total_qty column in sales_order_item table
                $orderItem->setTotalQty($totalSoldQty);
                $orderItem->save();

                $output->writeln(sprintf("Updated SKU: %s with Total Qty: %d", $sku, $totalSoldQty));
            }

            $output->writeln('Update completed successfully.');
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>An error occurred: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
