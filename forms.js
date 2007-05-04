function setAllCheckBoxes(FormName, FieldName, CheckValue) {
	if(!document.forms[FormName])
		return;
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
		objCheckBoxes.checked = CheckValue;
	else
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			objCheckBoxes[i].checked = CheckValue;
}

function invertAllCheckBoxes(FormName, FieldName) {
	if(!document.forms[FormName])
		return;
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes) {
		if (objCheckBoxes.checked) {
			objCheckBoxes.checked=false;
		}
		else {
			objCheckBoxes.checked=true;
		}
	}
	else {
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			if (objCheckBoxes[i].checked) {
				objCheckBoxes[i].checked=false;
			}
			else {
				objCheckBoxes[i].checked=true;
			}
	}
}

function removeChecked(FormName,FieldName,ParentObject,SubmitTo,HiddenVariable) {
	if(!document.forms[FormName])
		return;
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	var req=new DataRequestor();
	req.onfail = function (status) {alert("removeChecked died with a status of " + status);}	
	req.setObjToReplace(ParentObject);
	req.addArg(_GET,"remove","yes");
	if (HiddenVariable) {
		req.addArg(_GET,HiddenVariable,document.forms[FormName].elements[HiddenVariable].value);
	}
	if(!countCheckBoxes) {
		if (objCheckBoxes.checked) {
			req.addArg(_GET,objCheckBoxes.name,objCheckBoxes.value);
		}
	} else {
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			if (objCheckBoxes[i].checked) {
				req.addArg(_GET,objCheckBoxes[i].name+i,objCheckBoxes[i].value);
			}
	}
	req.getURL(SubmitTo);
}

function addItem(FormName,FieldName,ParentObject,SubmitTo,HiddenVariable) {
	if(!document.forms[FormName])
		return;
	var itemToAdd = document.forms[FormName].elements[FieldName];
	if(!itemToAdd)
		return;
	var req=new DataRequestor();
	req.onfail = function (status) {alert("addItem died with a status of " + status);}
	req.setObjToReplace(ParentObject);
	req.addArg(_GET,"add","yes");
	if (HiddenVariable)	req.addArg(_GET,HiddenVariable,document.forms[FormName].elements[HiddenVariable].value);
	req.addArg(_GET,itemToAdd.name,itemToAdd.value);
	req.getURL(SubmitTo);
}