<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class voletsEtOuvrants extends eqLogic {
	
	public static function writeLog($logType,$message,$equipmentName) {
		if ($logType != '') {
			$nameOfEquipment='';
			if ($equipmentName !='') {
					$nameOfEquipment= '( ' . $equipmentName . ' )';
			}
			switch ($logType) {
				case 'info':
					log::add('voletsEtOuvrants','info',$message .  $nameOfEquipment );
					break;
				case 'debug':
					log::add('voletsEtOuvrants','debug',$message . $nameOfEquipment);
					break;
				case 'error':
					log::add('voletsEtOuvrants','error',$message .  $nameOfEquipment);
					break;
				default:
					break;
			}
		} else {
			log::add('voletsEtOuvrants','debug',"pas de log type pour ce message : " . $message);
		}
	}

	public static function timeout($_option) {	
		$Volet = eqlogic::byId($_option['Volets_id']); 
        $Timeout*=1000000;
		if (is_object($Volet) && $Volet->getIsEnable()) {
			while(true){
				$changeWay = cache::byKey('voletsEtOuvrants::ChangeWay::'.$Volet->getId())->getValue(false);
				
				//Le timeout sert a quoi puisque executé uniquement si pas d'action sur le bouton stop .. mais permet de mettre à jour la hauteur du VR via simulation du stop
				if($changeWay == 'up') {
					$TempsTimeout = $Volet->getTime('cfgDecolTime') + $Volet->getTime('cfgOpenTime');
				} else {
					$TempsTimeout = $Volet->getTime('cfgDecolTime') + $Volet->getTime('cfgCloseTime');
				}
				
				if($TempsTimeout <= 0) {
					break;
				}
				
				//if(cache::byKey('voletsEtOuvrants::Move::'.$Volet->getId())->getValue(false)){
				$isMoving=cache::byKey('voletsEtOuvrants::Move::'.$Volet->getId())->getValue(false);
				if ($changeWay != '' && $isMoving) {
					$Volet->writeLog('info','		# changeWay valeur : ' . $changeWay . ' attente pour simuler le bouton stop',$Volet->getHumanName());
					$ChangeStateStart = cache::byKey('voletsEtOuvrants::ChangeStateStart::'.$Volet->getId())->getValue(microtime(true));
					$Timeout = microtime(true)-$ChangeStateStart;
					$Timeout*=1000000;
					if($Timeout >= $TempsTimeout){
						//$Volet->writeLog('info','		# [Timeout] Execution du stop au bout de : ' . $Timeout . ' microsecondes',$Volet->getHumanName());
						//$Volet->getCmd(null,'stop')->execute(null);						
                       
                      	if ($changeWay == 'up') {
                          $rollerState='100';
                        } else {
                          $rollerState='0';
                        }
						 $Volet->writeLog('info','		# [Timeout] MAJ de la hauteur du VR : ' . $rollerState,$Volet->getHumanName());
                      	$Volet->checkAndUpdateCmd('rollerState',$rollerState);
                      	cache::set('voletsEtOuvrants::stopAction::'.$Volet->getId(),true, 0);
						cache::set('voletsEtOuvrants::Move::'.$Volet->getId(),false, 0);
						cache::set('voletsEtOuvrants::ChangeWay::'.$Volet->getId(),"", 0);
					}else{
						$Volet->writeLog('info','		# [Timeout] Temps d\'attente: '.$Timeout.' < '.$TempsTimeout.', Nous attendons ',$Volet->getHumanName());
						$TempsTimeout -= ceil($Timeout);
					}
				}
				if($Timeout - $TempsTimeout > 1000000 || $Timeout - $TempsTimeout <= 0) {
					usleep(intval(1000000));
				} else {
					usleep(intval($Timeout - $TempsTimeout));
				}				
			}
		}
	}
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'voletsEtOuvrants';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('voletsEtOuvrants') as $Volet){
			if($Volet->getIsEnable()){
				$cron = cron::byClassAndFunction('voletsEtOuvrants', 'timeout', array('Volets_id' => $Volet->getId()));
				if(!is_object($cron) || !$cron->running()) 	
					return $return;
			}
		}
		$return['state'] = 'ok';
		return $return;
	}
	
	public static function deamon_start($_debug = false) {
		log::remove('voletsEtOuvrants');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
		foreach(eqLogic::byType('voletsEtOuvrants') as $Volet){
			cache::set('voletsEtOuvrants::Move::'.$Volet->getId(),false, 0);
			$Volet->StartListener();
			$Volet->CreateDemon();   
		}
	}
	public static function deamon_stop() {	
		foreach(eqLogic::byType('voletsEtOuvrants') as $Volet){
			$Volet->StopListener();
			$cron = cron::byClassAndFunction('voletsEtOuvrants', 'timeout', array('Volets_id' => $Volet->getId()));
			if (is_object($cron)) 	
				$cron->remove();
		}
	}

	private function CreateDemon() {
		$cron =cron::byClassAndFunction('voletsEtOuvrants', 'timeout', array('Volets_id' => $this->getId()));
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('voletsEtOuvrants');
			$cron->setFunction('timeout');
			$cron->setOption(array('Volets_id' => $this->getId()));
			$cron->setEnable(1);
			$cron->setDeamon(1);
			$cron->setSchedule('* * * * *');
			$cron->setTimeout('1');
			$cron->save();
		}
		$cron->save();
		$cron->start();
		$cron->run();
		return $cron;
	}
	
	public function StopListener() {
		
		$cache = cache::byKey('voletsEtOuvrants::ChangeStateStart::'.$this->getId());
		if (is_object($cache)) {
			$cache->remove();
		}
		
		$cache = cache::byKey('voletsEtOuvrants::ChangeStateStop::'.$this->getId());
		if (is_object($cache)) {
			$cache->remove();
		}
		
		$cache = cache::byKey('voletsEtOuvrants::Move::'.$this->getId());
		if (is_object($cache)) {
			$cache->remove();
		}
		
		$cache = cache::byKey('voletsEtOuvrants::ChangeState::'.$this->getId());
		if (is_object($cache)){
			$cache->remove();
		}
		
		$cache = cache::byKey('voletsEtOuvrants::ChangeWay::'.$this->getId());
		if (is_object($cache)){
			$cache->remove();
		}
	}
	
	public function StartListener() {
		if($this->getIsEnable()){
			//todo
		}
	}
	
	
	public function boolToText($value){
		if (is_bool($value)) {
			if ($value) 
				return __('Vrai', __FILE__);
			else 
				return __('Faux', __FILE__);
		} else 
			return $value;
	}
	
	
	public function EvaluateCondition($Condition){
		$_scenario = null;
		$expression = scenarioExpression::setTags($Condition, $_scenario, true);
		$message = __('Evaluation de la condition : ['.jeedom::toHumanReadable($Condition).'][', __FILE__) . trim($expression) . '] = ';
		$result = evaluate($expression);
		$message .=$this->boolToText($result);
		$this->writeLog('info',$message,$this->getHumanName());
		if(!$result)
			return false;		
		return true;
	}
	
	public function UpdateRollerState($rollerStateValue) {
		$this->writeLog('info','		# UpdateRollerState --> rollerState manage by plugin ? ' . json_encode($this->getConfiguration('cfgPluginManageRollerState')),$this->getHumanName());		
		if($this->getConfiguration('cfgPluginManageRollerState') != '' && $this->getConfiguration('cfgPluginManageRollerState') == '1' ){
			$rollerState='';
			if ($rollerStateValue !='') {
				$rollerState=$rollerStateValue;
			} else {
				$ChangeState = cache::byKey('voletsEtOuvrants::ChangeState::'.$this->getId())->getValue(false);
				$ChangeStateStart = cache::byKey('voletsEtOuvrants::ChangeStateStart::'.$this->getId())->getValue(microtime(true));
				$ChangeStateStop = cache::byKey('voletsEtOuvrants::ChangeStateStop::'.$this->getId())->getValue(microtime(true));
				$changeWay = cache::byKey('voletsEtOuvrants::ChangeWay::'.$this->getId())->getValue(false);			
				
				$TempsAction=$ChangeStateStop-$ChangeStateStart;
				$TempsAction=round($TempsAction*1000000);
				$actualRollerState=$this->getCmd(null,'rollerState')->execCmd();


				if($actualRollerState == 0){
					$TempsAction += $this->getTime('cfgDecolTime');			
				}

				
				if ($changeWay == 'down') {
					$Temps = $this->getTime('cfgCloseTime');
					$rollerState=round($actualRollerState-(round(($TempsAction/$Temps)*100)));
				} else {
					$Temps = $this->getTime('cfgOpenTime');
					$rollerState=round($actualRollerState+(round(($TempsAction/$Temps)*100)));
				}
				
				if($rollerState<0) {
					$rollerState=0;
				} else if ($rollerState>100) {
					$rollerState=100;
				}
			}

			$this->writeLog('info', '			=> Maj du statut du volet roulant : ' . $rollerState,$this->getHumanName());	
			$this->checkAndUpdateCmd('rollerState',$rollerState);
		} else {
			$this->writeLog('error', '			=> Function UpdateRollerState --> rollerState not manage by plugin',$this->getHumanName());		
		}
	}
	
	/*
	public function CheckSynchro($newRollerState,$actualRollerState) {
		
		//Check if cmd stop, up and down exist otherwise do nothing
		if($this->getConfiguration('cfgCmdStop') != ''){
			$Stop=cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdStop')));
			if(!is_object($Stop)) {
				return false;
			}
		}
		
		$Down=cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdDown')));
		if(!is_object($Down)) {
			return false;
		}
		
		$Up=cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdUp')));
		if(!is_object($Up))
			return false;
		}
	
		if($newRollerState == 100){
			log::add('voletsEtOuvrants','info',$this->getHumanName().'[Synchronisation] Montée complete');
			$Up->execute(null);
			usleep($this->getTime('cfgOpenTime'));
			if($this->getConfiguration('cfgCmdStop') == '')			
				$Up->execute(null);
			else
				$Stop->execute(null);		
			if($this->getConfiguration('useStateJeedom'))
				$this->checkAndUpdateCmd('rollerState',100);
			return false;
		}
		if($newRollerState == 0){
			log::add('voletsEtOuvrants','info',$this->getHumanName().'[Synchronisation] Descente complete');
			$Down->execute(null);
			usleep($this->getTime('cfgCloseTime'));
			if($this->getConfiguration('cfgCmdStop') == '')			
				$Down->execute(null);
			else
				$Stop->execute(null);	
			if($this->getConfiguration('useStateJeedom'))
				$this->checkAndUpdateCmd('rollerState',0);
			return false;
		}
		if($this->getConfiguration('Synchronisation')){
			if($actualRollerState > $newRollerState){
				log::add('voletsEtOuvrants','info',$this->getHumanName().'[Synchronisation] Descente complete');
				$Down->execute(null);
				usleep($this->getTime('cfgCloseTime'));
				if($this->getConfiguration('cfgCmdStop') == '') {			
					$Down->execute(null);
				} else {
					$Stop->execute(null);	
				}
				if($this->getConfiguration('useStateJeedom')) {
					$this->checkAndUpdateCmd('rollerState',100);
				}
			}else{
				log::add('voletsEtOuvrants','info',$this->getHumanName().'[Synchronisation] Montée complete');
				$Up->execute(null);
				if(!isset($Stop)) {
					$Stop=$Down;
				}
				
				usleep($this->getTime('cfgOpenTime'));
				
				if($this->getConfiguration('cfgCmdStop') == '') {			
					$Up->execute(null);
				} else {
					$Stop->execute(null);		
				}
				
				if($this->getConfiguration('useStateJeedom')) {
					$this->checkAndUpdateCmd('rollerState',0);
				}
			}
			return true;
		}
		return true;
	}
	*/
	
	private function checkConfiguration($configName) {
		if($this->getConfiguration($configName) != ''){
			$cmd=cmd::byId(str_replace('#','',$this->getConfiguration($configName)));
			if(!is_object($cmd)) {
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
	
	public function execPropVolet($newRollerState) {
		//Get actual roller state
		$rollerState=$this->getCmd(null,'rollerState')->execCmd();
		$this->writeLog('info', '	* ExecPropVolet- position actuelle du VR : ' .$rollerState . " => ".$newRollerState,$this->getHumanName());	
		
		//if same roller state do nothing
		if($rollerState == $newRollerState) {
			$this->writeLog('info', '		# Statut d\'ouverture avant et apres action équivalent ==> pas d\'action',$this->getHumanName());	
			return;
		}
		
		if (!$this->checkConfiguration('cfgCmdStop') || !$this->checkConfiguration('cfgCmdDown') || !$this->checkConfiguration('cfgCmdUp') ){
			throw new Exception($this->getHumanName().'Pas de commande up, down et stop de definie pour cet équipement');
		}
		
		$AutorisationDecollement=false;
		if($newRollerState == 0 || $rollerState == 0) {
			$AutorisationDecollement=true;
		}
		
		cache::set('voletsEtOuvrants::Move::'.$this->getId(),true, 0);
		if($rollerState > $newRollerState){
			cache::set('voletsEtOuvrants::ChangeWay::'.$this->getId(),"down", 0);		
			$Delta=$rollerState-$newRollerState;
			$temps=$this->TpsAction($Delta,$AutorisationDecollement);
			cache::set('voletsEtOuvrants::ChangeState::'.$this->getId(),false, 0);

			$this->writeLog('info', '		# on execute la commande down pendant '.$temps .' microsecondes',$this->getHumanName());	
			
			(cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdDown'))))->execute(null);

			cache::set('voletsEtOuvrants::ChangeStateStart::'.$this->getId(),microtime(true), 0);
			if(!isset($Stop)) {
				$Stop=$Down;
			}
		}else{

			cache::set('voletsEtOuvrants::ChangeWay::'.$this->getId(),"up", 0);
			$Delta=$newRollerState-$rollerState;
			$temps=$this->TpsAction($Delta,$AutorisationDecollement);
			cache::set('voletsEtOuvrants::ChangeState::'.$this->getId(),true, 0);	
			
			$this->writeLog('info', '		# on execute la commande up pendant '.$temps .' microsecondes',$this->getHumanName());		
			
			(cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdUp'))))->execute(null);
			
			cache::set('voletsEtOuvrants::ChangeStateStart::'.$this->getId(),microtime(true), 0);
			if(!isset($Stop)) {
				$Stop=$Up;
			}
		}
		
		//si montée ou descente total pas de pause et pas de simulation du bouton stop
		if ($newRollerState > 0 && $newRollerState <100) {
			usleep(intval($temps));
		
			cache::set('voletsEtOuvrants::ChangeStateStop::'.$this->getId(),microtime(true), 0);
			(cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdStop'))))->execute(null);
			cache::set('voletsEtOuvrants::Move::'.$this->getId(),false, 0);
			$this->UpdateRollerState($newRollerState);
		} else {
			$this->writeLog('info', '		# Montée ou descente total => pas de calcul de la hauteur => on force à la position du volet demandée : ' .$newRollerState ,$this->getHumanName());	
			//Pour qu'il ne se passe rien dans la fonction timeout
			cache::set('voletsEtOuvrants::Move::'.$this->getId(),false, 0);
			$this->checkAndUpdateCmd('rollerState',$newRollerState);
		}
	}
	
	private function getTime($Type) {
		return intval($this->getConfiguration($Type,0))*intval($this->getConfiguration($Type.'Unit',1000000));
	}
	
	public function getRollerTime($cmdType){
		$response='';
		$rollerState = $this->getCmd(null, 'rollerState');
		if (is_object($rollerState)) {
			if ($rollerState->execCmd() == '0') {
				if ($cmdType == 'up') {
					$response= (intval($this->getConfiguration('cfgOpenTime',0))*intval($this->getConfiguration('cfgOpenTime'.'Unit',1000000))) + (intval($this->getConfiguration('cfgDecolTime',0))*intval($this->getConfiguration('cfgDecolTime'.'Unit',1000000)));
				} else {
					$response= intval($this->getConfiguration('cfgCloseTime',0))*intval($this->getConfiguration('cfgCloseTime'.'Unit',1000000));
				}
			} else {
				if ($cmdType == 'up') {
					$response= intval($this->getConfiguration('cfgOpenTime',0))*intval($this->getConfiguration('cfgOpenTime'.'Unit',1000000));
				} else {
					$response= intval($this->getConfiguration('cfgCloseTime',0))*intval($this->getConfiguration('cfgCloseTime'.'Unit',1000000));
				}
			}
		} else {
			throw new Exception($this->getEqLogic()->getHumanName()." - Commande rollerState n\'existe pas");
		}
		return ($response/1000000);
	}
	
    public function TpsAction($rollerStateDelta, $AutorisationDecollement) {
		$changeWay = cache::byKey('voletsEtOuvrants::ChangeWay::'.$this->getId())->getValue(false);
		if($changeWay == 'down') {
			$Temps = $this->getTime('cfgCloseTime');
		} else {
			$Temps = $this->getTime('cfgOpenTime');
		}

		$TempsAction=round($rollerStateDelta*$Temps/100);

		if($AutorisationDecollement){
			$TempsAction += $this->getTime('cfgDecolTime');
		}
		
		$this->writeLog('info','		# Temps d\'action '.$TempsAction.'µs pour un deplacement du volet de ' .$rollerStateDelta . ' | sens du deplacement : ' .$changeWay,$this->getHumanName());	
		return $TempsAction;
	}
	
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Value=null,$icon=null,$generic_type=null,$buildCalcul=false,$order) {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande)){
			$Commande = new voletsEtOuvrantsCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$Commande->setType($Type);
			$Commande->setSubType($SubType);		
			
          
           	if($Value != null) {
				if ($buildCalcul) {
					$Commande->setConfiguration('calcul',$Value);
				}
              	$Commande->setValue($Value);
        	}
          
			if($icon != null) {
				$Commande->setDisplay('icon', $icon);
            }
          
			if($generic_type != null) {
              	$Commande->setDisplay('generic_type', $generic_type);
            }
          
          	if($order != null) {
              	$Commande->setOrder($order);
            }
          
			$Commande->save();
		}
      
		return $Commande;
	}
  
  	private function deleteCommande($_logicalId){
      	$Commande = $this->getCmd(null,$_logicalId);
		if (is_object($Commande)){
          	$this->writeLog('info',' - Suppression de la commande inutilisée : '.$_logicalId,$this->getHumanName());	
          	$Commande->remove();
        }
    }
	public function postSave() {
		/*
		if($this->getId() != null){
			if(($this->getConfiguration('UpStateCmd') == '' || $this->getConfiguration('DownStateCmd') == '')
			   &&  !$this->getConfiguration('useStateJeedom'))
				throw new Exception(__('Erreur dans la configuration, il n\'est pas possible d\'activer la gestion des états si aucun état n\’est configuré', __FILE__));
		}
		*/
	}
	public function preSave() {
		$this->StopListener();
					
		//create command up if it is set
		if ($this->getConfiguration('cfgCmdUp') !='') {
			$this->writeLog('info',' - Creation cmd action up',$this->getHumanName());	
			$this->AddCommande("Up","up","action", 'other',1,null,'<i class="fa fa-arrow-up"></i>','FLAP_UP',false,1);
		} else {
          	$this->deleteCommande("up");
        }
		
		//create command down if it is set
		if ($this->getConfiguration('cfgCmdDown') !='') {
			$this->writeLog('info',' - Creation cmd action down',$this->getHumanName());
			$this->AddCommande("Down","down","action", 'other',1,null,'<i class="fa fa-arrow-down"></i>','FLAP_DOWN',false,3);
		}else {
          	$this->deleteCommande("down");
        }
		
		//create command stop if it is set
		if ($this->getConfiguration('cfgCmdStop') !='' ) {
			$this->writeLog('info',' - Creation cmd action stop',$this->getHumanName());
			$this->AddCommande("Stop","stop","action", 'other',1,null,'<i class="fa fa-stop"></i>','FLAP_STOP',false,2);
		}else {
          	$this->deleteCommande("stop");
        }
		


		if (($this->getConfiguration('cfgPluginManageRollerState') != '') && ($this->getConfiguration('cfgPluginManageRollerState') == '1')) {
			$this->writeLog('info',' - Creation cmd virtuelle rollerSlider et rollerState',$this->getHumanName());
			$rollerState=$this->AddCommande("Roller state","rollerState","info",'numeric',1,null,null,'FLAP_STATE',false,1);
			$this->AddCommande("Roller Slider","rollerSlider","action",'slider',1,$rollerState->getId(),null,'FLAP_SLIDER',false,4);
		} else {			
			if ($this->getConfiguration('cfgCmdRollerSlider') != '') {
				$cmdRollerSlider=cmd::byId(str_replace('#','',$this->getConfiguration('cfgCmdRollerSlider')));
				$cmdRollerState=cmd::byId(str_replace('#','',$this->getConfiguration('cfgRollerState')));
				if (is_object($cmdRollerSlider)){			
					$this->writeLog('info',' - Creation cmd rollerSlider et rollerState en fonction de celle de l\'équipement',$this->getHumanName());
					$rollerState=$this->AddCommande("Roller state","rollerState","info",'numeric',1,$this->getConfiguration('cfgRollerState'),null,'FLAP_STATE',true,1);
					$this->AddCommande("Roller Slider","rollerSlider","action",'slider',1,$rollerState->getId(),null,'FLAP_SLIDER',false,4);
				}
			}
		}
		
		if ($this->getConfiguration('cfgCmdOpenSlider')  !='' ){
			$this->writeLog('info',' - Creation cmd openSlider et openState',$this->getHumanName());
			$cmdOpenState=cmd::byId(str_replace('#','',$this->getConfiguration('cfgOpenState')));
			$openState=$this->AddCommande("Open state","openState","info",'numeric',1,$this->getConfiguration('cfgOpenState'),null,'OPENING',true,2);
			$this->AddCommande("Open Slider","openSlider","action",'slider',1,$openState->getId(),null,'FLAP_SLIDER',false,5);
          
          	//suppresion commande inutile
          	$this->deleteCommande("openStateLeft");
          	$this->deleteCommande("openStateRight");
          
		} else {
			if ($this->getConfiguration('cfgCmdOpenStateLeft') !='') {
				$this->writeLog('info',' - Creation cmd info openState left',$this->getHumanName());
				$this->AddCommande("Open state left","openStateLeft","info",'binary',1,$this->getConfiguration('cfgCmdOpenStateLeft'),null,'OPENING',true,3);
            } else {
          		$this->deleteCommande("openStateLeft");
        	}
			
            if ($this->getConfiguration('cfgCmdOpenStateRight') != '') {
				$this->writeLog('info',' - Creation cmd info openState right',$this->getHumanName());
				$this->AddCommande("Open state right","openStateRight","info",'binary',1,$this->getConfiguration('cfgCmdOpenStateRight'),null,'OPENING',true,4);
            } else {
          		$this->deleteCommande("openStateRight");
        	}
          
          	//suppresion commande inutile
          	$this->deleteCommande("openState");
          	$this->deleteCommande("openSlider");
              
		}
		
		//add cmd to get the roller time ==> only for dashboard
      	if ($this->getConfiguration('cfgOpenTime') != '' || $this->getConfiguration('cfgCloseTime') != '') {
          	$cmdAction = $this->getCmd(null, 'rollerTime');
            if (!is_object($cmdAction)) {
                $this->writeLog('info',' - Creation cmd info getRollerTime',$this->getHumanName());
                $this->AddCommande("Get roller time","rollerTime","action",'message',1,null,null,null,false,6);
            }
        }
		
		
		$this->StartListener();
		$this->CreateDemon();   
	}	
	
	public function preRemove() {
		$cron = cron::byClassAndFunction('voletsEtOuvrants', 'timeout', array('Volets_id' => $this->getId()));
		if (is_object($cron)) {
			$cron->remove();
		}
	}
	
	 public function toHtml($_version = 'dashboard') {
      $replace = $this->preToHtml($_version);
      //$replace=array();
      /*
      log::add('alarmeIMA_V2', 'debug',  "Function toHtml - ap pretohtml");
      if (!is_array($replace)) {
        log::add('alarmeIMA_V2', 'debug',  "Function toHtml - dans le if");
        return $replace;
        log::add('alarmeIMA_V2', 'debug',  "Function toHtml - return replace");
        
      }
      */
      	$version = jeedom::versionAlias($_version);
      	$replace['#' . $replace['#id#'] . '_cfgWindowType#'] = $this->getConfiguration('cfgWindowType');
		$replace['#' . $replace['#id#'] . '_cfgWindowColor#'] = $this->getConfiguration('cfgWindowColor');
       	$replace['#' . $replace['#id#'] . '_cfRollerSliderColor#'] = $this->getConfiguration('cfRollerSliderColor');
		$replace['#' . $replace['#id#'] . '_cfgWindowSize#'] = $this->getConfiguration('cfgWindowSize');		
		$replace['#' . $replace['#id#'] . '_cfgLabel#'] = $this->getConfiguration('cfgLabel');
		$replace['#' . $replace['#id#'] . '_cfgColorLabel#'] = $this->getConfiguration('cfgColorLabel');
		$replace['#' . $replace['#id#'] . '_cfgColorBorderEq#'] = $this->getConfiguration('cfgColorBorderEq');
		$replace['#' . $replace['#id#'] . '_cfgTypeBorderEq#'] = $this->getConfiguration('cfgTypeBorderEq');
		$replace['#' . $replace['#id#'] . '_cfgDecoLTimeUnit#'] = $this->getConfiguration('cfgDecoLTimeUnit');
		$replace['#' . $replace['#id#'] . '_cfgCloseTimeUnit#'] = $this->getConfiguration('cfgCloseTimeUnit');
		$replace['#' . $replace['#id#'] . '_cfgOpenTimeUnit#'] = $this->getConfiguration('cfgOpenTimeUnit');
		$replace['#' . $replace['#id#'] . '_cfgCmdUp#'] = $this->getConfiguration('cfgCmdUp');
		$replace['#' . $replace['#id#'] . '_cfgCmdStop#'] = $this->getConfiguration('cfgCmdStop');
		$replace['#' . $replace['#id#'] . '_cfgCmdDown#'] = $this->getConfiguration('cfgCmdDown');
       	$replace['#' . $replace['#id#'] . '_cfgColorRollerSliderInfo#'] = $this->getConfiguration('cfgColorRollerSliderInfo');
		$replace['#' . $replace['#id#'] . '_cfgColorRollerSlider#'] = $this->getConfiguration('cfgColorRollerSlider');
		$replace['#' . $replace['#id#'] . '_cfgColorOpenSlider#'] = $this->getConfiguration('cfgColorOpenSlider');
		$replace['#' . $replace['#id#'] . '_cfgColorOpenSliderInfo#'] = $this->getConfiguration('cfgColorOpenSliderInfo');
       	$replace['#' . $replace['#id#'] . '_cfgBackgroundImg#'] = $this->getConfiguration('cfgBackgroundImg');
       	$replace['#' . $replace['#id#'] . '_cfgRotateBackgroundImg#'] = $this->getConfiguration('cfgRotateBackgroundImg');
		
		
      	$cmdis=$this->getCmd('info', null);
      	foreach ($cmdis as $cmd) {
          	$cmd_LogId=$cmd->getLogicalId(); 
          	//log::add('voletsEtOuvrants', 'debug',  "Function toHtml - commande info : $cmd_LogId | id : ". $cmd->getId());
          	$replace['#' . $cmd_LogId . '#'] = $cmd->execCmd();
			$replace['#' . $cmd_LogId . '_id#'] = $cmd->getId();
			$replace['#' . $cmd_LogId . '_collectDate#'] =date('d-m-Y H:i:s',strtotime($cmd->getCollectDate()));
			$replace['#' . $cmd_LogId . '_updatetime#'] =date('d-m-Y H:i:s',strtotime( $this->getConfiguration('updatetime')));
			
		}
      
      	$cmdas=$this->getCmd('action', null);
      	foreach ($cmdas as $cmd) {
          	$cmd_LogId=$cmd->getLogicalId(); 
            $replace['#' . $cmd_LogId . '_id#'] = $cmd->getId();
          	//log::add('voletsEtOuvrants', 'debug',  "Function toHtml - commande action : $cmd_LogId | id : ". $cmd->getId());
            if ($cmd->getConfiguration('listValue', '') != '') {
				$listOption = '';
				$elements = explode(';', $cmd->getConfiguration('listValue'));
				$foundSelect = false;
				foreach ($elements as $element) {
					//list($item_val, $item_text) = explode('|', $element);
					$coupleArray = explode('|', $element);
                  	$item_val = $coupleArray[0];
                  	$item_text  = (isset($coupleArray[1])) ? $coupleArray[1]: $item_val;
                  
					$cmdValue = $cmd->getCmdValue();
					
                  	if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
						if ($cmdValue->execCmd() == $item_val) {
                          	$valSelected=$item_text;
							$listOption .= '<option value="' . $item_val . '" selected>' . $item_text . '</option>';
							$foundSelect = true;
						} else {
							$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
						}
					} else {
						$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
					}
				}
				if (!$foundSelect) {
					$listOption = '<option value="" selected>Aucun</option>' . $listOption;
                  	$replace['#' . $cmd->getLogicalId() . '_Value#'] = 'Aucun';
				}else{
                  	$replace['#' . $cmd->getLogicalId() . '_Value#'] = $valSelected;
                }
                  
				
              	$replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listOption;
			}
        }
                                                                                                               
          $html = template_replace($replace, getTemplate('core', $_version, 'default_voletsEtOuvrants', 'voletsEtOuvrants'));
          cache::set('widgetHtml' . $_version . $this->getId(), $html, 1);
          return $html;
	}
	
	public function logCacheDatas($id) {
		$changeStateStart =cache::byKey('voletsEtOuvrants::ChangeStateStart::'.$id)->getValue(false);
		$changeState =cache::byKey('voletsEtOuvrants::ChangeState::'.$id)->getValue(false);
		$changeWay = cache::byKey('voletsEtOuvrants::ChangeWay::'.$id)->getValue();
		$move =cache::byKey('voletsEtOuvrants::Move::'.$id)->getValue(false);
		//$lastCmd =cache::byKey('voletsEtOuvrants::lastCmd::'.$id)->getValue(false);
		$this->writeLog('info','Get data store in cache : changeState ? ' .  $changeState . '| move ?' . $move . '| start time ? ' . $changeStateStart . '| change way ? ' . $changeWay,$this->getHumanName());
	}
}


class voletsEtOuvrantsCmd extends cmd {

  	public function preSave() {
      	$this->getEqLogic()->writeLog('info','		=> PostSave cmd',$this->getHumanName());
      	
		if ($this->getType() == 'info' ) {
          	if ($this->getConfiguration('calcul') != '') {
              	$this->getEqLogic()->writeLog('info','		=> PostSave cmd : ' . $this->getName() . ' -> evaluation : ' . $this->getConfiguration('calcul'),$this->getHumanName());
				$this->event($this->execute());
            } else {
              	if ($this->execCmd() == '' ) {
              		$this->getEqLogic()->writeLog('info','		=> PostSave cmd : ' . $this->getName() . ' -> on positionne la valeur a 100%',$this->getHumanName());
              		$this->getEqLogic()->checkAndUpdateCmd($this->getLogicalId(),100);
                }
            }
          
		}
	}
  
  	public function postSave() {
    }
  
    public function execute($_options = null) {
			
		$eqlogic = $this->getEqLogic();
		$logicalId=$this->getLogicalId();
		$eqlogic->writeLog('info','          ','');	
		$eqlogic->writeLog('info','**************************************************************************************************************************************************','');	
		$eqlogic->writeLog('info','Execution commande : ' . $this->getLogicalId() . '| id eqlogic : ' . $eqlogic->getId() .'| type : ' .$this->getType(),$this->getEqLogic()->getHumanName());	

      switch($this->getLogicalId()) {
			case "up":
				$cmd=cmd::byId(str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdUp')));
				if(is_object($cmd)){
					cache::set('voletsEtOuvrants::ChangeStateStart::'.$this->getEqLogic()->getId(),microtime(true), 0);
					cache::set('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId(),true, 0);
					cache::set('voletsEtOuvrants::ChangeState::'.$this->getEqLogic()->getId(),true, 0);
					cache::set('voletsEtOuvrants::ChangeWay::'.$this->getEqLogic()->getId(),"up", 0);
					cache::set('voletsEtOuvrants::stopAction::'.$this->getEqLogic()->getId(),false, 0);
					
					$this->getEqLogic()->writeLog('info',' - Execution de la commande '.$cmd->getHumanName(),$this->getEqLogic()->getHumanName());	
					$cmd->execute(null);
				} else {
					throw new Exception($this->getHumanName().' la commande ' . $this->getEqLogic()->getConfiguration('cfgCmdUp') . ' n\'exitse pas');
				}
				break;
			case "down":
				$cmd=cmd::byId(str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdDown')));
				if(is_object($cmd)){
					cache::set('voletsEtOuvrants::ChangeStateStart::'.$this->getEqLogic()->getId(),microtime(true), 0);
					cache::set('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId(),true, 0);
					cache::set('voletsEtOuvrants::ChangeState::'.$this->getEqLogic()->getId(),false, 0);
					cache::set('voletsEtOuvrants::ChangeWay::'.$this->getEqLogic()->getId(),"down", 0);
					cache::set('voletsEtOuvrants::stopAction::'.$this->getEqLogic()->getId(),false, 0);
				
					$this->getEqLogic()->writeLog('info',' - Execution de la commande '.$cmd->getHumanName(),$this->getEqLogic()->getHumanName());	
					$cmd->execute(null);
				} else {
					throw new Exception($this->getHumanName().' la commande ' . $this->getEqLogic()->getConfiguration('cfgCmdDown') . ' n\'exitse pas');
				}
				break;
			case "stop":
				cache::set('voletsEtOuvrants::stopAction::'.$this->getEqLogic()->getId(),true, 0);
				$eqlogic->logCacheDatas($eqlogic->getId());
				
				if($this->getEqLogic()->getConfiguration('cfgCmdStop') != ''){
					$cmd=cmd::byId(str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdStop')));
					if(is_object($cmd)){
						$this->getEqLogic()->writeLog('info',' - Execution de la commande '.$cmd->getHumanName(),$this->getEqLogic()->getHumanName());	
						$cmd->execute(null);
					} else {
						throw new Exception($this->getHumanName().' la commande ' . $this->getEqLogic()->getConfiguration('cfgCmdStop') . ' n\'exitse pas');
					}
					
				}else{
					if(cache::byKey('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId())->getValue(false)){
						if(cache::byKey('voletsEtOuvrants::ChangeState::'.$this->getEqLogic()->getId())->getValue(false)) {
							cache::set('voletsEtOuvrants::ChangeWay::'.$this->getEqLogic()->getId(),"up", 0);
							$cmdId=str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdUp'));
						} else {
							cache::set('voletsEtOuvrants::ChangeWay::'.$this->getEqLogic()->getId(),"down", 0);
							$cmdId=str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdDown'));
						}
						$cmd=cmd::byId($cmdId);
						if(is_object($cmd)){
							$this->writeLog('info',' Execution de la commande '.$cmd->getHumanName(),$this->getEqLogic()->getHumanName());	
							$cmd->execute(null);
						} else {
							throw new Exception($this->getHumanName().' la commande ' . $cmdId . ' n\'exitse pas');
						}
					}
				}		
				
				if(cache::byKey('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId())->getValue(false)){
					$this->getEqLogic()->writeLog('info',' Mise a jours manuel de la hauteur',$this->getEqLogic()->getHumanName());	
					cache::set('voletsEtOuvrants::ChangeStateStop::'.$this->getEqLogic()->getId(),microtime(true), 0);
					$eqlogic->UpdateRollerState('');
				}
				
				cache::set('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId(),false, 0);
				cache::set('voletsEtOuvrants::ChangeWay::'.$this->getEqLogic()->getId(),"", 0);
				break;
			case "rollerSlider":
				if(!cache::byKey('voletsEtOuvrants::Move::'.$this->getEqLogic()->getId())->getValue(false)){
					$newRollerState=$_options['slider'];
					if ($this->getEqLogic()->getConfiguration("cfgCmdRollerSlider") != '') {						
						$cmd=cmd::byId(str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdRollerSlider')));
						$this->getEqLogic()->writeLog('info',' - Utilisation du slider interne a l\'équipement pour ouvrir le volet roulant de la fenêtre - id equipement : '. $cmd->getId() . ' | value : ' . $newRollerState ,$this->getEqLogic()->getHumanName());	
						if(is_object($cmd)){
							$options = array('slider'=>$newRollerState);
							$cmd->execCmd($options, $cache=0);
						} else {
							throw new Exception($this->getEqLogic()->getHumanName().' - La commande '. $this->getEqLogic()->getConfiguration('cfgCmdRollerSlider') . ' rollerSlider n\'existe pas');
						}
					} else {
						$eqlogic->execPropVolet($newRollerState);
					}
				} else {
					$this->getEqLogic()->writeLog('info',' - Le voler roulant bouge => pas d\'action sur la commande slider' ,$this->getEqLogic()->getHumanName());	
				}
				break;
			case "openSlider":
				$newOpenState=$_options['slider'];
				if ($this->getEqLogic()->getConfiguration("cfgCmdOpenSlider") != '') {
					$this->getEqLogic()->writeLog('info',' - Utilisation du slider interne a l\'équipement pour ouvrir la fenêtre' ,$this->getEqLogic()->getHumanName());	
					$cmd=cmd::byId(str_replace('#','',$this->getEqLogic()->getConfiguration('cfgCmdOpenSlider')));
					if(is_object($cmd)){
						$options = array('slider'=>$newOpenState);
						$cmd->execCmd($options, $cache=0);
					} else {
						throw new Exception($this->getHumanName().' la commande ' . $this->getEqLogic()->getConfiguration('cfgCmdOpenSlider') . ' n\'exitse pas');
					}
				}	
				break;
			case "openStateRight":
          	case "openStateLeft":
                $cmd=cmd::byId(str_replace('#','',$this->getConfiguration('calcul')));
                if(is_object($cmd)){
                  if ($result == "1") {
                    $this->getEqLogic()->checkAndUpdateCmd($this->getLogicalId(),true);
                    return true;
                  } else {
                    $this->getEqLogic()->checkAndUpdateCmd($this->getLogicalId(),false);
                    return false;
                  }
                  break;
                } else {
                  throw new Exception($this->getHumanName().' la commande ' . $this->getConfiguration('calcul') . ' n\'exitse pas');
                }									
			case "openState":
          	case "rollerState":
                $cmd=cmd::byId(str_replace('#','',$this->getConfiguration('calcul')));
                if(is_object($cmd)){
                  $this->getEqLogic()->checkAndUpdateCmd($this->getLogicalId(),$cmd->execCmd());
                  return $result;
                  break;
                } else {
                  throw new Exception($this->getHumanName().' la commande ' . $this->getConfiguration('calcul') . ' n\'exitse pas');
                }
                //$result = jeedom::evaluateExpression($this->getConfiguration('calcul'));
			case 'rollerTime':
                $this->getEqLogic()->writeLog('info','		* Requête title : '.$_options['title'] . '| message : ' .$_options['message'] ,$this->getEqLogic()->getHumanName());	
                $res='';
                if (isset($_options['message']) and isset($_options['title'])){
                  if ($_options['title']=="up") {
                    $res = $this->getEqLogic()->getRollerTime($_options['title']);
                  } else if ($_options['title']=="down"){
                    $res = $this->getEqLogic()->getRollerTime($_options['title']);
                  }else {
                    $this->getEqLogic()->writeLog('info','		=> Requête non prise en charge : '.$_options['title'],$this->getEqLogic()->getHumanName());
                  }
                }
                $this->getEqLogic()->writeLog('info','		* Temps de travail du volet roulant : ' . $res ,$this->getEqLogic()->getHumanName());	
                return $res;
                break;
        }
    }
}
?>