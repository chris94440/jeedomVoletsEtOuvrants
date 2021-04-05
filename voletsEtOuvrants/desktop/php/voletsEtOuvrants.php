<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('voletsEtOuvrants');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">    
   	<div class="col-xs-12 eqLogicThumbnailDisplay">
  		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
      			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      				<i class="fas fa-wrench"></i>
    				<br>
    				<span>{{Configuration}}</span>
  			</div>
  		</div>
  		<legend><i class="fas fa-table"></i> {{Mes volets roulants et ouvrants}}</legend>
	   	<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
    		<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
		?>
		</div>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure">
					<i class="fa fa-cogs"></i>
					 {{Configuration avancée}}
				</a>
				<a class="btn btn-default btn-sm eqLogicAction" data-action="copy">
					<i class="fas fa-copy"></i>
					 {{Dupliquer}}
				</a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save">
					<i class="fas fa-check-circle"></i>
					 {{Sauvegarder}}
				</a>
				<a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove">
					<i class="fas fa-minus-circle"></i>
					 {{Supprimer}}
				</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Equipement}}
				</a>
			</li>
			<li role="presentation">
				<a href="#configTabVr" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Volet roulant}}
				</a>
			</li>
			<li role="presentation">
				<a href="#configTabListeners" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Listeners}}
				</a>
			</li>
			<li role="presentation" >
				<a href="#configTabOuvrants" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Fenêtre}}
				</a>
			</li>
			<li role="presentation" >
				<a href="#configTabDesign" aria-controls="home" role="tab" data-toggle="tab">
				<i class="fa fa-tachometer"></i> 
					{{Design tuile}}
				</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> 
					{{Commandes}}
				</a>
			</li>
  		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<div class="col-sm-6">
    					<form class="form-horizontal">
						<legend>Général</legend>
							<fieldset>
								<div class="form-group ">
									<label class="col-sm-3 control-label">{{Nom du volet}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Indiquer le nom de votre volet}}" style="font-size : 1em;color:grey;"></i>
										</sup>
									</label>
									<div class="col-sm-3">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du volet}}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label" >{{Objet parent}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Indiquer l'objet dans lequel le widget de cette zone apparaîtra sur le Dashboard}}" style="font-size : 1em;color:grey;"></i>
										</sup>
									</label>
									<div class="col-sm-3">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
												foreach (jeeObject::all() as $object) 
													echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">
										{{Catégorie}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Choisir une catégorie. Cette information n'est pas obigatoire mais peut être utile pour filtrer les widgets}}" style="font-size : 1em;color:grey;"></i>
										</sup>
									</label>
									<div class="col-sm-9">
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
									<label class="col-sm-3 control-label" >
										{{Etat du widget}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Choisir les options de visibilité et d'activation. Si l'équipement n'est pas activé, il ne sera pas utilisable dans Jeedom ni visible sur le Dashboard. Si l'équipement n'est pas visible, il sera caché sur le Dashboard}}" style="font-size : 1em;color:grey;"></i>
										</sup>
									</label>
									<div class="col-sm-9">
										<label class="checkbox-inline">
											<input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
											{{Activer}}
										</label>
										<label class="checkbox-inline">
											<input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
											{{Visible}}
										</label>
									</div>
								</div>
								<!--
								<div class="form-group ">
									<label class="col-sm-3 control-label">{{Délais minimum entre 2 commandes}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Saisir un délai minimum(s) entre 2 commande}}" style="font-size : 1em;color:grey;"></i>
										</sup>
									</label>
									<div class="col-sm-5">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="delaisMini" placeholder="{{Délais minimum (s)}}"/>
									</div>
								</div>								
								-->
							</fieldset>
						</form>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="configTabVr">
					<br/>
					<div class="row">
						<div class="col-sm-7">
							<form class="form-horizontal">
							<fieldset>
								<legend>Contrôle du volet roulant</legend>
								<div class="form-group">
									<label class="col-md-6 control-label">{{Commande d'ouverture du volet roulant}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant d'ouvrir le volet}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdUp" placeholder="{{Séléctionner une commande}}"/>
											<span class="input-group-btn">
												<a class="btn btn-success btn-sm listCmdAction" data-type="action">
													<i class="fa fa-list-alt"></i>
												</a>
											</span>
										</div>
									</div>
								</div>	
								<div class="form-group">
									<label class="col-md-6 control-label">{{Commande d'arrêt du volet roulant}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant l'arrêt du volet}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdStop" placeholder="{{Séléctionner une commande}}"/>
											<span class="input-group-btn">
												<a class="btn btn-success btn-sm listCmdAction" data-type="action">
													<i class="fa fa-list-alt"></i>
												</a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-6 control-label">{{Commande de fermeture du volet roulant}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant la fermeture du volet}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdDown" placeholder="{{Séléctionner une commande}}"/>
											<span class="input-group-btn">
												<a class="btn btn-success btn-sm listCmdAction" data-type="action">
													<i class="fa fa-list-alt"></i>
												</a>
											</span>
											
										</div>
									</div>
								</div>
								<div class="form-group">
										<label class="col-md-6 control-label" >
											{{Deleguer l'état du volet roulant au plugin}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Si actif le plugin ne gèrera pas la hauteur du volet roulant}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="cfgPluginManageRollerState"/>
										</div>
									</div>
								<div class="form-group" data-l1key="configuration" data-l2key="cfgGrpSliderRoller">
									<label class="col-md-6 control-label">{{Curseur gestion ouverture / fermeture }}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettent de gérer l'ouverture et la fermeture du volet roulant}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdRollerSlider" placeholder="{{Séléctionner une commande}}"/>
											<span class="input-group-btn">
												<a class="btn btn-success btn-sm listCmdAction" data-type="action">
													<i class="fa fa-list-alt"></i>
												</a>
											</span>
											
										</div>
									</div>
									<label class="col-md-6 control-label">{{Etat du volet roulant }}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant de définir la position du volet roulant}}"></i>
										</sup>
									</label>
									
									<div class="col-md-6">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgRollerState" placeholder="{{Séléctionner une commande}}"/>
											<span class="input-group-btn">
												<a class="btn btn-success btn-sm listCmdAction" data-type="info">
													<i class="fa fa-list-alt"></i>
												</a>
											</span>
											
										</div>
									</div>								
								</div>
							<legend>Delais</legend>
								<div class="form-group">
									<label class="col-md-6 control-label">{{Temps d'ouverture total}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Saisissez le temps total pour exécuter une ouverture complète du volet roulant}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="col-md-6">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgOpenTime" placeholder="{{Saisir le temps d'ouverture totale}}">	
											</input>											
										</div>
										<div class="col-md-6">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgOpenTimeUnit">
												<option value="1000000">{{Seconde}}</option>                  
												<option value="1000">{{Miliseconde}}</option>                  
												<option value="1">{{Microseconde}}</option>   
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-6 control-label">{{Temps fermeture totale}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Saisissez le temps total pour exécuter une fermeture complète du volet roulant}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="col-md-6">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgCloseTime" placeholder="{{Saisir le temps de fermeture totale}}"/>
										</div>
										<div class="col-md-6">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgCloseTimeUnit">
												<option value="1000000">{{Seconde}}</option>                  
												<option value="1000">{{Miliseconde}}</option>                  
												<option value="1">{{Microseconde}}</option>   
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-6 control-label">{{Temps de décollement}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Saisissez le temps de décollement. Temps avant que le volet se décolle de son seuil}}"></i>
										</sup>
									</label>
									<div class="col-md-6">
										<div class="col-md-6">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgDecolTime" placeholder="{{Saisir le temps de décollement}}"/>
										</div>
										<div class="col-md-6">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgDecolTimeUnit">
												<option value="1000000">{{Seconde}}</option>                  
												<option value="1000">{{Miliseconde}}</option>                  
												<option value="1">{{Microseconde}}</option>   
											</select>
										</div>
									</div>
								</div>
							</fieldset>
						</form>
						</div>
                        <div id="infoNodeVrs" class="col-sm-4">
							<fieldset>
								<legend>{{Informations}}</legend>
									<div id="div_instruction_vrs"></div>
							</fieldset>					
						</div> 
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="configTabListeners">
					<br/>
					<div class="row">
						<div class="col-sm-7">
							<form class="form-horizontal">
								<fieldset>
									<legend>Définition des commandes à écouter</legend>
									<div class="form-group">
										<label class="col-md-6 control-label">{{Commande externe de statut d'action du volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande à écouter pour connaitre le statut de l'action demandée}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="EventMvtCmd" placeholder="{{Séléctionner une commande}}"/>
												<span class="input-group-btn">
													<a class="btn btn-success btn-sm listCmdAction" data-type="info">
														<i class="fa fa-list-alt"></i>
													</a>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormExterneStatutValue">
										<label class="col-md-6 control-label">{{Valeur pour une action de montée du volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la valeur renvoyée par la commande info lors d'une action de montée du volet roulant}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgStatutUp" placeholder="{{Saisir la valeur attendue}}"/>
											</div>
										</div>
										<label class="col-md-6 control-label">{{Valeur pour une action d'arrêt du volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la valeur renvoyée par la commande info lors d'une action d'arrêt du volet roulant}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgStatutStop" placeholder="{{Saisir la valeur attendue}}"/>
											</div>
										</div>
										<label class="col-md-6 control-label">{{Valeur pour une action de descente du volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la valeur renvoyée par la commande info lors d'une action de descente du volet roulant}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgStatutDown" placeholder="{{Saisir la valeur attendue}}"/>
											</div>
										</div>
									</div>
								</fieldset>
							</form>
						</div>
                        <div id="infoNodeVrs" class="col-sm-4">
							<fieldset>
								<legend>{{Informations}}</legend>
									<div id="div_instruction_vr_listeners"></div>
							</fieldset>					
						</div> 
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="configTabOuvrants">
					<br/>
					<div class="row">
						<div class="col-sm-7">
							<form class="form-horizontal">
								<fieldset>
									<div class="form-group">
										<label class="col-md-6 control-label">{{Type fenêtre}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Choisissez le type de fenêtre}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgWindowType">
													<option value="windows">{{Fenêtre                }}</option>                  
													<option value="porte">{{Porte fenêtre     }}</option>                  
													<option value="baie">{{Baie vitrée         }}</option>   
													<option value="velux">{{Vélux             }}</option>   
												</select>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-6 control-label">{{Taille}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la taille de la fenêtre}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgWindowSize">
													<option value="size-s">{{Taille s               }}</option>                  
													<option value="size-m">{{Taille m               }}</option>
													<option value="size-l">{{Taille l               }}</option>
													<option value="size-xl">{{Taille xl               }}</option>
													<option value="size-mk">{{Taille mk               }}</option>
													<option value="size-uk">{{Taille uk               }}</option>
												</select>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-6 control-label">{{Couleure fenêtre}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleure de la fenêtre}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgWindowColor">
													<option value="white">{{Blanc          }}</option>                  
													<option value="wood">{{Bois            }}</option>
													<option value="beige">{{Beige           }}</option> 													
													<option value="black">{{Noir           }}</option>   
												</select>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-6 control-label">{{Couleur du volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleure des lames du volet roulant}}"></i>
											</sup>
										</label>
										<div class="col-sm-5">
											<div class="input-group">
												<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfRollerSliderColor">
													<option value="white">{{Blanc          }}</option>                  
													<option value="wood">{{Bois            }}</option>                  
													<option value="black">{{Noir           }}</option>   
												</select>
											</div>
										</div>
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgGrpCmdOpen">
										<label class="col-md-6 control-label">{{Commande d'ouverture de la fenêtre}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant l'ouverture de la fenêtre}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdOpenSlider" placeholder="{{Sélectionner la commande permettant l'ouverture / fermeture dela fenêtre}}"/>
												<span class="input-group-btn">
													<a class="btn btn-success listCmdAction input-group-addon roundedLeft" data-type="action">
														<i class="fa fa-list-alt"></i>
													</a>
												</span>
												
											</div>
										</div>
										<label class="col-md-6 control-label">{{Info état ouverture }}
												<sup>
													<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant de récupérer l'état d'ouverture }}"></i>
												</sup>
											</label>
											<div class="col-md-6">
												<div class="input-group">
													<span class="input-group-btn">
														<a class="btn btn-success listCmdAction input-group-addon roundedLeft" data-type="info">
															<i class="fa fa-list-alt"></i>
														</a>
													</span>
													<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgOpenState" placeholder="Séléctionner une commande" style="width: 180px">											
												</div>
											</div>
									</div>
									<!--<div data-l1key="configuration" data-l2key="cfgGrpInfoOpenState">-->
										<div class="form-group" data-l1key="configuration" data-l2key="cfgInfoOpenStateRight">
											<label class="col-md-6 control-label">{{Info état ouverture côté droit}}
												<sup>
													<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant de récupérer l'état d'ouverture côté droit}}"></i>
												</sup>
											</label>
											<div class="col-md-6">
												<div class="input-group">
													<span class="input-group-btn">
														<a class="btn btn-success listCmdAction input-group-addon roundedLeft" data-type="info">
															<i class="fa fa-list-alt"></i>
														</a>
													</span>
													<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdOpenStateRight" placeholder="Séléctionner une commande" style="width: 180px">											
												</div>
											</div>
										</div>
										<div class="form-group" data-l1key="configuration" data-l2key="cfgInfoOpenStateLeft">
											<label class="col-md-6 control-label">{{Info état ouverture côté gauche}}
												<sup>
													<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande permettant de récupérer l'état d'ouverture côté gauche}}"></i>
												</sup>
											</label>
											<div class="col-md-6">
												<div class="input-group">
													<span class="input-group-btn">
														<a class="btn btn-success listCmdAction input-group-addon roundedLeft" data-type="info">
															<i class="fa fa-list-alt"></i>
														</a>
													</span>
													<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="cfgCmdOpenStateLeft" placeholder="Séléctionner une commande" style="width: 180px">											
												</div>
											</div>
										</div>	
									<div class="form-group" >
									<label class="col-sm-3 control-label" >
										{{Mode debug ?}}
										<sup>
											<i class="fa fa-question-circle tooltips" title="{{Permet d'afficher plus de traces dans la log du plugin et dans la console du navigateur}}"></i>
										</sup>
									</label>
									<div class="col-sm-5">
										<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="cfgDebug"/>
									</div>
								</div>								
								</fieldset>
							</form>
						</div>
						<div id="infoNodeOuvrants" class="col-sm-4">
							<fieldset>
								<legend>{{Informations}}</legend>
									<div id="div_instruction_ouvrants"></div>
								<center>
								</br>
								<img src="core/img/no_image.gif" data-original=".png" id="img_type" class="img-responsive" style="max-height : 200px;"  onerror=""/>
								</center>								
							</fieldset>					
						</div> 
					</div>

				</div>
				<div role="tabpanel" class="tab-pane" id="configTabDesign">
					<br/>
					<div class="row">
						<div class="col-sm-7">
							<form class="form-horizontal">
								<fieldset>
									<div class="form-group">
										<label class="col-sm-3 control-label" >
											{{Afficher label de l'équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Voulez-vous afficher un label sur le design de l'équipement ?}}"></i>
											</sup>
										</label>
										<div class="col-sm-5">
											<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="cfgHasEqLabel"/>
										</div>
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormLabel">
										<label class="col-md-6 control-label">{{Label équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez le nom à afficher sur la tuile}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgLabel" placeholder="{{Saisir le nom à afficher}}"/>
											</div>
										</div>
									</div>
									
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormColorLabel">
										<label class="col-md-6 control-label">{{Couleure du label}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleur du label}}"></i>
											</sup>
										</label>
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorLabel" value="#424242">
										</div>
									</div>																		
									<div class="form-group" >
										<label class="col-sm-3 control-label" >
											{{Afficher bordure équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Voulez-vous afficher une bordure autour de votre équipement}}"></i>
											</sup>
										</label>
										<div class="col-sm-5">
											<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="cfgHasEqBorder"/>
										</div>
									</div>								
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormColorBorder">
										<label class="col-md-6 control-label">{{Couleur bordure équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleur de la bordure}}"></i>
											</sup>
										</label>
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorBorderEq" value="#424242">
										</div>
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormBorder">
										<label class="col-md-6 control-label">{{Type bordure équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la type de bordure}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgTypeBorderEq" placeholder="{{Saisir le type de bordure}}"/>
											</div>
										</div>
									</div>
                                    <div class="form-group" data-l1key="configuration" data-l2key="cfgFormWidthBorder">
										<label class="col-md-6 control-label">{{Epaisseur bordure équipement}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez l'épaisseur de la bordure}}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgWidthBorderEq" placeholder="{{Saisir le type de bordure}}"/>
											</div>
										</div>
									</div>
                                    </br>
									<div class="form-group">
									<label class="col-sm-3 control-label" >
											{{Gestion slider et info bulle}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Voulez-vous gérer la couleur des sliders et des infos bulles }}"></i>
											</sup>
									</label>
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormColorRollerSlider">
										<label class="col-md-6 control-label">{{Couleur slider gestion volets roulants}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleur }}"></i>
											</sup>
										</label>
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorRollerSlider" value="">
										</div>									
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormValueRollerSlider">
										<label class="col-md-6 control-label">{{Couleur bulle info position volet roulant}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleur}}"></i>
											</sup>
										</label>										
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorRollerSliderInfo" value="">
										</div>
									</div>
                                    </br>
                                    </br>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormColorOpenSlider">
										<label class="col-md-6 control-label">{{Couleur slider gestion de l''ouverture de la fenêtre}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleur }}"></i>
											</sup>
										</label>
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorOpenSlider" value="green">
										</div>	
									</div>
									<div class="form-group" data-l1key="configuration" data-l2key="cfgFormValueOpenSlider">
										<label class="col-md-6 control-label">{{Couleur bulle info position ouverture fenêtre}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez la couleure}}"></i>
											</sup>
										</label>
										<div class="col-md-1">											
											 <input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgColorOpenSliderInfo" value="">
										</div>										
									</div>
									
									</br>
									<div class="form-group">
										<label class="col-sm-3 control-label">{{Image de fond}}
											<sup>
												<i class="fa fa-question-circle tooltips" title="{{Saisissez le path de l''image à afficher }}"></i>
											</sup>
										</label>
										<div class="col-md-6">
											<div class="input-group">
												<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgBackgroundImg" placeholder="{{Saisir le path de l''image}}"/>
											</div>
										</div>
									</div>                                  
								</fieldset>
							</form>
						</div>
						<div id="infoNodeDesign" class="col-sm-4">
							<fieldset>
								<legend>{{Informations}}</legend>
								<div class="form-group">
									<div id="div_instruction_design"></div>
								</div>							
							</fieldset>					
						</div> 
					</div>

				</div>
				<!--Modif ChD-->
<div role="tabpanel" class="tab-pane" id="commandtab">
<legend>
<center class="title_cmdtable">{{Tableau de commandes <?php echo ' - '.$plugName.': ';?>}}
	<span class="eqName"></span>
</center>
</legend>

<legend><i class="fas fa-info-circle"></i>  {{Infos}}</legend>
	
	<table id="table_cmdi" class="table table-bordered table-condensed ">
		<!--<table class="table  tablesorter tablesorter-bootstrap tablesorter hasResizable table-striped hasFilters" id="table_update" style="margin-top: 5px;" role="grid"><colgroup class="tablesorter-colgroup"></colgroup>
		</table>-->
		<thead>
			<tr>
				<th style="width: 40px;">Id</th>
				<th style="width: 280px;">{{Nom}}</th>
				<th style="width: 100px;">{{Type}}</th>
				<th style="width: 220px;">{{Options}}</th>
				<th style="width: 80px;">{{Action}}</th>
				 
			</tr>
		</thead>
		<tbody></tbody>
	</table>

	<legend><i class="fas fa-list-alt"></i>  {{Actions}}</legend>
	<table id="table_cmda" class="table table-bordered table-condensed">
		
		<thead>
			<tr>
				<th style="width: 40px;">Id</th>
				<th style="width: 280px;">{{Nom}}</th>
				<th style="width: 100px;">{{Type}}</th>
				<th style="width: 220px;">{{Options}}</th>
				<th style="width: 80px;">{{Action}}</th>
				 
			</tr>
		</thead>
		<tbody></tbody>
	</table>


</div>
				<!--
				<div role="tabpanel" class="tab-pane" id="commandtab">	
					<table id="table_cmd" class="table table-bordered table-condensed">
					    <thead>
						<tr>
						    <th>{{Nom}}</th>
						    <th>{{Paramètre}}</th>
						</tr>
					    </thead>
					    <tbody></tbody>
					</table>
				</div>	
				-->
			</div>
		</div>
</div>

<?php include_file('desktop', 'voletsEtOuvrants', 'js', 'voletsEtOuvrants'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>