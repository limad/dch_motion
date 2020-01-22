<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class dchmotion extends eqLogic {
   /*     * *************************Attributs****************************** */
	
	//$this->_dchConfig = $matches[2][0];
	/*     * ***********************Methode static*************************** */
	public $_dchConfig = null;
    public $_isMultizone = false;

    protected $_dchmotion_user;
    protected $_dchmotion_pass;
	public static $_lastdetect = '';
	public static $_client = array();
	public static $_widgetPossibility = array('custom' => true);
	public static $_confar = array(
								'Username'=>"admin",
								'soapUrl' =>"\"http://purenetworks.com/HNAP1/"
								);
    
	public static $_CURLOPTS = array(
        				CURLOPT_CONNECTTIMEOUT => 10,//opt
        				CURLOPT_RETURNTRANSFER => TRUE,
       					CURLOPT_TIMEOUT        => 10,
                        CURLOPT_USERAGENT      => 'MyDlink',//opt
                        CURLOPT_SSL_VERIFYPEER => 0,
                        CURLOPT_FOLLOWLOCATION => TRUE, 
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
                        CURLOPT_POST => true,
   						);
/////////////////////////////////////*********************/////////////////////////////////////

/////////////////////////////////////*********************/////////////////////////////////////
	public function setConf($name=null,$value=null){
		if(isset($name)){
			//return self::Config(null,$name,$value);
			$confar2=array(
				$name=>$value,
			);
			//dchmotion::$_confar=array_merge($confar2,dchmotion::$_confar);
          self::$_confar[$name]=$value;
			//return ;
		}
	}

/////////////////////////////////////*********************///////////////////////////////////// 
    public function Config($param){
		if(self::$_confar[$param]){
			return self::$_confar[$param];
		}
	}

/////////////////////////////////////*********************///////////////////////////////////// 
	public static function interact1($_eqlogic_id = null) {
		log::add('dchmotion', 'info', '  ********** '.__FUNCTION__ .' started... Equipement : '.$this->getName());
	}

/////////////////////////////////////*********************///////////////////////////////////// 
    public static function cron($_eqlogic_id = null) {
    	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' started...');
      	if (config::byKey('functionality::cron5::enable', 'dchmotion', 0) == 1){
			config::save('functionality::cron::enable', 0, 'dchmotion');
          	//$eqLogic->setConfiguration('cronPlugin', 'cron5');
          	//$eqLogic->save();
			return;
		}
      	//$delay=$eqLogic->getConfiguration('cronhalf', null);
		dchmotion::cronJob($_eqlogic_id, null, __FUNCTION__);
    }
/////////////////////////////////////*********************/////////////////////////////////////       
	public static function initPlugConfig($from = null) {//initPlugConfig
		log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' started...');
      	$eqLogics = eqLogic::byType('dchmotion');
      	$return=false;
      	if (count($eqLogics) != 0){
            foreach($eqLogics as $eqLogic) {
                if ($eqLogic->getIsEnable() == 1) {
                    $isEq_enable=true;
                    break;
                }
            }
            if ($isEq_enable != false) {
              	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' Actifs eqlogic exist, Enabling crons...');
                config::save('functionality::cron::enable', 1, 'dchmotion');
                config::save('functionality::cron5::enable', 0, 'dchmotion');
                config::save('functionality::cron15::enable', 1, 'dchmotion');
                config::save('functionality::cronHourly::enable', 1, 'dchmotion');
                config::save('functionality::cronDaily::enable', 1, 'dchmotion');
                config::save('functionality::interact::enable', 0, 'dchmotion');
            }else{
              	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' eqlogics Not actifs, Disabling crons sync...');
                
              	config::save('functionality::cron::enable', 0, 'dchmotion');
                config::save('functionality::cron5::enable', 0, 'dchmotion');
                config::save('functionality::cron15::enable', 0, 'dchmotion');
                config::save('functionality::cronHourly::enable', 0, 'dchmotion');
            }
        }else{
          	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' No eqlogic found, Disabling crons...');
			config::save('functionality::cron::enable', 0, 'dchmotion');
            config::save('functionality::cron5::enable', 0, 'dchmotion');
            config::save('functionality::cron15::enable', 0, 'dchmotion');
            config::save('functionality::cronHourly::enable', 0, 'dchmotion');
            config::save('functionality::cronDaily::enable', 0, 'dchmotion');
            config::save('functionality::interact::enable', 0, 'dchmotion');
		}
      	
    }  	
/////////////////////////////////////*********************/////////////////////////////////////       
	public static function cron5($_eqlogic_id = null) {
		log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' started...');
        if (config::byKey('functionality::cron::enable', 'dchmotion', 0) == 1){
			config::save('functionality::cron5::enable', 0, 'dchmotion');
          	return;
		}
      	dchmotion::cronJob($_eqlogic_id, null, __FUNCTION__);
    }
  
/////////////////////////////////////*********************/////////////////////////////////////
  	public static function cronJob($_eqlogic_id = null, $delay=null, $from=null) {
		if (isset($this)){
			$eqLogics[] = eqLogic::byId($this->getId());
		}elseif($_eqlogic_id !== null){
			$eqLogics[] = eqLogic::byId($_eqlogic_id);
		}else{
			$eqLogics = eqLogic::byType('dchmotion');
		}
		foreach($eqLogics as $eqLogic) {
			if ($eqLogic->getIsEnable() == 1) {
				$eqLogigId = $eqLogic->getId();
				$eqLogicName = $eqLogic->getName();
                $eq_ip= $eqLogic->getConfiguration('addr');       
              	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' started... Equipement: '.$eqLogicName.'(ID '.$eqLogigId.')'.' **********  ');
				//$eq_privK = cache::bykey('dchmotion' .$eqLogigId . 'privK')->getValue();
					if ($eqLogic->getConfiguration('type')=='S150'){
						$eqLogic->getLastMotion('GetLatestDetection');
						//$eqLogicinfo = $eqLogic->getmotionInfos(__FUNCTION__);
						//$eqLogicinfo = $eqLogic->getmotionInfos('GetLatestDetection', 'LatestDetectTime', null,'1');
						$lastcache = cache::bykey('dchmotion' . $eqLogigId . 'motionTstamp')->getValue();
						if ($lastcache != '' && !$delay) {
							log::add('dchmotion', 'debug', __FUNCTION__ .'cron2: '.$lastcahe);
                          	sleep($delay);
                            $eqLogic->getLastMotion('GetLatestDetection');
                        }//return $cahe->getValue();
						
					}
				
            log::add('dchmotion', 'debug', '********** Fin '.__FUNCTION__ .' '.$eqLogicName);
            }//FIN if ($eqLogic->getIsEnable() == 1) {
        	
        }//FIN foreach
		
        return;
	}
/////////////////////////////////////*********************///////////////////////////////////// 
	public static function cron15($_eqlogic_id = null) {
		if($_eqlogic_id !== null){
			$eqLogics = array(eqLogic::byId($_eqlogic_id));
		}
		else{
			$eqLogics = eqLogic::byType('dchmotion');
		}
		foreach($eqLogics as $eqLogic) {
			if ($eqLogic->getIsEnable() == 1) {
				$eqLogigId = $eqLogic->getId();
				$eqLogicName = $eqLogic->getName();
				log::add('dchmotion', 'debug', '  ********** '. __FUNCTION__ .' start : '.$eqLogicName .' **********  ');   
				$nbFailed=$eqLogic->getStatus('numberTryWithoutSuccess', 0);
                if ($nbFailed > 1) {
					log::add('dchmotion', 'error', '  ********** '. __FUNCTION__ .' Plug : '.$eqLogicName .' injoignable nbFailed: '.$nbFailed);
					return;
				}
			}
		}		
		return;
    }

