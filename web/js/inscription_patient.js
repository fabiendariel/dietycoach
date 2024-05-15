var inscription = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),
  
  // Initialisation
  init : function()
  {
	$('#form_inscription_cp').mask('99999');
	$('#form_inscription_tel').mask('9999999999');
	$('#form_inscription_fax').mask('9999999999');
	$('#form_inscription_cp_traitant').mask('99999');
	$('#form_inscription_tel_traitant').mask('9999999999');
	$('#form_inscription_cp_patient').mask('99999');
	$('#form_inscription_tel_patient').mask('9999999999');
	$('#form_inscription_mobile_patient').mask('9999999999');
	$('#form_inscription_cp_representant').mask('99999');
	$('#form_inscription_tel_representant').mask('9999999999');
	$('#form_inscription_mobile_representant').mask('9999999999');
	
	$('#form_inscription_naissance_patient').datepicker({
	    format: "dd/mm/yyyy",
	    weekStart: 1,
	    startView: 2,
	    language: "fr"
	});
	
	$('#form_inscription_cp_patient').keyup(function(){
		if($('#form_inscription_cp_patient').val().length >= 3
		&& $('#form_inscription_nom_patient').val().length >= 3){			
			$.ajax({
    			url:url_test_doublon,
    			type     : 'post',
    			dataType : 'json',
    			data: {
    				cp_patient: $('#form_inscription_cp_patient').val(),
    				nom_patient: $('#form_inscription_nom_patient').val()
    			},
    			success:function(retour){
    				if(retour.doublon){
    					$('#alert_doublon').closest('td').show();  					
    				}else{
    					$('#alert_doublon').closest('td').hide();  		
    				} 
    			}
    		});
			
		}
		
	});
	
	$('#form_inscription_nom_patient').keyup(function(){
		if($('#form_inscription_cp_patient').val().length >= 3
		&& $('#form_inscription_nom_patient').val().length >= 3){			
			$.ajax({
    			url:url_test_doublon,
    			type     : 'post',
    			dataType : 'json',
    			data: {
    				cp_patient: $('#form_inscription_cp_patient').val(),
    				nom_patient: $('#form_inscription_nom_patient').val()
    			},
    			success:function(retour){    				
    				if(retour.doublon){
    					$('#alert_doublon').closest('td').show();  					
    				}else{
    					$('#alert_doublon').closest('td').hide();  		
    				}   					
    			}
    		});
		}
		
	});
	  
	$('.generate_sms').click(function(){
		if($('#form_inscription_mobile_patient').val() == '' || $('#form_inscription_mobile_patient').val() == undefined){
			$('#alert_sms').text('Vous devez définir un numéro de mobile pour le patient avant de cliquer');
			$('#alert_sms').addClass('alert-danger');
			$('#alert_sms').show();
		}else{
			$('#alert_sms').removeClass('alert-danger');
			$('#alert_sms').hide();
		
			if($('#code_sms').val() == '' || $('#code_sms').val() == undefined){
				var code = inscription.getRandom(10000,99999);
	    		$.ajax({
	    			url:url_envoi_code,
	    			type     : 'post',
	    			dataType : 'json',
	    			data: {
	    				code: code,
	    				mobile: $('#form_inscription_mobile_patient').val()
	    			},
	    			success:function(retour){
	    				if(retour.envoi){
	    					$('#code_sms').val(retour.code);
	    					$('#alert_sms').text('Le code a été envoyé par SMS au patient');
	    					$('#alert_sms').addClass('alert-success');
			    			$('#alert_sms').show();	    					
	    				}
	    					
	    			}
	    		});
			}else{
				$('#alert_sms').text('Vous avez déjà envoyé le code de vérification par SMS');
				$('#alert_sms').addClass('alert-danger');
				$('#alert_sms').show();
			}
		}
		
	});

	
	$('.renvoi_sms').click(function(){
		if($('#form_inscription_mobile_patient').val() == '' || $('#form_inscription_mobile_patient').val() == undefined){
			$('#alert_sms').text('Vous devez définir un numéro de mobile pour le patient avant de cliquer');
			$('#alert_sms').addClass('alert-danger');
			$('#alert_sms').show();
		}else{
			$('#alert_sms').removeClass('alert-danger');
			$('#alert_sms').hide();
		
			$('#code_sms').val('');
			var code = inscription.getRandom(10000,99999);
			$.ajax({
				url:url_envoi_code,
				type     : 'post',
				dataType : 'json',
				data: {
					code: code,
					mobile: $('#form_inscription_mobile_patient').val()
				},
				success:function(retour){
					if(retour.envoi){
						$('#code_sms').val(retour.code);
						$('#alert_sms').text('Le code a été envoyé par SMS au patient');
						$('#alert_sms').addClass('alert-success');
		    			$('#alert_sms').show();
					}    					
				}
			}); 
		}
		return false;
	});
	

	'use strict';  
	
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {    	
    	
		$(".textbtn1").click(function(event) {
	      		
		  var valide = true;
		  
		  if($('#alert_doublon').closest('td').attr('style')=='')
    		  valide = false;

		  
    	  if($('input[name="formulaire_inscription[accepte_infos]"]:checked').val() == undefined || $('input[name="formulaire_inscription[cgv]"]:checked').val() == undefined){
    		  $('#alert_coche').show();
    		  valide = false;
    	  }else{
    		  $('#alert_coche').hide();
    	  }
    	  if($('#code_sms').val() == undefined || $('#code_sms').val() == ''){
    		  $('#alert_sms').text('Vous devez envoyer le code par SMS au patient avant de continuer');
    		  $('#alert_sms').addClass('alert-danger');
			  $('#alert_sms').show();
			  valide = false;
		  }else{
			  $('#alert_sms').removeClass('alert-danger');
			  $('#alert_sms').hide();
		  
			  if($('#code_sms').val() != $('#form_inscription_code_validation').val()){
				  $('#alert_sms_diff').show();
				  valide = false;
			  }else{					  
				  $('#alert_sms_diff').hide();
			  }				  
		  }
	      if (form.checkValidity() === false) {
			  console.log('test');
	        event.preventDefault();
	        event.stopPropagation();
	      }else{
	    	  
	    	  if(valide)
	    		  $('#form_inscription').submit();
		  }
	      form.classList.add('was-validated');   	        
	    
	    
		});
    });

  },
  
  getRandom: function(min, max) {
	  min = Math.ceil(min);
	  max = Math.floor(max);
	  return Math.floor(Math.random() * (max - min)) + min;
  }
};

$(function()
{
	inscription.init();
});
