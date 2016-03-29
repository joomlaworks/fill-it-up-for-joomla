<?php
/**
 * @version    1.x
 * @package    Fill It Up
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldK2Images extends JFormField
{
	var $type = 'K2Images';

	public function getInput()
	{
		// Get K2 version
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('name').' = '.$db->quote('com_k2'));
		$db->setQuery($query);
		$manifest = json_decode($db->loadResult());
		$this->version = $manifest->version;

		// Initialize output variable
		$output = '';

		if (version_compare($this->version, '3.0.0', 'ge'))
		{
			$params = JComponentHelper::getParams('com_k2');
			$sizes = (array)$params->get('imageSizes');
			$values = array();
			if (count($sizes))
			{
				$output .= '<ul style="margin:0;">';
				foreach ($sizes as $key => $size)
				{
					$value = isset($values[$size->id]) ? $values[$size->id] : '';
					$output .= '<li>
					<label>'.$size->name.'</label> 
					<input type="text" value="'.$size->width.'" name="'.$this->name.'['.$key.'][width]" />
					<input type="hidden" value="'.$size->id.'" name="'.$this->name.'['.$key.'][id]" />
					<input type="hidden" value="'.$size->name.'" name="'.$this->name.'['.$key.'][name]" />
					<input type="hidden" value="'.$size->quality.'" name="'.$this->name.'['.$key.'][quality]" />
					</li>';
				}
				$output .= '</ul>';
			}
		}
		else
		{
			$params = JComponentHelper::getParams('com_k2');
			$sizes = array('xsmall' => $params->get('itemImageXS'), 'small' => $params->get('itemImageS'), 'medium' => $params->get('itemImageM'), 'large' => $params->get('itemImageL'), 'xlarge' => $params->get('itemImageXL'), 'generic' => $params->get('itemImageGeneric'));
			$output .= '<ul style="margin:0;">';
			foreach ($sizes as $key => $width)
			{
				$output .= '<li>
					<label>'.JText::_('PLG_FILLITUP_K2_ITEM_IMAGE_'.strtoupper($key)).'</label> 
					<input type="text" value="'.$width.'" name="'.$key.'" />
					</li>';
			}
			$output .= '</ul>';

		}

		return $output;

	}

}
