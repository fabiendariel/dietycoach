var inscription = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/frontend_dev.php' : '/index.php'),
  
  // Initialisation
  init : function()
  {
	$('#form_inscription_cp').mask('99999');
	$('#form_inscription_tel').mask('9999999999');
	$('#form_inscription_fax').mask('9999999999');
	  
	'use strict';  
	
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
		
		
		$(".textbtn1").click(function(event) {
	      
		  /*if($('input[name="form_inscription[choix_contact]"]:checked').val() == undefined){
			  $('#alert_choix_contact').show();
		  }else{
			  $('#alert_choix_contact').hide();
		  }*/
		  if(!grecaptcha.getResponse()){
			  $('#alert_captcha').show();
		  }else{
			  $('#alert_captcha').hide();
		  }
		  		  
	      if (form.checkValidity() === false || inscription.validatePassword() == false) {
	        event.preventDefault();
	        event.stopPropagation();
	      }else{
	    	submit_1 = submit_2 = false;
			  submit_1 = true;
		    /*if($('input[name="form_inscription[choix_contact]"]:checked').val() == undefined){
			  $('#alert_choix_contact').show();
		    }else{
		      $('#alert_choix_contact').hide();
		      submit_1 = true;
		    }*/
		    if(!grecaptcha.getResponse()){
	  		  $('#alert_captcha').show();
	  	  	}else{
	  	  	  $('#alert_captcha').hide();
	  	  	  submit_2 = true;
		    }
			if(submit_1 && submit_2)
				$('#form_inscription').submit();
		  }
	      form.classList.add('was-validated');   	        
	    
	    
		});
    });
	  
	  
	  /*$('.textbtn1').click(function(){
		  $('#form_inscription').submit();		
	  });*/
  },
  
  validatePassword: function()
  {
	  var value = $("#form_inscription_mdp").val();	  		  
	  var password = $("#form_inscription_confirm_mdp").val();

	  $("#form_inscription_mdp").removeClass("is-invalid");
	  $("#form_inscription_confirm_mdp").removeClass("is-invalid");
	  
	  $("#form_inscription_confirm_mdp").removeClass("is-valid");
	  if (value != password) {
		$('#form_inscription').removeClass("was-validated")
	    $("#form_inscription_confirm_mdp").addClass("is-invalid");
		return false;
	  } else {
		$("#form_inscription_mdp").removeClass("is-invalid");
		$("#form_inscription_confirm_mdp").addClass("is-valid");
		$("#form_inscription_confirm_mdp").removeClass("is-invalid");
		return true;
	  }
  }
};

$(function()
{
	inscription.init();
});
