<?php
 
namespace Platon\PlatonPay\Controller\Success;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Session\SessionManager;

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
     * Index constructor.
     *
     * @param Context        $context
     * @param PageFactory    $pageFactory
     * @param Session        $session
     * @param SessionManager $session_manager
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        SessionManager $session_manager
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->session_manager = $session_manager;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $this->session->setQuoteId($this->session->getPlatonQuoteId());
        $this->session->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
    }
}
