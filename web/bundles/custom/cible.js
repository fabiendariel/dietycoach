'use strict';

var cible = {

        base_href: (1 < document.location.href.split('_dev.php').length ? '/app_dev.php' : ''),

        init: function () {
            this.set_datatables();
            this.show_form();
            this.update_cible();
            this.update_qualification();
            this.creer_contact();
            this.creer_contact_base();
            this.creer_rappel();
        },

        set_datatables: function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('#table_cible').DataTable();
        },

        update_cible: function () {

            var self = this;

            $('#form_cible').validate();
            $("#cible_numeroTelephone").rules('add', { maxlength: 10 });
            $("#cible_mobile").rules('add', { maxlength: 10 });

            $('#button_cible').on("click", function (event) {

                if ($('#form_cible').valid() == true){
                    $.ajax({
                        url: self.base_href + '/cible/cible',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            'cible_id': $('#fiche_cible').data('cible_id'),
                            'email'   : $('#cible_email').val(),
                            'fixe'    : $('#cible_numeroTelephone').val(),
                            'mobile'  : $('#cible_mobile').val()
                        },
                        success: function (result) {
                            $('#button_cible').notify("Modification enregistrée", "success");
                        }
                    });
                }


            });
        },

        update_qualification: function () {

            var self = this;

            $('#form_qualification').validate();
            $("#qualification_autreCommentaire").rules('add', { maxlength: 255 });
            $("#qualification_dateChangementTitulaire").rules('add', { required: '#qualification_changementTitulaire:checked' });
            $("#qualification_dateArretLabo").rules('add', { required: '#qualification_arretLabo:checked' });

            $('#update_qualification').on("click", function (event) {
                event.preventDefault();

                if($('#form_qualification').valid()){

                    $.ajax({
                        url: self.base_href + '/cible/qualification',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            'cible_id'             : $('#fiche_cible').data('cible_id'),
                            'stopAppel'            : $('#qualification_stopAppel').prop('checked'),
                            'passageDelegue'       : $('#qualification_passageDelegue').prop('checked'),
                            'recontactCommande'    : $('#qualification_recontactCommande').prop('checked'),
                            'recontactInfo'        : $('#qualification_recontactInfo').prop('checked'),
                            'recontactContrat'     : $('#qualification_recontactContrat').prop('checked'),
                            'recontactMecontent'   : $('#qualification_recontactMecontent').prop('checked'),
                            'recontactPlv'         : $('#qualification_recontactPlv').prop('checked'),
                            'litigeProduit'        : $('#qualification_litigeProduit').prop('checked'),
                            'litigeLivraison'      : $('#qualification_litigeLivraison').prop('checked'),
                            'litigeFacturation'    : $('#qualification_litigeFacturation').prop('checked'),
                            'litigeMecontent'      : $('#qualification_litigeMecontent').prop('checked'),
                            'arretLabo'            : $('#qualification_arretLabo').prop('checked'),
                            'changementTitulaire'  : $('#qualification_changementTitulaire').prop('checked'),
                            'autre'                : $('#qualification_autre').prop('checked'),
                            'autreCommentaire'     : $('#qualification_autreCommentaire').val(),
                            'dateArretLabo'        : $('#qualification_dateArretLabo').val(),
                            'dateChangementTitulaire' : $('#qualification_dateChangementTitulaire').val()
                        },
                        success: function (result) {
                            $('#update_qualification').notify("Modification enregistrée", "success");
                        }
                    });

                }

            });
        },

        creer_contact_base: function () {

            var self = this;

            $('#button_contact').on("click", function (event) {
                    event.preventDefault();
                    $('#form_appel').show();
                    $('#button_contact').hide();
                    $('#button_contact').hide();

                    $.ajax({
                        url: self.base_href + '/cible/basecontact',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            'cible_id': $('#fiche_cible').data('cible_id')
                        },
                        success: function (result) {
                            $('#contact_id').val(result.contact_id);
                        }
                    });

            });
        },

        creer_contact: function () {

            var self = this;

            $('#appel_aboutissement').change(function () {
                var selected = $(this).find('option:selected');

                if (selected.text() == 'Refus') {
                    $('#motif_refus').show();
                } else {
                    $('#motif_refus').hide();
                }
            });

            $('#appel_motifRefus').change(function () {
                var selectedRefus = $(this).find('option:selected');

                if (selectedRefus.text() == 'Autre') {
                    $('#refus_autre').show();
                } else {
                    $('#refus_autre').hide();
                }
            });

            $('#form_appel').validate();
            $("#appel_commentaire").rules('add', { maxlength: 1000 });
            $("#appel_motifRefus").rules('add', {
                required: function(element) {
                    return $("#appel_aboutissement option:selected").text() == 'Refus';
                }
            });
            $("#appel_refusAutre").rules('add', { maxlength: 255 });

            $('#creer_contact').on("click", function (event) {

                if($('#form_appel').valid()){

                    var selected = $('#appel_aboutissement').find('option:selected');
                    if ((selected.text() != 'Refus' && $('#appel_aboutissement').val() != "") ||
                        selected.text() == 'Refus' && $('#appel_aboutissement').val() != "" && $('#appel_motifRefus').val() != "") {
                        event.preventDefault();

                        $('#creer_contact').hide();

                        $.ajax({
                            url: self.base_href + '/cible/contact',
                            type: 'post',
                            dataType: 'json',
                            data: {
                                'cible_id': $('#fiche_cible').data('cible_id'),
                                'contact_id': $('#contact_id').val(),
                                'aboutissement': $('#appel_aboutissement').val(),
                                'motif_refus': $('#appel_motifRefus').val(),
                                'refus_autre': $('#appel_refusAutre').val(),
                                'commentaire': $('#appel_commentaire').val(),
                            },
                            success: function (result) {
                                $('#creer_contact').show();
                                if (result.has_error == false) {

                                    $('#appel_aboutissement').val("");
                                    $('#appel_motifRefus').val("");
                                    $('#appel_refusAutre').val("");
                                    $('#appel_commentaire').val("");
                                    $('#motif_refus').hide();
                                    $('#refus_autre').hide();
                                    $('#button_contact').show();
                                    $('#form_appel').hide();
                                    $('#button_contact').notify("Contact enregistré", "success");

                                }
                            }
                        });
                    }
                }
            });
        },

        creer_rappel: function () {

            var self = this;

            $('#form_rappel').validate();

            $('#creer_rappel').on("click", function (event) {

                    event.preventDefault();
                    if($('#form_rappel').valid()){

                        $.ajax({
                            url: self.base_href + '/cible/rappel',
                            type: 'post',
                            dataType: 'json',
                            data: {
                                'cible_id': $('#fiche_cible').data('cible_id'),
                                'contact_id': $('#contact_id').val(),
                                'date'    : $('#rappel_date').val()
                            },
                            success: function (result) {
                                $('#creer_rappel').show();
                                if (result.has_error == false) {
                                    $('#rappel_date').val("");
                                    $('#button_rappel').show();
                                    $('#form_rappel').hide();
                                    $('#button_rappel').notify("Rappel enregistré", "success");
                                    $('#myModal').modal('hide');
                                }
                            }
                        });

                    }

            });

        },

        show_form: function () {

            $('#button_rappel').on("click", function (event) {
                $('#form_rappel').show();
            });

            if($('#contact_id').val() != ""){
                $('#button_contact').hide();
                $('#form_appel').show();
            }

            if($('#qualification_autre').prop('checked')){
                $('#qualif_autre').show();
            }else{
                $('#qualif_autre').hide();
            }
            $('#qualification_autre').click(function () {
                if($('#qualification_autre').prop('checked')){
                    $('#qualif_autre').show();
                }else{
                    $('#qualif_autre').hide();
                }
            });

            if($('#qualification_arretLabo').prop('checked')){
                $('#qualif_arretLabo').show();
            }else{
                $('#qualif_arretLabo').hide();
            }
            $('#qualification_arretLabo').click(function () {
                if($('#qualification_arretLabo').prop('checked')){
                    $('#qualif_arretLabo').show();
                }else{
                    $('#qualif_arretLabo').hide();
                }
            });

            if($('#qualification_changementTitulaire').prop('checked')){
                $('#qualif_changementTitulaire').show();
            }else{
                $('#qualif_changementTitulaire').hide();
            }
            $('#qualification_changementTitulaire').click(function () {
                if($('#qualification_changementTitulaire').prop('checked')){
                    $('#qualif_changementTitulaire').show();
                }else{
                    $('#qualif_changementTitulaire').hide();
                }
            });
        },
    }
;

$(function () {
    cible.init();
});
