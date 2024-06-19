// Obtener el modal y el botón de cerrar
var modal = document.getElementById("myModal");
var span = document.getElementsByClassName("close")[0];

// Función para abrir el modal y setear el ID del reporte
function openModal(reportID) {
    document.getElementById("reportID").value = reportID;  // Setea el ID del reporte en el input oculto
    modal.style.display = "block";  // Muestra el modal
    resetForm();  // Restablecer el formulario cada vez que se abre el modal
}

// Función para cerrar el modal al hacer clic en el botón de cerrar (X)
span.onclick = function() {
    modal.style.display = "none";
}

// Función para cerrar el modal al hacer clic fuera de él
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Función para mostrar los detalles de un reporte específico
function showDetails(count) {
    var details = document.querySelectorAll('.report-details');
    var defaultMessage = document.getElementById('default-message');

    // Ocultar todos los detalles
    details.forEach(function(detail) {
        detail.style.display = 'none';
    });

    // Mostrar el reporte seleccionado
    document.getElementById('details' + count).style.display = 'block';

    // Ocultar el mensaje por defecto
    defaultMessage.style.display = 'none';
}

// Get the modal
var rejectModal = document.getElementById('rejectModal');

// Get the button that opens the modal
var rejectBtn = document.getElementsByClassName('action-button-reject');

// Get the <span> element that closes the modal
var spanReject = document.getElementsByClassName('close-reject')[0];

// When the user clicks the button, open the modal 
for (let i = 0; i < rejectBtn.length; i++) {
    rejectBtn[i].onclick = function() {
        rejectModal.style.display = "block";
        document.getElementById('rejectReportID').value = this.getAttribute('data-report-id'); // Set report ID
    }
}

// When the user clicks on <span> (x), close the modal
spanReject.onclick = function() {
    rejectModal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == rejectModal) {
        rejectModal.style.display = "none";
    }
}

