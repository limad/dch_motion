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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
$plugId = 'dchmotion';
function dchmotion_install() {
  	global $plugId;
  	log::add('dchmotion', 'info', '  ********** '.__FUNCTION__ .' started... Plugin: '.$plugId);
  	log::add('dchmotion', 'info', '  ********** '.__FUNCTION__ .' Configuration des crons... ');
    config::save('functionality::cron::enable', 1, $plugId);
	config::save('functionality::cron5::enable', 0, $plugId);
  	config::save('functionality::cron15::enable', 1, $plugId);
  	config::save('functionality::cronHourly::enable', 1, $plugId);
  	config::save('functionality::cronDaily::enable', 1, $plugId);
  	config::save('functionality::interact::enable', 0, $plugId);
  	log::add('dchmotion', 'info', '  ********** '.__FUNCTION__ .' Fin ');
}

function dchmotion_update() {
  	global $plugId;
  	log::add('dchmotion', 'info', '  ********** '.__FUNCTION__ .' started... Plugin: '.$plugId);
 	foreach (eqLogic::byType($plugId) as $eqLogic) {
        $eqLogic->save();
    }
}


function dchmotion_remove() {
    
}
dchmotion_install();
?>