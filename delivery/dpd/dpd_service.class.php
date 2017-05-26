<?
class DPD_service {

    var $module_id = "rocketstudio.dpdext";

    public $arMSG = array(); // массив-сообщение ('str' => текст_сообщения, 'type' => тип_сообщения (по дефолту: 0 - ошибка)
    private $IS_ACTIVE = 1; // флаг активности сервиса (0 - отключен, 1 - включен)
    private $IS_TEST = 1;// флаг тестирования (0 - работа, 1 - тест)
    private $SOAP_CLIENT; // SOAP-клиент
    private $MY_NUMBER = ''; // ЗАМЕНИТЬ НА СВОЙ!!! - клиентский номер в системе DPD (номер договора с DPD)
    private $MY_KEY = ''; // ЗАМЕНИТЬ НА СВОЙ!!! - уникальный ключ для авторизации
    private $CHARSET_UTF = 0;

    private $arDPD_HOST = array(
        0 => 'ws.dpd.ru', //рабочий хост
        1 => 'wstest.dpd.ru', //тестовый хост
    );

    private $arSERVICE = array(

        'getCitiesCashPay' => '/services/geography2',
        'getTerminalsSelfDelivery2' => '/services/geography2',
        'getParcelShops' => '/services/geography2',

        'getServiceCost' => '/services/calculator2',
        'getServiceCostByParcels' => '/services/calculator2',

        'createOrder' => '/services/order2',
        'getOrderStatus' => '/services/order2',
        'getInvoiceFile' => '/services/order2',
        'cancelOrder' => '/services/order2',

        'createLabelFile' => '/services/label-print',

        'getStatesByClient' => ':80/services/tracing',
        'confirm' => ':80/services/tracing',
        'getStatesByClientOrder' => ':80/services/tracing',
        'getStatesByClientParcel' => ':80/services/tracing',
        'getStatesByDPDOrder' => ':80/services/tracing',

    );

    public function __construct()
    {
        $this->IS_TEST = COption::GetOptionInt($this->module_id, "IS_TEST");
        $this->IS_TEST = $this->IS_TEST ? 1 : 0;
        $this->MY_NUMBER = COption::GetOptionString($this->module_id, "KLIENT_NUMBER");
        $this->MY_KEY = COption::GetOptionString($this->module_id, "KLIENT_KEY");
        $this->POST_TYPE = COption::GetOptionString($this->module_id, "POST_TYPE");
        $this->CHARSET_UTF = intval(SITE_CHARSET != 'UTF-8');
    }

    public function getStatesByClient()
    {
        $obj = $this->_getDpdData('getStatesByClient', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function confirmStates($arData)
    {
        $obj = $this->_getDpdData('confirm', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function getStatesByClientOrder($arData)
    {
        $obj = $this->_getDpdData('getStatesByClientOrder', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function getStatesByClientParcel($arData)
    {
        $obj = $this->_getDpdData('getStatesByClientParcel', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function getStatesByDPDOrder($arData)
    {
        $obj = $this->_getDpdData('getStatesByDPDOrder', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function getCityList($arData)
    {
        $obj = $this->_getDpdData('getCitiesCashPay', $arData);
        $res = $this->_parceObj2Arr( $obj->return, $this->CHARSET_UTF );
        return $res;
    }

    public function getTerminalsList()
    {
        $obj = $this->_getDpdData('getTerminalsSelfDelivery2');
        $res = $this->_parceObj2Arr( $obj->return, $this->CHARSET_UTF );
        return $res;
    }

    public function getParcelShops($arData)
    {
        $obj = $this->_getDpdData('getParcelShops', $arData, 'request');
        $res = $this->_parceObj2Arr( $obj->return, $this->CHARSET_UTF );
        return $res;
    }

    public function getServiceCost($arData)
    {
        $obj = $this->_getDpdData('getServiceCost', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function createOrder($arData)
    {
        $obj = $this->_getDpdData('createOrder', $arData, 'orders');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function cancelOrder($arData)
    {
        $obj = $this->_getDpdData('cancelOrder', $arData, 'orders');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $res;
    }

    public function getOrderStatus($arData)
    {
        $obj = $this->_getDpdData('getOrderStatus', $arData, 'orderStatus');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $obj;
    }

    public function getInvoiceFile($arData)
    {
        $obj = $this->_getDpdData('getInvoiceFile', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $obj;
    }

    public function createLabelFile($arData)
    {
        $obj = $this->_getDpdData('createLabelFile', $arData, 'getLabelFile');
        $res = $this->_parceObj2Arr($obj->return, $this->CHARSET_UTF);
        return $obj;
    }


    private function _connect2Dpd($method_name)
    {
        if(!$this->IS_ACTIVE) return false;
        if(!$service = $this->arSERVICE[$method_name])
        {
            $this->arMSG['str'] = 'В свойствах класса нет сервиса "'.$method_name.'"';
            return false;
        }
        $host = $this->arDPD_HOST[$this->IS_TEST].$service.'?WSDL';
        try
        {// Soap-подключение к сервису
            $this->SOAP_CLIENT = new SoapClient('http://'.$host, array(
							"trace"=>1,
							'exceptions'=>true,
						));
            if(!$this->SOAP_CLIENT) throw new Exception('Error');
        }
        catch(Exception $ex)
        {
            $this->arMSG['str'] = 'Не удалось подключиться к сервисам DPD'.$service;
            return false;
        }
        return true;
    }

    private function _getDpdData($method_name, $arData = array(), $is_request = 0)
    {
			
        if(!$this->_connect2Dpd($method_name))
            return false;

        $arData['auth'] = array(
            'clientNumber' => $this->MY_NUMBER,
            'clientKey' => $this->MY_KEY,
        );

        if($is_request)
            $arRequest[$is_request] = $arData;
        else
            $arRequest = $arData;
					
        try
        {
            $obj = $this->SOAP_CLIENT->$method_name($arRequest);
            if(!$obj)
                throw new Exception('Error');
        }
        catch(Exception $ex)
        {
            $this->arMSG['str'] = 'Не удалось вызвать метод '.$method_name.' / '.$ex;
        }

        return $obj ? $obj : false;
    }

    private function _parceObj2Arr($obj, $isUTF = 1, $arr = array())
    {
        $isUTF = $isUTF ? 1 : 0;

        if(is_object($obj) || is_array($obj))
        {
            $arr = array();
            for(reset($obj); list($k, $v) = each($obj);)
            {
                if($k === "GLOBALS")
                    continue;
                $arr[$k] = $this->_parceObj2Arr($v, $isUTF, $arr);
            }

            return $arr;
        }
        elseif(gettype($obj) == 'boolean')
        {
            return $obj ? 'true' : 'false';
        }
        else
        {
            if($isUTF && gettype($obj) == 'string')
                $obj = iconv('utf-8', 'windows-1251', $obj);
            return $obj;
        }
    }


}
?>