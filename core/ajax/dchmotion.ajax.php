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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	
	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	
	ajax::init();
  	////////// 
	if (init('action') == 'removeAll') {
        $return = dchmotion::removeAll();
        
        if ($return[0]) {
            ajax::success($return[1]);
        } else {
            ajax::error($return[1]);
        }
    }
    
  	if (init('action') == 'getdchmotion') {
		if (init('object_id') == '') {
			$object = jeeObject::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
		} else {
			$object = jeeObject::byId(init('object_id'));
		}
		if (!is_object($object)) {
			$object = jeeObject::rootObject();
		}
		$return = array();
		$return['eqLogics'] = array();
		if (init('object_id') == '') {
			foreach (jeeObject::all() as $object) {
				foreach ($object->getEqLogic(true, false, 'dchmotion') as $dchmotion) {
					$return['eqLogics'][] = $dchmotion->toHtml(init('version'));
				}
			}
		} else {
			foreach ($object->getEqLogic(true, false, 'dchmotion') as $dchmotion) {
				$return['eqLogics'][] = $dchmotion->toHtml(init('version'));
			}
			foreach (jeeObject::buildTree($object) as $child) {
				$dchmotions = $child->getEqLogic(true, false, 'dchmotion');
				if (count($dchmotions) > 0) {
					foreach ($dchmotions as $dchmotion) {
						$return['eqLogics'][] = $dchmotion->toHtml(init('version'));
					}
				}
			}
		}
		ajax::success($return);
	}
	if (init('action') == 'getclient') {
      	//$cost = config::byKey(init('cost'), 'dchmotion');
		$motion = dchmotion::byId(init('id'));
      	$eqLogic = eqLogic::byId(init('id'));
		
      	if (!is_object($motion)) {
			throw new Exception(__('Impossible de trouver cet équipement : ' . init('id'), __FILE__).$motion.$eqLogic);
		}
		//$motion->getClient('Ajax');
      	$eqLogic->getClient('AjaxPhp', true);
      	//$motion->setComment($values['comment']);
		//$motion->save();
		ajax::success();
      	//ajax::success($motion);
	}
  	
  	if (init('action') == 'fcTest') {
      	//$cost = config::byKey(init('cost'), 'dchmotion');
		$motion = dchmotion::byId(init('id'));
      	$eqLogic = eqLogic::byId(init('id'));
		
      	if (!is_object($motion)) {
			throw new Exception(__('Impossible de trouver cet équipement : ' . init('id'), __FILE__).$motion.$eqLogic);
		}
		//$motion->getClient('Ajax');
      	$testRsp=$eqLogic->fcTest('AjaxPhp');
      	//$motion->setComment($values['comment']);
		//$motion->save();
		ajax::success($testRsp);
      	//ajax::success($motion);
	}
	
  	if (init('action') == 'emptyHistory') {
		$eqLogic = eqLogic::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement introuvable : ', __FILE__) . init('id'));
		}
		$eqLogic->emptyHistory();
		ajax::success();
	}
  
	if (init('action') == 'syncDsp2') {
		//$act = dchmotion::testlogin("Login");
		//$act=dchmotion::syncDsp();
		$act=dchmotion::getClient() ;
		//$act=dchmotion::getsmartplugsetting();
		//$act=cron();
		//$act=dchmotion::getsmartplugInfo();
		ajax::success();
	}		

    throw new Exception(__('AjaxPhp; Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>