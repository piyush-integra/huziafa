<?php

defined('ABSPATH') or die('Forbidden');

class CriteoTagsGenerator
{
    private $params = array();
    private $dataLayer = array();
    private $deviceType = 'deviceType';
    private $EOL = PHP_EOL;

    function __construct($account)
    {
        $this->setAccount($account);
        $this->setSiteType();
    }

    private function setAccount($account)
    {
        $this->add_param('setAccount', array('account' => $account));
    }

    private function setSiteType()
    {
        $this->add_param('setSiteType', array('type' => $this->deviceType));
    }

    public function setEmail($email)
    {
        $this->add_param('setEmail', array('email' => $email));
    }

    public function viewHome()
    {
        $this->add_param('viewHome', []);
    }

    public function viewList($productIds)
    {
        $this->add_param('viewList', array('item' => $productIds));
    }

    public function viewItem($product_id)
    {
        $this->add_param('viewItem', array('item' => $product_id));
    }

    public function viewBasket($products)
    {
        $this->add_param('viewBasket', $products);
    }

    public function trackTransaction($transactionId, $items)
    {
        $this->add_param('trackTransaction', array(
            'id' => $transactionId,
            'item' => $items
        ));
    }

    private function add_param($event, $array)
    {
        $param = array('event' => $event);

        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $param[$key] = $value;
            }
        }

        $json_encoded_params = json_encode($param);
        array_push($this->params, $json_encoded_params);
        array_push($this->dataLayer, $json_encoded_params);
    }

    public function getTagCode()
    {
        $separator = "," . PHP_EOL . str_repeat(' ', 18);
        $jsonTags = $this->format($separator, $this->params);

        $code = <<<EOT
            <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
            <script type="text/javascript">
                var {$this->deviceType} = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
                window.criteo_q = window.criteo_q || [];
                window.criteo_q.push(  {$jsonTags} );
            </script>
            $this->EOL
EOT;
        return $code;
    }

    private function format($separator, $text)
    {
        $text = implode($separator, $text);
        //deviceType is a variable in javascript so it must be shown as a variable.
        $text = str_replace("\"" . $this->deviceType . "\"", $this->deviceType, $text);
        return $text;
    }

    public function getAjaxTagCode()
    {
        $code = <<<EOT
            <script type="text/javascript">
               jQuery(document).ajaxComplete(function(event, xhr, settings) {
                   var criteoAjaxHeader = xhr.getResponseHeader('Criteo-Tag');
                   if (criteoAjaxHeader) {
                       var {$this->deviceType} = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
                       criteoAjaxHeader = criteoAjaxHeader.replace('deviceType', $this->deviceType);
                       var parsedValues = JSON.parse(criteoAjaxHeader);
                       window.criteo_q = window.criteo_q || [];
                       window.criteo_q.push.apply(window.criteo_q, parsedValues);
                   }
                });
            </script>
            $this->EOL
EOT;
        return $code;
    }

    public function getJsonTagValues()
    {
        return '[' . implode(',', $this->params) . ']';
    }

}
