// funcion reset para JQuery
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}

$(init);

function buscarServicios(event){
	event.preventDefault();
	//alert("hola");
	$.post("../ajax/servicios.php",
				{ 
					action:"getServicios.do",
					placa: $('#param_busqueda').val()
				},
				function(data){
					//document.write(data);
					$('#resultados').fadeIn();
					$('#resultados h2').text("Estado de cuenta del vehiculo "+data.vehiculos_id);
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
					
					$.each(data.pagos_service,function(index,val){
						var tmp = [];
						tmp.push('<td>$'+val.valor_pago+'</td>');
						tmp.push('<td>'+val.fecha_pago+'</td>');
						$('<tr/>',{html: tmp.join('')}).appendTo('#pagos');
					});
					$('#totser').append('<p>Valor total servicios prestados: $'+data.deuda+'</p>');
					$('#totser').append('<p>Valor total deudas pendientes: $'+sum_subtotal_pago+'</p>');
					$('#totpag').append('<p>Valor total pagos realizados: $'+data.pagos+'</p>');
					//});
				},"json");
}

/*
function buscarServicios(event){
	event.preventDefault();
	//alert("hola");
	$.post("../ajax/servicios.php",
				{ 
					action:"getServicios.do",
					placa: $('#param_busqueda').val()
				},
				function(data){
					//document.write(data);
					$('#resultados').fadeIn();
					$('#resultados h2').text("Estado de cuenta del vehiculo "+data.vehiculos_id);
					//var sum_subtotal_pago = 0;
					$.each(data.services,function(index,val){
						//sum_subtotal_pago += parseInt(val.subtotal_pago);
						var tmp = [];
						tmp.push('<td>'+val.id+'</td>');
						tmp.push('<td>'+val.fecha_ingreso+'</td>');
						tmp.push('<td>'+val.tiempo+val.und_tiempo+'</td>');
						tmp.push('<td>$'+val.precio+'</td>');
						tmp.push('<td>$'+(val.precio - val.subtotal_pago)+'</td>');
						$('<tr/>',{html: tmp.join('')}).appendTo('#servicios');
					});
					$('#servicios').append('<p>Valor total servicios prestados: $'+val.deuda</p>);
					//$('#servicios').append('<p>Valor total deudas pendientes: $'+sum_subtotal_pago</p>);
					
					$.each(data.pagos_service,function(index,val){
						var tmp = [];
						tmp.push('<td>$'+val.valor_pago+'</td>');
						tmp.push('<td>'+val.fecha_pago+'</td>');
						$('<tr/>',{html: tmp.join('')}).appendTo('#pagos');
					});
					$('#pagos').append('<p>Valor total pagos realizados: $'+val.pagos</p>);
					//});
				},"json");
}
*/

function cancelarPagoForm(){
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
					if(data){
						$('#ingresovehiculo').on("submit", ingresarServicio);
						$('#tipo_vehiculo').val(data.tipo_vehiculo);
						$('#propietario').val(data.nombre_propietario);
						$('#id_propietario').val(data.id_propietario);
						$('#tel_propietario').val(data.tel_propietario);
						$('#tiempo').focus();
					}
				},"json");
}

function enviarServicio(){
	//event.preventDefault();
	$.post("../ajax/servicios.php",
				{ 
					action:"addServicio.do",
					placa: $('#placa').val(), 
					tiempo: $('#tiempo').val(), 
					und_tiempo: $('#und_tiempo').val(),
					precio: $('#precio').val()
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
				},"json");

}

function ingresarVehiculo(event){
	event.preventDefault();
	if(validarForm('#ingresovehiculo')){
		enviarVehiculo();
		enviarServicio();
		$('#ingresovehiculo').reset();
	}
}

function ingresarServicio(event){
	event.preventDefault();
	enviarServicio();
	$('#ingresovehiculo').reset();
}

function init(){
	$('#resultados').tabs();
	$('#resultados #addpago').on("click", mostrarPagoForm);
	$('#pagoservicio #cancelarpago').on("click", cancelarPagoForm);
	$('#generarreporte #cancelarreporte').on("click", cancelarReporteForm);
	$('#ingresovehiculo #btncancelar').on("click", cancelarVehiculoForm);
	$('#ingresovehiculo').on("submit", ingresarVehiculo);
	$('#ingresovehiculo #placa').on("blur", cargarDataVehiculo);
	$('#buscaservicio').on("submit", buscarServicios);
}

function mostrarPagoForm(){
	$('#formpago').fadeIn(2000);
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
	//event.preventDefault();
	var valid = true;
	var msgs = [];
	//$('#ingresovehiculo :input').each(function(){
	$(form+' :input').each(function(){
		/*
		if($(this).attr("autofocus")=="autofocus"){
			$(this).focus();
		}
		*/
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
	
	if(!valid){
		$('<ul/>',{
			html: msgs.join('')
		}).appendTo('#msgs');
		$('#msgs').addClass('error');
	}
	else{
		$('<ul/>',{
			html: '<li><p>El formulario ha sido enviado correctamente</p></li>'
		}).appendTo('#msgs');
		$('#msgs').addClass('ok');
	}
	}
	return valid;
}

