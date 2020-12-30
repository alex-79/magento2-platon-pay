<?php


namespace Platon\PlatonPay\Controller\Process;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Platon\PlatonPay\Model\Logger\Logger;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    const REQUIRED_FIELDS = [
        'id',
        'order',
        'status',
        'rrn',
        'approval_code',
        'description',
        'amount',
        'currency',
        'name',
        'email',
        'country',
        'state',
        'city',
        'address',
        'date',
        'ip',
        'sign',
    ];
    const REQUIRED_CARD_FIELDS = [
        'number',
        'card',
    ];

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
     * @var Logger
     */
    protected $logger;

    /**
     * @var Order
     */
    protected $order;
    protected $order_sender;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Index constructor.
     *
     * @param Context         $context
     * @param PageFactory     $pageFactory
     * @param Session         $session
     * @param SessionManager  $session_manager
     * @param Logger          $logger
     * @param Order           $order
     * @param OrderSender     $order_sender
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        SessionManager $session_manager,
        Logger $logger,
        Order $order,
        OrderSender $order_sender,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->session_manager = $session_manager;
        $this->logger = $logger;
        $this->order = $order;
        $this->order_sender = $order_sender;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Index Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()
            ->getParams();

        $answer = $this->processCallback($data);

        $this->getResponse()
            ->setBody($answer);

        return $this->_response->sendResponse();
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function processCallback($data)
    {
        try {
            $this->logger->info(var_export($data, 1));

            foreach (self::REQUIRED_FIELDS as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = null;
                }
            }

            if (!$this->verifySignature($data)) {
                $this->logger->error("Invalid signature");

                return "ERROR: Bad signature";
            }

            $this->logger->info("Callback signature OK");

            $order = $this->session->getLastRealOrder()
                ->loadByIncrementId($data['order']);

            if (!$order->getId()) {
                // log wrong order
                $this->logger->error("ERROR: Bad order ID");

                return "ERROR: Bad order ID";
            }

            // do processing stuff

            $payment = $order->getPayment();

            $payment
                ->setAmount($data['amount'])
                ->setTransactionId($data['id'])
                ->setPreparedMessage('');

            switch ($data['status']) {
                case 'SALE':
                    $payment->setIsTransactionClosed(1)
                        ->registerCaptureNotification($data['amount'])
                        ->setStatus('platon_payment_successful')
                        ->save();

                    $order->setStatus('platon_payment_successful')
                        ->save();

                    // $this->logger->info(var_export($order, true));
                    $this->logger->info("Order {$data['order']} processed as successfull sale");
                    break;
                case 'REFUND':
                    $order->setStatus('canceled')
                        ->save();

                    $this->logger->info("Order " . $data['order'] . " processed as successfull REFUND");
                    break;
                case 'CHARGEBACK':
                    $order->setStatus('canceled')
                        ->save();

                    $this->logger->info("Order {$data['order']} processed as successfull chargeback");
                    break;
                default:
                    $this->logger->error("Invalid callback data");

                    return "ERROR: Invalid callback data";
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return "OK";
    }

    private function verifySignature($data)
    {
        if (isset($data['card'])) {
            $card = $data['card'];
        } elseif (isset($data['number'])) {
            $card = $data['number'];
        } else {
            return false;
        }

        return $this->getSignature($data['email'], $data['order'], $card) === $data['sign'];
    }

    private function getSignature($email, $order, $card)
    {
        $pass = $this->scopeConfig->getValue('payment/platon_pay/pass');
        $email = strrev($email);
        $card_1 = substr($card, 0, 6);
        $card_2 = substr($card, -4);
        $card = strrev($card_1.$card_2);

        return hash('md5', strtoupper($email.$pass.$order.$card));
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
