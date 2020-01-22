
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
//var eqid = $('.eqLogicAttr[data-l1key=name]').getValues();
//var eqid1 = $(this).getValues('.eqLogicAttr');
//var eqid2 = $(this).attr('data-eqLogic_id');


//console.log('eqid: ' + eqid.name);
$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').on('change', function () {
  	$(".eqName").empty().append($('.eqLogicAttr[data-l1key=name]').value());   
 	
  	if($('.li_eqLogic.active').attr('data-eqlogic_id') != ''){
    	$('#img_type').attr("src", 'plugins/dchmotion/core/img/'+$(this).value()+'.png');
	}else{
    	$('#img_type').attr("src",'plugins/dchmotion/core/img/mydlink.png');
	}
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cronPlugin]').on('change', function () {
  	
  	if( $(this).value() == 'cron'){
      	$('#cronhalfselect').show();
	}
});



$('#bt_removeAll').on('click', function () {
	 //console.log('init removeAll action');
	 bootbox.confirm('{{Etes-vous sûr de vouloir supprimer tous les équipements ?}}', function (result) {
	        if (result) {
	            $.ajax({
	                type: "POST", // méthode de transmission des données au fichier php
	                url: "plugins/dchmotion/core/ajax/dchmotion.ajax.php", 
	                data: {
	                    action: "removeAll",
	                    id: $('.eqLogicAttr[data-l1key=id]').value()
	                },
	                dataType: 'json',
	                global: false,
	                error: function (request, status, error) {
	                    handleAjaxError(request, status, error);
	                },
	                success: function (data) { 
	                    if (data.state != 'ok') {
                          	$('#div_alert').showAlert({message: data.result, level: 'danger'});
	                        return;
	                    }
                      	//$(this).location=document.URL;
                      	location.reload();
	                    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
	                    $('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
	                }
	            });
	        }
	    });
	 console.log('end removeAll action');
 });


$('#bt_healthsmartplug').on('click', function () {
	$('#md_modal').dialog({title: "{{Santé SmarPlug}}"});
	$('#md_modal').load('index.php?v=d&plugin=dchmotion&modal=health').dialog('open');
});
$('#bt_logdsp').on('click', function () {
	$('#md_modal').dialog({title: "{{logdsp}}"});
	$('#md_modal').load('log/dchmotion').dialog('open');
});
 
$('#bt_fctest').on('click',function(){
	//var eqid = $('.eqLogicAttr[data-l1key=id]').value();
  	//var eqLogics = [];
  	var eqname = $('.eqLogicAttr[data-l1key=name]').value();
  /*$.ajax({
        type: "POST",
        url: "plugins/dchmotion/core/ajax/dchmotion.ajax.php",
        data: {
          action: "fcTest",
          id : $('.eqLogicAttr[data-l1key=id]').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
          handleAjaxError(request, status, error);
        },
        success: function (data) {
          if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
          }
          
          // config::save($key, jeedom::fromHumanReadable($value), init('plugin', 'core'));
                var vare1 = $('.configKey[data-l1key=addr]').val();// $('.configKey[data-l1key=username]');//'Synchronisation réussie';
                var vare2 = $('.configKey[data-l1key=pwd]').val();
                //var vare3 = $('.configKey[data-l1key=]').val();
                //ok $('#div_alert').showAlert({message: '{{'+ vare1 +' * '+ vare2 +'}}', level: 'success'});
          $('#div_alert').showAlert({message: data.result, level: 'success'});
        }
      });
    */
  console.log('name: ' + eqname + '--' + eqType + '--' + eqid);
  $('.eqLogicAction[data-action=save]').click();
  /*
  jeedom.eqLogic.save({
    type: eqType,
    id: eqid,
    eqLogics: [{name: eqname}]
     
    //eqLogics: eqLogics
    
  });
*/

  //type: eqLogic.eqType_name,
  //eqLogics: [{name: result}],
  /*jeedom.getCronSelectModal({},function (result) {
	   //options.selectedIndex
       $('.eqLogicAttr[data-l1key=configuration][data-l2key=cronrefresh]').value(result.value);
   });*/
  //savePluginConfig();
});

$('.eqLogicAction[data-action=saveeq]').on('click', function () {
    var eqLogics = [];
    $('.eqLogic').each(function () {
        if ($(this).is(':visible')) {
            var eqLogic = $(this).getValues('.eqLogicAttr');
            eqLogic = eqLogic[0];
            eqLogic.cmd = $(this).find('.cmd').getValues('.cmdAttr');
            if ('function' == typeof (saveEqLogic)) {
                eqLogic = saveEqLogic(eqLogic);
            }
            eqLogics.push(eqLogic);
        }
    });
    jeedom.eqLogic.save({
        type: isset($(this).attr('data-eqLogic_type')) ? $(this).attr('data-eqLogic_type') : eqType,
        id: $(this).attr('data-eqLogic_id'),
        eqLogics: eqLogics,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {
            console.log(data)
            modifyWithoutSave = false;
            if (updateDisplayPlugin !== undefined)
                updateDisplayPlugin(function () {
                    $('body .li_eqLogic[data-eqLogic_id="' + data.id + '"]').click();
                });
            $('#div_alert').showAlert({message: '{{Sauvegarde effectuée avec succès}}', level: 'success'});
        }
    });
    return false;
});

$('#bt_getclient').on('click',function(){
	//saveEqLogic(_eqLogic)
  	$.ajax({
        type: "POST",
        url: "plugins/dchmotion/core/ajax/dchmotion.ajax.php",
        data: {
          action: "getclient",
          id : $('.eqLogicAttr[data-l1key=id]').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
          handleAjaxError(request, status, error);
        },
        success: function (data) {
          if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
          }
          
          // config::save($key, jeedom::fromHumanReadable($value), init('plugin', 'core'));
                var vare1 = $('.configKey[data-l1key=addr]').val();// $('.configKey[data-l1key=username]');//'Synchronisation réussie';
                var vare2 = $('.configKey[data-l1key=pwd]').val();
                //var vare3 = $('.configKey[data-l1key=]').val();
                //ok $('#div_alert').showAlert({message: '{{'+ vare1 +' * '+ vare2 +'}}', level: 'success'});
          $('#div_alert').showAlert({message: 'ok: ' + data.result, level: 'success'});
        }
      });
    
});

