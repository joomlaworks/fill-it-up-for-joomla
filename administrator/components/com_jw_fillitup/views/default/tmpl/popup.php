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

<script type="text/javascript">
function generate() {
    jQuery.ajax({
        url : jQuery('#adminForm').attr('action'),
        type : 'post',
        data : jQuery('#adminForm input'),
        dataType : 'json',
        success : function(response) {
            if(response.category > 0) {
                jQuery('#adminForm input[name="categories[]"]:first').remove();
                jQuery('#adminForm').append('<input type="hidden" name="generatedCategories[]" value="'+response.category+'" />');
            }
            jQuery('input[name=offset]').attr('value', response.offset);
            jQuery('input[name=definitions]').attr('value', response.definitions);
            jQuery('#fillItUpPercentage').text(response.percentage+'%');
            jQuery('#fillItUpStatusBar').animate({
                'width' : (response.percentage) + '%'
            }, 'slow', 'linear', function() {
                if(response.percentage >= 100 && response.type == 'row') {
                    setTimeout(function() { window.opener.Joomla.submitbutton('cancel'); window.close(); }, 1000);
                } else {
                    generate();
                }
            });
        }
    });
}

jQuery(document).ready(function() {
    generate();
});
</script>
<div class="fillItUpContainer">
    <div id="fillItUpPercentage">0%</div>
    <div id="fillItUpStatus">
        <div id="fillItUpStatusBar"></div>
    </div>
    <div class="fillItUpNote"><?php echo JText::_('COM_JW_FILLITUP_DO_NOT_CLOSE_THIS_WINDOW'); ?></div>
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="adminForm">
        <input type="hidden" name="option" value="com_jw_fillitup" />
        <input type="hidden" name="view" value="default" />
        <input type="hidden" name="task" value="generate" />
        <input type="hidden" name="extension" value="<?php echo $this->extension; ?>" />
        <input type="hidden" name="total" value="<?php echo $this->total; ?>" />
        <input type="hidden" name="offset" value="0" />
        <input type="hidden" name="format" value="json" />
        <input type="hidden" name="params" value="<?php echo htmlspecialchars($this->params, ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="hidden" name="imagesFolder" value="<?php echo $this->imagesFolder; ?>" />
        <input type="hidden" name="definitions" value="<?php echo htmlspecialchars($this->definitions, ENT_QUOTES, 'UTF-8'); ?>" />
        <?php foreach($this->categories as $category): ?>
        <input type="hidden" name="categories[]" value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" />
        <?php endforeach; ?>
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>
<div id="fillItUpAdminFooter">
    Fill It Up v1.0 | Copyright &copy; 2006-<?php echo date('Y'); ?> <a target="_blank" href="http://www.joomlaworks.net/">JoomlaWorks Ltd.</a>
</div>
