<?
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$iModuleID = "rocketstudio.dpdext";


require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule('iblock') or !CModule::IncludeModule('catalog') or !CModule::IncludeModule("sale"))
{
    die('Îøèáêà çàãðóçêè ');
};

switch ($_REQUEST['dpdext_ajax']){
    case getTerminals:
        getTerminals();
        break;

    case addTerminal:
        addTerminal();
        break;

    case createOrder:
        createOrder();
        break;

    case addOrderDB:
        addOrderDB();
        break;

    case cancelOrder:
        cancelOrder();
        break;

    case cancelOrderDB:
        cancelOrderDB();
        break;

    case ChangeStatusWhenDPDCreated:
        ChangeStatusWhenDPDCreated();
        break;

    case createLabelFile:
        createLabelFile();
        break;

    case getInvoiceFile:
        getInvoiceFile();
        break;

    case status_update:
        status_update();
        break;

    case status_update_order:
        status_update_order();
        break;

    default:
        break;
}

function getTerminals()
{
    $location = $_REQUEST['location'];

    $arr = "";

    if($location)
    {
        $locationDPD = CDeliveryDPD::__GetServiceLocation($location);
        $city = $locationDPD["cityName"];
        $region = $locationDPD["regionName"];

        $arr = CRocketstudioDPDExt::GetTerminal($city, $region);

        if(!$arr)
        {
            $arr = "";
        }
    }

    //$res = $APPLICATION->ConvertCharset($arr, SITE_CHARSET, 'UTF-8');
    $res = json_encode($arr);

    echo $res;
}

function addTerminal()
{
    echo CRocketstudioDPDExt::AddTerminalFromAdmin($_REQUEST['orderID'], $_REQUEST['terminalAddress']);
}

