<?

$iModuleID = "rocketstudio.dpdext";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$iModuleID."/include.php");

IncludeModuleLangFile(__FILE__);

//Проверка прав
$CONS_RIGHT = $APPLICATION->GetGroupRight($iModuleID);
if ($CONS_RIGHT <= "D")
{
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$sTableID = "tbl_dpd_export";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
    "filter_id",
    "filter_date_insert_from",
    "filter_date_insert_to",
    "filter_date_update_from",
    "filter_date_update_to",
    "filter_status",
    "filter_delivery",
    "filter_payed",
    "filter_canceled"
);
$lAdmin->InitFilter($arFilterFields);

$arHeaders = array(
    array(
        "id"        =>"ID",
        "content"    =>"ID",
        "sort"        =>"id",
        "align"        =>"left",
        "default"    =>true,
    ),
    array(
        "id"         => "DATE_UPDATE",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_DATE_UPDATE"),
        "sort"       => "date_update",
        "default"    => false,
    ),
    array(
        "id"         => "PERSON_TYPE_ID",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_PERSON_TYPE_ID"),
        "sort"       => "person_type_id",
        "default"    => false,
    ),
    array(
        "id"         => "STATUS_ID",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_STATUS"),
        "sort"       => "status",
        "default"    => true,
    ),
    array(
        "id"         => "DELIVERY_ID",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_DELIVERY"),
        "sort"       => "delivery",
        "default"    => false,
    ),
    array(
        "id"         => "TERMINALS",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_TERMINALS"),
        "default"    => false,
    ),
    array(
        "id"         => "PAYED",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_PAYED"),
        "sort"       => "payed",
        "default"    => true,
    ),
    array(
        "id"         =>"CANCELED",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_CANCELED"),
        "sort"       =>"canceled",
        "default"    => true,
    ),
    array(
        "id"         =>"COMMENTS",
        "content"    => GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_COMMENTS"),
        "sort"       =>"canceled",
        "default"    => true,
    ),
);

foreach ($arOrderProps as $key => $value)
{
    $arHeaders[] = array("id" => "PROP_".$key, "content" => $value["NAME"]." (".$value["PERSON_TYPE_NAME"].")", "sort" => "", "default" => false);
    $arColumn2Field["PROP_".$key] = array();
}
foreach ($arOrderPropsCode as $key => $value)
{
    $arHeaders[] = array("id" => "PROP_".$key, "content" => $value["NAME"], "sort" => "", "default" => false);
    $arColumn2Field["PROP_".$key] = array();
}

$lAdmin->AddHeaders($arHeaders);

// обработаем поля которые надо выводить
$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$bNeedProps = False;
foreach ($arVisibleColumns as $visibleColumn)
{
    if (!$bNeedProps && SubStr($visibleColumn, 0, StrLen("PROP_")) == "PROP_"){
        $bNeedProps = True;
    }

    if(SubStr($visibleColumn, 0, StrLen("PROP_")) != "PROP_") {
        $arSelectFields[] = $visibleColumn;
    }
}
$arSelectFields[] = 'DATE_INSERT';
$arSelectFields[] = 'ID';

if(empty($filter_canceled)) {
    $filter_canceled = 'N';
}
if($filter_canceled == 'ALL') {
    unset($filter_canceled);
}

$arFilter = Array(
    "ID"      => $filter_id,
    ">=DATE_INSERT"    => $filter_date_insert_from,
    "<=DATE_INSERT"    => $filter_date_insert_to,
    ">=DATE_UPDATE"    => $filter_date_update_from,
    "<=DATE_UPDATE"    => $filter_date_update_to,
    "STATUS_ID"        => $filter_status,
    "PAYED"            => $filter_payed,
    "CANCELED"         => $filter_canceled,
    "DELIVERY_ID"      => $filter_delivery,
);
foreach($arFilter as $key => $val){
    if(empty($val)){
        unset($arFilter[$key]);
    }
}

