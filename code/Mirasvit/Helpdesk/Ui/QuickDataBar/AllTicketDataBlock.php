<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.2.14
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Ui\QuickDataBar;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Mirasvit\Core\Ui\QuickDataBar\ScalarDataBlock;
use Mirasvit\Helpdesk\Api\Data\TicketInterface;
use Mirasvit\Helpdesk\Model\Config;

class AllTicketDataBlock extends ScalarDataBlock
{
    private $resource;

    public function __construct(
        ResourceConnection $resource,
        Template\Context $context
    ) {
        $this->resource = $resource;

        parent::__construct($context);
    }

    public function getCode(): string
    {
        return 'helpdesk_all_tickets';
    }

    public function getLabel(): string
    {
        return (string)__('All Tickets');
    }

    public function getScalarValue(): string
    {
        $select = $this->getSelect();

        $value = (int)$this->resource->getConnection()
            ->fetchOne($select);

        return number_format($value, 0, '.', ' ');
    }

    public function getSelect(array $columns = []): Select
    {
        $columns = array_merge($columns, [
            'value' => new \Zend_Db_Expr('COUNT(' . TicketInterface::KEY_ID . ')'),
        ]);

        return $this->resource->getConnection()
            ->select()
            ->from($this->resource->getTableName(TicketInterface::TABLE_NAME), $columns)
            ->where(TicketInterface::KEY_FOLDER . ' = ' . Config::FOLDER_INBOX);
    }
}
