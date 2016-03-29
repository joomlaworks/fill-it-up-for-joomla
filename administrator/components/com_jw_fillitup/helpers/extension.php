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

class FillItUpHelperExtension
{

	static function getExtensions()
	{
		$mainframe = JFactory::getApplication();
		$defaultIcon = JURI::base(true).'/templates/'.$mainframe->getTemplate().'/images/header/icon-48-generic.png';
		$plugins = JPluginHelper::getPlugin('fillitup');
		foreach ($plugins as $plugin)
		{
			FillItUpHelperExtension::loadLanguage($plugin->name);
			$plugin->link = JRoute::_('index.php?option=com_jw_fillitup&view=default&task=extension&extension='.$plugin->name);
			$plugin->label = JText::_('PLG_FILLITUP_'.JString::strtoupper($plugin->name).'_LABEL');
			$plugin->icon = (JFile::exists(JPATH_SITE.'/plugins/fillitup/'.$plugin->name.'/icon.png')) ? JURI::root(true).'/plugins/fillitup/'.$plugin->name.'/icon.png' : $defaultIcon;
		}
		return $plugins;
	}

	static function loadLanguage($name)
	{
		$extension = 'plg_fillitup_'.$name.'.sys';
		$lang = JFactory::getLanguage();
		return $lang->load(strtolower($extension), JPATH_ADMINISTRATOR, null, false, false) || $lang->load(strtolower($extension), JPATH_PLUGINS.'/fillitup/'.$name, null, false, false) || $lang->load(strtolower($extension), JPATH_ADMINISTRATOR, $lang->getDefault(), false, false) || $lang->load(strtolower($extension), JPATH_PLUGINS.'/fillitup/'.$name, $lang->getDefault(), false, false);
	}

	static function getForm($name)
	{
		return JForm::getInstance($name.'SettingsForm', JPATH_SITE.'/plugins/fillitup/'.$name.'/settings.xml');
	}

	static function getDefinitions()
	{
		jimport('joomla.filesystem.file');
		$params = JComponentHelper::getParams('com_jw_fillitup');
		$json = false;
		if ($params->get('definitionsUrl'))
		{
			$data = JFile::read($params->get('definitionsUrl'));
		}
		if ($data)
		{
			$definitions = json_decode($data);
			if(is_array($definitions) && count($definitions))
			{
				foreach ($definitions as $definition)
				{
					$definition->tagIDs = array();

					if (!isset($definition->tags))
					{
						$definition->tags = array();
					}

					if (!isset($definition->galleries))
					{
						$definition->galleries = array();
					}

					if (!isset($definition->media))
					{
						$definition->media = array();
					}
				}
				$json = json_encode($definitions);
			}

		}
		return $json;
	}

	static function generateTmpFolder()
	{
		jimport('joomla.filesystem.folder');
		$folder = uniqid();
		JFolder::create(JPATH_SITE.'/media/jw_fillitup/'.$folder);
		return $folder;
	}

	static function removeTmpFolder($folder)
	{
		jimport('joomla.filesystem.folder');
		if ($folder && JFolder::exists(JPATH_SITE.'/media/jw_fillitup/'.$folder))
		{
			JFolder::delete(JPATH_SITE.'/media/jw_fillitup/'.$folder);
		}
	}

	static function donwloadCategoryImages($categoryID, $categoryName, $definitions, $folder)
	{
		jimport('joomla.filesystem.archive');
		foreach ($definitions as $definition)
		{
			if ($definition->name == $categoryName)
			{
				$buffer = @file_get_contents($definition->images);
				if ($buffer !== false)
				{
					JFile::write(JPATH_SITE.'/media/jw_fillitup/'.$folder.'/'.$categoryID.'.zip', $buffer);
					JArchive::extract(JPATH_SITE.'/media/jw_fillitup/'.$folder.'/'.$categoryID.'.zip', JPATH_SITE.'/media/jw_fillitup/'.$folder.'/'.$categoryID);
				}
			}
		}

		return true;
	}

	static function updateDefinitions(&$definitions, $categoryID, $categoryName)
	{
		foreach ($definitions as $definition)
		{
			if ($definition->name == $categoryName)
			{
				$definition->id = $categoryID;
			}
		}
		return $definitions;
	}

}