$nPageSize = CAdminResult::GetNavSize($sTableID);
if($_REQUEST['PAGEN_1']) {
    $iNumPage = $_REQUEST['PAGEN_1'];
}
else {
    $iNumPage = '1';
}
$arrFillterConstant = array();
if(empty($arFilter['DELIVERY_ID'])){
    $arrFillterConstant["DELIVERY_ID"] =  array("dpd_csm:CSM_DD","dpd_csm:CSM_DT","dpd_csm:CSM_TD","dpd_csm:CSM_TT","dpd_pcl:PCL_DD","dpd_pcl:PCL_DT","dpd_pcl:PCL_TD","dpd_pcl:PCL_TT");
}
$arrFillter = array_merge($arrFillterConstant, $arFilter);
$nPage = array("nPageSize" => $nPageSize , "iNumPage" => $iNumPage);
$obOrder = CSaleOrder::GetList(Array($by => $order), $arrFillter ,false, $nPage , $arSelectFields);
//$obOrder = CSaleOrder::GetList(array('ID' => 'DESC'), array() ,false, $nPage , array());
$arr_sales = array();
while($ar_sales = $obOrder->Fetch())
{
    $arr_sales[] = $ar_sales;
}

$rsData = new CAdminResult($obOrder, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint("x8")); //название постранички

while($arRes = $rsData->NavNext(true, "f_")):
    $row =& $lAdmin->AddRow($f_ID, $arRes);
    $arOrder = $arRes;
    $entity_str = "";
    $arResNew = array(
        "ID" => $arRes["ID"],
        "field2" => $arRes["DATE_INSERT"],
    );
    foreach($arResNew as $k => $v) {
        $row->AddViewField($k, $v);
    }
endwhile;

$lAdmin->AddFooter(
    array(
        array("title"=>"x1", "value"=>$rsData->SelectedRowsCount()),
        array("counter"=>true, "title"=> "x2", "value"=>"0"),
    )
);

//Групповые операции
$arActionsTable = Array(
    "deleveryOrder" => GetMessage("ROCKETSTUDIO_DPDEXT_GROUP_DELAY_1"),
    //"callOrder" => GetMessage("ROCKETSTUDIO_DPDEXT_GROUP_DELAY_2"),       
    //"courierOrder" => GetMessage("ROCKETSTUDIO_DPDEXT_GROUP_DELAY_3"),   
    "printOrder" => GetMessage("ROCKETSTUDIO_DPDEXT_GROUP_DELAY_4"),
    "deleteOrder" => GetMessage("ROCKETSTUDIO_DPDEXT_GROUP_DELAY_5"),
);


$lAdmin->AddGroupActionTable($arActionsTable);
//Групповые операции END
$aContext = array(
    array(
        "ICON" => "btn_new",
        "TEXT"=> GetMessage("ROCKETSTUDIO_DPDEXT_BUTTOM_REFRESH"),
        "LINK" => "?refresh=Y&lang=".LANG,
        "TITLE"=> GetMessage("ROCKETSTUDIO_DPDEXT_BUTTOM_REFRESH_DESCRIPT"),
    ),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();
$GLOBALS['APPLICATION']->SetTitle(GetMessage('ROCKETSTUDIO_DPDEXT_ADMIN_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$IS_TEST = COption::GetOptionInt("rocketstudio.dpd", "IS_TEST");
?>
    <form name="form_dpd" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<pre>
<? var_dump($arr_sales);?>
</pre>
        <?
        $arFilterFieldsTmp  = array(
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_DATE_INSERT"),
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_DATE_UPDATE"),
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_STATUS"),
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_DELIVERY"),
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_PAYED"),
            GetMessage("ROCKETSTUDIO_DPDEXT_FIELD_CANCELED"),
        );

        $filter = new CAdminFilter(
            $sTableID."_filter_id",
            $arFilterFieldsTmp
        );

        $filter->Begin();
        ?>
        <? $filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form2"));?>
        <? $filter->End();?>
        <div>
            <?
            if($IS_TEST):
                echo GetMessage("ROCKETSTUDIO_DPDEXT_IS_TEST");
            else:
                echo GetMessage("ROCKETSTUDIO_DPDEXT_IS_WORK");
            endif;
            ?>
            <br /><br />
        </div>
    </form>
<?
$lAdmin->DisplayList();

ob_start();
?>
    <script>
        alert("OK");
    </script>
<?
$sContent = ob_get_contents();
ob_end_clean();
$GLOBALS['APPLICATION']->AddHeadString($sContent);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>