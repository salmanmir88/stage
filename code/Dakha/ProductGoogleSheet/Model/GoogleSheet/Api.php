<?php


namespace Dakha\ProductGoogleSheet\Model\GoogleSheet;


use Magento\Framework\App\Filesystem\DirectoryList;

class Api
{
    /**
     * @var \Google_Client $_client
     */
    private $_client;

    /**
     * @var \Dakha\ProductGoogleSheet\Helper\Data
     */
    private $_dataHelper;

    /**
     * @var \Dakha\ProductGoogleSheet\Model\SheetFactory
     */
    protected $sheetFactory;

    /**
     * ContactForm constructor.
     * @param \Dakha\ProductGoogleSheet\Helper\Data $dataHelper
     * @param \Dakha\ProductGoogleSheet\Model\SheetFactory $sheetFactory
     */
    public function __construct(
        \Dakha\ProductGoogleSheet\Helper\Data $dataHelper,
        \Dakha\ProductGoogleSheet\Model\SheetFactory $sheetFactory
    )
    {
        $this->_dataHelper = $dataHelper;
        $this->sheetFactory = $sheetFactory;
    }

    public function append($contact)
    {
        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $range = date('Y-m-d', time());
        $sheet = $this->sheetFactory->create();
        $sheet->setSheetName($range);
        $sheet->save();
        
        $spreadsheetId = $this->_dataHelper->getProductSheetId();
        try {
            return $this->appendRow($service, $spreadsheetId, $range, $contact);
        } catch (\Exception $ex) {
            
        }
    }
    private function appendRow($service, $spreadsheetId, $range, $contact)
    {
        
        $data = [];
        
        array_push(
            $data,
            new \Google\Service\Sheets\ValueRange([
                'range' => 'A1',
                'values' => $contact
            ])
        );
        
        $body = new \Google\Service\Sheets\BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data
        ]);
        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);
    }
    private function getClient()
    {
        if (!$this->_client) {
            $credential = $this->_dataHelper->getCredential();
            $client = new \Google_Client();
            $client->setScopes([
                \Google_Service_Sheets::SPREADSHEETS,
            ]);
            
            $client->setAuthConfig($credential);
            $this->_client = $client;
        }

        return $this->_client;
    }
}
