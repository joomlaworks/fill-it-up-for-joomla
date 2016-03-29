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
?>
<div class="fillItUpContainer">
	<table class="fillItUpTable adminlist table">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_JW_FILLITUP_EXTENSIONS'); ?></th>
				<th><?php echo JText::_('COM_JW_FILLITUP_ABOUT'); ?></th>
				<th><?php echo JText::_('COM_JW_FILLITUP_SYSTEM_INFORMATION'); ?></th>
				<th><?php echo JText::_('COM_JW_FILLITUP_CREDITS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="fillItUpExtensions">
				<ul class="fillItUpExtensionsList">
					<?php foreach($this->extensions as $extension): ?>
					<li class="fillItUpExtension fillItUpButton">
					    <a href="<?php echo $extension->link; ?>"> <img alt="<?php echo $extension->label; ?>" src="<?php echo $extension->icon; ?>" /> <span><?php echo $extension->label; ?></span> </a>
					</li>
					<?php endforeach; ?>
				</ul>
				</td>
				<td class="fillItUpAbout"><?php echo JText::_('COM_JW_FILLITUP_ABOUT_TEXT'); ?></td>
				<td class="fillItUpInformation">
				<table class="adminlist table-striped">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_JW_FILLITUP_CHECK'); ?></th>
							<th><?php echo JText::_('COM_JW_FILLITUP_RESULT'); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php echo JText::_('COM_JW_FILLITUP_PHP_VERSION'); ?></strong></td>
							<td><?php echo $this->info['php']; ?></td>
						</tr>
						<tr>
							<td><strong><?php echo JText::_('COM_JW_FILLITUP_GD_IMAGE_LIBRARY'); ?></strong></td>
							<td><?php echo ($this->info['gd'])? $this->info['gd'] : JText::_('COM_JW_FILLITUP_DISABLED'); ?></td>
						</tr>
						<tr>
							<td><strong><?php echo JText::_('COM_JW_FILLITUP_MEMORY_LIMIT'); ?></strong></td>
							<td><?php echo $this->info['memory']; ?></td>
						</tr>
					</tbody>
				</table>
				</td>
				<td class="fillItUpCredits">
				    <table class="adminlist table-striped">
				        <thead>
				            <tr>
                                <th><?php echo JText::_('COM_JW_FILLITUP_PROVIDER'); ?></th>
                                <th><?php echo JText::_('COM_JW_FILLITUP_VERSION'); ?></th>
                                <th><?php echo JText::_('COM_JW_FILLITUP_TYPE'); ?></th>
                                <th><?php echo JText::_('COM_JW_FILLITUP_LICENSE'); ?></th>
				            </tr>
				        </thead>
				        <tbody>
                            <tr>
                                <td><a target="_blank" href="http://jquery.com">jQuery</a></td>
                                <td>1.x</td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_JS_LIB'); ?></td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_MIT'); ?></td>
                            </tr>
                            <tr>
                                <td><a target="_blank" href="https://github.com/fzaninotto/Faker">Faker</a></td>
                                <td>1.5.0</td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_PHP_CLASS'); ?></td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_MIT'); ?></td>
                            </tr>
                            <tr>
                                <td><a target="_blank" href="http://www.verot.net/php_class_upload.htm">class.upload.php</a></td>
                                <td>0.33dev</td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_PHP_CLASS'); ?></td>
                                <td><?php echo JText::_('COM_JW_FILLITUP_GNUGPL'); ?></td>
                            </tr>
				        </tbody>
				    </table>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div id="fillItUpAdminFooter">
	Fill It Up v1.0 | Copyright &copy; 2006-<?php echo date('Y'); ?> <a target="_blank" href="http://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
</div>