/////////////////////////////////////*********************///////////////////////////////////// 
    public static function cronHourly($_eqlogic_id = null) {
		try {
			if($_eqlogic_id !== null){
				$eqLogics = array(eqLogic::byId($_eqlogic_id));
			}
			else{
				$eqLogics = eqLogic::byType('dchmotion');
			}
			foreach($eqLogics as $eqLogic) {
				if ($eqLogic->getIsEnable() == 1) {
					$eqLogigId = $eqLogic->getId();
					$eqLogicName = $eqLogic->getName();
					log::add('dchmotion', 'debug', '********** '. __FUNCTION__ .' Plug : '.$eqLogicName .' **********  ');   
					//$eqLogicinfo = $eqLogic->getsmartplugInfo();//ok
					//$eqLogicinfo = $eqLogic->getsmartplugInfo(null, null, null, null, $eqLogic);
					//$isConnected = $eqLogic->testIp($eqLogic);
					$testlogin = $eqLogic->testlogin(__FUNCTION__);
				
                    if ($testlogin != false){
                      	$motionInfo = '';
                        $motionInfo = $eqLogic->getmotionInfos(__FUNCTION__);
                      	if ($motionInfo != '') $eqLogic->setStatus('numberTryWithoutSuccess', 0);
                        //$eqLogic->setConfiguration('numberFailed', 0);
                        //$eqLogic->save();
                      	/*if ($eqLogic->getStatus('numberTryWithoutSuccess', 0) > 0) {
							config::save('numberFailed', 0, 'dchmotion');
						}*/
                    }else {
                      	$eqLogic->setStatus('numberTryWithoutSuccess', $eqLogic->getStatus('numberTryWithoutSuccess', 0) + 1);
                      	if ($eqLogic->getStatus('numberTryWithoutSuccess', 0) > 30) {
							log::add('dchmotion', 'error', '  ********** '. __FUNCTION__ .' Plug : '.$eqLogicName .' injoignable  ');
							return;
						}
							
                      	//$eqLogic->setConfiguration('numberFailed', getConfiguration('numberFailed', 0)+1);
                        //$eqLogic->save();
                        
                    }
              	}
			}		
		} 
		catch (Exception $e) {
			//$eqLogic->setStatus('numberTryWithoutSuccess', getStatus('numberTryWithoutSuccess', 0)+1);
			//$eqLogic->save();
			log::add('dchmotion', 'error', __('Erreur' .$eqLogic->getConfiguration('numberFailed', 0) .'sur ', __FILE__) .$eqLogic->getHumanName() . ' : ' . $e->getMessage());
					
		}
        return;
    }
/////////////////////////////////////*********************///////////////////////////////////// 
	public static function cronDaily($from=null) {
		log::add('dchmotion', 'debug', ''. __FUNCTION__ .' started ********* '.' from: '.$from);
		$eqLogics = eqLogic::byType('dchmotion');
		foreach($eqLogics as $eqLogic) {
			if ($eqLogic->getIsEnable() == 1) {
				$eqLogigId = $eqLogic->getId();
				$eqLogicName = $eqLogic->getName();
				
                //$eqLogicinfo = $eqLogic->getsmartplugInfo(__FUNCTION__);
				$eqLogicsetting = $eqLogic->makeEq(__FUNCTION__);
				log::add('dchmotion', 'debug', __FUNCTION__ .' started... Equipement : '.$eqLogicName.'(ID '.$eqLogigId.')' );
			}
		}
		return;
	}

/////////////////////////////////////*********************///////////////////////////////////// 
	public function getLastMotion($action){
		$detect=$this->rqst_infos('GetLatestDetection', 'LatestDetectTime', null,'1');
        $cmd_motionTstamp = $this->getCmd('info', "motionTstamp");
        $cmd_motionTstamp_exec = $cmd_motionTstamp->execCmd();
       
		if ($detect == '' || $detect == null){
			log::add('dchmotion','debug','        '. __FUNCTION__ .' No data ');
			$this->setStatus('numberTryWithoutSuccess', $this->getStatus('numberTryWithoutSuccess', 0)+1);
          	$this->setStatus('Login_Nok', $this->getStatus('Login_Nok', 0)+1);
          	$this->getClient(__FUNCTION__, true);
          
			$return=false;
		} else if ($cmd_motionTstamp_exec == $detect) {
          	$this->setStatus('numberTryWithoutSuccess',0);
          	$this->checkAndUpdateCmd('motionState', false);
			log::add('dchmotion','debug','        '. __FUNCTION__ .' No Change: '.date('d/m/Y H:i:s', $detect));
			$return=$detect;
		} else if ($cmd_motionTstamp_exec != $detect){
			log::add('dchmotion','debug','        '. __FUNCTION__ ." motionTstamp changed from: ".date(' H:i:s', $cmd_motionTstamp_exec).' to: '.date('H:i:s', $detect));
			$changed = true;
			cache::set('dchmotion' . $this->getId() . 'motionTstamp', $detect);
			//cache::set('dchmotion' . $this->getId() . 'lastdetect', $latestDetect);
			$this->checkAndUpdateCmd('motionTstamp', $detect);
			$this->checkAndUpdateCmd('lastDetection', date('d/m/Y H:i:s', $detect));
			$this->checkAndUpdateCmd('motionState', true);
          	$this->setStatus('numberTryWithoutSuccess',0);
          	$this->refreshWidget();
			$return=$detect;
		}else{
			log::add('dchmotion','debug','        '. __FUNCTION__ .' Unknown! detect :'.date('H:i:s', $detect).' cmd :'.date(' H:i:s', $cmd_motionTstamp_exec));
			$return=$detect;
        }
      	if ($changed != false) {
        }
      
      
		return $return;
    }

/////////////////////////////////////*********************///////////////////////////////////// 
    public function syncDsp($from=null){
		log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ .' started *****************');
		return $this->testIp(__FUNCTION__);
	}
/////////////////////////////////////*********************///////////////////////////////////// 
    private function setError($from=null, $level=null){
		log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ .' started *****************');
		
	}
/////////////////////////////////////*********************///////////////////////////////////// 
    private function cretaEq($from=null, $level=null){
		log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ .' started *****************');
		
	}
/////////////////////////////////////*********************///////////////////////////////////// 
	public function getmotionInfos($from=null) {
		
		try {
			$return = array();
            $changed = false;
			$ipsmartplug = $this->getConfiguration('addr');
			$pass = $this->getConfiguration('pwd');
			//$eqLogic = eqLogic::byLogicalId($device['_id'], 'netatmoWeather');
          	//$eqLogic = $this->getEqlogic();
          	log::add('dchmotion', 'debug', '        **** '.__FUNCTION__ .' started '.$ipsmartplug.' **** from: '.$from);
			
			if(!isset($action) || $action==null){
			
				$rqst_msetting = $this->rqst_infos("GetMotionDetectorSettings", array('ModuleID', 'NickName', 'Description', 'Sensitivity', 'OPStatus', 'Backoff'), null, '1');
					
              	if($rqst_msetting != ''){
                	log::add('dchmotion', 'debug','        '. __FUNCTION__ ." rqst_msetting1: ".json_encode($rqst_msetting));
                  	$rqst_minfos = $this->rqst_infos('GetDeviceSettings', array('DeviceMacId', 'DeviceName', 'FirmwareVersion',  'ModelName', 'ModelDescription', 'HardwareVersion'));	
					log::add('dchmotion', 'debug','        '. __FUNCTION__ ." rqst_minfos1: ".json_encode($rqst_minfos));
                }else{
                  	log::add('dchmotion', 'debug','        '. __FUNCTION__ ." rqst_msetting1: walou");
                  	$isConnected = $this->cronHourly($this->getId());
                }
              	
				/*rqst_minfos: {"DeviceMacId":"90:8D:78:ED:8B:62","DeviceName":"motionDsp","FirmwareVersion":"1.23","ModelName":"DCH-S150","ModelDescription":"D-Link Motion Detector","HardwareVersion":"A2"}*/
              
              /*rqst_msetting: {"ModuleID":"1","NickName":"Motion Detector 1","Description":"Motion Detector 1","Sensitivity":"50","OPStatus":"true","Backoff":"20","HardwareVersion":"A2","FirmwareVersion":"1.23","FirmwareRegion":"Default","HNAPVersion":"0111"}
*/
              	$state ="";
				if($rqst_msetting['OPStatus']=='true'){
					$state="1";
				}else if($rqst_msetting['OPStatus']=='false' || $rqst_msetting['OPStatus'] == null ){
					$state="0";
				}
				//($this->dsp_action("GetSocketSettings", "OPStatus") != "false") ? "1" : "0";//  $privatekey, $sess_Cookie, 
				$ModuleID = $rqst_msetting['ModuleID'];
				$NickName = $rqst_msetting['NickName'];
				$Description = $rqst_msetting['Description'];
				$Sensitivity =$rqst_msetting['Sensitivity'];
				$Backoff= $rqst_msetting['Backoff'];
				
              	$DeviceMacId = $rqst_minfos['DeviceMacId'];
                $Device_Name = $rqst_minfos['DeviceName'];
              	$ModelName = $rqst_minfos['ModelName'];
                //log::add('dchmotion', 'debug','        '. __FUNCTION__ .$DeviceMacId. " --- ".$Device_Name. " --- ".$ModelName);
              	$changed = false;
              	
              	$cmd_deviceName = $this->getCmd('info', 'deviceName');
                if ($cmd_deviceName->execCmd() != $Device_Name) {
                  	$changed = true;
                    $this->checkAndUpdateCmd('deviceName', $Device_Name);
                }
              
              	$cmd_macAddress = $this->getCmd('info', 'macAddress');
                if ($cmd_macAddress->execCmd() != $DeviceMacId) {
                  	$changed = true;
                    $this->checkAndUpdateCmd('macAddress', $DeviceMacId);
                }
              
              	$cmd_etat = $this->getCmd('info', 'etat');
                if ($cmd_etat->execCmd() != $state) {
                  	$changed = true;
                    $this->checkAndUpdateCmd('etat', $state);
                }
              
              	$cmd_sensitivity = $this->getCmd('info', 'sensitivity');
                if ($cmd_sensitivity->execCmd() != $Sensitivity) {
                    $changed = true;
                    $this->checkAndUpdateCmd('sensitivity', $Sensitivity);
				}
              
              	$return = $rqst_msetting;
              
				$motionTstamp ='';
				$motionTstamp = $this->rqst_infos('GetLatestDetection', 'LatestDetectTime', null,'1');
				//cache::set('dchmotion' . $this->getId() . 'motionTstamp', $motionTstamp);
              	//cache::set('dchmotion' . $this->getId() . 'lastdetect', date('d/m/Y H:i:s', $motionTstamp));
				$return[] = $motionTstamp;
              
              
				//$cac=cache::bykey('dchmotion' . $this->getId() . 'lastdetect')->getValue();
				log::add('dchmotion','debug','        '. __FUNCTION__ ." motionTstamp: ".$motionTstamp.'--'.date('d/m/Y H:i:s', $motionTstamp));
              	
				
				//$this->getLastMotion('GetLatestDetection');
			}
			else{
				$rqst_act = $this->rqst_infos($action, $responseTag, $opstat, $moduleId);
				if($rqst_act != ''){
                	log::add('dchmotion', 'debug','        '. __FUNCTION__ .' Action: '. $action.' rsp: '.json_encode($rqst_act));
                    if($action == 'GetLatestDetection'){
                       	$motionTstamp = $rqst_act;
                       	$return = $rqst_act;
                    }
                }else{
					log::add('dchmotion', 'debug','        '. __FUNCTION__ .' Action: '. $action.' rsp: Walou');
					//$isConnected = $eqLogic->testlogin(__FUNCTION__);
					//$isConnected = dchmotion::cronHourly($this->getId());
                    $return = $this->cronHourly($this->getId());
                    
                }
              
              //log::add('dchmotion','debug','        '. __FUNCTION__ .' Act: '. $action.': '.json_encode($rqst_resp));
			}
			////////////////////////////////////////////////////////////////
			$changed = false;
			// **** set cmd motionTstamp val
          	$cmd_motionTstamp = $this->getCmd('info', "motionTstamp");
          	$cmd_motionTstamp_exec = $cmd_motionTstamp->execCmd();
          	//$cmd_motionTstamp2 = dchmotionCmd::byEqLogicIdAndLogicalId($this->getId(),'motionTstamp');
            //$cmd_motionTstamp3 = cmd::byEqLogicIdAndLogicalId($this->getId(), 'motionTstamp');
			if ($motionTstamp != '' && $motionTstamp != null && $cmd_motionTstamp_exec != $motionTstamp) {
              	log::add('dchmotion','debug','        '. __FUNCTION__ ." motionTstamp changed from: ".$cmd_motionTstamp_exec.' to: '.$motionTstamp);
              	$changed = true;
				$this->checkAndUpdateCmd('motionState', true);
				$this->checkAndUpdateCmd('motionTstamp', $motionTstamp);
              	$this->checkAndUpdateCmd('lastDetection', date('d/m/Y H:i:s', $motionTstamp));
            }else{
				$this->checkAndUpdateCmd('motionState', false);
			}
          
         ///////////////////////
          	
			
          	if ($changed == true){
				  $this->refreshWidget();
			}
		} catch (Exception $e) {
			log::add('dchmotionCmd','debug','         Exception '.$e);
			return '';
		}
        return $return;
		log::add('dchmotion', 'debug', '		*****Fin '.__FUNCTION__ .' *****		');
	}// fin fc getmotionInfos
	
