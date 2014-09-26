// Set project data, and manipulate images
$(document).ready(function () {
	$(document).ajaxError(function(e, xhr, settings, exception) {
		alert('error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText );
	});
	//alert( 'jqproject loaded and ready' );
	$('a.deleteImage').click( imagedelete );
	$('a.deleteProject').click( projectdelete );
	// Various project update stuff
	$('input.save,select.save,textarea.save').change( saveItem );
	// Initial display of Preview and Submit buttons
	$("#response").hide();
	// Image (re)arranging
	// Project (re)arranging
	$(function() {
		$('#images li, #projects li').hover( hoverOver, hoverAway );
		$("#images ul").sortable({ opacity: 0.8, cursor: 'move',
			update: function() {
				var order = $(this).sortable("serialize") + '&id=move';
				$.post(updateserver, order, function(theResponse){
					}, "json");
				}
		});
		$("#projects ul").sortable({ opacity: 0.8, cursor: 'rearrange',
			update: function() {
				var order = $(this).sortable("serialize") + '&id=rearrange';
				$.post(updateserver, order, function(theResponse){
					}, "json");
				}
		});
	});

	// Update the form action according to which button was clicked
    $('#uploadForm :input').focus(function(){
		$('#uploadForm').attr('action',$(this).attr('alt'));
    });
	
	$('#uploadForm').submit(function(){
		var submitOK = true;
		$('.required').each(function() {
			var errorID = 'error'+$(this).attr("id");
			//alert( 'Checking '+$(this).attr('name'));
			$('#'+errorID).remove();
			if( $(this).val().length <= 0 ) {
				//alert( $(this).attr('name')+': failed');
				$(this).parent().append('<div id="'+errorID+'" class="red">Cannot be empty</div>');
				submitOK = false;
			}
		});
		if( !submitOK )
			submitOK = confirm( "You have errors that should be corrected. Do you still want to leave?" );
		return submitOK;
	});
	// Execute a project search
	$('#searchForm').submit(function() {
		//alert( 'validating search input, length = '+$('#query').val().length );
		if( $('#query').val().length <= 0 ) {
			alert( 'Please enter something to search for' );
			this.blur();	// Kill further link processing
			return false;
		}
		return true;
	});
});

// Event functions
function imagedelete(){
	try {
		var image_id = $(this).attr('href');
		if( confirm( 'You are about to delete this image.  This cannot be undone and your photo cannot be restored.  Delete image?') ) {
			// We first back up the move in the DB.  If that works we'll update the page.
			var dataString = 'id=move&direction=delete&image_id='+image_id;
			//alert( 'Sending '+dataString );
			$.get( updateserver, dataString,
				function(resp) {
					if( resp.status == 'OK' ) {
						$('#arrayorder_'+image_id).remove();
					} else
						alert( 'ERROR: '+resp.message );
				},
				'json');
		}
	} catch(e) {
		alert( "An exception occurred in the script. Error name: [" + e.name + "]. Error message: " + e.message);
	}
	this.blur();	// Kill further link processing
	return false;
}

function projectdelete(){
	try {
		var project_id = $(this).attr('href');
		if( confirm( 'You are about to delete this project and all photos.  This cannot be undone and your project cannot be restored.  Delete project?') ) {
			// We first back up the move in the DB.  If that works we'll update the page.
			var dataString = 'id=rearrange&direction=delete&project_id='+project_id;
			//alert( 'Sending '+dataString );
			$.get( updateserver, dataString,
				function(resp) {
					if( resp.status == 'OK' ) {
						$('#arrayorder_'+project_id).remove();
					} else
						alert( 'ERROR: '+resp.message );
				},
				'json');
		}
	} catch(e) {
		alert( "An exception occurred in the script. Error name: [" + e.name + "]. Error message: " + e.message);
	}
	this.blur();	// Kill further link processing
	return false;
}

function saveItem(){
	//alert( 'Saving '+ $(this).parent().attr('name') );
	var errorID = 'error'+$(this).attr("id");
	$('#'+errorID).remove();
	if( $(this).val().length > 0 ) {
		try {
			var item_id = $(this).parent().attr('title');
			var field = $(this).attr('name');
			var value = $(this).val();
			var type = 'project';
			//alert( 'Changing '+$(this).attr('name')+' of project/image '+item_id+' to '+value );
			$('.wait_update').show();
			// We first back up the move in the DB.  If that works we'll update the page as needed.
			if( field == 'Caption' || field == 'Main' )
				type = 'image';
			if( $(this).is(':checkbox') )
				value = $(this).attr("checked") ? 1 : 0;	// Only useful for true/false
			//alert( 'field = '+field+', value = '+value+' to '+updateserver );
			$.get( updateserver, { id: 'save', type: type, field: field, key: item_id, value: value },
				function( resp ) {
					//alert( field+': value = '+window[field]);
					if( resp.status == 'ERROR' )
						alert( 'ERROR: '+resp.message );
					else if( window[field] != undefined ) {
						len = value.length;
						window[field] = len > 0;
						if( len <= 0 )
							$(this).append('<span id="'+errorID+'">Field must contain a value</span>');
					}
				}, 'json' );
				//alert( 'after get' );
		} catch(e) {
			alert( "An exception occurred in the script. Error name: [" + e.name + "]. Error message: " + e.message);
		}
		$('.wait_update').hide();
	} else {
		//alert( $(this).attr('name')+': failed');
		$(this).parent().append('<div id="'+errorID+'" class="red">Cannot be empty</div>');
	}
	this.blur();	// Kill further link processing
	return false;
}

function hoverOver(){
	$(this).css('border', 'thin solid red');
//	$(this).find('span:first').css('display','inline');
}

function hoverAway(){
	$(this).css('border', 'none');
//	$(this).find('span:first').css('display','none');
}