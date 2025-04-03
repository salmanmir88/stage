<?php

namespace Xigen\CsvUpload\Ui\Component\Listing\Column;

/**
 * Xigen CsvUpload CsvActions class
 */
class CsvActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_DELETE = 'xigen_csvupload/csv/delete';
    const URL_PATH_EDIT = 'xigen_csvupload/csv/edit';
    const URL_PATH_DETAILS = 'xigen_csvupload/csv/details';
    const URL_PATH_PROCESS = 'xigen_csvupload/csv/process';
    const URL_PATH_REMOVE = 'xigen_csvupload/csv/remove';
    const URL_PATH_DOWNLOAD = 'xigen_csvupload/csv/download';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['csv_id'])) {
                    $item[$this->getData('name')] = [
                        'download' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DOWNLOAD,
                                [
                                    'csv_id' => $item['csv_id'],
                                ]
                            ),
                            'label' => __('Download'),
                        ],
                        'process' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_PROCESS,
                                [
                                    'csv_id' => $item['csv_id'],
                                ]
                            ),
                            'label' => __('Apply this rule'),
                            'confirm' => [
                                'title' => __('Apply Rule with ID %1', $item['csv_id']),
                                'message' => __('Are you sure you want to apply the Rule with ID %1 ?', $item['csv_id']),
                            ],
                        ],
                        'remove' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_REMOVE,
                                [
                                    'csv_id' => $item['csv_id'],
                                ]
                            ),
                            'label' => __('Remove this Rule'),
                            'confirm' => [
                                'title' => __('Remove ID %1', $item['csv_id']),
                                'message' => __('Are you sure you want to remove ID %1 ?', $item['csv_id']),
                            ],
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'csv_id' => $item['csv_id'],
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "%1"', $item['csv_id']),
                                'message' => __('Are you sure you want to delete a record with ID "%1"  ?', $item['csv_id']),
                            ],
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