/////////////////////////////////////*********************///////////////////////////////////// 
    public function makeEq($from=null) {
		log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ .' started ********* '.' from: '.$from.' on: '.$this->getName());
		$ipsmartplug = $this->getConfiguration('addr');
		//$pass = $this->getConfiguration('pwd');
		$type = $this->getConfiguration('type');
      	$DeviceSettings = $this->rqst_infos('GetDeviceSettings', array('DeviceMacId', 'DeviceName', 'FirmwareVersion', 'ModelName', 'ModelDescription', 'HardwareVersion'));	
			
		if($DeviceSettings['DeviceMacId'] == ''){
			log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ ." erreur: DeviceSettings NULL".json_encode($DeviceSettings));
			return;
		}
		log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ ." DeviceSettings: ".json_encode($DeviceSettings));
		$mac = $DeviceSettings['DeviceMacId'];
			log::add('dchmotion',"debug",'mac: '.$mac);
		$Device_Name = $DeviceSettings['DeviceName'];
			//log::add('dchmotion',"debug",'DeviceName: '.$Device_Name);
		$Firmware_Version = $DeviceSettings['FirmwareVersion'];
			//log::add('dchmotion',"debug",'FirmwareVersion: '.$Firmware_Version);
		$Model_Name = $DeviceSettings['ModelName'];
			//log::add('dchmotion',"debug",'ModelName: '.$Model_Name);
             if($Model_Name == 'DCH-S150'){
                 $type = 'S150';
             }
             elseif ($Model_Name == 'DSP-W215'){
                 $type = 'W215';
             }	
		$Model_Description = $DeviceSettings['ModelDescription'];
			//log::add('dchmotion',"debug",'ModelDescription: '.$Model_Description);
		$Hardware_Version = $DeviceSettings['HardwareVersion'];
			//log::add('dchmotion',"debug",'HardwareVersion: '.$Hardware_Version);
				
///////////		
		$eqLog_id = $ipsmartplug.'|'.$mac;
              	
		if($type ==='S150'){
			$MotionSettings = $this->rqst_infos("GetMotionDetectorSettings", array('ModuleID', 'NickName', 'Description', 'Sensitivity', 'OPStatus', 'Backoff', 'HardwareVersion', 'FirmwareVersion', 'FirmwareRegion', 'HNAPVersion'), null, '1');
			log::add('dchmotion', 'debug', '    **** '. __FUNCTION__ ." MotionSettings: ".json_encode($MotionSettings));

			$ModuleID = $MotionSettings['ModuleID'];
			$NickName = $MotionSettings['NickName'];
			$Description = $MotionSettings['Description'];
			$Sensitivity =$MotionSettings['Sensitivity'];
			$Backoff= $MotionSettings['Backoff'];
		}
              
              
            
			////Create eqlogic
			if($this->getLogicalId() == '' && isset($eqLog_id)){
              	$this->setLogicalId($eqLog_id);
              	$this->setIsEnable(1);
              	$this->setIsVisible(1);
               	$this->setConfiguration('type', $type);
              	$this->setConfiguration('DeviceName', $Device_Name);//
                $this->setConfiguration('FirmwareVersion', $Firmware_Version);//
                $this->setConfiguration('ModelName', $Model_Name);//
            
                if ($this->getConfiguration('cronPlugin') == null){
                    $this->setConfiguration('cronPlugin', 'cron');
                }
                if($type ==='S150'){
                  	$this->setCategory('energy', 1);
					$this->setConfiguration('ModuleID', $ModuleID);
					$this->setConfiguration('NickName', $NickName);
					$this->setConfiguration('Description', $Description);
					$this->setConfiguration('Sensitivity', $Sensitivity);
					$this->setConfiguration('Backoff', $Backoff);
					$this->setConfiguration('ModelName', $Model_Name);
                  	$this->setStatus('last_motions', array());
				}
				
				$this->setConfiguration('cronCount', '');
				$this->setConfiguration('numberFailed', 0);
				$this->save();
              	self::initPlugConfig(__FUNCTION__);
		}// fin if($this->getLogicalId()
      	$this->getmotionInfos(__FUNCTION__);
        
      	log::add('dchmotion', 'debug',  '    **** '. __FUNCTION__ .' fin ********* '.' from: '.$from.' on: '.$this->getName());
		return $DeviceSettings;
	}// fin fc makeEq
/////////////////////////////////////*********************///////////////////////////////////// 
	public function extractTagValue($tag, $xmlstring){ 
		$array_resp=array();
      	$tagsarray=array();
		$listzone='';
		$doc = new DOMDocument;
		$doc->loadXML($xmlstring);
		
		if(is_array($tag)){
			foreach ($tag as $key) {
			$node = $doc->getElementsByTagName($key); 
				for($c = 0; $c<$node->length; $c++){ 
						///$text[$c] =$doc->saveXML($node->item($c)); 
						//$value[$c] = $doc->saveXML($tagvalues->item($c));
						//$listzone[$c] =  $doc->saveXML($tagvalues->item($c));
					foreach ($node as $tagvalue) {
						$value = $tagvalue->nodeValue; PHP_EOL;
					}
					$tagsarray=array($key=>$value);
					$listzone=$value[$c] .'|'.$listzone;
				} 
				$array_resp=array_merge($array_resp, $tagsarray);
			}
			return $array_resp;
			
			} 
		else{
			$nodes = $doc->getElementsByTagName($tag);
			if($nodes->length>1){
				foreach ($nodes as $tagvalue) {
					$value= $tagvalue->nodeValue;
					$tagsarray=array($value);
					$array_resp=array_merge($array_resp,$tagsarray);
				} 
				return $array_resp;
			}			
			else{
				foreach ($nodes as $tagvalue) {
					$value= $tagvalue->nodeValue;
				}
				return $value;
			}	
		}
	} 

