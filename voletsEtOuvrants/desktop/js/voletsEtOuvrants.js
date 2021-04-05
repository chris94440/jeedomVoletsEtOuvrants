$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$('#tab_zones a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});
$("body").on('click', ".listCmdAction", function() {
	var el = $(this).closest('.input-group').find('.CmdAction');
	var type=$(this).attr('data-type');
	jeedom.cmd.getSelectModal({cmd: {type: type}}, function (result) {
		el.value(result.human);
	});
});

setInstructions();

//manage display of custom open slider
$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgOpenState]').on('change', function () {
  	if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgOpenState]').val() != '') {
        $('.form-group[data-l1key=configuration][data-l2key=cfgFormColorOpenSlider]').show();
        $('.form-group[data-l1key=configuration][data-l2key=cfgFormValueOpenSlider]').show();
    } else {
      	$('.form-group[data-l1key=configuration][data-l2key=cfgFormColorOpenSlider]').hide();
        $('.form-group[data-l1key=configuration][data-l2key=cfgFormValueOpenSlider]').hide();
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSliderInfo]').val('');
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSlider]').val('');
    }
});

function setInstructions() {
	
  	$('#div_instruction_vrs').empty();
	$('#div_instruction_ouvrants').empty();
	$('#div_instruction_design').empty();

  	$('#div_instruction_vrs').html('<div class="alert alert-info">'+getInstructionVrs()+'</div>');
	$('#div_instruction_vr_listeners').html('<div class="alert alert-info">'+getInstructionVrListeners()+'</div>');
	$('#div_instruction_ouvrants').html('<div class="alert alert-info">'+getInstructionOuvrants()+'</div>');
	$('#div_instruction_design').html('<div class="alert alert-info">'+getInstructionDesign()+'</div>');

}

//Event on tab "Roller"
isPluginManageRollerState($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgPluginManageRollerState]').prop("checked"));

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgPluginManageRollerState]').on('change', function () {
	isPluginManageRollerState($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgPluginManageRollerState]').prop("checked"));
});

//Event on tab "ouvrants"
$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowType]').on('change', function () {
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowSize]').find('option').remove().end().append(buildSelectSize($(this).value()));
	setImg();
	console.log("type window : " + $(this).value());
	if ($(this).value() == 'velux') {
		console.log("1");
		$('.form-group[data-l1key=configuration][data-l2key=cfgGrpCmdOpen]').show();
		$('.form-group[data-l1key=configuration][data-l2key=cfgInfoOpenStateRight]').hide();
		$('.form-group[data-l1key=configuration][data-l2key=cfgInfoOpenStateLeft]').hide();
	} else {
		console.log("2");
		$('.form-group[data-l1key=configuration][data-l2key=cfgGrpCmdOpen]').hide();
		$('.form-group[data-l1key=configuration][data-l2key=cfgInfoOpenStateRight]').show();
		$('.form-group[data-l1key=configuration][data-l2key=cfgInfoOpenStateLeft]').show();
	}
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowSize]').on('change', function () {
	setImg();
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowColor]').on('change', function () {
	setImg();
});

//Event on tab "Design"
isDisplayDesign($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHasEqLabel]').prop("checked"),"label");
isDisplayDesign($('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHasEqBorder]').prop("checked"),"border");

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHasEqLabel]').on('change', function () {
	isDisplayDesign($(this).prop("checked"),"label");
});


$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgHasEqBorder]').on('change', function () {
	isDisplayDesign($(this).prop("checked"),"border");
});

/*
$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSliderInfo]').on('change', function () {
	console.log("change color slider info : " + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSliderInfoChoice]').text());
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSliderInfoChoice]').text('toto');
	console.log('	=> la valeur est  : ' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgColorOpenSliderInfoChoice]').text());
});
*/

function isPluginManageRollerState(isPluginManageRollerState) {
	if (isPluginManageRollerState) {
		$('.form-group[data-l1key=configuration][data-l2key=cfgGrpSliderRoller]').hide();
	} else {
		$('.form-group[data-l1key=configuration][data-l2key=cfgGrpSliderRoller]').show();
	}

}

function isDisplayDesign(isDisplay,element) {
	console.log("isDisplayDesign : " + isDisplay + "| element : " + element);
	if (isDisplay) {
		if (element == "label") {
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormLabel]').show();
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormColorLabel]').show();		
		} else if (element == "border") {
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormBorder]').show();		
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormColorBorder]').show();
      		$('.form-group[data-l1key=configuration][data-l2key=cfgFormWidthBorder]').show();
		}
		
	} else {
		if (element == "label") {
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormLabel]').hide();
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormColorLabel]').hide();
			
          	$('.form-control[data-l1key=configuration][data-l2key=cfgColorLabel]').val('');
          	$('.form-control[data-l1key=configuration][data-l2key=cfgLabel]').val('');
			
		} else if (element == "border") {
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormBorder]').hide();			
			$('.form-group[data-l1key=configuration][data-l2key=cfgFormColorBorder]').hide();
          	$('.form-group[data-l1key=configuration][data-l2key=cfgFormWidthBorder]').hide();
			$('.form-control[data-l1key=configuration][data-l2key=cfgColorBorderEq]').val('');
          	$('.form-control[data-l1key=configuration][data-l2key=cfgTypeBorderEq]').val('');
          $('.form-group[data-l1key=configuration][data-l2key=cfgFormWidthBorder]').val('');
		}
	}
}
function buildSelectSize(value) {
	var selectOption;
	
	if (value == 'velux') {
		selectOption +='<option value="size-mk">{{Taille mk               }}</option>';
		selectOption +='<option value="size-uk">{{Taille uk               }}</option>';
		
	} else {
		if (value == 'porte') {
			selectOption +='<option value="size-s">{{Taille s               }}</option>';
		}
		selectOption +='<option value="size-m">{{Taille m               }}</option>';
		selectOption +='<option value="size-l">{{Taille l               }}</option>';
		selectOption +='<option value="size-xl">{{Taille xl               }}</option>';
	}
	return selectOption;
}

