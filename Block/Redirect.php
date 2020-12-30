<?php

namespace Platon\PlatonPay\Block;

use Magento\Framework\View\Element\Template;

class Redirect extends Template
{
    private $data;
    /**
     * @var string $_template
     */
    protected $_template = "Platon_PlatonPay::redirect.phtml";

    public function _toHtml()
    {
        $html = "<html><body>
    <form method='POST' id='platon_checkout' name='platon_checkout'>
        
</form>
</body></html>";
    }
}