/////////////////////////////////////*********************///////////////////////////////////// 
	public static function removeAll(){
        log::add('dchmotion', 'debug', __FUNCTION__ . ' start ');
        $eqLogics = eqLogic::byType('dchmotion');
        foreach ($eqLogics as $eqLogic) {
            $eqLogic->remove();
        }
        return array(true,'remove ok');//'remove ok'
    }//fin fc removeAll
/////////////////////////////////////*********************///////////////////////////////////// 
    public function testlogin($from=null) {
		log::add('dchmotion', 'debug', '     '. __FUNCTION__ .' started ********* ' .' sur: '.$this->getName().' from: '.$from);
		$ipsmartplug = $this->getConfiguration('addr');
      		//$this->setConf('addr',$ipsmartplug);
		$pass = $this->getConfiguration('pwd');
      		//$this->setConf('Password',$pass);
          
        $logdsp = $this->soap_login("Login");
		
      	if(!$logdsp){
			$testresult=false;
          	$testIp = $this->testIp(__FUNCTION__);
			if(!$testIp){ 
				throw new Exception(__('L\'adresse IP: '.$ipsmartplug.' est injoignable', __FILE__));
          	}else{
              	$ip_msg='IP joignable mais ';
            }
          	$er_msg=$ip_msg.'Echec Login; Vérifier le mot de pass et la coréspendance IP ! de l\'équipement: '.$this->getName().' '.$this->getLogicalId();
          	log::add('dchmotion', 'error', '     ' .__FUNCTION__ .' '.$er_msg);
			throw new Exception(__($er_msg, __FILE__));
		}else{	
          	$testresult=true;
			log::add('dchmotion', 'debug', '        ' .__FUNCTION__ .' Test-Login Ok '.$this->getName().' '.$this->getLogicalId());
		}
      	return $testresult;
	}//fin fc testlogin
/////////////////////////////////////*********************///////////////////////////////////// 
    public function fcTest($from=null, $forced=false) {
      	/*$start_time = time();
        for ($i = 0; $i <= 1000; $i++) {
    		//$ad=$this->getConfiguration('addr');
          $privK = cache::bykey('dchmotion' . $this->getId() . 'privK')->getValue();
          	usleep(100);
		}
      	$end_time = time();
      	$crono= ($end_time - $start_time)*100;
		return $crono.'sec ** '.$start_time.' ** '.$end_time.' ** '.$ad;
        */
      	$old = $this->getConfiguration('test', 1);
      	$this->setConfiguration('test', $old + 1);
      	$new = $this->getConfiguration('test');
      	$str = 'old: '. $old .' -- new: '. $new;
      	return $str;
	}//fin fc testlogin
/////////////////////////////////////*********************///////////////////////////////////// 
    public function getClient($from=null, $forced=false) {
		      	
      	log::add('dchmotion', 'debug', ''. __FUNCTION__ .' started ********* '.' from: '.$from .' on: '.$this->getName());
        $ipsmartplug = $this->getConfiguration('addr');
      	$eqLogigId = $this->getId();
      	//$pass = $this->getConfiguration('pwd');
      	//log::add('dchmotion', 'debug', ''. __FUNCTION__ .' Login_Nok: '.$this->getStatus('Login_Nok'));
        //log::add('dchmotion', 'debug', ''. __FUNCTION__ .' Login_Nok3: '.$this->getStatus('Login_Nok3'));
      	$oldprivK = cache::bykey('dchmotion' . $eqLogigId . 'privK')->getValue();
      	$oldsesCookie = cache::bykey('dchmotion' .$eqLogigId . 'sesCookie')->getValue();
      	log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' oldprivK: '.$oldprivK .' oldsesCookie: '.$oldsesCookie);
      	if (!$forced){
            if ($oldprivK == null){
                $forced = true;
            }
            $login_state=$this->getStatus('Login_Nok');
            if ($login_state != '' || $login_state != null || $login_state === 0 && $forced==false) {
                log::add('dchmotion', 'debug', '*                      *****'. __FUNCTION__ .' Login est OK: '.$login_state);
                return $oldprivK;
            }
        }
       	try {
				cache::set('dchmotion' . $eqLogigId . 'privK', '');
          		cache::set('dchmotion' . $eqLogigId . 'sesCookie', '');
          		$this->setStatus('Login_Nok', 1);
          		$dsplog = $this->soap_login("Login");
				if(!$dsplog || $dsplog['privK'] == ''){
                    $testresult=false;
                  	log::add('dchmotion', 'debug', '*                      *****'. __FUNCTION__ .' Echec Login: Vérifier le mot de pass et la coréspendance IP !');
                    throw new Exception(__(' Vérifier le mot de pass et la coréspendance IP !', __FILE__));
                }
				//log::add('dchmotion', 'debug', "getClient ".json_encode($dsplog));
          		$this->setStatus('Login_Nok', 0);
                          		
          		cache::set('dchmotion' . $eqLogigId . 'privK', $dsplog['privK']);
          		cache::set('dchmotion' . $eqLogigId . 'sesCookie', $dsplog['sesCookie']);
          		cache::set('dchmotion' . $eqLogigId . 'auth_date', $dsplog['time']);
          		$privK = cache::bykey('dchmotion' .$eqLogigId . 'privK')->getValue();
          		$sesCookie = cache::bykey('dchmotion' .$eqLogigId . 'sesCookie')->getValue();
         		log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' New privK from cache: '.$privK.' New sesCookie from cache: '.$sesCookie);
      			$return = $dsplog;
          		
		} catch (Exception $ex)
			{
				$error_msg = "Echec Login: " . $ex->getMessage() . "\n";
          		log::add('dchmotion', 'debug', '*                      *****'. __FUNCTION__ .' 22 Echec Login: Vérifier le mot de pass et la coréspendance IP !');
				throw new Exception(__($error_msg, __FILE__));
          		//log::add('dchmotion', 'debug', $error_msg);
			}
        //return self::$_client;
        return $return;
	}//fin fc getClient
/////////////////////////////////////*********************///////////////////////////////////// 
	public function preSave() {
		//log::add('dchmotion', 'debug', ' '. __FUNCTION__ .' started ***************** '.$this->getName());
       	//log::add('dchmotion', 'debug', ' '. __FUNCTION__ .' fin '.$this->getName());
	}
/////////////////////////////////////*********************///////////////////////////////////// 
	public function preUpdate() {
      	log::add('dchmotion', 'debug','    '. __FUNCTION__ .' started ***************** '.$this->getName().'->'.$this->getConfiguration('addr'));
		$ip = $this->getConfiguration('addr');
      	if ($ip == '') {
			throw new Exception(__('Le champs adresse IP ne peut être vide', __FILE__));
		}
      	if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        	throw new Exception(__($ip.' ?... C\'est une adresse IP ça ?!!', __FILE__));
		}
      	if ($this->getConfiguration('pwd') == '') {
			throw new Exception(__('Et le mot de passe ? ', __FILE__));
		}
      	log::add('dchmotion', 'debug', '    '. __FUNCTION__ .' fin 1 ***************** '.$this->getName());
    }

//////////////////////////////////////*********************///////////////////////////////////// 
    public function postSave() {
      	log::add('dchmotion', 'debug','    '. __FUNCTION__ .' started ***************** '.$this->getName().'->'.$this->getConfiguration('addr'));
		if (!$this->getConfiguration('addr')){
			return;
        }
      	//$eqLogic = eqLogic::byLogicalId($this->getLogicalId(),'dchmotion');
		//$eqLogic = eqLogic::byId($this->getId());
		
        if ($this->getLogicalId() == null){
          		log::add('dchmotion', 'debug','    ****'. __FUNCTION__ .' LogicalId  null ');
          		//$testlogin = $this->testlogin(__FUNCTION__);
          		$testlogin = $this->getClient(__FUNCTION__, true);
                if(!$testlogin){
                    return;
                }
              	$this->makeEq(__FUNCTION__);
                
      	}
      
      
      	if ($this->getConfiguration('cmdsMaked') != true) {
			$this->makeCmd($this->getLogicalId());
          	
          if($this->getConfiguration('type') != 'S150'){
              	//$this->setStatus('last_seen', '');
                $this->setStatus('last_motions', array());
            }
		}
      	log::add('dchmotion', 'debug', '    '. __FUNCTION__ .' fin *****************'.$this->getName());
	}