function setImg() {
	var ouvrant = $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowType]').value();
	var size = $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowSize]').value();
	var color = $('.eqLogicAttr[data-l1key=configuration][data-l2key=cfgWindowColor]').value();
	var newImg='plugins/voletsEtOuvrants/desktop/img/'+ouvrant+'_'+size+'.JPG';

	$('#img_type').attr("src", newImg);
}



function getInstructionVrs() {
  	var instruction ="<span><u>Contrôle du volet roulant : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la commande permettant de piloter la montée du volet roulant</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la commande permettant de stoper la montée ou la descente du volet roulant (ne pas renseigner pour des moteurs type RTS)</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la commande permettant de piloter la descente du volet roulant</span>";
  	instruction += "</br>";
  	instruction += "</br>";
  	instruction += "<span><u>Déléguer l'état du volet roulant au plugin: </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- cas ou la gestion d'état du vr n'est pas géré par l'équipement</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- le plugin va créer une commande état du volet roulant et y gérer sa valeur (pourcentage d'ouverture)</span>";
  	instruction += "</br>";
  	instruction += "</br>";
	instruction += "<span><u>Délais : </u></span>";
	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet au plugin de gérer le pourcentage d'ouverture ou de fermeture du volet roualnt</span>";
  	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- si le temps de montée oude descente est inférieur au temps réel que le volet met pour faire l'action la course du volet sera limité au temps d'action indiqué</span>";
  	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- le temps de décollement correspond au temps que le volet se décolle du sol (uniquement pour la montée)</span>";
  
  	return instruction;
}

function getInstructionVrListeners() {
	var instruction ="<span><u>Ecoute commandes externes de contrôle du volet roulant : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir les commandes (montée, descente, stop) externes permettant de piloter le volet roualnt</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=> Exemple d'un appui sur un bouton d'une télécommande physique non appairée à jeedom</span>";
  	instruction += "</br>";
  
  	return instruction;
}

function getInstructionOuvrants() {
  	var instruction ="<span><u>Customisation fenêtre : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir le modèle de fenêtre</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la taille de la fenêtre</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir le modèle de fenêtre parmis la liste proposée</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la couleur des volets roulants</span>";
  	instruction += "</br>";
  	instruction += "</br>";
  	instruction += "<span><u>Cas d'un vélux : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- possibilité de piloter l\'ouverture du vélux au travers du plugin</span>";
  	instruction += "</br>";
  	instruction += "</br>";
	instruction += "<span><u>Notion état ouverture : </u></span>";
	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de spécifier si un ou plusieurs capteurs d'ouverture sont positionnes sur la fenêtre (permet de voir visuellement si la fenêtre est ouverte) </span>";
	instruction += "</br>";	
  	instruction += "</br>";
  	instruction += "<span><u>Mode debug ? </u></span>";
	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet d\'activer un niveau de trace supplémentaire dans le fichier de log du plugin et dans la console web du navigateur</span>"; 
  
  
  	return instruction;
}

function getInstructionDesign(){
  	var instruction ="<span><u>Label : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- Permet d'afficher ou non le nom d\u00e9fini de l\'\u00e9quipement</span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- couleur de l\'entourage du texte </span>";
  	instruction += "</br>";
  	instruction += "</br>";
	instruction += "<span><u>Bordure : </u></span>";
	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet d\'afficher ou non une bordure (uniquement si la couleur de la bordure est choisie)</span>";
  	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la couleur de la bordure</span>";
  	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir le type de la bordure (<a href=\"https://www.pierre-giraud.com/html-css-apprendre-coder-cours/bordure-border-width-style-color/\"><span><u>les bordures en html</u>)</span></a>)</span>";
	instruction += "</br>";	
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir l\'\u00e9paisseur de la bordure (en pixel)</span>";
  	instruction += "</br>";
  	instruction += "</br>";
  	instruction += "<span><u>Slider et info bulles : </u></span>";
  	instruction += "</br>";
  	instruction += "<span>&nbsp;&nbsp;&nbsp;&nbsp;- permet de choisir la couleur du slider et de la bulle d'information (ex : )</span>";
  
  	return instruction;
}

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
   		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes Infos</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		//tr += '<span class="cmdAttr" data-l1key="type"></span>';
		//tr += '   /   ';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Evaluer}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
  
  	if (init(_cmd.type) == 'info') {
    	$('#table_cmdi tbody').append(tr);
    	$('#table_cmdi tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
  	if (init(_cmd.type) == 'action') {
    	$('#table_cmda tbody').append(tr);
    	$('#table_cmda tbody tr:last').setValues(_cmd, '.cmdAttr');
    }
	//$('#table_cmd tbody').append(tr);
    //$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}