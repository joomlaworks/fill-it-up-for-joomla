<?php
/**
 * @version    1.x
 * @package    Fill It Up
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    http://www.joomlaworks.net/license
 */

// No direct access
defined('_JEXEC') or die ;

class FillItUpController extends JControllerLegacy
{

	public function display($cachable = false, $urlparams = false)
	{
		$this->default_view = JRequest::getCmd('view', 'default');
		parent::display();
		return $this;
	}

	public function extension()
	{
		JRequest::setVar('hidemainmenu', true);
		$view = $this->getView('default', 'html');
		$view->extension();
		return $this;
	}

	public function popup()
	{
		$view = $this->getView('default', 'html');
		$view->popup();
		return $this;
	}

	public function generate()
	{
		// Check token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Hide errors to avoid breaking JSON response
		//ini_set("display_errors", 0);

		// Get generator
		require_once JPATH_COMPONENT.'/lib/autoload.php';
		$generator = Faker\Factory::create();

		// Get variables
		$definitions = json_decode(JRequest::getVar('definitions'));
		$folder = JRequest::getCmd('imagesFolder');
		$params = json_decode(JRequest::getVar('params'));
		$categories = JRequest::getVar('categories');
		$generatedCategories = JRequest::getVar('generatedCategories');
		JArrayHelper::toInteger($generatedCategories);

		// Initialize the response object
		$response = new stdClass();
		$response->errors = array();
		$response->offset = JRequest::getInt('offset') + 1;
		$response->total = JRequest::getInt('total');
		$response->message = '';

		// Import the extensin plugin
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('fillitup', JRequest::getCmd('extension'));

		// Determine if we are going to generate categories or rows
		if (count($categories) > 0)
		{
			$category = $categories[0];
			$dispatcher->trigger('generateCategory', array(&$response, $generator, $category, $params, &$definitions));
			$response->type = 'category';
			if ($response->category)
			{
				FillItUpHelperExtension::donwloadCategoryImages($response->category, $category, $definitions, $folder);
				FillItUpHelperExtension::updateDefinitions($definitions, $response->category, $category);
				$dispatcher->trigger('createCategoryImage', array($response->category, $params, $generator));
				$response->definitions = json_encode($definitions);
			}
		}
		else
		{
			$dispatcher->trigger('generateRow', array(&$response, $generator, $generatedCategories, $params));
			$response->type = 'row';
		}

		if ($response->total == $response->offset)
		{
			FillItUpHelperExtension::removeTmpFolder($folder);
		}
		$response->percentage = ($response->offset / $response->total) * 100;
		$response->percentage = (int)$response->percentage;
		echo json_encode($response);
		return $this;
	}

	function cancel()
	{
		$folder = JRequest::getCmd('imagesFolder');
		if ($folder && JFolder::exists(JPATH_SITE.'/media/jw_fillitup/'.$folder))
		{
			JFolder::delete(JPATH_SITE.'/media/jw_fillitup/'.$folder);
		}
		$this->setRedirect('index.php?option=com_jw_fillitup');
		return $this;
	}

}