/////////////////////////////////////*********************///////////////////////////////////// 
	public function postAjax() {
		log::add('dchmotion', 'debug', '    '. __FUNCTION__ .' started ***************** '.$this->getName());
      		$stateLogin=$this->getStatus('Login_Nok','');
          	//log::add('dchmotion', 'debug','    '. __FUNCTION__ .' Login_Nok_0: '.$stateLogin);
          	/*if ($stateLogin != 0 || $stateLogin >= 1 ){//|| $stateLogin === ''
          		log::add('dchmotion', 'debug','    ****'. __FUNCTION__ .'test Login_Nok '.$stateLogin);
          		$testlogin = $this->testlogin(__FUNCTION__);
                if(!$testlogin){
                    return;
                }
              	$this->makeEq(__FUNCTION__);
                
      		}*/
      		if ($this->getConfiguration('addr') != '' ){//|| $stateLogin === ''
          		log::add('dchmotion', 'debug','    ****'. __FUNCTION__ .' test addr non'.$stateLogin);
          		/*$testlogin = $this->testlogin(__FUNCTION__);
                if(!$testlogin){
                    return;
                }
              	$this->makeEq(__FUNCTION__);
                */
      		}
       	log::add('dchmotion', 'debug', '    '. __FUNCTION__ .' fin '.$this->getName());
    }

/////////////////////////////////////*********************///////////////////////////////////// 
	public function makeCmd($forced=null) {
		log::add('dchmotion', 'info', __FUNCTION__ .' started *****************'.$this->getName());
		$eqtype = $this->getConfiguration('type'); 
		if (!$this->getId() || !$eqtype) return;
      	
      	//$eqLogic=$this;
		
		//////////////////////////////////////////////////	
      	
      	
        /* refresh */
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new dchmotionCmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
        }
      	$refresh->setOrder(1);
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->setEqLogic_id($this->getId());
        $refresh->save();
        
        /* etat */
        $etat = $this->getCmd(null, 'etat');
        if (!is_object($etat)) {
            $etat = new dchmotionCmd();
            $etat->setLogicalId('etat');
            $etat->setName(__('Etat', __FILE__));
        }
        $etat->setOrder(1);
        $etat->setType('info');
        $etat->setDisplay('generic_type','ENERGY_STATE');
        $etat->setSubType('binary');
        $etat->setEqLogic_id($this->getId());
        $etat->save();
        $etatid = $etat->getId();            
               
        /* add mac Adresse */
        $macAddress = $this->getCmd(null, 'macAddress');
        if (!is_object($macAddress)) {
            $macAddress = new dchmotionCmd();
            $macAddress->setLogicalId('macAddress');
            $macAddress->setName(__('Addresse mac', __FILE__));
        }
        $macAddress->setType('info');
        $macAddress->setSubType('other');
        $macAddress->setEqLogic_id($this->getId());
        $macAddress->save();
        
        /* add deviceName */
        $deviceName = $this->getCmd(null, 'deviceName');
		if (!is_object($deviceName)) {
			$deviceName = new dchmotionCmd();
			$deviceName->setName(__('Device Name', __FILE__));
		}
			
		$deviceName->setLogicalId('deviceName');
		$deviceName->setType('info');
		$deviceName->setSubType('string');
		$deviceName->setEqLogic_id($this->getId());
		$deviceName->save();
		
		/* ----------------------------- */
        /*       commandes W215      */
        /* ----------------------------- */
		
		if($eqtype == 'S150') {
			/* set motion on */
			/* add sensitivity */    
			$sensitivity = $this->getCmd(null, 'sensitivity');
			if (!is_object($sensitivity)) {
				$sensitivity = new dchmotionCmd();
				$sensitivity->setLogicalId('sensitivity');
				$sensitivity->setName(__('Sensibilité', __FILE__));
			}
			$sensitivity->setType('info');
			$sensitivity->setSubType('other');
          	$sensitivity->setUnite('%');
			$sensitivity->setEqLogic_id($this->getId());
			$sensitivity->save();
          	
          /* add lastDetection */
          	$lastDetection = $this->getCmd(null, 'lastDetection');
            if (!is_object($lastDetection)) {
                $lastDetection = new dchmotionCmd();
                //$lastDetection->setTemplate('dashboard', 'lastDetection');
               // $lastDetection->setTemplate('mobile', 'lastDetection');
                $lastDetection->setIsVisible(1);
              	//$lastDetection->setIsHistorized(1);
                $lastDetection->setLogicalId('lastDetection');
                $lastDetection->setName(__('lastDetection', __FILE__));
            }
          	$lastDetection->setOrder(3);
          	$lastDetection->setType('info');
            //$lastDetection->setDisplay('generic_type','ENERGY_STATE');
            $lastDetection->setSubType('other');
            $lastDetection->setEqLogic_id($this->getId());
            $lastDetection->save();
		
          /* add motionTstamp */
			$motionTstamp = $this->getCmd(null, 'motionTstamp');
            if (!is_object($motionTstamp)) {
            	$motionTstamp = new dchmotionCmd();
                //$motionTstamp->setTemplate('dashboard', 'motionTstamp');
                //$motionTstamp->setTemplate('mobile', 'motionTstamp');
                $motionTstamp->setIsVisible(0);
              	//$motionTstamp->setIsHistorized(1);
            	$motionTstamp->setLogicalId('motionTstamp');
                $motionTstamp->setName(__('motionTstamp', __FILE__));
            }
            $motionTstamp->setType('info');
          	$motionTstamp->setOrder(4);
            //$motionTstamp->setDisplay('generic_type','ENERGY_STATE');
            $motionTstamp->setSubType('numeric');
            $motionTstamp->setEqLogic_id($this->getId());
            $motionTstamp->save();
                      	         	
          /* add $motionState */
        	$motionState = $this->getCmd(null, 'motionState');
            if (!is_object($motionState)) {
                $motionState = new dchmotionCmd();
                $motionState->setTemplate('dashboard', 'motionState');
                $motionState->setTemplate('mobile', 'motionState');
                $motionState->setIsVisible(1);
                $motionState->setIsHistorized(1);
                $motionState->setLogicalId('motionState');
                $motionState->setName(__('motionState', __FILE__));
            }
            $motionState->setType('info');
          	$motionState->setOrder(4);
            //$motionState->setDisplay('generic_type','ENERGY_STATE');
            $motionState->setSubType('binary');
            $motionState->setEqLogic_id($this->getId());
            $motionState->save();
          	
          	/* add motionDate */
          	$setMon = $this->getCmd(null, 'setMon');
			if (!is_object($setMon)) {
				$setMon = new dchmotionCmd();
				$setMon->setLogicalId('setMon');
				$setMon->setName(__('Activer la détection', __FILE__));
			}
			$setMon->setType('action');
			$setMon->setIsVisible(1);
			//$setMon->setDisplay('generic_type','ENERGY_ON');
			$setMon->setSubType('other');
			$setMon->setEqLogic_id($this->getId());
			$setMon->setValue($etatid);
			$setMon->save();
			/*  set motion off */
			$setMoff = $this->getCmd(null, 'setMoff');
			if (!is_object($setMoff)) {
				$setMoff = new dchmotionCmd();
				$setMoff->setLogicalId('setMoff');
				$setMoff->setName(__('Désactiver la détection', __FILE__));
			}
			$setMoff->setType('action');
			$setMoff->setIsVisible(1);
			$setMoff->setDisplay('generic_type','ENERGY_OFF');
			$setMoff->setSubType('other');
			$setMoff->setEqLogic_id($this->getId());
			$setMoff->setValue($etatid);
			$setMoff->save();
			/*  set sensivity  */
            $sensivityset = $this->getCmd(null, 'sensivityset');
			if (!is_object($sensivityset)) {
				$sensivityset = new dchmotioncmd();
				//$sensivityset->setTemplate('dashboard', 'sensivityset');
				//$sensivityset->setTemplate('mobile', 'sensivityset');
				$sensivityset->setUnite('%');
				$sensivityset->setName(__('Réglage Sensibilité', __FILE__));
				$sensivityset->setIsVisible(1);
			}
			//$sensivityset->setGeneric_type( 'THERMOSTAT_SET_SETPOINT');
			$sensivityset->setEqLogic_id($this->getId());
			$sensivityset->setConfiguration('minValue', 0);
          	$sensivityset->setConfiguration('maxValue', 100);
			$sensivityset->setType('action');
			$sensivityset->setSubType('slider');
			$sensivityset->setLogicalId('sensivityset');
			$sensivityset->setValue($sensitivity->getId());
			$sensivityset->save();
			/* add lastDetection */    
            
            
		}
		$this->setConfiguration('cmdsMaked', true);
      	$this->save();
	}

