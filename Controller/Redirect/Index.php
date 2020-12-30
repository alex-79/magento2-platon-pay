<?php

namespace Platon\PlatonPay\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Session
     */
    protected $session;

    protected $session_manager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Session $session
     * @param SessionManager $session_manager
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        SessionManager $session_manager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->session_manager = $session_manager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    /**
     * Index Action
     */
    public function execute()
    {
        $this->session->setQuoteId($this->session->getPlatonQuoteId());
        $this->session->getLastRealOrder()->setStatus('pending_payment')->save();
        $this->session_manager->setPlatonQuoteId($this->session->getQuoteId());

        $data = $this->getData($this->session->getLastRealOrder()
            ->getIncrementId());
        $html = $this->getHtml($data);

        $this->getResponse()
            ->setBody($html);
    }

    private function getData($order)
    {
        $base_url = $this->storeManager->getStore()
            ->getBaseUrl();
        $data = base64_encode(json_encode([
            'amount' => sprintf("%01.2f", $this->session->getLastRealOrder()
                ->getGrandTotal()),
            'name' => 'Order from ' . $this->storeManager->getStore()
                ->getGroup()
                ->getName(),
            'currency' => $this->session->getLastRealOrder()
                ->getGlobalCurrencyCode(),
        ]));
        $result = [
            'key' => $this->scopeConfig->getValue('payment/platon_pay/key'),
            'payment' => 'CC',
            'data' => $data,
            'url' => $base_url . "platon_platon_pay/success/index",
            'action' => $this->scopeConfig->getValue('payment/platon_pay/url'),
            'email' => $this->session->getLastRealOrder()->getCustomerEmail(),
            'phone' => $this->session->getLastRealOrder()->getShippingAddress()->getTelephone(),
            'order' => $order,
            'first_name' => $this->session->getLastRealOrder()->getShippingAddress()->getFirstname(),
            'last_name' => $this->session->getLastRealOrder()->getShippingAddress()->getLastname(),
        ];

        $result['sign'] = hash(
            'md5',
            strtoupper(
                strrev($result['key']) .
                    strrev($result['payment']) .
                    strrev($result['data']) .
                    strrev($result['url']) .
                    strrev($this->scopeConfig->getValue('payment/platon_pay/pass'))
            )
        );
        return $result;
    }

    private function getHtml($data)
    {
        $html = "<html>
                <body>
                    <form action='" . $data['action'] . "' method='post' name='platon_checkout' id='platon_checkout'>";

        unset($data['action']);

        foreach ($data as $field => $value) {
            $html .= "<input hidden name='" . $field . "' value='" . $value . "'>";
        }

        $html .= "</form>
        " . __('You will be redirected to Platon when you place an order.') . "
        <script type=\"text/javascript\">document.getElementById(\"platon_checkout\").submit();</script>
        </body>
        </html>";

        return $html;
    }
}
