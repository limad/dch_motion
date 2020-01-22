<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('dchmotion');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$eqname = $plugin->getName();
//$object->getEqLogic(true, false, 'thermostat')

?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br/>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf" >
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
            <div class="cursor eqLogicAction logoDefault" data-action="removeAll" id="bt_removeAll">
				<i class="fas fa-minus-circle" style="color: #FA5858;"></i>
				<br/>
				<span>{{Supprimer tous}}</span>
			</div>
  			<div class="cursor eqLogicAction logoSecondary" id="bt_healthsmartplug">
				<i class="fas fa-medkit"></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes SmartPlugs}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				//if ($eqLogic->getConfiguration('model', '') != '') {
            	if ($eqLogic->getConfiguration('type', '') != '') {
                  	echo '<img src="plugins/dchmotion/core/img/' . $eqLogic->getConfiguration('type', '') . '.jpg" />';
        		} else {
            		echo '<img src="plugins/dchmotion/core/img/mydlink.png" />';
        		}
              	echo '<br/>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
              	<a class="btn btn-default btn-sm roundedLeft" id="bt_eqConfigRaw"><i class="fas fa-info"></i> {{ }}</a>
				
              <a class="btn btn-default btn-sm eqLogicAction" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fas fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}
</legend>
             
    <div class="row">
						
      
      
      					<div class="col-lg-8">
  						<br/>
              			<div class="form-group">
							<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" >{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (jeeObject::all() as $object) {
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Catégorie}}</label>
							<div class="col-sm-8">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-8">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>
  						</div>
  		<div class="col-lg-2"><!--img-->
		</br>
		<form class="form-horizontal">
		<fieldset>
				
			<div class="form-group" style="display : none;">
				
				<label class="col-sm-2 control-label">{{Type }}</label>
				<div class="col-sm-9">
					
					<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type">
						<option value="S150" selcted>{{Motion DCH‑S150}}</option>
                        <option value="W215">{{Plug DSP‑W215}}</option>
					</select>
					
				</div>
				
			</div>
        </fieldset>
		</form>
						
                        
                        
			<center>
			  <img src="core/img/no_image.gif" data-original=".jpg" id="img_type" class="img-responsive" style="max-height : 200px;" onerror="this.src='plugins/dchmotion/core/img/mydlink.png'"/>
			
			</center>
			
		
	</div>
	</div><!--row-->
  
  
  
                        <legend><i class="fas fa-wrench"></i>  {{Configuration}}</legend>
                        
						 <div class="form-group">
                          	<label class="col-sm-2 control-label">{{Adresse IP}}</label>
                          	<div class="col-sm-2">
                         	 	<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addr" placeholder="{{Adresse IP}}"/>
                          	</div>
                         </div>

                         <div class="form-group">
                              <label class="col-sm-2 control-label">{{Password}}</label>
                              <div class="col-sm-2">
                                  <input type="password" autocomplete="new-password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pwd" placeholder="{{Password}}"/>
                                  
                              </div>
                         </div>
						
						<div class="form-group cronhalfselect" style="display: none;" id="cronhalfselect">
                            <label class="col-sm-2 control-label">{{Sélection (cron)}}</label>
                            <div class="col-sm-2">
                                <select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="cronhalf" id="bt_cronrefresh">					<option value="30">{{30 secondes}}</option>
                                    <option value="" selected="selected">{{1 minute}}</option>
                                    
                                </select>
                            </div>
                        </div>	
						 <div class="form-group" style="display: none;" id="cronPlugin">
                              <label class="col-sm-2 control-label">{{cron Plugin}}</label>
                               
                              <div class="col-sm-2">
              <select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="cronPlugin" id="sl_cronPlugin">						<option value="cron" selected="selected">cron</option>
              			<option value="cron5">cron5</option>
                    
                                    
                                </select>
                                </div>
                         </div>
                         
                         
                         
                         <div class="form-group">
                              <label class="col-sm-2 control-label">{{Synchroniser}}</label>
                              <div class="col-sm-2">
                                  <a class="btn btn-warning" id="bt_getclient"><i class='fas fa-refresh'></i> {{Synchroniser...}}</a>
                                  <a class="btn btn-warning" id="bt_fctest"><i class='fas fa-refresh'></i> {{fctest...}}</a>
                              </div>
                               <div class="col-sm-2">
                                  
                              </div>
                          </div>
						 
                        
					</fieldset>
				</form>
			</div>
            
            
            <!-- *********** commandtab  ****-->
			<div class="tab-pane" id="commandtab">
				
				<legend>
					<center class="title_cmdtable">{{Tableau de commandes <?php echo ' - '.$eqname.': ';?>}}
						<span class="eqName"></span>
					</center>
				</legend>
				
				<legend><i class="fas fa-info-circle"></i>  {{Commandes Infos}}</legend>
						
						<table id="table_infos" class="table table-bordered table-condensed">
							
							<thead>
								<tr>
                                    <th width="65%">{{Nom}}</th>
                                    <th width="25%" align="center">{{Options}}</th>
                                    <th width="10%" align="right">{{Action}}</th>
                                  </tr>
							</thead>
							<tbody></tbody>
						</table>

						<legend><i class="fas fa-list-alt"></i>  {{Commandes Actions}}</legend>
						<table id="table_actions" class="table table-bordered table-condensed">
							
							<thead>
								  <tr>
                                    <th width="65%">{{Nom}}</th>
                                    <th width="25%" align="center">{{Options}}</th>
                                    <th width="10%" align="right">{{Action}}</th>
                                  </tr>
							</thead>
							<tbody></tbody>
						</table>

				
					</div><!--fin *********** commandtab  ****-->
                    
                    
             
            
            
		</div>
	</div>
</div>


    
<?php include_file('desktop', 'dchmotion', 'js', 'dchmotion');?>
<?php include_file('core', 'plugin.template', 'js');?>