/////////////////////////////////////*********************///////////////////////////////////// 
	public function testIp($from=null){
        
        $host =  $this->getConfiguration('addr');
        log::add('dchmotion', 'debug', '        '. __FUNCTION__ .' started ********* @IP: '.$host.' from: '.$from);
        
        $fsock = fsockopen($host, '80', $errno, $errstr, 10   );
        if (! $fsock ) {
            log::add('dchmotion', 'error', '        '. __FUNCTION__ .' IP injoignable: '. $host.' --- ' .$this->getName());
          	$this->setStatus('Ip_Nok', $this->getStatus('Ip_Nok', 0)+1);
          	$return= false;
		}else{
          	$this->setStatus('Ip_Nok', 0);
			log::add('dchmotion', 'debug', '        '. __FUNCTION__ .$host.' semble en ligne ---' .$this->getName());
          	$return = true;
		}
        fclose($fsock);
      	return $return;
    }

/////////////////////////////////////*********************///////////////////////////////////// 
	public function soap_login($action, $ipsmartplug=null, $pass=null, $from=null){
		//log::add('dchmotion', 'debug', '    '. __FUNCTION__ .' eq: '.'-'. $ipsmartplug);
		if($ipsmartplug==null){
			$ipsmartplug = $this->getConfiguration('addr');
			$pass = $this->getConfiguration('pwd');
		}
		//log::add('dchmotion', 'debug','    '. __FUNCTION__ .' started ** '.$this->getName().'->'.$this->getConfiguration('addr'));
		
      	$xml_post_string = '<?xml version="1.0" encoding="utf-8"?>'
					.'<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
						.'<soap:Body>'
							.'<'.$action.' xmlns="http://purenetworks.com/HNAP1/">'
									.'<Action>request</Action>'
									.'<Username>'.self::$_confar['Username'].'</Username>'
									.'<LoginPassword></LoginPassword>'
									.'<Captcha></Captcha>'
							.'</'.$action.'>'
						.'</soap:Body>'
					.'</soap:Envelope>';   // data from the form, e.g. some ID number

			$headers = array(
                        "Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
                        "SOAPAction: ".self::$_confar['soapUrl'].$action."\"",
                        "Content-length: ".strlen($xml_post_string),
                    ); 

            $url = 'http://'.$ipsmartplug.'/HNAP1/';

            // PHP cURL  for https connection with auth
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_USERPWD, Config('Username').":".$pass); // username and password - declared at the top of the doc
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
			//curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
//*************************Login Phase2*******************************//////
			
			$challenge = urlencode(self::extractTagValue('Challenge', $response));	
			$sessionCookie = self::extractTagValue('Cookie', $response);
			$publickey = self::extractTagValue('PublicKey', $response);
            $loginresult = self::extractTagValue('LoginResult', $response);
			
			$privatekey =strtoupper(hash_hmac('md5', $challenge, $publickey. $pass));
			$loginpassword = strtoupper(hash_hmac('md5', $challenge, $privatekey));
					
			$SoapLogin="\"http://purenetworks.com/HNAP1/Login\"";
			
			$xml_post_string = '<?xml version="1.0" encoding="utf-8"?>'
					.'<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
						.'<soap:Body>'
							.'<Login xmlns="http://purenetworks.com/HNAP1/">'
									.'<Action>login</Action>'
									.'<Username>'.self::$_confar['Username'].'</Username>'
									.'<LoginPassword>'.$loginpassword.'</LoginPassword>'
									.'<Captcha></Captcha>'
							.'</Login>'
						.'</soap:Body>'
					.'</soap:Envelope>';   

			$headers = array(
                        "Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
						"SOAPAction: ".self::$_confar['soapUrl'].$action."\"",
                        "Content-length: ".strlen($xml_post_string),
						"Cookie : uid=" .$sessionCookie,
                    );

            $url = 'http://'.$ipsmartplug.'/HNAP1/';
			
            // PHP cURL  for https connection with auth
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			
		if($httpcode == 200){
				
			$action_url=self::$_confar['soapUrl'].$action."\"";
			$date = new DateTime();
			$timestamp = $date->getTimestamp();
			//$auth = hash_hmac('md5',$timestamp.$action_url, $privK);
			$hnap_auth = strtoupper(hash_hmac('md5',$timestamp.$action_url, $privK)." ". $timestamp);
			
			$now=date('d.m.Y H:i:s', $timestamp);
			$loginResp= array('privK' => $privatekey, 'sesCookie' => $sessionCookie, 'time' => $now);
			//$this->setConfiguration('auth', $loginResp);
			$this->setStatus('Login_Nok',0);
          	$this->setStatus('auth_date', $now);
          	//$this->setConf('privK_'.$this->getId(), $privatekey);
			//$this->setConf('sesCookie_'.$this->getId(), $sessionCookie);
          	//$this->_dchConfig['privK_'.$this->getId()] = $privatekey;
          	//$this->_dchConfig['sesCookie_'.$this->getId()] = $sessionCookie;
          	//$this->_fullDatas = $jsonDatas['content'];
          	//log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' loginResp: '.json_encode($loginResp));
                  //$this->getId();
			//$this->save();
			return $loginResp;
		} else{
			//log::add('dchmotion', 'error',  __FUNCTION__ .' httpcode: '.$httpcode);
          	//$this->setStatus('auth', '');
          	$this->setStatus('auth_date', '');
			$this->setStatus('Login_Nok', $this->getStatus('Login_Nok', 0) + 1);
			//$this->save();
			//log::add('dchmotion', 'error',  __FUNCTION__ .' Echec login sur: '.$this->getName());
			return false;
		}
	}

/////////////////////////////////////*********************///////////////////////////////////// 
	public function generalRequest($path, $method = 'GET', $action, $xml_add, $headers= array(), $curl_add= array()){
        $moduleId='1';	
		$ipsmartplug = $this->getConfiguration('addr');
		$pass = $this->getConfiguration('pwd');
		$eqLogigId=$this->getId();
		//$privK = $this->getConfiguration('auth')['privK'];
		//$sesCookie = $this->getConfiguration('auth')['sesCookie'];
      	$privK = cache::bykey('dchmotion' . $eqLogigId . 'privK')->getValue();
        $sesCookie = cache::bykey('dchmotion' . $eqLogigId . 'sesCookie')->getValue();
        //log::add('dchmotion', 'debug', '********** '.__FUNCTION__ .' privK: '.$privK.' sesCookie: '.$sesCookie); 		
		
      	$action_url=self::$_confar['soapUrl'].$action."\"";
		
		$date = new DateTime();
		$timestamp = $date->getTimestamp();
		$hnap_auth = strtoupper(hash_hmac('md5',$timestamp.$action_url, $privK)." ". $timestamp);
        $xmltoadd='';
		foreach ($xml_add as $key => $value){
			$xmlparam= '<'.$key.'>'.$value.'</'.$key.'>';
			$xmltoadd=$xmltoadd.$xmlparam;
		}
		
		//log::add('dchmotion', 'debug', 'xmltoadd: ' .$xmltoadd);
		
		$xml_post_string = '<?xml version="1.0" encoding="utf-8"?>'
					.'<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
						.'<soap:Body>'
							.'<'.$action.' xmlns="http://purenetworks.com/HNAP1/">'
							.$xmltoadd		
							.'</'.$action.'>'
						.'</soap:Body>'
					.'</soap:Envelope>'; 
        $headers=	$headers = array(
						"Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
						"SOAPAction: ".self::$_confar['soapUrl'].$action."\"",
						"HNAP_AUTH: " .$hnap_auth,
                        "Content-length: ".strlen($xml_post_string),
						"Cookie : uid=" .$sesCookie,
						"Connection: keep-alive",
						
                    ); 
                    //$headers_add;
		
		
		$url = 'http://'.$ipsmartplug.'/HNAP1/';
		$curl_add = array('CURLOPT_URL'=> $url);
		
		$opts = self::$_CURLOPTS;
        $opts[CURLOPT_POSTFIELDS] = $xml_post_string;
        $opts[CURLOPT_HTTPHEADER] =  $headers;
        $opts[CURLOPT_URL] = $curl_add['CURLOPT_URL'];
		
		$ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
           
		if($httpcode === 200){
			//log::add('dchmotion', 'debug', __FUNCTION__ .' result: '.$result); 
			return $result;
		}else{
			log::add('dchmotion', 'debug', __FUNCTION__ .' Erreur httpcode: '.$httpcode); 
			$this->testlogin(__FUNCTION__);
			
			return $httpcode;
		}
    }//fin fc generalRequest

