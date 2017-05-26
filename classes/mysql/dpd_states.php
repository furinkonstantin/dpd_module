<?
class CRocketstudioDPDExtMySQLStates
{
    
    function Add($arFields)
    {
        global $DB;
        $arInsert = $DB->PrepareInsert("b_dpdext_states", $arFields);
        $strSql =
            "INSERT INTO b_dpdext_states(".$arInsert[0].") ".
            "VALUES(".$arInsert[1].")";
        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);       
        $ID = IntVal($DB->LastID());
        return $ID;
    }
	
	function GetNumbersList($dpdOrderNr)
    {
        global $DB;
        $strSql = "SELECT P.* FROM b_dpdext_states P WHERE P.dpdOrderNr='".$DB->ForSql($dpdOrderNr)."'";
        $dbRes = $DB->Query($strSql, true);        
        return $dbRes;        
    }    
                 
}
?>