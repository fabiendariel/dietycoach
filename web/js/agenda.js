'use strict';

var agenda = {

  base_href : (1 < document.location.href.split('_dev.php').length ? '/app_dev.php' : ''),
  // Appel global
  init: function () {

    this.update_rdv();
    this.pref_rdv();
    this.bind_element();

    $('#dateChoice1').datepicker({
      format: "dd/mm/yyyy",
      weekStart: 1,
      startView: 0,
      language: "fr"
    });
    $('#dateChoice2').datepicker({
      format: "dd/mm/yyyy",
      weekStart: 1,
      startView: 0,
      language: "fr"
    });
    $('#dateChoice3').datepicker({
      format: "dd/mm/yyyy",
      weekStart: 1,
      startView: 0,
      language: "fr"
    });
    var largeur = window.innerWidth;
  },

  //------------------------------------------------------------------------------------------------------------------

  bind_element: function () {

    $('#other_date_rdv_modal').click(function(event){
      event.preventDefault();
      $('#other_date_rdv').modal('show');
      other_date_rdv_submit
    });

    $('.slot a').click(function(event){
      event.preventDefault();
      var url_dest = $(this).attr('href');
      $('#detail_date').html($(this).data('date'));
      $('#detail_heure').html($(this).data('heure'));
      $('#confirm_rdv').modal('show');
      $('#create_rdv').modal('hide');
      $('#valid_rdv').modal('hide');
      $('#url_dest').val('');
      $('#valid_rdv .modal-title').removeClass('text-success').removeClass('text-danger');

      $('#annule_rdv').click(function(){
        $('#detail_date').html('');
        $('#detail_heure').html('');
        $('#confirm_rdv').modal('hide');
      });
      $('#confirm_rdv_submit').click(function(event){
        event.preventDefault();
        $('#confirm_rdv').modal('hide');
        $('#url_dest').val(url_dest);
        $('#create_rdv').modal('show');

      });
    });

    var max_height = 0;
    $('.day').each(function(){
      if(max_height < $(this).height()){
        max_height = $(this).height();
      }
    });
    $('.day').height(max_height);
  },

  // Mise à jour de la date de suivi
  update_rdv: function () {

    'use strict';

    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {

      $('#create_rdv_submit').on('click', function (event) {
        $('#create_rdv_submit').attr('disabled', true);
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
          $('#create_rdv_submit').attr('disabled', false);
        }else{
          $('#create_rdv_submit').attr('disabled', false);
          var form_data = new FormData($('#form_create_rdv')[0]);

          $.ajax({
            url: agenda.base_href + $('#url_dest').val(),
            type: 'post',
            processData: false,
            contentType: false,
            async: false,
            cache: false,
            data: form_data,
            success: function (result) {
              $('#submit_date_suivi').attr('disabled', false);
              $('#valid_rdv .modal-text').html(result.message);
              if (false === result.has_error) {
                $('#valid_rdv .modal-text').addClass('text-success');
              } else {
                $('#valid_rdv .modal-text').addClass('text-danger');
              }
              $('#create_rdv').modal('hide');
              $('#valid_rdv').modal('show');
              $('#valid_rdv_close').click(function () {
                $('#valid_rdv').modal('hide');
                $('#valid_rdv .modal-text').html('');
              });
            }
          });
        }
        form.classList.add('was-validated');

      });
    });
  },

  pref_rdv: function () {

    'use strict';

    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      $('#dateChoice1').on('change', function (event) {
        if($(this).val() != ''){
          $('#debutChoice1').attr('required','required');
          $('#finChoice1').attr('required','required');
        }else{
          $('#debutChoice1').removeAttr('required');
          $('#finChoice1').removeAttr('required');
        }
      });
      $('#dateChoice2').on('change', function (event) {
        if($(this).val() != ''){
          $('#debutChoice2').attr('required','required');
          $('#finChoice2').attr('required','required');
        }else{
          $('#debutChoice2').removeAttr('required');
          $('#finChoice2').removeAttr('required');
        }
      });
      $('#dateChoice3').on('change', function (event) {
        if($(this).val() != ''){
          $('#debutChoice3').attr('required','required');
          $('#finChoice3').attr('required','required');
        }else{
          $('#debutChoice3').removeAttr('required');
          $('#finChoice3').removeAttr('required');
        }
      });
      $('#other_date_rdv_submit').on('click', function (event) {
        $('#other_date_rdv_submit').attr('disabled', true);
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
          $('#other_date_rdv_submit').attr('disabled', false);
        }else{
          $('#other_date_rdv_submit').attr('disabled', false);
          var form_data = new FormData($('#form_other_date_rdv')[0]);

          $.ajax({
            url: agenda.base_href + '/agenda/preferences',
            type: 'post',
            processData: false,
            contentType: false,
            async: false,
            cache: false,
            data: form_data,
            success: function (result) {
              $('#other_date_rdv_submit').attr('disabled', false);
              $('#valid_rdv .modal-text').html(result.message);
              if (false === result.has_error) {
                $('#valid_rdv .modal-text').addClass('text-success');
              } else {
                $('#valid_rdv .modal-text').addClass('text-danger');
              }
              $('#other_date_rdv').modal('hide');
              $('#valid_rdv').modal('show');
              $('#valid_rdv_close').click(function () {
                $('#valid_rdv').modal('hide');
                $('#valid_rdv .modal-text').html('');
              });
            }
          });
        }
        form.classList.add('was-validated');

      });
    });
  },

};