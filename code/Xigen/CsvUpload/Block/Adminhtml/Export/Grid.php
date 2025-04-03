<?php

namespace Xigen\CsvUpload\Block\Adminhtml\Export;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Filesystem;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\CollectionFactory;

class Grid extends Extended
{
    protected $filesystem;
    protected $collectionFactory;
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        BackendHelper $backendHelper,
        Filesystem $filesystem,
        array $data = []
    ) {
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data); // Pass context and backendHelper to parent
    }

    protected function _prepareCollection()
    {
        $directory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $customCsvDir = $directory->getAbsolutePath() . 'import/outputcsv/';
        if (!is_dir($customCsvDir)) {
            mkdir($customCsvDir, 0777, true);
        }

        // Now list files
        $files = array_diff(scandir($customCsvDir), ['..', '.']);

        // Ensure CollectionFactory is used to create a valid collection
        $collection = $this->collectionFactory->create();

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                $item = new \Magento\Framework\DataObject(['file_name' => $file]);
                $collection->addItem($item);
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }



    protected function _prepareColumns()
    {
        $this->addColumn('file_name', [
            'header' => __('File Name'),
            'index' => 'file_name', // Ensure 'file_name' matches the key in your collection
        ]);

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'renderer' => \Xigen\CsvUpload\Block\Adminhtml\Export\Renderer\Actions::class,
            'filter' => false,
            'sortable' => false,
        ]);

        return parent::_prepareColumns();
    }
}
