<?php
require('api.php');
$solus = new Solus('http://defensiveservers.com', 'key', 'pass');

try{
	$db = new PDO('mysql:host=localhost;dbname=DBNAME;charset=utf8', 'DBUSER', 'DBPASS');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch(PDOException $e) {
	die($e->getMessage());
}

$nodeNames = array(8 => 5, 10 => 7, 11 => 8);
$nodeIDs = array(8 => 0, 10 => 0, 11 => 0);
$pGroupCount = array(5 => 0, 7 => 0, 8 => 0);
$pGroupLimit = array(5 => 4, 7 => 4, 8 => 4);
$planNames = array("KVM VPS Bronze" => 20, "KVM VPS Silver" => 40, "KVM VPS Gold" => 80, "KVM VPS Platinum" => 140);

foreach($nodeNames as $nID => $pID){
	$ipcount = json_decode($solus->getNodeIPs($nID))->ipcount;
	
	if($ipcount == 0){
		$nodeIDs[$nID] = 1;
	}else{
		$nodeStats = json_decode($solus->nodeStats($nID));
		$gbLeft = number_format($nodeStats->freedisk / 1048576, 2);
		
		foreach($planNames as $planName => $planGB){
			if($gbLeft < $planGB){
				echo "Stock 0 for $planName Group ID $pID<br />";
				$stmt = $db->prepare("UPDATE `defensiv_whmcs`.`tblproducts` SET `qty` = '0' WHERE `configoption4` = ? AND `gid` = ?");
				$stmt->execute(array($planName, $pID));
				$pGroupCount[$pID]++;
			}else{
				echo "Stock <1 for $planName Groupe ID $pID<br />";
				$stmt = $db->prepare("UPDATE `defensiv_whmcs`.`tblproducts` SET `qty` = '10' WHERE `name` = ? AND `gid` = ?");
				$stmt->execute(array($planName, $pID));
				$stmt = $db->prepare("UPDATE  `defensiv_whmcs`.`tblproductgroups` SET  `hidden` =  '0' WHERE  `tblproductgroups`.`id` =?");
				$stmt->execute(array($pID));
			}
		}
	}
}
foreach($nodeIDs as $nID => $nStatus){
	if($nStatus == 1){
		echo "Stock 0 for Group ID $nodeNames[$nID]<br />";
		$stmt = $db->prepare("UPDATE `tblproducts` SET `qty` = '0' WHERE `gid` = ?");
		$stmt->execute(array($nodeNames[$nID]));
		$stmt = $db->prepare("UPDATE  `defensiv_whmcs`.`tblproductgroups` SET  `hidden` =  '1' WHERE  `tblproductgroups`.`id` =?");
		$stmt->execute(array($nodeNames[$nID]));
	}
}
foreach($pGroupCount as $pGCid => $pGCc){
	if($pGCc == $pGroupLimit[$pGCid]){
		echo "$pGCid set to hidden group<br />";
		$stmt = $db->prepare("UPDATE  `defensiv_whmcs`.`tblproductgroups` SET  `hidden` =  '1' WHERE  `tblproductgroups`.`id` =?");
		$stmt->execute(array($pGCid));
	}
}
?>