/////////////////////////////////////*********************///////////////////////////////////// 
	public function rqst_infos($action, $responseTag=null, $opstat=null,$moduleId=null){
		
		//log::add('dchmotion', 'debug','	'. __FUNCTION__ .' started ***************** '.$action);
		if (!isset($moduleId)) { $moduleId='1';}
		if (!isset($Sensitivity)) {$Sensitivity='50';}
		if (!isset($Backoff)) {$Backoff='20';}
		
		$ipsmartplug = $this->getConfiguration('addr');	
      		//$this->setConf('addr',$ipsmartplug);
		$pass = $this->getConfiguration('pwd'); 
      		//$this->setConf('Password',$pass);
		$eqLogigId = $this->getId();
        $eqLogicName = $this->getName();
		
		//$privK = $this->getConfiguration('auth')['privK'];
		//$sesCookie = $this->getConfiguration('auth')['sesCookie'];
		$privK = cache::bykey('dchmotion' .$eqLogigId . 'privK')->getValue();
        $sesCookie = cache::bykey('dchmotion' .$eqLogigId . 'sesCookie')->getValue();
        $action_url=self::$_confar['soapUrl'].$action."\"";
		
		$date = new DateTime();
		$timestamp = $date->getTimestamp();
		$hnap_auth = strtoupper(hash_hmac('md5',$timestamp.$action_url, $privK)." ". $timestamp);
		
		
		$action_url=self::$_confar['soapUrl'].$action."\"";
		
		$headers = array(
						"Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
						"SOAPAction: ".self::$_confar['soapUrl'].$action."\"",
						"HNAP_AUTH: " .$hnap_auth,
                        //"Content-length: ".strlen($xml_post_string),
						"Cookie : uid=" .$sesCookie,
					); 

        
		$url = 'http://'.$ipsmartplug.'/HNAP1/';
		$curl_add = array('CURLOPT_URL'=> $url);
		//SetMotionDetectorSettings, 
		
			switch($action) {
				
				case 'GetMotionDetectorSettings'://'GetMotionDetectorLogs', ModuleID=self.module_id, MaxCount=1, PageOffset=1, StartTime=0, EndTime='All'
					$xml_add = array('ModuleID' => $moduleId);
					break;//call('GetSystemLogs', MaxCount=100,PageOffset=1, StartTime=0, EndTime='All')	
				
				case 'GetLatestDetection'://'GetMotionDetectorLogs', ModuleID=self.module_id, MaxCount=1, PageOffset=1, StartTime=0, EndTime='All'
					$xml_add = array('ModuleID' => $moduleId);
					break;//call('GetSystemLogs', MaxCount=100,PageOffset=1, StartTime=0, EndTime='All')	
				case 'GetMotionDetectorLogs'://'GetMotionDetectorLogs', ModuleID=self.module_id, MaxCount=1, PageOffset=1, StartTime=0, EndTime='All'
					$xml_add = array(
								'ModuleID' => $moduleId,
								'NickName' => 'Motion Detector 1',
								'Description' => 'Motion Detector 1',
								'OPStatus' => $opstat,
								//'MaxCount' => '1',
								//'PageOffset' => '1',
								//'StartTime' => '0',
								//'EndTime' => 'All',
							);
					break;
				case 'GetSystemLogs'://'GetMotionDetectorLogs', ModuleID=self.module_id, MaxCount=1, PageOffset=1, StartTime=0, EndTime='All'
					$xml_add = array(
								'ModuleID' => $moduleId,
								'NickName' => 'Motion Detector 1',
								'Description' => 'Motion Detector 1',
								'OPStatus' => $opstat,
								'MaxCount' => '1',
								'PageOffset' => '1',
								'StartTime' => '0',
								'EndTime' => 'All',
							);
					break;
				default:
					$xml_add = array('ModuleID' => $moduleId);
				break;		
			}//fin  swich
		
		//log::add('dchmotion', 'debug','	'. __FUNCTION__ .' $xml_add: '.json_encode($xml_add));	
		
		
		//generalRequest($path, $method = 'GET', $action, $xml_add, $headers= array(), $curl_add= array()){
		$ret = $this->generalRequest($url, $method = 'GET', $action, $xml_add, $headers, $curl_add);
			//log::add('dchmotion', 'debug','	'. __FUNCTION__ .' ret: '.$action.'-'.$ret);	
		
		
		$reponse = self::extractTagValue($responseTag, $ret);
			//log::add('dchmotion', 'debug','		'. __FUNCTION__ .' reponse pour '.$action.': '.json_encode($reponse));	
		
			
		return $reponse;
			
			
	}//fin fc rqst_infos

/////////////////////////////////////*********************///////////////////////////////////// 
    public function rqst_action($action, $responseTag=null, $opstat=null,$moduleId=null){
		
		log::add('dchmotion', 'debug','	'. __FUNCTION__ .' started ***************** '.$action.'-'.$opstat.'-'.$moduleId);
		if (!isset($moduleId)) { $moduleId='1';}
		if (!isset($Sensitivity)) {$Sensitivity='50';}
		if (!isset($Backoff)) {$Backoff='20';}
		
		$ipsmartplug = $this->getConfiguration('addr');	
      		//$this->setConf('addr',$ipsmartplug);
		$pass = $this->getConfiguration('pwd'); 
      		//$this->setConf('Password',$pass);
				
		$eqLogigId = $this->getId();
        $eqLogicName = $this->getName();
		
			
		//$privK = $this->getConfiguration('auth')['privK'];
		//$sesCookie = $this->getConfiguration('auth')['sesCookie'];
		$privK = cache::bykey('dchmotion' .$eqLogigId . 'privK')->getValue();
        $sesCookie = cache::bykey('dchmotion' .$eqLogigId . 'sesCookie')->getValue();
        $action_url=self::$_confar['soapUrl'].$action."\"";
		
		$date = new DateTime();
		$timestamp = $date->getTimestamp();
		$hnap_auth = strtoupper(hash_hmac('md5',$timestamp.$action_url, $privK)." ". $timestamp);
		
		
		$action_url=self::$_confar['soapUrl'].$action."\"";
		
		$headers = array(
						"Content-type: text/xml;charset=\"utf-8\"",
                        "Accept: text/xml",
                        "Cache-Control: no-cache",
                        "Pragma: no-cache",
						"SOAPAction: ".self::$_confar['soapUrl'].$action."\"",
						"HNAP_AUTH: " .$hnap_auth,
                        "Content-length: ".strlen($xml_post_string),
						"Cookie : uid=" .$sesCookie,
					); 

        
		$url = 'http://'.$ipsmartplug.'/HNAP1/';
		$curl_add = array('CURLOPT_URL'=> $url);
		//SetMotionDetectorSettings, 
		
			switch($action) {
				case 'SetMotionDetectorSettings':
					$xml_add = array(
								'ModuleID' => $moduleId,
								'NickName' => 'Motion Detector 1',
								'Description' => 'Motion Detector 1',
								'OPStatus' => $opstat,
								'Sensitivity' => $Sensitivity,
								'Backoff' => $Backoff,
								//'Controller' => '1',
							);
					break;
				case 'SetSocketSettings':
					$xml_add = array(
								'ModuleID' => $moduleId,
								'NickName' => 'Motion Detector 1',
								'Description' => 'Motion Detector 1',
								'OPStatus' => $opstat,
								//'Duration' => '300',
								//'RadioID' => 'RADIO_2.4GHz',
								//'Controller' => '1',
							);
					break;
				
				default:
					$xml_add = array('ModuleID' => $moduleId);
				break;		
			}//fin  swich
		
		//log::add('dchmotion', 'debug', __FUNCTION__ .' $xml_add: '.json_encode($xml_add));	
		
		
		//generalRequest($path, $method = 'GET', $action, $xml_add, $headers= array(), $curl_add= array()){
		$ret = $this->generalRequest($url, $method = 'GET', $action, $xml_add, $headers, $curl_add);
			//log::add('dchmotion', 'debug', __FUNCTION__ .' ret: '.$action.'-'.$ret);	
		
		
		$reponse = self::extractTagValue($responseTag, $ret);
			//log::add('dchmotion', 'debug','	'. __FUNCTION__ .' reponse pour '.$responseTag.': '.json_encode($reponse));	
		
		if ($this->getConfiguration('type')=='W215'){
			$this->getsmartplugInfo();
		} else if ($this->getConfiguration('type')=='S150'){
			$this->getmotionInfos(__FUNCTION__);
		} 
		return $reponse;
			
			
	}//fin fc rqst_action
  