$('#bt_testdchmotion').on('click', function () {
	$('#md_modal').dialog({title: "{{Panel test}}"});
	$('#md_modal').load('../../plugins/dchmotion/desktop/php/panel.php').dialog('open');
});

$('#bt_cronrefresh1').on('click',function(){
	//alert($('#bt_cronrefresh').options.value);
	jeedom.getCronSelectModal({},function (result) {
	   //options.selectedIndex
       $('.eqLogicAttr[data-l1key=configuration][data-l2key=cronrefresh]').value(result.value);
   });
});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) var _cmd = {configuration: {}}
    if (!isset(_cmd.configuration)) _cmd.configuration = {}
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
        tr += '<td>'
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none">'
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none">'
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none">'
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 60%" placeholder="{{Nom}}"></td>'
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="display : none" />'

        tr += '</td>'

        tr += '<td>'
  		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		
        if (_cmd.subType == "numeric" || _cmd.subType == "binary") {
            tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized"/>{{Historiser}}</label></span> '
        }
        tr += '</td>'

        tr += '<td>'
        if (is_numeric(_cmd.id))
        {
            tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> '
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>'
        }
        tr += '</td>'
    tr += '</tr>'

    if (_cmd.type == 'info')
    {
        $('#table_infos tbody').append(tr)
        $('#table_infos tbody tr:last').setValues(_cmd, '.cmdAttr')
        if (isset(_cmd.type)) $('#table_infos tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type))
        jeedom.cmd.changeType($('#table_infos tbody tr:last'), init(_cmd.subType))
    }
    else
    {
        $('#table_actions tbody').append(tr)
        $('#table_actions tbody tr:last').setValues(_cmd, '.cmdAttr')
        if (isset(_cmd.type)) $('#table_actions tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type))
        jeedom.cmd.changeType($('#table_actions tbody tr:last'), init(_cmd.subType))
    }
}
/////////////////////////////////////////////////////////////////////
function saveEqLogic(_eqLogic) {
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {}
    }

    _eqLogic.configuration.programs = []
  	//jeedom.eqLogic.getSelectModal({}, function (result)
    console.log("saveEq: " + _eqLogic.name)
    return _eqLogic
}

$('#bt_eqConfigRaw').off('click').on('click',function(){
  var eqid= $('.eqLogicAttr[data-l1key=id]').value();
  
	$('#md_modal2').dialog({title: "{{Informations brutes}}"});
	$("#md_modal2").load('index.php?v=d&modal=object.display&class=eqLogic&id='+eqid).dialog('open');
})

//$('.eqLogicDisplayCard[data-eqLogic_id='+$('.eqLogicAttr[data-l1key=id]').value()+']').click();
/*
tr.find('.cmdAttr[data-l1key=value]').append(result);
        tr.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').append(result);
        tr.setValues(_cmd, '.cmdAttr');
        jeedom.cmd.changeType(tr, init(_cmd.subType));
*/
//jeedom.eqLogic.builSelectCmd(

//jeedom.eqLogic.getCmd
//#bt_savePluginFunctionalityConfig
/*
jwerty.key('ctrl+s/⌘+s', function (e) {
  e.preventDefault();
  $("#bt_savePluginConfig").click();
});
*/
//jeedom.eqLogic.simpleSave