<?php

namespace Platon\PlatonPay\Model\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/platon_callback.log';
}
