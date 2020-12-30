<?php

namespace Platon\PlatonPay\Controller\Cancel;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;

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

    /**
     * @var SessionManager
     */
    protected $session_manager;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Index constructor.
     *
     * @param Context        $context
     * @param PageFactory    $pageFactory
     * @param Session        $session
     * @param SessionManager $session_manager
     * @param Order          $order
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        SessionManager $session_manager,
        Order $order
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->session_manager = $session_manager;
        $this->order = $order;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $this->session->setQuoteId($this->session->getPlatonQuoteId());
        if ($this->session->getLastRealOrder()) {
            $order = $this->order->loadByIncrementId($this->session->getLastRealOrder()
                ->getId());
            if ($order->getId()) {
                $order->cancel()
                    ->save();
            }
        }

        $this->_redirect('checkout/cart')
            ->sendResponse();
    }
}