/////////////////////////////////////*********************/////////////////////////////////////    
    public function toHtml($_version = 'dashboard') {
		
		$replace = $this->preToHtml($_version);
 		if (!is_array($replace)) {
 			return $replace;
  		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		$_eqType = $this->getConfiguration('type');
		////////////////////// CMD Info /////////////////////
		
            
        foreach ($this->getCmd('info') as $cmd) {
          	$cmdi_logId = $cmd->getLogicalId();
            //traitement particulier
			if ($cmdi_logId == 'motionTstamp'){
				$actualdate=date('d/m/Y');
              	//$hierdate = date('d/m/Y', strtotime("-1 days"));
              	$hierdate = date('d/m/Y', strtotime('yesterday'));
              	$motionTstamp = $cmd->execCmd();
              	
              	if ($actualdate <= date('d/m/Y', $motionTstamp-43200) || $actualdate == date('d/m/Y', $motionTstamp) ) {
					$lastDetectH = date('H:i', $motionTstamp);
				} elseif ($hierdate == date('d/m/Y', $motionTstamp) ) {
					$lastDetectH = 'Hier '.date('H:i', $motionTstamp);
				} else {
					$lastDetectH = date('d/m H:i', $motionTstamp);
				}
              	$replace['#lastDetection_human#'] = $lastDetectH;
            }
			
          
          
          	$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				
			$replace['#' . $cmd->getLogicalId() . '_collectDate#'] =date('d-m-Y H:i:s',strtotime($cmd->getCollectDate()));
			$replace['#' . $cmd->getLogicalId() . '_updatetime#'] =date('d-m-Y H:i:s',strtotime( $this->getConfiguration('updatetime')));
			$replace['#lastCommunication#'] =date('d-m-Y H:i:s',strtotime($this->getStatus('lastCommunication')));
			$replace['#numberTryWithoutSuccess#'] = $this->getStatus('numberTryWithoutSuccess');//
			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
		}
		foreach ($this->getCmd('action') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            
            if ($cmd->getConfiguration('listValue', '') != '') {
				$listOption = '';
				$elements = explode(';', $cmd->getConfiguration('listValue', ''));
				$foundSelect = false;
				foreach ($elements as $element) {
					list($item_val, $item_text) = explode('|', $element);
					//$coupleArray = explode('|', $element);
					$cmdValue = $cmd->getCmdValue();
					if (is_object($cmdValue) && $cmdValue->getType() == 'info') {
						if ($cmdValue->execCmd() == $item_val) {
							$listOption .= '<option value="' . $item_val . '" selected>' . $item_text . '</option>';
							$foundSelect = true;
						} else {
							$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
						}
					} else {
						$listOption .= '<option value="' . $item_val . '">' . $item_text . '</option>';
					}
				}
				//if (!$foundSelect) {
				//	$listOption = '<option value="">Aucun</option>' . $listOption;
				//}
				
				//$replace['#listValue#'] = $listOption;
				 $replace['#' . $cmd->getLogicalId() . '_id_listValue#'] = $listOption;
				 $replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listOption;
			}
			
        }
		/*switch ($this->getDisplay('layout::' . $version)) {
			case 'table':
			$replace['#eqLogic_class#'] = 'eqLogic_layout_table';
			$table = self::generateHtmlTable($this->getDisplay('layout::' . $version . '::table::nbLine', 1), $this->getDisplay('layout::' . $version . '::table::nbColumn', 1), $this->getDisplay('layout::' . $version . '::table::parameters'));
			$br_before = 0;
			foreach ($this->getCmd(null, null, true) as $cmd) {
				if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
					continue;
				}
				$tag = '#cmd::' . $this->getDisplay('layout::' . $version . '::table::cmd::' . $cmd->getId() . '::line', 1) . '::' . $this->getDisplay('layout::' . $version . '::table::cmd::' . $cmd->getId() . '::column', 1) . '#';
				if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
					$table['tag'][$tag] .= '<br/>';
				}
				$table['tag'][$tag] .= $cmd->toHtml($_version, '', $replace['#cmd-background-color#']);
				$br_before = 0;
				if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
					$table['tag'][$tag] .= '<br/>';
					$br_before = 1;
				}
			}
			$replace['#cmd#'] = template_replace($table['tag'], $table['html']);
			break;
			default:
			$replace['#eqLogic_class#'] = 'eqLogic_layout_default';
			$cmd_html = '';
			$br_before = 0;
			foreach ($this->getCmd(null, null, true) as $cmd) {
				if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
					continue;
				}
				if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
					$cmd_html .= '<br/>';
				}
				$cmd_html .= $cmd->toHtml($_version, '', $replace['#cmd-background-color#']);
				$br_before = 0;
				if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
					$cmd_html .= '<br/>';
					$br_before = 1;
				}
			}
			$replace['#cmd#'] = $cmd_html;
			break;
		}
		
		*/
		
			if (!isset(self::$_templateArray[$version])) {
				//self::$_templateArray[$version] = getTemplate('core', $version, 'eqLogic');
				//$templateArray[$version] = getTemplate('core', $version, 'eqLogic');
				if (version_compare(jeedom::version(), '4.0.0') >= 0) {
					$templateArray[$version] = getTemplate('core', $version, $_eqType, 'dchmotion');
					//$templateArray[$version] = getTemplate('core', $version, 'eqLogic');
				} else {
					$templateArray[$version] = getTemplate('core', $version, $_eqType.'_v3', 'dchmotion');
				}
			//$replace['#template#'] = $template;
			//$replace['#cmd#'] = $cmd_html;

			}
		
			return $this->postToHtml($_version, template_replace($replace, $templateArray[$version])); 
		}

    /*     * **********************Getteur Setteur*************************** */
    
}

class dchmotionCmd extends cmd {

/////////////////////////////////////*********************/////////////////////////////////////   
   public function execute($_options = array()) {
        
        if ($this->getType() == '') {
            return '';
        }
        
        $action= $this->getLogicalId();
        $eqLogic = $this->getEqlogic();
        $eqlogicId = $eqLogic->getId();
        $ipsmartplug = $eqLogic->getConfiguration('addr');
		log::add('dchmotion', 'debug','action: '. $action.' sur: '.$eqLogic->getName().'(ID '.$eqlogicId.')');
		 
        if ($action == 'refresh') {
            //log::add('dchmotion','debug','refresh !!! '.$eqLogic->getName().'(ID '.$eqlogicId.')');
            //$eqLogic->cron($eqlogicId);
          	//$eqLogic->cronHourly($eqlogicId);
          	$eqLogic->getmotionInfos(__FUNCTION__);
          //
            //$eqLogic->getLastMotion('GetLatestDetection');
				
        }
       /* set  : setMon */
       //getmotionInfos(__FUNCTION__)
        if ($action == 'setMon') {
			$command = $eqLogic->rqst_action("SetMotionDetectorSettings", 'SetMotionDetectorSettingsResult', "true",'1'); 
				log::add('dchmotion','info','cmd '.$action.' is: '.json_encode($command));
				$eqLogic->getmotionInfos(__FUNCTION__);
        }
        /* set  : setMoff */
        if ($action == 'setMoff') {
			$command = $eqLogic->rqst_action("SetMotionDetectorSettings", 'SetMotionDetectorSettingsResult', "false",'1');
				log::add('dchmotion','info','cmd '.$action.' is: '.json_encode($command));
				$eqLogic->getmotionInfos(__FUNCTION__);
        }
        /* set  : sensivityset */
        if ($action == 'sensivityset') {
			log::add('dchmotion','debug','****cmd: '.$action.' to slider value: '.$_options['slider']);
          	if (!isset($_options['slider']) || $_options['slider'] == '' || !is_numeric(intval($_options['slider']))) {
				return;
			}
          	$command = $eqLogic->rqst_action("SetSocketSettings", 'SetSocketSettingsResult', "true",'1'); 
			log::add('dchmotion','debug','cmd '.$action.' rsp: '.json_encode($command));
			$eqLogic->getmotionInfos(__FUNCTION__);
        }
       /* set  : on */
        if ($action == 'on') {
			$command = $eqLogic->getsmartplugInfo("SetSocketSettings", 'SetSocketSettingsResult', "true",'1'); 
			//$command = $eqLogic->rqst_action("SetSocketSettings", "SetSocketSettingsResult", "true"); 
				log::add('dchmotion','info','cmd '.$action.' rsp is: '.json_encode($command));
				//$eqLogic->cron($eqlogicId);
          		$eqLogic->getmotionInfos(__FUNCTION__);
        }
        
        /* set  : off */
        if ($action == 'off') {
            $command= $eqLogic->getsmartplugInfo("SetSocketSettings", "SetSocketSettingsResult", "false","1");
           	log::add('dchmotion','info','cmd '.$action.' rsp is: '.json_encode($command));
            //$eqLogic->cron($eqlogicId);
          	$eqLogic->getmotionInfos(__FUNCTION__);
        }
        
        /* set  : nightmodeon */
		if ($action == 'nightmodeon') {
			$command= $eqLogic->getClient();
			log::add('dchmotion','info','cmd '.$action.' : '.json_encode($command));
            //$eqLogic->cron($eqlogicId);
          	$eqLogic->getmotionInfos(__FUNCTION__);
		}
        if ($action == 'planningset') {
			$scheduleid = $_options['message'];
			$command = $eqLogic->changescheduleTherm($eqLogic->getLogicalId(),$scheduleid);
		}
        
        
    }
  /*     * **********************Getteur Setteur*************************** */
}

?>