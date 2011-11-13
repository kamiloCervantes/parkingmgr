// funcion reset para JQuery
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}

$(init);

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

function enviarVehiculo(event){
	event.preventDefault();
	alert($('#tipo_vehiculo').val());
	$.post("../ajax/vehiculos.php",
				{ 
					action:"addVehiculo.do",
					placa: $('#placa').val(), 
					tipo_vehiculo: $('#tipo_vehiculo').val(), 
					propietario: $('#propietario').val(),
					id_propietario: $('#id_propietario').val(),
					tel_propietario: $('#tel_propietario').val()
				}, 
		function(data){
			alert(data.respuesta);
	},"json");
}

/*
function enviarData(event){
	event.preventDefault();
	alert($('#placa').val());
	$.post("../ajax/test.php",{ num:"1" }, 
		function(data){
			alert(data.respuesta);
	},"json");
}
*/

function init(){
	$('#resultados a').on("click", mostrarPagoForm);
	$('#pagoservicio #cancelarpago').on("click", cancelarPagoForm);
	$('#generarreporte #cancelarreporte').on("click", cancelarReporteForm);
	$('#ingresovehiculo').on("submit", enviarVehiculo);
	$('#ingresovehiculo #btncancelar').on("click", cancelarVehiculoForm);
}

function mostrarPagoForm(){
	$('#formpago h2').text("Pago del servicio #");
	var a = $('#formpago h2').text()+$(this).attr('id');
	$('#formpago h2').text(a);
	$('#formpago').fadeIn(2000);
}

