jQuery(document).ready(function($) {
    var updateShortcodeTextbox = function() {
    	var selectedMcCode = $('#shortcodeSelectListId option:selected').attr('data-mc-code');
    	if (typeof selectedMcCode !== 'undefined') {
    		var selectedColor = $('#shortcodeSelectColor option:selected').val();
	    	var showLinkCheckbox = $('#shortcodeShowLink').is(':checked');
	    	var postfixText = ($('#shortcodePostfixText').val() == "") ? " Subscribers" : $('#shortcodePostfixText').val();
	    	$("#mscw_shortCodeResult").val('[subscriber-chiclet listId="' + selectedMcCode + '" color="' + selectedColor + '" showlink="'+ String(showLinkCheckbox) + '" postfixtext="' + postfixText + '"]');
    	}
    }
    $("#shortcodeSelectListId").change(function() {
    	updateShortcodeTextbox();
    });
    $("#shortcodeSelectColor").change(function() {
    	updateShortcodeTextbox();
    });
    $("#shortcodeShowLink").change(function() {
    	updateShortcodeTextbox();
    });
    $("#shortcodePostfixText").keyup(function() {
    	updateShortcodeTextbox();
    });
    $("#mscw_shortCodeResult").click(function() {
    	$(this).select();
    });
});