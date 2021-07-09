function toggleOwnerSelection(owner) {
	var ownerField = document.getElementById('owners');
	var ownerText = ownerField.value;
	var selectedOwner = document.getElementById(owner);
	selectedOwner.classList.toggle('selection');
	if (ownerText == '') {
		ownerText += owner;
	} else {
		ownerText += ';' + owner;
	}
	ownerField.value = ownerText;
}
