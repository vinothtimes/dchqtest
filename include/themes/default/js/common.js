

function confirmLink(theLink, theConfirmMsg) {
	// Check if Confirmation is not needed
	// or browser is Opera (crappy js implementation)
	if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
		return true;
	}

	var is_confirmed = confirm(theConfirmMsg + '\n');
	if (is_confirmed) {
			theLink.href += '&is_js_confirmed=1';
	}

	return is_confirmed;
} 




function checkBoxes(checkbox, name) {
	
	var form, state, boxes, count,i;
	form = checkbox.form;
	state = checkbox.checked;

	boxes = document.getElementsByName(""+name);

	count = boxes.length; 
	for (i=0;i<count;i++)
		boxes[i].checked = state;

}

// disable the save button on the form.

function SubmitOnce(theform) {
	theform.savebutton.disabled=true;
}



