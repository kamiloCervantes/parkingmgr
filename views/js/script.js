// funcion reset para JQuery
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}

//funcion obtener week number para objeto Date
/**
 * Returns the week number for this date.  dowOffset is the day of week the week
 * "starts" on for your locale - it can be from 0 to 6. If dowOffset is 1 (Monday),
 * the week returned is the ISO 8601 week number.
 * @param int dowOffset
 * @return int
 */
Date.prototype.getWeek = function (dowOffset) {
/*getWeek() was developed by Nick Baicoianu at MeanFreePath: http://www.meanfreepath.com */

	dowOffset = typeof(dowOffset) == 'int' ? dowOffset : 0; //default dowOffset to zero
	var newYear = new Date(this.getFullYear(),0,1);
	var day = newYear.getDay() - dowOffset; //the day of week the year begins on
	day = (day >= 0 ? day : day + 7);
	var daynum = Math.floor((this.getTime() - newYear.getTime() - 
	(this.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
	var weeknum;
	//if the year starts before the middle of a week
	if(day < 4) {
		weeknum = Math.floor((daynum+day-1)/7) + 1;
		if(weeknum > 52) {
			nYear = new Date(this.getFullYear() + 1,0,1);
			nday = nYear.getDay() - dowOffset;
			nday = nday >= 0 ? nday : nday + 7;
			/*if the next year starts before the middle of
 			  the week, it is week #1 of that year*/
			weeknum = nday < 4 ? 1 : 53;
		}
	}
	else {
		weeknum = Math.floor((daynum+day-1)/7);
	}
	return weeknum;
};

$(init);

function agregarPago(event){
	event.preventDefault();
	if(validarForm("#pagoservicio")){
		$.post("../ajax/pagos.php",
				{ 
					action:"addPago.do",
					servicio_id: $('#servicio_id').val(),
					valor_pago: $('#valor_pago').val(),
					fecha_pago: $('#fecha_pago').val()
				},
				function(data){
					redireccion(parseInt(data.login));
					$('#buscaservicio').submit();
					$('#pagoservicio #cancelarpago').click();
				},"json");
	}
}


function buscarServicios(event){
	if(!vacio($('#param_busqueda').val())){
		localStorage.placa = $('#param_busqueda').val();
	}
		
	//limpiar estado cuenta
	$('#resultados h2').text("");
	$('#servicios tr:not(#servicios tr:first-child)').remove();
	$('#pagos tr:not(#pagos tr:first-child)').remove();
	$('#totser').empty();
	$('#totpag').empty();
	
	event.preventDefault();
	$.post("../ajax/servicios.php",
				{ 
					action:"getServicios.do",
					placa: localStorage.placa
				},
				function(data){
					redireccion(parseInt(data.login));
					if(data.vehiculos_id!='undefined'){
						$('#resultados').fadeIn();
						$('#resultados h2').text("Estado de cuenta del automotor "+data.vehiculos_id);
						var sum_subtotal_pago = 0;
						$.each(data.services,function(index,val){
							sum_subtotal_pago += parseInt((val.precio - val.subtotal_pago));
							var tmp = [];
							tmp.push('<td>'+val.id+'</td>');
							tmp.push('<td>'+val.fecha_ingreso+'</td>');
							tmp.push('<td>'+val.tiempo+val.und_tiempo+'</td>');
							tmp.push('<td>$'+val.precio+'</td>');
							tmp.push('<td>$'+(val.precio - val.subtotal_pago)+'</td>');
							$('<tr/>',{html: tmp.join('')}).appendTo('#servicios');
						});
						var pagos = 0;
						$.each(data.pagos_service,function(index,val){
							var tmp = [];
							pagos+= parseInt(val.valor_pago);
							tmp.push('<td>$'+val.valor_pago+'</td>');
							tmp.push('<td>'+val.fecha_pago+'</td>');
							$('<tr/>',{html: tmp.join('')}).appendTo('#pagos');
						});
						$('#totser').append('<p>Valor total servicios prestados: $'+data.deuda+'</p>');
						$('#totser').append('<p>Valor total deudas pendientes: $'+sum_subtotal_pago+'</p>');
						$('#totpag').append('<p>Valor total pagos realizados: $'+pagos+'</p>');
						
					}
					else{
						$('#resultados').hide();
						alert("No hay resultados");
					}
					},"json");
					
}

function cancelarPagoForm(){
	$('#msgs').empty().removeClass('error');
	$('#msgs').empty().removeClass('ok');
	$('#formpago').fadeOut(200);
	$('#pagoservicio').reset();
}

function cancelarReporteForm(){
	$('#generarreporte').reset();
}

function cancelarVehiculoForm(){
	$('#ingresovehiculo').reset();
}

function cargarDataVehiculo(){
	$.post("../ajax/vehiculos.php",
				{ 
					action:"getVehiculo.do",
					placa: $('#placa').val()
				},
				function(data){
					if(data.login==0){
						window.location = 'index.html';
					}
					else{
						if(data){
							localStorage.loadedVehicle = 1;
							$('#tipo_vehiculo').val(data.tipo_vehiculo);
							$('#propietario').val(data.nombre_propietario);
							$('#id_propietario').val(data.id_propietario);
							$('#tel_propietario').val(data.tel_propietario);
							$('#tiempo').focus();
						}
						else{
							localStorage.loadedVehicle = 0;
						}
					}
				},"json");
}

function checkPagoValue(){
	var check = localStorage.debe - $('#pagoservicio #valor_pago').val();
	var valid = true;
	var msgs = [];
	if(check < 0){
		valid = false;	
		msgs.push("<li><p>El valor a pagar es mayor que la deuda pendiente</p></li>");
	}
	if(!valid){
		$('#msgs').empty().removeClass('error');
		$('<ul/>',{
			html: msgs.join('')
		}).appendTo('#msgs');
		$('#msgs').addClass('error');
	}
	else{
		$('#msgs').empty().removeClass('error');
	}
	//localStorage.validPagoValue = valid;
}

function checkServiceStatus(event){
	$('#msgs').empty().removeClass('error');
	$('#msgs').empty().removeClass('ok');
	event.preventDefault();
	var valid = true;
	var msgs = [];
	if($('#servicio_id').val()){
		$.post("../ajax/servicios.php",
					{ 
						action:"checkServicio.do",
						servicio_id: $('#servicio_id').val(),
						placa: localStorage.placa
					},
					function(data){
						redireccion(parseInt(data.login));
						var debe = parseInt(data.precio) - parseInt(data.subtotal);
						localStorage.debe = debe;
						
						if(parseInt(data.check)==1){
							valid = false;	
							msgs.push("<li><p>El servicio no tiene deudas pendientes</p></li>");
						}
						else{
							if(parseInt(data.check)==-1){
							valid = false; 
							msgs.push("<li><p>El servicio no existe</p></li>");
							}
						}	
						
						if(!valid){
							$('#msgs').empty().removeClass('error');
							$('<ul/>',{
								html: msgs.join('')
							}).appendTo('#msgs');
							$('#msgs').addClass('error');
							}
							else{
								
								$('#msgs').empty().removeClass('error');
							}
						},"json");
						//localStorage.validServiceStatus = valid;
	}
}

function enviarServicio(){
	$.post("../ajax/servicios.php",
				{ 
					action:"addServicio.do",
					placa: $('#placa').val(), 
					tiempo: $('#tiempo').val(), 
					und_tiempo: $('#und_tiempo').val(),
					precio: $('#precio').val()
				},
				function(){
					redireccion(parseInt(data.login));
				},"json");
}

function enviarVehiculo(){
	$.post("../ajax/vehiculos.php",
				{ 
					action:"addVehiculo.do",
					placa: $('#placa').val(), 
					tipo_vehiculo: $('#tipo_vehiculo').val(), 
					propietario: $('#propietario').val(),
					id_propietario: $('#id_propietario').val(),
					tel_propietario: $('#tel_propietario').val()
				});
}

function generarReporte(event){
	event.preventDefault();
	if(validarForm('#generarreporte')){
	var date = $('#fecha_inicial').val().split('-');
	$.post("../ajax/pagos.php",
				{ 
					action: "getDataReportes.do",
					day: date[2],
					month: date[1],
					year: date[0],
					fecha: date.join('-')
				},
				function(data){
					redireccion(parseInt(data.login));
					$('#reporte #mensual tr:not(#reporte #mensual tr:first-child)').remove();
					$('#reporte #semanal tr:not(#reporte #semanal tr:first-child)').remove();
					$('#reporte #diario tr:not(#reporte #diario tr:first-child)').remove();
					var mes = [];
					if(data.dia=="0"){
						alert("No hay reportes disponibles");
					}
					else{
						$('#generarreporte').hide();
						$('#reporte').show();
						$('#reporte').on('click',function(){
							$('#generarreporte').show();
							$('#reporte').hide();
						});
						mes.push('<td>'+meses(parseInt(data.mes[0].mes)-1,date[0])+'</td>');
						mes.push('<td>'+data.mes[0].sum+'</td>');
						$('<tr/>',{
							html: mes.join()
						}).appendTo('#reporte #mensual');
						
						$.each(data.semana,function(index,val){
							var semana = [];
							semana.push('<td>'+val.fecha_pago+'</td>');
							semana.push('<td>'+val.sum+'</td>');
							$('<tr/>',{
							html: semana.join()
							}).appendTo('#reporte #semanal');
							$('#week').text("Pagos de la semana "+val.week+" de "+date[0]);
						});	
								
						$.each(data.dia,function(index,val){
							var dia = [];
							dia.push('<td>'+val.vehiculos_id+'</td>');
							dia.push('<td>'+val.valor_pago+'</td>');
							dia.push('<td>'+val.fecha_ingreso+'</td>');
							$('<tr/>',{
								html: dia.join()
							}).appendTo('#reporte #diario');
							$('#now').text("Pagos del "+date.join('-'));
						});
					}
				},'json');
	}
}

function ingresarVehiculo(event){
	event.preventDefault();
	if(validarForm('#ingresovehiculo')){
		if(localStorage.loadedVehicle == 0){
			enviarVehiculo();
			enviarServicio();
		}
		else{
			enviarServicio();
		}
		$('#ingresovehiculo').reset();
	}
}

function init(){
	$('#resultados').tabs();
	$('#resultados #addpago').on("click", mostrarPagoForm);
	$('#generarreporte #cancelarreporte').on("click", cancelarReporteForm);
	$('#ingresovehiculo #btncancelar').on("click", cancelarVehiculoForm);
	$('#ingresovehiculo').on("submit", ingresarVehiculo);
	$('#ingresovehiculo #placa').on("blur", cargarDataVehiculo);
	$('#pagoservicio #servicio_id').on("blur", checkServiceStatus);
	$('#pagoservicio #valor_pago').on("keyup", checkPagoValue);
	$('#pagoservicio').on("submit", agregarPago);
	$('#pagoservicio #cancelarpago').on("click", cancelarPagoForm);
	$('#buscaservicio').on("submit", buscarServicios);
	$('#generarreporte').on("submit", generarReporte);
	$('#form_login').on("submit", login);
	$('#menu #salir').on("click", logout);
	var date = new Date();
	$('#fecha_inicial').val(date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate());
}

function login(event){
	event.preventDefault();
	if(validarForm('#form_login')){
	$.post("../ajax/session.php",
				{ 
					action: 'login.do',
					user: $('#user').val(), 
					pass: $('#pass').val()
				},
				function(data){
					redireccion(parseInt(data.login));
				},"json");
	}
}

function logout(){
	$.post("../ajax/session.php",
				{ 
					action: 'logout.do'
				},
				function(data){
					if(data.logout==1){
						window.location = 'index.html';
					}
					else{
						alert("Error");
					}
				},"json");
	}

function meses(month,year){
	var meses = [];
	meses.push('Enero de '+year);
	meses.push('Febrero de '+year);
	meses.push('Marzo de '+year);
	meses.push('Abril de '+year);
	meses.push('Mayo de '+year);
	meses.push('Junio de '+year);
	meses.push('Julio de '+year);
	meses.push('Agosto de '+year);
	meses.push('Septiembre de '+year);
	meses.push('Octubre de '+year);
	meses.push('Noviembre de '+year);
	meses.push('Diciembre de '+year);
	
	return meses[month];
}

function mostrarPagoForm(){
	var date = new Date();
	$('#fecha_pago').val(date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate());
	$('#formpago').fadeIn(2000);
}

function redireccion(index){
	if(index==1){
		window.location = 'nuevoingreso.html';
	}
	else{
		if(index==0){
			window.location = 'index.html';
		}
	}
}

function vacio(cadena)  
  {                                    
    var blanco = " \n\t" + String.fromCharCode(13);                                    
    var i;                            
    var es_vacio;                     
    for(i = 0, es_vacio = true; (i < cadena.length) && es_vacio; i++)
      es_vacio = blanco.indexOf(cadena.charAt(i)) != - 1;  
    return(es_vacio);  
  }

function validarForm(form){
	var valid = true;
	var msgs = [];
	$(form+' :input').each(function(){
		if($(this).attr("required")=="required" && vacio($(this).val())){
			$(this).addClass("error");
			$(this).on("keyup", function(){$(this).removeClass("error");});
			valid = false;
		}
		if(!Modernizr.inputtypes.number){
		if($(this).attr("type")=="number" && $(this).attr("min") && $(this).attr("max")){
			if($(this).val() < parseInt($(this).attr("min")) || $(this).val() > parseInt($(this).attr("max"))){
				alert($(this).val());
				$(this).addClass("error");
				$(this).on("keyup", function(){$(this).removeClass("error");});
				valid = false;
			}
		}
		}
	});
	if(form == '#ingresovehiculo'){
		if($('#placa').val().length > 10){
			valid = false;
			msgs.push("<li><p>La placa del veh&iacute;culo puede tener m&aacute;ximo 10 caract&eacute;res</p></li>");
		}
		if($('#propietario').val().length > 50){
			valid = false;
			msgs.push("<li><p>El nombre del propietario puede tener m&aacute;ximo 50 caract&eacute;res</p></li>");
		}
		if($('#id_propietario').val().length > 20){
			valid = false;
			msgs.push("<li><p>La c&eacute;dula del propietario puede tener m&aacute;ximo 20 caract&eacute;res</p></li>");
		}
		if(isNaN($('#id_propietario').val())){
			valid = false;
			msgs.push("<li><p>La c&eacute;dula del propietario solo puede contener n&uacute;meros</p></li>");
		}
		if($('#tel_propietario').val().length > 15){
			valid = false;
			msgs.push("<li><p>El tel&eacute;fono del propietario puede tener m&aacute;ximo 15 caract&eacute;res</p></li>");
		}
		if(isNaN($('#tel_propietario').val())){
			valid = false;
			msgs.push("<li><p>El tel&eacute;fono del propietario solo puede contener n&uacute;meros</p></li>");
		}
		if(isNaN($('#tiempo').val())){
			valid = false;
			msgs.push("<li><p>El tiempo solo puede contener n&uacute;meros</p></li>");
		}
		else{
			if($('#tiempo').val() > 100 || $('#tiempo').val() < 0){
				valid = false;
				msgs.push("<li><p>El valor de tiempo debe estar dentro del rango 0 - 100</p></li>");
			}
		}
		if(isNaN($('#precio').val())){
			valid = false;
			msgs.push("<li><p>El precio solo puede contener n&uacute;meros</p></li>");
		}
		
	}
	if(form == '#pagoservicio'){
		if(isNaN($('#servicio_id').val())){
			valid = false;
			msgs.push("<li><p>El id del servicio debe ser un valor num&eacute;rico</p></li>");
		}
		else{
			if($('#servicio_id').val() < 0){
				valid = false;
				msgs.push("<li><p>El id del servicio debe ser mayor que 0</p></li>");
			}
		}
		
		if(isNaN($('#valor_pago').val())){
			valid = false;
			msgs.push("<li><p>El valor del pago debe ser un valor num&eacute;rico</p></li>");
		}
		else{
			if($('#valor_pago').val() < 0){
				valid = false;
				msgs.push("<li><p>El valor del pago debe ser mayor que 0</p></li>");
			}
		}
		var regex = new RegExp("(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))");
		
		if(!regex.test($('#fecha_pago').val())){
				valid = false;
				msgs.push("<li><p>La fecha ingresada no tiene el formato YYYY-MM-DD</p></li>");
			}
		
	}
	$('#msgs').empty().removeClass('error');
	if(!valid){
			if(msgs.length == 0){
				msgs.push("<li><p>El formulario no pudo ser enviado</p></li>"); 
			}

			$('<ul/>',{
				html: msgs.join('')
			}).appendTo('#msgs');
			$('#msgs').addClass('error');
		}
		else{
			$('#msgs').removeClass('ok');
			$('<ul/>',{
				html: '<li><p>El formulario ha sido enviado correctamente</p></li>'
			}).appendTo('#msgs');
			$('#msgs').addClass('ok');
		}
		
	return valid;
}



