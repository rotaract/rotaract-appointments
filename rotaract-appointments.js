function toggleOwnerSelection(owner){
    console.log('hello mich braucht man nicht' + owner);
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

jQuery(document).ready(function () {
		jQuery('.fc-list-event').on('click', function () {
			var elm = this;
			
			if (!jQuery(this).hasClass('is-displayed')){
				jQuery(this).addClass('is-displayed');
			
				for (var i = 0; i < elm.childNodes.length; i++) {
				    if (elm.childNodes[i].className == "event-description") {
				      var notes = elm.childNodes[i];
				      break;
				    }
				}

				var attribute = notes.innerHTML;
				elm.insertAdjacentHTML("afterend","<tr class=\"displayed-description\"><td colspan=\"3\">" + attribute  + "</td></tr>");
			} else {
				jQuery(this).next().remove();
			}
		});
		jQuery('.displayed-description').on('click', function() {
			console.log('ich bin hier');
		});
        });

