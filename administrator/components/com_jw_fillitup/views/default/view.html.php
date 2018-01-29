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

class FillItUpViewDefault extends JViewLegacy
{

	public function display($tpl = null)
	{
		$extensions = FillItUpHelperExtension::getExtensions();
		$this->assignRef('extensions', $extensions);
		$info = array();
		$info['php'] = phpversion();
		if (extension_loaded('gd'))
		{
			$gdinfo = gd_info();
			$info['gd'] = $gdinfo["GD Version"];
		}
		else
		{
			$info['gd'] = false;

		}
		$info['memory'] = ini_get('memory_limit');
		$this->assignRef('info', $info);
		JToolBarHelper::title(JText::_('COM_JW_FILLITUP'), 'fillItUp');
		$user = JFactory::getUser();
		if ($user->authorise('core.admin', 'com_jw_fillitup'))
		{
			JToolBarHelper::preferences('com_jw_fillitup', 550, 875);
		}
		parent::display($tpl);
	}

	public function extension($tpl = null)
	{
		$extension = JRequest::getCmd('extension');
		$this->assignRef('extension', $extension);
		FillItUpHelperExtension::loadLanguage($extension);
		$form = FillItUpHelperExtension::getForm($extension);
		$this->assignRef('form', $form);
		$this->setLayout('extension');

		$plugin = JPluginHelper::getPlugin('fillitup', $extension);
		$plugin->label = JText::_('PLG_FILLITUP_'.JString::strtoupper($plugin->name).'_LABEL');
		$title = JText::sprintf('COM_JW_FILLITUP_GENERATION_SETTINGS', $plugin->label);
		$this->assignRef('title', $title);
		JToolBarHelper::title($title, 'fillItUp');
		JToolBarHelper::custom('cancel', 'back', 'back', 'JTOOLBAR_BACK', false);
		parent::display($tpl);
	}

	public function popup($tpl = null)
	{
		// Get the extension
		$extension = JRequest::getCmd('extension');
		$this->assignRef('extension', $extension);

		// Get the post variables and pass them in JSON
		$params = htmlspecialchars(json_encode(JRequest::get('post')), ENT_QUOTES, 'UTF-8');
		$this->assignRef('params', $params);

		$definitions = '';
		$categories = array();
		if ($this->extension == 'k2' || $this->extension == 'content')
		{
			// Download the definitions file
			$definitions = FillItUpHelperExtension::getDefinitions();
			if (!$definitions)
			{
				$mainframe = JFactory::getApplication();
				$mainframe->enqueueMessage(JText::_('COM_JW_FILLITUP_COULD_NOT_READ_DEFINITIONS_FILE'), 'error');
				return;
			}

			// Build the array of categories that will be created based on the definition file
			$json = json_decode($definitions);
			foreach ($json as $definition)
			{
				$categories[] = $definition->name;
			}
		}

		$this->assignRef('definitions', $definitions);
		$this->assignRef('categories', $categories);

		// Generate a tmp folder to store all the definitions files
		$imagesFolder = FillItUpHelperExtension::generateTmpFolder();
		$this->assignRef('imagesFolder', $imagesFolder);

		// Set the total variable. Needed to create the loop
		$total = JRequest::getInt('total');
		$total = $total + count($categories);
		$this->assignRef('total', $total);

		// Load plugin language
		FillItUpHelperExtension::loadLanguage($extension);

		// Display
		$this->setLayout('popup');
		parent::display($tpl);
	}

}
