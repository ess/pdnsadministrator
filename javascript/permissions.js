function checkrow(element, check)
{
	var elements = document.forms['form'].elements;
	var count    = elements.length;

	for (var i=0; i<count; i++) {
		var current = elements[i];
		var temp = current.name.split('[');

		if (!temp[1]) continue;
			temp2 = temp[1].split(']');

		if (temp2[0] == element) {
			current.checked = check;
		}
	}
}

function changeall(element, check)
{
	if (!check) {
		checkallbox(element, false);
	} else if (areallchecked(element)) {
		checkallbox(element, true);
	}
}

function checkallbox(element, check)
{
	var elements = document.forms['form'].elements;
	var count    = elements.length;

	var allchecked = true;

	for (var i=0; i<count; i++) {
		var current = elements[i];

		if (current.name == ('perms[' + element + '][-1]')) {
			current.checked = check;
		}
	}
}

function areallchecked(element)
{
	var elements = document.forms['form'].elements;
	var count    = elements.length;

	var allchecked = true;

	for (var i=0; i<count; i++) {
		var current = elements[i];

		if (current.name == ('perms[' + element + '][-1]')) {
			continue;
		}

		var temp = current.name.split('[');

		if (!temp[1]) continue;
		temp2 = temp[1].split(']');

		if (temp2[0] == element) {
			if (!current.checked) {
				allchecked = false;
				break;
			}
		}
	}
	return allchecked;
}