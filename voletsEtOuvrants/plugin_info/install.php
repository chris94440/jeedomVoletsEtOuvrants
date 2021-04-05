<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function voletsEtOuvrants_install(){
}
function voletsEtOuvrants_update(){
	log::add('voletsEtOuvrants','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('voletsEtOuvrants') as $voletProp){
		$voletsEtOuvrants->save();
	}
	log::add('voletsEtOuvrants','debug','Fin du script de mise a jours');
}
function voletsEtOuvrants_remove(){
}
?>
