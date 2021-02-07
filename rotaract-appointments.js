function toggleAppointmentDescription(id){
    var appointmentDescription = document.getElementById('appointment-description-'+id);
    if (appointmentDescription.style.display === "none") {
        appointmentDescription.style.display = "flex";
    } else {
        appointmentDescription.style.display = "none";
    }

}
function toggleOwnerSelection(owner){
    console.log('hello ' + owner);
    var ownerField = document.getElementById('owners');
    var ownerText = ownerField.value;
    var selectedOwner = document.getElementById(owner);
    selectedOwner.classList.toggle('selection');
    if (ownerText == '') {
        ownerText += owner;
    } else {
        ownerText += '-' + owner;
    }
    ownerField.value = ownerText;

}