function createOrder()
{
    global $DB, $DBType, $APPLICATION;

    $arr = array();
    $arr["error"] = "";

    $error_text_req = GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_REQ");
    $error_text_br = "<br>";

    $orderArr = array();

    /*********************
    $datePickup
     *********************/
    $orderArr['datePickup'] = ConvertDateTime($_REQUEST["datePickup"], "YYYY-MM-DD", "ru");
    if(!$orderArr['datePickup'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_DATE_ORDER").$error_text_br;
    }
    else
    {
        $dateNow = date("Y-m-d");
        $result = $DB->CompareDates($orderArr['datePickup'], $dateNow);
        if ($result == -1)
        {
            $arr["error"] .= GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_DATE_ORDER2").$error_text_br;
        }
    }

    /*********************
    $orderNumberInternal
     *********************/
    if(!$orderArr['orderNumberInternal'] = $_REQUEST["orderNumberInternal"])
    {
        //$arr["error"] .= $error_text_req."orderNumberInternal".$error_text_br;

        $OrderMaxId = CRocketstudioDPDExt::OrderMaxId();
        if(empty($OrderMaxId))
        {
            $orderArr['orderNumberInternal'] =  1;
        }
        else
        {
            $orderArr['orderNumberInternal'] = $OrderMaxId + 1;
        }
    }

    /*
    $res = json_encode($orderArr);

    echo $res;

    return true;
    */

    /*********************
    $serviceCode
     *********************/
    if(!$orderArr['serviceCode'] = $_REQUEST["serviceCode"])
    {
        $arr["error"] .= $error_text_req."serviceCode".$error_text_br;
    }

    /*********************
    $serviceVariant
     *********************/
    if(!$orderArr['serviceVariant'] = $_REQUEST["serviceVariant"])
    {
        $arr["error"] .= $error_text_req."serviceVariant".$error_text_br;
    }

    /*********************
    $cargoNumPack
     *********************/
    if(!$orderArr['cargoNumPack'] = $_REQUEST["cargoNumPack"])
    {
        $arr["error"] .= $error_text_req."cargoNumPack".$error_text_br;
    }

    /*********************
    $cargoCategory
     *********************/
    if(!$orderArr['cargoCategory'] = $_REQUEST["cargoCategory"])
    {
        $arr["error"] .= $error_text_req."cargoCategory".$error_text_br;
    }

    /*********************
    $cargoValue
     *********************/
    $cargoValue = $_REQUEST['cargoValue'];
    if(!preg_match('/^\+?\d+$/', $cargoValue))
        $arr["error"] .= GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_CARGO_VALUE").GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_INTEGER").$error_text_br;
    if(!$orderArr['cargoValue'] = $cargoValue)
    {
        $orderArr['cargoValue'] = "";
    }

    /*********************
    $cargoWeight
     *********************/
    $cargoWeight = $_REQUEST["cargoWeight"];
    if($cargoWeight == 0 || $cargoWeight == "" || !$cargoWeight)
    {
        $cargoWeight = 2;
    }
    $orderArr['cargoWeight'] = $cargoWeight;

    /*********************
    $receiverAddress
     *********************/
    $orderArr['receiverAddress'] = array();

    /*********************
    $receiverAddress['terminalCode']
     *********************/
    if($_REQUEST["terminalOn"] == '1'):
        if(!$orderArr['receiverAddress']['terminalCode'] = $_REQUEST["terminalCode"])
        {
            $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_TERMINAL").$error_text_br;
        };
    else:
        $orderArr['receiverAddress']['terminalCode'] = '';
    endif;

    /*********************
    $receiverAddress['name']
     *********************/
    if(!$orderArr['receiverAddress']['name'] = $_REQUEST['name'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_NAME").$error_text_br;
    }

    /*********************
    $receiverAddress['contactFio']
     *********************/
    if(!$orderArr['receiverAddress']['contactFio'] = $_REQUEST['name'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_NAME").$error_text_br;
    }

    /*********************
    $receiverAddress['contactPhone']
     *********************/
    if(!$orderArr['receiverAddress']['contactPhone'] = $_REQUEST['phone'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_PHONE").$error_text_br;
    }

    /*********************
    $receiverAddress['countryName']
     *********************/
    if(!$orderArr['receiverAddress']['countryName'] = $_REQUEST['country'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_REGION").$error_text_br;
    }

    /*********************
    $receiverAddress['region']
     *********************/
    if(!$orderArr['receiverAddress']['region'] = $_REQUEST['region'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_ADMIN_ORDER_PROP_COUNTRY").$error_text_br;
    }

    /*********************
    $receiverAddress['city']
     *********************/
    if(!$orderArr['receiverAddress']['city'] = $_REQUEST['city'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_CITY").$error_text_br;
    }

    /*********************
    $receiverAddress['street']
     *********************/
    if(!$orderArr['receiverAddress']['street'] = $_REQUEST['street'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_STREET").$error_text_br;
    };

    /*********************
    $receiverAddress['streetAbbr']
     *********************/
    if(!$orderArr['receiverAddress']['streetAbbr'] = $_REQUEST['streetAbbr'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_STREETABBR").$error_text_br;
    }

    /*********************
    $receiverAddress['house']
     *********************/
    if(!$orderArr['receiverAddress']['house'] = $_REQUEST['house'])
    {
        $arr["error"] .= $error_text_req.GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_HOUSE").$error_text_br;
    };

    $orderArr['receiverAddress']['houseKorpus'] = $_REQUEST['korpus'];
    $orderArr['receiverAddress']['str'] = $_REQUEST['str'];
    $orderArr['receiverAddress']['vlad'] = $_REQUEST['vlad'];
    $orderArr['receiverAddress']['office'] = $_REQUEST['office'];
    $orderArr['receiverAddress']['flat'] = $_REQUEST['flat'];

    /*********************
    $extraService
     *********************/
    $extraService = array();

    /*********************
    'ÍÏÏ'
     *********************/
    $sum_npp = $_REQUEST['sum_npp'];
    if(!preg_match('/^\+?\d+$/', $sum_npp))
        $arr["error"] .= GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_SUM_NPP").GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_ERROR_INTEGER").$error_text_br;
    if($sum_npp != '0')
    {
        $extraService[] = array(
            'esCode' => GetMessage("ROCKETSTUDIO_DPDEXT_AJAX_NPP"),//'ÍÏÏ',
            'param' => array(
                'name' => 'sum_npp',
                'value' => $sum_npp
            ),
        );
    }

    $orderArr['extraService'] = $extraService;

    /*********************
    order
     *********************/
    if(!$arr["error"])
    {
        $arr["order"] = CRocketstudioDPDExtOrder::CreateOrder($orderArr);
    }
    else
    {
        $arr["order"] = array();
        $arr["order"]["errorMessage"];
    }

    $res = json_encode($arr);

    echo $res;
}

function addOrderDB()
{
    global $DB, $DBType, $APPLICATION;

    $ID = 0;

    $orderID = $_REQUEST["orderID"];

    $orderArr = array(
        'ID' => $_REQUEST["orderNumberInternal"],
        'orderID' => $orderID,
        'orderNum' => $_REQUEST["orderNum"],
        'orderStatus' => $_REQUEST["orderStatus"],
        'orderError' => $_REQUEST["orderError"],
        'datePickupBitrix' => $_REQUEST["datePickup"],
        'datePickup' => ConvertDateTime($_REQUEST["datePickup"], "YYYY-MM-DD", "ru"),
        'serviceCode' => $_REQUEST["serviceCode"],
        'serviceVariant' => $_REQUEST["serviceVariant"],
        'cargoCategory' => $_REQUEST["cargoCategory"],
        'cargoWeight' => $_REQUEST["cargoWeight"],
        'cargoNumPack' => $_REQUEST["cargoNumPack"],
        'cargoValue' => $_REQUEST["cargoValue"],
        'sum_npp' => $_REQUEST["sum_npp"],
        'pickupTimePeriod' => $_REQUEST["pickupTimePeriod"],
        'receiverAddress' => array(
            //'terminalCode' => $_REQUEST["terminalCode"],
            'name' => $_REQUEST["name"],
            'fio' => $_REQUEST["name"],
            'phone' => $_REQUEST["phone"],
            'country' => $_REQUEST['country'],
            'region' => $_REQUEST['region'],
            'city' => $_REQUEST['city'],
            'street' => $_REQUEST['street'],
            'streetAbbr' => $_REQUEST['streetAbbr'],
            'house' => $_REQUEST['house'],
            'korpus' => $_REQUEST['korpus'],
            'str' => $_REQUEST['str'],
            'vlad' => $_REQUEST['vlad'],
            'office' => $_REQUEST['office'],
            'flat' => $_REQUEST['flat'],
        )
    );

    if($_REQUEST["terminalOn"])
    {
        $orderArr['receiverAddress']['terminalCode'] = $_REQUEST["terminalCode"];
    }
    else
    {
        $orderArr['receiverAddress']['terminalCode'] = "";
    }

    if($orderID)
    {
        $ID = CRocketstudioDPDExt::AddOrderDB($orderArr);
    }

    $res = json_encode($ID);

    echo $res;
}

function ChangeStatusWhenDPDCreated()
{
    $res = CRocketstudioDPDExt::ChangeStatusWhenDPDCreated($_REQUEST["ID"]);
    echo $res;
}

function cancelOrder()
{
    global $DB, $DBType, $APPLICATION;

    $orderNumberInternal = $_REQUEST["orderNumberInternal"];
    $orderNum = $_REQUEST['orderNum'];

    $cancelArr = array(
        "cancel" => array(
            "orderNum" => $orderNum,
            "orderNumberInternal" => $orderNumberInternal
        )
    );

    if($orderNum || $orderNumberInternal)
    {
        $arr = CRocketstudioDPDExtOrder::CancelOrder($cancelArr);

        if(!$arr)
        {
            $arr = "";
        }
    }
    else
    {
        $arr = "";
    }

    $res = json_encode($arr);

    echo $res;
}

function cancelOrderDB()
{
    global $DB, $DBType, $APPLICATION;

    $arr = array();

    $orderNumberInternal = $_REQUEST["orderNumberInternal"];

    if($orderNumberInternal)
    {

        $arr = CRocketstudioDPDExt::CancelOrderDB($orderNumberInternal);

        if(!$arr)
        {
            $arr = "";
        }
    }
    else
    {
        $arr = "";
    }

    $res = json_encode($arr);

    echo $res;
}

function createLabelFile()
{
    global $DB, $DBType, $APPLICATION;

    $arr = array();
    $arr["url"] = "";

    $url = '/rocketstudio.dpdext/labels/label_'.$_REQUEST["orderNumberInternal"].'.pdf';

    function getLabelIs($url)
    {
        $io = CBXVirtualIo::GetInstance();
        $fp = $io->RelativeToAbsolutePath($url);
        $f = $io->GetFile($fp);
        $s = $f->GetContents();
        return $s;
    }

    $labelIs = getLabelIs($url);

    if(!$labelIs)
    {
        $labelFileArr = array(
            "fileFormat" => "PDF",
            "pageSize" => "A5",
            "order" => array(
                "orderNum" => $_REQUEST["orderNum"],
                "parcelsNumber" => $_REQUEST["parcelsNumber"]
            )
        );
        $arrLabel = CRocketstudioDPDExtOrder::CreateLabelFile($labelFileArr);
        $arr['dpd'] = $arrLabel;

        $ok = '';

        $file = $arrLabel->return->file;

        if(!empty($file))
        {
            function faleW($file, $url)
            {
                $io = CBXVirtualIo::GetInstance();
                $fp = $io->RelativeToAbsolutePath($url);
                $f = $io->GetFile($fp);
                $s = $f->PutContents($file);
                return $s;
            }

            $ok = faleW($file, $url);

            if($ok)
            {
                $arr["url"] = $url;
            }

        };
    }
    else
    {
        $arr["url"] = $url;
    }

    $res = json_encode($arr);

    echo $res;
}

function getInvoiceFile()
{
    global $DB, $DBType, $APPLICATION;

    $arr = array();
    $arr["url"] = "";

    $orderNum = $_REQUEST["orderNum"];

    $url = '/rocketstudio.dpdext/invoice/invoice_'.$orderNum.'.pdf';

    function getInvoiceIs($url)
    {
        $io = CBXVirtualIo::GetInstance();
        $fp = $io->RelativeToAbsolutePath($url);
        $f = $io->GetFile($fp);
        $s = $f->GetContents($file);
        return $s;
    }

    //$invoiceIs = getInvoiceIs($url);

    if(empty($invoiceIs))
    {
        $arrInvoice = CRocketstudioDPDExtOrder::GetInvoiceFile($orderNum);

        $arr['dpd'] = $arrInvoice;

        $ok = '';

        $file = $arrInvoice->return->file;

        if(!empty($file))
        {
            function faleW($file, $url)
            {
                $io = CBXVirtualIo::GetInstance();
                $fp = $io->RelativeToAbsolutePath($url);
                $f = $io->GetFile($fp);
                $s = $f->PutContents($file);
                return $s;
            }

            $ok = faleW($file, $url);

            if($ok)
            {
                $arr["url"] = $url;
            }

        };
    }
    else
    {
        $arr["url"] = $url;
    }

    $res = json_encode($arr);

    echo $res;
}

function status_update()
{
    CRocketstudioDPDExt::AgentStatusDPD();
    LocalRedirect('/bitrix/admin/sale_order.php?lang='.LANGUAGE_ID);
}

function status_update_order()
{
		$arOrder = CRocketstudioDPDExtOrder::CompleteDataOrder($_REQUEST['ID']);
    $arData = array(
			"order"=>array(
				"orderNumberInternal"=>$_REQUEST['ID'],
				"datePickup"=>CRocketstudioDPDExtOrder::SetDateForDPD($arOrder ["ORDER_PROPS_FOR_DETAIL"]["datePickup"])
			)
		);
		$dpdService = new DPD_service;
		$getOrder = $dpdService->getOrderStatus($arData);
		CRocketstudioDPDExtOrder::SetOrderDPDForSaleProperty($_REQUEST['ID'], $getOrder->return->orderNum, $arOrder["PERSON_TYPE_ID"]);
		CRocketstudioDPDExtOrder::UpdateStatusByOrderId($_REQUEST['ID'], $getOrder->return->status);
		
    LocalRedirect('/bitrix/admin/sale_order_view.php?lang='.LANGUAGE_ID.'&ID='.$_REQUEST["ID"].'&message_status_dpd='.$getOrder->return->status.'&message_error_dpd='.$getOrder->return->errorMessage);
}
?>