<?php

namespace Bananacode\Kip\Model;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\Store;
use Bananacode\Kip\Api\ReportsInterface;
use Monolog\Handler\RotatingFileHandler;

/**
 *
 */
class Reports implements ReportsInterface
{
    const XML_PATH_TRANS_EMAIL_GENERAL_EMAIL = 'trans_email/ident_general/email';

    const XML_PATH_TRANS_EMAIL_GENERAL_NAME = 'trans_email/ident_general/name';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig,
        \Magento\Framework\App\Response\Http\FileFactory    $fileFactory,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\Mail\Template\TransportBuilder   $transportBuilder,
        \Magento\Wishlist\Model\Wishlist                    $wishlist,
        \Magento\Catalog\Model\ProductRepository            $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface   $customerRepository
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_fileFactory = $fileFactory;
        $this->_orderCollection = $orderCollection;
        $this->_transportBuilder = $transportBuilder;
        $this->_wishlist = $wishlist;
        $this->_productRepository = $productRepository;
        $this->_customerRepository = $customerRepository;

        $logHandler = new RotatingFileHandler(BP . '/var/log/reports.log');
        $this->_logger = new \Monolog\Logger('Reports');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Customer recurring report
     *
     * @param integer $id
     * @param string $sort
     * @param string $from
     * @param string $to
     * @param string $email
     * @param string $name
     * @return mixed
     */
    public function recurring($id, $sort, $from = '', $to = '', $email = '', $name = '')
    {
        try {
            $customer = $this->_customerRepository->getById($id);
        } catch (\Exception $e) {
            return;
        }

        $orders = $this->_orderCollection
            ->addFieldToFilter('customer_id', $id);

        if($from != 'empty' && $to != 'empty') {
            $orders
                ->addFieldToFilter('created_at', ['gteq' => date('Y-m-d H:i:s', strtotime($from))])
                ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d H:i:s', strtotime($to))]);
        }

        $orders = $orders->getItems();

        $recurring = [];

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */
            foreach ($order->getAllVisibleItems() as $item) {
                if (!isset($recurring[$item->getProductId()])) {
                    $product = $item->getProduct();
                    if($product) {
                        if($product->getVisibility() == Visibility::VISIBILITY_BOTH) {
                            $brand = $product->getCustomAttribute('ls_marca');
                            $presentation = $product->getCustomAttribute('ls_presentacion');
                            $recurring[$item->getProductId()] = [
                                'name' => $product->getName(),
                                'presentation' => $presentation ? $presentation->getValue() : '',
                                'brand' => $brand ? $brand->getValue() : '',
                                'purchases' => 1
                            ];
                        }
                    }
                } else {
                    $recurring[$item->getProductId()]['purchases'] += 1;
                }
            }
        }

