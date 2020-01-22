<?php

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');

if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();

}

?>


<div class="rowPlugConfig">No Config</div>


		
<script>
//////////////////////////
  	$(document).ready(function() {
		var parentTag = $('.rowPlugConfig').parent().parent().parent();
  		//$('.rowPlugConfig').append( ' - ' + parentTag.attr('class') );
       	//parentTag.attr('style', 'color : #B93A3E!important');
		parentTag.attr('style', 'display : none');
  	});

//////////////////////////
$('#bt_savePluginPanelConfig').on('click',function(){
		if($('.configKey[data-l1key=ipbox]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'IP invalide ! Renseignez l\'IP de la box SVP' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
				return;
		}
		else if($('.configKey[data-l1key=dchmotionLogin]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'Le champ Identifiant ne peut être vide !' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
			return;
		}
		else if($('.configKey[data-l1key=dchmotionPassword]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'Le champ Mot de passe ne peut être vide !' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
				return;
		}
	
	
	
	});

//////////////////////////
	$("input[data-l1key='functionality::cron::enable']").on('change',function(){
        if ($(this).is(':checked')) 
			$("input[data-l1key='functionality::cron5::enable']").prop("checked", false)
	});

//////////////////////////
    $("input[data-l1key='functionality::cron5::enable']").on('change',function(){
        if ($(this).is(':checked')) 
			$("input[data-l1key='functionality::cron::enable']").prop("checked", false)
    });
	
//////////////////////////
	$('#bt_syncBox').on('click', function (){	
			if($('.configKey[data-l1key=ipbox]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'IP invalide ! Renseignez l\'IP de la box SVP' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
				return;
			}
			else if($('.configKey[data-l1key=dchmotionLogin]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'Le champ Identifiant ne peut être vide !' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
				return;
			}
			else if($('.configKey[data-l1key=dchmotionPassword]').val() === ''){
				$('#div_alertPluginConfiguration').showAlert({message: 'Le champ Mot de passe ne peut être vide !' , level: 'warning'});
				$('#div_alertPluginConfiguration').removeClass('alert-success');
				$('#div_alertPluginConfiguration').addClass('alert-danger');
				return;
			}
			else{
				$('#div_alertPluginConfiguration').removeClass('alert-success');
              	$('#div_alertPluginConfiguration').removeClass('alert-danger');
              	$('#div_alert').empty();
				savePluginConfig();
				$.ajax({
					type: "POST",
						url: "plugins/dchmotion/core/ajax/dchmotion.ajax.php",
						data: { action: "syncData",},
						dataType: 'json',
						error: function (request, status, error) {
							handleAjaxError(request, status, error);
						},
						success: function (data) {
							if (data.state != 'ok') {//erreur api
								if (data.result != 'bad request') {
									$('#div_alert').showAlert({message: '{{Echec de la tentative de connexion: }}' + data.result, level: 'danger'});
									$('#div_alertPluginConfiguration').append('<br>Echec de connexion. Vérifier que les informations saisies sont correctes. Msg: ' + data.result);
									$('#div_alertPluginConfiguration').removeClass('alert-success');
									$('#div_alertPluginConfiguration').addClass('alert-danger');
								}else{ //pas de reponse api
									$('#div_alert').showAlert({message: '{{Echec de la tentative de connexion: }}' + data.result , level: 'danger'});
									$('#div_alertPluginConfiguration').append('<br>Echec de connexion. Vérifier la connectivité internet. No response' );
									$('#div_alertPluginConfiguration').removeClass('alert-success');
									$('#div_alertPluginConfiguration').addClass('alert-danger');

								}
								return;
							}
							//$('#div_alertPluginConfiguration').append('<br>Bravo!.. Synchronisation réussie');
							$('#div_alertPluginConfiguration').append('<br>Bravo!.. Synchronisation '  + ' - ' + data.result);//+ data.state
							$('#div_alert').showAlert({message: 'Actualiser la page ! (F5)', level: 'success'});
							//$('#ul_plugin .li_plugin[data-plugin_id=dchmotion]').click();
						}
				});
			}
	});
	
	

</script>