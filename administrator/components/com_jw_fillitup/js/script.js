/**
 * @version    1.x
 * @package    Fill It Up
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    http://www.joomlaworks.net/license
 */

jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery('.fillItUpExtensionButton').click(function(event){
		event.preventDefault();
		jQuery('input[name=extension]').val(jQuery(this).attr('href'));
		window.open('', 'generatorPopUp', 'width=640,height=480,0,0');
		submitform();
	});
});
