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

class plgFillItUpUsers extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_'.$this->_type.'_'.$this->_name.'.sys');
	}

	public function generateCategory($response, $generator, $categoryName, $params, $definitions)
	{

	}

	public function createCategoryImage($catid, $params, $generator)
	{
	}

	public function generateRow($response, $generator, $categories, $params)
	{
		$data = array();
		$data['block'] = 0;
		$data['name'] = $generator->name;
		$data['username'] = $generator->userName;
		$data['email'] = $generator->email;
		$data['password'] = '';
		$data['password2'] = $data['password'];
		$groups = $params->groups;
		if (!is_array($groups) || !count($groups))
		{
			$userParams = JComponentHelper::getParams('com_users');
			$groups = array($userParams->get('new_usertype', 2));
		}
		$data['groups'] = $groups;
		$row = JUser::getInstance(0);
		$row->bind($data);
		$row->save();
	}

}
