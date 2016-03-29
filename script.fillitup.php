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

class Com_Jw_FillItUpInstallerScript
{
    public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();
        $manifest = $parent->getParent()->manifest;
        $src = $parent->getParent()->getPath('source');
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/'.$group.'/'.$name;
            $installer = new JInstaller;
            $installer->install($path);
            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
            $db->setQuery($query);
            $db->query();
        }
    }

    public function uninstall($parent)
    {
        $db = JFactory::getDBO();
        $manifest = $parent->getParent()->manifest;
        $src = $parent->getParent()->getPath('source');
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($name)." AND folder = ".$db->Quote($group);
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions))
            {
                foreach ($extensions as $id)
                {
                    $installer = new JInstaller;
                    $installer->uninstall('plugin', $id);
                }
            }
        }
    }

}
