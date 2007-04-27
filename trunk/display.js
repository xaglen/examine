function show(what)
{
  if (!document.getElementById) return null;
  showWhat = document.getElementById(what);
  showWhat.className = "content";
}

function hide(what)
{
	if (!document.getElementById) return null;
	showWhat = document.getElementById(what);
	showWhat.className = "hidden";
}

function editmode() {
	hide('content');
	show('edit');
}

function displaymode() {
	hide('edit');
	show('content');
}

function addChildLayer(parentID,childID,childHTML) {
	var parentLayer = document.getElementById(parentID);
	var childLayer = document.createElement("div");
	childLayer.setAttribute("id",childID);
	childLayer.innerHTML = childHTML
	parentLayer.appendChild(childLayer);
}