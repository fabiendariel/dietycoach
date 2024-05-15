var main = {
  
  base_href : (document.location.href.split('_dev.php').length > 1 ? '/app_dev.php' : ''),

	transition : [],
	//------------------------------------------------------------------------

	// Initialisation de l'agenda
	init : function(eventsBase)
	{
		this.set_calendar(eventsBase);
	},

	// ------------------------------------------------------------------------

	// Paramètres et comportement de l'agenda
	set_calendar : function(eventsBase)
	{

		var self = this;

		$('#calendar').fullCalendar({
				themeSystem: 'bootstrap3',
				defaultView: 'agendaWeek',
				timeZone: 'local',
				allDaySlot: false,
				weekends: true,
				minTime:'08:00:00',
				maxTime:'23:00:00',
				height: 'auto',
				axisFormat    : 'HH:mm',
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'addEventButton',
				},
				hiddenDays: [ 0 ],
				weekNumbers: true,
				eventLimit: true, // allow "more" link when too many events
				events: eventsBase,
				selectable:true,
				customButtons: {
					addEventButton: {
						text: 'Ajouter une disponibilité',
						click: function() {
							setTimeout(function(){
								var new_event =
									{
										id: 0,
										coa_id: $('form[name="administration_coach"]').attr('user_id')
									};
								self.new_popup_rdv(new_event);
							},500);
						}
					}
				},
				editable      : true,
				eventOverlap  : false,
				eventDurationEditable : false, // Resize
				eventDrop     : function(Event, delta, revertFunc, jsEvent, ui, view)
				{
					self.set_confirm_move(Event, revertFunc);
				},
				eventClick: function(Event, jsEvent, view)
				{
					console.log(Event,Event.start);
					self.open_popup_rdv(Event);
				},
				select: function(start, end)
				{
					var new_event =
					{
						id: 0,
						start: start,
						end: end,
						coa_id: $('form[name="administration_coach"]').attr('user_id')
					};
					self.new_popup_rdv(new_event);
				}
		});


	},

	// ------------------------------------------------------------------------

	// Ouverture du popup du créneau en mode édition
	open_popup_rdv : function(Event)
	{
		this.set_popup_rdv('update',Event);
	},

	// Ouverture du popup du créneau en mode édition
	new_popup_rdv : function(Event)
	{
		this.set_popup_rdv('insert',Event);
	},

	// ------------------------------------------------------------------------

	// popup d'ajout/modification de créneau
	set_popup_rdv : function(mode,datas)
	{

		var self = this;
		var title;
		var btn_submit;

		// Modification du form en fonction du mode
		if ('insert' === mode)
		{
			title = 'Création de créneau de disponibilité';
			btn_submit = 'Ajouter le créneau les données';
		}
		else if ('update' === mode)
		{
			title = 'Modification du créneau de disponibilité';
			btn_submit = 'Modifier le créneau';
		}

		// Conteneur des messages d'erreur
		$('#creneau_error').hide();

		var datas_send = {};
		datas_send = {
			start : datas.start?moment(datas.start).format('YYYY-MM-DD HH:mm'):null,
			end : datas.end?moment(datas.end).format('YYYY-MM-DD HH:mm'):null,
			id   : datas.id,
			coa_id : datas.coa_id,
			title: title,
			btn_submit: btn_submit
		};

		var uidialog = $('#add_creneau');

		var dest = self.base_href + '/admin/coach/creneau/update';

		$.ajax(
				{
					url     : dest,
					type    : 'post',
					cache   : false,
					data    : datas_send,
					success : jQuery.proxy(function(retourServeur)
							{
								dest = self.base_href + '/admin/coach/creneau/update';

								uidialog = $('#add_creneau');
								uidialog.html(retourServeur);

								this.uidialog.find('#administration_coach_disponibilite_save').click(function(event) {
									$(this).attr('disabled', true);
									$('#inline_error').html('');
									self.valid_creneau(event,$('#add_creneau'),this.dest);
									$(this).attr('disabled', false);
								});
								//$('administration_coach_disponibilite_date').datepicker();
								if ('update' === mode)
								{
									this.uidialog.find('#delete_creneau').click(jQuery.proxy(function(event)
									{
										creneau_id = this.uidialog.find('#administration_coach_disponibilite_id').val();
										event.preventDefault();
										this.uidialog.modal('hide');
										$('#confirm_del').modal('show');
										$('#confirm_del').find('#id_creneau_del').val(creneau_id);
										self.set_confirm_del($('#confirm_del'));
									},
									{
										uidialog   : this.uidialog,
										mode       : mode,
										datas      : datas
									}));
								}else{
									this.uidialog.find('#delete_creneau').addClass('none');
								}

								this.uidialog.modal({
									title         : title
								});
							},
							{
								uidialog: uidialog,
								mode: mode
							})
				});

	},

	// ------------------------------------------------------------------------

	// Validation d'un form de rendez-vous
	valid_creneau : function(event,uidialog)
	{

		if (false === event.isDefaultPrevented()) {

			event.preventDefault();
			dest = 'creneau/update';
			test = 'creneau/test';

			var form_data = new FormData(uidialog.find('#form_add_creneau')[0]);
			$.ajax({
				url: test,
				type: 'post',
				processData: false,
				contentType: false,
				async: false,
				cache: false,
				data: form_data,
				success: function (result) {
					if (false === result.has_error) {
						$.ajax({
							url: dest,
							type: 'post',
							processData: false,
							contentType: false,
							async: false,
							cache: false,
							data: form_data,
							success: function (result) {
								if (false === result.has_error) {
									$('#calendar').fullCalendar('refetchEvents');
									uidialog.modal('hide');
									location.reload();
								}
							}
						});
					}else{
						var html = '<div class="alert alert-error alert-dismissible">';
						html += '<h4><i class="icon fa fa-ban"></i>Erreur</h4>';
						html += 'Une superposition de créneau de disponibilité n\'est pas possible, veuillez vérifier les données choisies';
						html += '</div>';
						$('#inline_error').html(html);
					}
				}
			});

		}
		else {
			event.preventDefault();
		}
	},

	// ------------------------------------------------------------------------

	// popup de confirmation de suppression de creneau
	set_confirm_del : function(uidialog)
	{
		$('#submit_del_creneau').on('click', function(event)
		{
			$('#submit_del_creneau').attr('disabled', true);
			event.preventDefault();


			var form_data = new FormData(uidialog.find('#delete_creneau')[0]);
			$.ajax({
				url      : main.base_href + '/admin/coach/creneau/del',
				type     : 'post',
				processData: false,
				contentType: false,
				async: false,
				cache: false,
				data : form_data,
				success  : function(result)
				{
					$('#submit_del_creneau').attr('disabled', false);
					if (false === result.has_error)
					{
						$('#confirm_del').modal('hide');
						$('#calendar').fullCalendar('refetchEvents');
						location.reload();
					}
				}
			});

		});

		$('#quitter_del_creneau').on('click', function(event)
		{
			event.preventDefault();
			$('#confirm_del').modal('hide');
		});
/*
		uidialog.dialog('close');
		$('#confirm_del').removeClass('none');

		var confirm_message = String(sf_label.agenda.confirm_cancel);

		$('#confirm_del').html(confirm_message);

		$('#confirm_del').dialog({
			title         : sf_label.agenda.cancel_title,
			resizable     : false,
			modal         : true,
			width         : 600,
			closeOnEscape : false,
			close         : function(event, ui)
			{
				// Uniquement au clic du bouton [X] de la popup
				if (undefined !== event.currentTarget)
				{
					$('#confirm_del').addClass('none');
					self.set_popup_creneau(mode,Event,0);
				}
			},
			buttons : [
				{
					text  : sf_label.generic.confirm,
					click : function()
					{
						self.delete_event(Event);
						if(vm_div == 0){
							$('#calendar').fullCalendar('refetchEvents');
						}else{
							$('#calendar_'+vm_div).fullCalendar('refetchEvents');
						}
					}
				},
				{
					text  : sf_label.generic.cancel,
					click : function()
					{
						$('#confirm_del').addClass('none');
						self.set_popup_creneau(mode,Event,0);
						$(this).dialog('close');
					}
				}
			]
		});
*/
	},

	// ------------------------------------------------------------------------

	// popup de confirmation de déplacement de creneau
	set_confirm_move : function(Event, revertFunc)
	{
		var self = this;
		var dest = self.base_href + '/admin/coach/creneau/move';
		$.ajax({
			type     : 'post',
			url      : dest,
			dataType : 'json',
			data     : {
				creneau_id           : Event.id,
				creneau_start        : moment(Event.start).format('YYYY-MM-DD HH:mm'),
				creneau_end          : moment(Event.end).format('YYYY-MM-DD HH:mm')
			},
			success : function(retour)
			{
				if(true === retour.has_error){
					revertFunc();
				}else if(true === retour.refresh){
					location.reload();
				}
				$('#calendar').fullCalendar('refetchEvents');
			}
		});
	},



};

// Evite les bug en cas de trace de console de debuggage
if (typeof console == 'undefined' || !console || !console.log)
{
  console = {log : function(){}};
}
