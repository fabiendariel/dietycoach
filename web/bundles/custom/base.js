'use strict';

var base = {

    base_href: (1 < document.location.href.split('_dev.php').length ? '/app_dev.php' : ''),

  init : function()
  {
    this.set_base();
    this.set_procedure();
  },
  
  set_base : function()
  {

        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'fr'
        });


        $('.datepicker').on('changeDate', function(){
            $(this).datepicker('hide');
        });
        
         $(".alert").fadeTo(2000, 500).slideUp(500, function(){
            $(".alert").slideUp(500);
         });

  },

    set_procedure: function () {

        var self = this;

        $('#maj_procedure').on("click", function (event) {

            event.preventDefault();
            console.log(self.base_href + '/procedure');
            $.ajax({
                url: self.base_href + '/procedure',
                type: 'post',
                dataType: 'json',
                success: function (result) {
                    if (result.has_error == false) {
                        $('#maj_procedure').notify("Mise à jour effectué", "success");
                    }
                }
            });

        });

    },
};

$(function()
{
  base.init();
});
