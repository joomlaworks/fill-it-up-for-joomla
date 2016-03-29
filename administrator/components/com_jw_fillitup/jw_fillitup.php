<?php
/**
 * @version    1.x
 * @package    Fill It Up
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    http://www.joomlaworks.net/license
 */

// No direct access
defined('_JEXEC') or die ;

$application = JFactory::getApplication();
$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_jw_fillitup'))
{
	JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
	$application->redirect('index.php');
}

jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.application.component.view');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once JPATH_COMPONENT.'/helpers/extension.php';

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(true).'/components/com_jw_fillitup/css/style.css');
if (version_compare(JVERSION, '3.2', 'lt'))
{
	$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js');
}
else
{
	JHtml::_('jquery.framework');
}
$document->addScript(JURI::base(true).'/components/com_jw_fillitup/js/script.js');

$controller = JControllerLegacy::getInstance('FillItUp');
$controller->execute($application->input->get('task'));
$controller->redirect();
