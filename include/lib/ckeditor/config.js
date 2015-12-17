/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	
	config.dialog_startupFocusTab = false;
	config.removePlugins = 'elementspath'; 
	config.resize_enabled = false;
	config.skin = 'v2';
	//config.enterMode = CKEDITOR.ENTER_BR;
	config.toolbar_Basic =
	[

		['Cut','Copy','Paste','PasteText', 'PasteFromWord'],
		['Undo','Redo','RemoveFormat', '-', 'Source'],
		
		'/',
		['Bold','Italic','Underline','Strike'],
		['TextColor','BGColor', '-'],
		['NumberedList','BulletedList','-','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink'],
		['Image','Rule','HorizontalRule'],
		'/',
		['Styles','Format','Font','FontSize']
	]

};