        if(count($recurring) > 0) {
            $this->generateReportPDF($customer, $this->asort($recurring, $sort), 'habituales', $id, $email, $name);
        }
    }

    /**
     * Customer wishlist report
     *
     * @param integer $id
     * @param string $sort
     * @param string $from
     * @param string $to
     * @param string $email
     * @param string $name
     * @return mixed
     */
    public function wishlist($id, $sort, $from = '', $to = '', $email = '', $name = '')
    {
        try {
            $customer = $this->_customerRepository->getById($id);
        } catch (\Exception $e) {
            return;
        }

        $wishlist = $this->_wishlist->loadByCustomerId($id);
        if ($wishlist) {
            $wishes = [];

            /**
             * @var \Magento\Wishlist\Model\Item $item
             */

            $wishesCollection = $wishlist
                ->getItemCollection();

            if($from != 'empty' && $to != 'empty') {
                $wishesCollection
                    ->addFieldToFilter('added_at', ['gteq' => date('Y-m-d H:i:s', strtotime($from))])
                    ->addFieldToFilter('added_at', ['lteq' => date('Y-m-d H:i:s', strtotime($to))]);
            }

            $wishesCollection = $wishesCollection->getItems();

            foreach ($wishesCollection as $item) {
                try {
                    $product = $this->_productRepository->getById($item->getProductId());
                    if($product) {
                        if($product->getVisibility() == Visibility::VISIBILITY_BOTH) {
                            $brand = $product->getCustomAttribute('ls_marca');
                            $presentation = $product->getCustomAttribute('ls_presentacion');
                            $wishes[] = [
                                'name' => $product->getName(),
                                'presentation' => $presentation ? $presentation->getValue() : '',
                                'brand' => $brand ? $brand->getValue() : ''
                            ];
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            if(count($wishes) > 0) {
                $this->generateReportPDF($customer, $this->asort($wishes, $sort), 'favoritos', $id, $email, $name);
            }
        }
    }

    /**
     * LS Power Bi Orders Data
     *
     * @param string $from
     * @param string $to
     * @param string $status
     * @param integer $count
     * @param integer $page
     *
     * @return mixed
     */
    public function lspowerbiorders($from, $to, $status, $count, $page) {
        $orders =
            $this->_orderCollection
                ->setPageSize($count)
                ->setPage($page, $count);

        if($from != 'empty' && $to != 'empty') {
            $orders
                ->addFieldToFilter('created_at', ['gteq' => date('Y-m-d H:i:s', strtotime($from))])
                ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d H:i:s', strtotime($to))]);
        }

        if($status != 'all') {
            $orders
                ->addFieldToFilter('status', ['eq' => $status]);
        }

        $result = [];

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            $result[] = [
                "magento_id" => $order->getId(),
                "ls_id" => $order->getDocumentId(),
                "created_at" => $order->getCreatedAt(),
                "status" => $order->getStatus(),
                "client_id" => $order->getCustomerId(),
                "client_name" => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                "shipping_code" => $order->getShippingMethod(),
                "shipping_title" => $order->getShippingDescription(),
                "delivery_datetime" => $order->getKippingDelivery(),
                "latitude" => $order->getAddressLatitude(),
                "longitude" => $order->getAddressLongitude(),
                "subtotal" => $order->getBaseSubtotal(),
                "discount" => $order->getBaseDiscountAmount(),
                "taxes" => $order->getBaseTaxAmount(),
                "shipping" => $order->getBaseShippingAmount(),
                "total" => $order->getBaseGrandTotal(),
                "items_count" => count($order->getAllVisibleItems())
            ];
        }

        $this->jsonResponse(["status" => 200, "output" => [
            "orders" => $result,
            "page" => $page,
            "pages-left" => ($orders->getLastPageNumber() - $page),
        ]]);
    }

    /**
     * @param $customer \Magento\Customer\Api\Data\CustomerInterface
     * @param $items
     * @param $title
     * @param $customerId
     * @param $email
     * @param $name
     * @throws \Zend_Pdf_Exception
     */
    private function generateReportPDF($customer, $items, $title, $customerId, $email, $name)
    {
        $this->_logger->addInfo($customer->getFirstname());
        $this->_logger->addInfo($title);

        $tmpLogoPath = $this->getLogoPath($this->_scopeConfig->getValue('bananacode/reports/logo'));
        $logoMarket = null;
        try {
            $logoMarket = \Zend_Pdf_Image::imageWithPath($tmpLogoPath);
            unlink($tmpLogoPath);
        } catch (\Exception $e) {
        }

        $pages = 0;
        $pdf = new \Zend_Pdf();

        $style = new \Zend_Pdf_Style();
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_COURIER);
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $style->setFont($font, 7);

        $styleBold = new \Zend_Pdf_Style();
        $fontBold = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_COURIER_BOLD);
        $styleBold->setLineColor(new \Zend_Pdf_Color_Rgb(51, 190, 129));
        $styleBold->setFont($fontBold, 9);

        $styleTitle = new \Zend_Pdf_Style();
        $styleTitle->setLineColor(new \Zend_Pdf_Color_Rgb(51, 190, 129));
        $styleTitle->setFont($fontBold, 14);

        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[$pages];
        $page->setStyle($style);

        $x2 = $page->getWidth() - (30);
        $col1 = 30;

        if ($logoMarket) {
            $page->drawImage($logoMarket, ($x2 - 40), 780, $x2, 820);
        }

        /**
         * Add text
         */
        $y1 = 800;
        $page->setStyle($styleTitle);
        $page->drawText(__($this->utf8($customer->getFirstname() . " " . $customer->getLastname() . " para tu facilidad")), $col1, $y1, 'UTF-8');
        $page->drawText(__($this->utf8("te compartimos tus productos " . $title . ":")), $col1, $y1 - 20, 'UTF-8');

        $y1 = 735;
        //$page->drawText(__($this->utf8("Productos")), $col1, $y1, 'UTF-8');

        $y1 = 740;
        $page->setStyle($styleBold);
        $page->drawText(__($this->utf8("Nombre de producto")), $col1, $y1, 'UTF-8');
        $page->drawText(__($this->utf8("Presentación")), $col1 + 220, $y1, 'UTF-8');
        $page->drawText(__($this->utf8("Marca")), $col1 + 320, $y1, 'UTF-8');
        if ($title !== 'favoritos') {
            $page->drawText(__($this->utf8("Compras")), $col1 + 430, $y1, 'UTF-8');
        }
        $page->drawText(__($this->utf8("")), $col1 + 490, $y1, 'UTF-8');

        $page->setStyle($style);
        foreach ($items as $item) {
            if (($y1 - 30) <= 0) {
                $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                $pages++;
                $page = $pdf->pages[$pages];
                $page->setStyle($style);
                $y1 = 800;

                if ($logoMarket) {
                    $page->drawImage($logoMarket, ($x2 - 40), 780, $x2, 820);
                }
            }

            $y1 -= 15;

            $page->drawText(__($this->utf8($item['name'])), $col1, $y1, 'UTF-8');
            $page->drawText(__($this->utf8($item['presentation'])), $col1 + 220, $y1, 'UTF-8');
            $page->drawText(__($this->utf8($item['brand'])), $col1 + 320, $y1, 'UTF-8');
            if ($title !== 'favoritos') {
                $page->drawText(__($this->utf8($item['purchases'])), $col1 + 430, $y1, 'UTF-8');
            }
            $page->drawText(__($this->utf8('[  ]')), $col1 + 490, $y1, 'UTF-8');
        }

        $fileName = 'reports/' . $title . $customerId . '.pdf';
        $this->_fileFactory->create(
            $fileName,
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::PUB,
            'application/pdf'
        );

        if($email != 'empty' && $name != 'empty') {
            $this->sendEmail($pdf->render(), $fileName, urldecode($email), urldecode($name), $title);
        }
    }

    /**
     * @param $imageUrl
     * @return string
     */
    private function getLogoPath($imageUrl)
    {
        $imgPath =
            BP . '/' .
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR . '/' .
            \Magento\Framework\App\Filesystem\DirectoryList::TMP . '/' .
            'tmpreportlogo.' .
            pathinfo($imageUrl, PATHINFO_EXTENSION);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $raw = curl_exec($ch);

        if (is_file($imgPath)) {
            unlink($imgPath);
        }

        $fp = fopen($imgPath, 'x');
        fwrite($fp, $raw);
        fclose($fp);

        return $imgPath;
    }

    /**
     * @param $text
     * @return false|string
     */
    private function utf8($text)
    {
        $encoding = mb_detect_encoding($text, mb_detect_order(), false);

        if ($encoding == "UTF-8") {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $out = iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);

        return $out;
    }

    /**
     * @param $pdf
     * @param $file
     * @param $email
     * @param $name
     * @param $subject
     */
    private function sendEmail($pdf, $file, $email, $name, $subject)
    {
        try {
            $transport =
                $this->_transportBuilder
                    ->setTemplateIdentifier('customer_report')
                    ->setTemplateOptions([
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => Store::DISTRO_STORE_ID
                    ])
                    ->setTemplateVars([
                        'type' => $subject,
                        'name' => $name,
                        'subject' => "Kip: Reporte de " . $subject
                    ])
                    ->setFromByScope(
                        [
                            'email' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID),
                            'name' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID)
                        ],
                        1
                    )
                    ->addTo($email, $name);

            $transport = $transport->getTransport();
            $html = $transport->getMessage()->getBody()->generateMessage("\n");
            $bodyMessage = new \Zend\Mime\Part(\Zend_Mime::encode(quoted_printable_decode($html), 'utf-8', "\n"));
            $bodyMessage->type = 'text/html';
            $attachment = $this->_transportBuilder->addAttachment($pdf, $file);

            $bodyPart = new \Zend\Mime\Message();
            $bodyPart->setParts([$bodyMessage, $attachment]);
            $transport->getMessage()->setBody($bodyPart);
            $transport->sendMessage();

            unlink(BP . '/pub/' . $file);
        } catch (\Exception $ex) {
            $this->_logger->addError(print_r($ex->getMessage(), true));
        }
    }

    /**
     * @param $money
     * @param string $s
     * @return string
     */
    private function money($money, $s = "c")
    {
        return $s . number_format((float)$money, 2, '.', ' ');
    }

    /**
     * @param $money
     * @return string
     */
    private function utf8Money($money)
    {
        $text = number_format((float)$money, 2, '.', ' ');

        $encoding = mb_detect_encoding($text, mb_detect_order(), false);

        if ($encoding == "UTF-8") {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $out = iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);

        return '₡' . $out;
    }

    /**
     * @param $page
     * @param $text
     * @param $x
     * @param $y
     * @param null $type
     * @throws \Zend_Pdf_Exception
     */
    private function write($page, $text, $x, $y, $type = null)
    {
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        if (strpos($type, 'bold') !== false) {
            $style->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES_BOLD), 12);
            $page->setStyle($style);
        }
        $page->drawText($this->utf8($text), $x, $y, 'UTF-8');

        $style->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES), 10);
        $page->setStyle($style);
    }

    /**
     * @param $array
     * @param $orderBy
     * @return mixed
     */
    private function asort(&$array, $orderBy){
        $orderBy = explode('_', $orderBy);
        $sortArray = array();
        foreach ($array as $item) {
            foreach ($item as $key => $value) {
                if (!isset($sortArray[$key])) {
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }
        if($orderBy[1] == 'asc') {
            array_multisort($sortArray[$orderBy[0]], SORT_ASC, $array);
        } else {
            array_multisort($sortArray[$orderBy[0]], SORT_DESC, $array);
        }
        return $array;
    }

    /**
     * @param $response
     */
    private function jsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
