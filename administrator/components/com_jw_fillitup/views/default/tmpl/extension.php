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
?>

<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton){
	    if(pressbutton == 'popup') {
	        var answer = confirm('<?php echo JText::_('COM_JW_FILLITUP_WARNING', true); ?>');
	        if (answer){
	            window.open('', '<?php echo $this->extension; ?>GeneratorPopUp', 'width=700,height=500,0,0');
	            submitform( pressbutton );
	        }
	    } else {
	        jQuery('#adminForm').attr('target', '_self');
	        submitform( pressbutton );
	    }
	}
</script>

<div class="fillItUpContainer">
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="adminForm" name="adminForm" target="<?php echo $this->extension; ?>GeneratorPopUp">
        <h2><?php echo $this->title; ?></h2>
        <h3><?php echo JText::_('COM_JW_FILLITUP_GENERAL_SETTINGS'); ?></h3>
        <fieldset class="adminform">
            <ul class="adminformlist">
                <li>
                    <label><?php echo JText::_('COM_JW_FILLITUP_NUMBER_OF_ROWS'); ?></label>
                    <input type="text" name="total" value="20" />
                </li>
            </ul>
        </fieldset>

        <?php foreach($this->form->getFieldsets() as $name => $fieldSet): ?>
            <h3><?php echo JText::_($fieldSet->label); ?></h3>
            <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
            <p><?php echo $this->escape(JText::_($fieldSet->description));?></p>
            <?php endif; ?>
            <fieldset class="adminform">
                <ul class="adminformlist">
                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
                     <?php endforeach; ?>
                </ul>
            </fieldset>
        <?php endforeach; ?>
        <div class="fillItUpButtonContainer fillItUpClr">
            <a class="fillItUpButton" href="#" onclick="Joomla.submitbutton('popup')"><?php echo JText::_('COM_JW_FILLITUP_GENERATE'); ?></a>
        </div>
        <input type="hidden" name="option" value="com_jw_fillitup" />
        <input type="hidden" name="view" value="default" />
        <input type="hidden" name="task" value="popup" />
        <input type="hidden" name="extension" value="<?php echo $this->extension; ?>" />
        <input type="hidden" name="tmpl" value="component" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>
<div id="fillItUpAdminFooter">
    Fill It Up v1.1.0 | Copyright &copy; 2006-<?php echo date('Y'); ?> <a target="_blank" href="http://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
</div>
