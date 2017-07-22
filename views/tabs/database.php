<div class="breeze-top-notice">
    <label class="breeze_tool_tip"><?php _e('It is recommended to backup your Database before using the following options.','breeze')?></label>
</div>
<table cellspacing="15">
    <tr>
        <td>
            <label for="data0" class="breeze_tool_tip"><?php _e('Select all','breeze')?></label>
        </td>
        <td>
            <input type="checkbox" id="data0" name="all_control" value="all_data"/>
            <label class="breeze_tool_tip"><?php _e('Select all following options. Click Optimize to perform actions.','breeze')?></label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="data1" class="breeze_tool_tip"><?php _e('Post revisions','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('revisions').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data1" name="clean[]" class="clean-data" value="revisions"/>
            <label class="breeze_tool_tip"><?php _e('Use this option to clear out post revisions from your WordPress database.','breeze')?></label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="data2" class="breeze_tool_tip" ><?php _e('Auto drafted content','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('drafted').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data2" name="clean[]" class="clean-data" value="drafted"/>
            <label class="breeze_tool_tip"><?php _e('Use this option to delete the auto saved drafts from WordPress database.','breeze')?></label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="data3" class="breeze_tool_tip" ><?php _e('All trashed content','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('trash').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data3" name="clean[]" class="clean-data" value="trash"/>
            <label class="breeze_tool_tip"><?php _e('Use this option to delete the trashed content from WordPress database.','breeze')?></label>

        </td>
    </tr>
    <tr>
        <td>
            <label for="data4" class="breeze_tool_tip" ><?php _e('Comments from trash & spam','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('comments').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data4" name="clean[]" class="clean-data" value="comments"/>
            <label class="breeze_tool_tip"><?php _e('Use this option to cleanup trash and spam comments from WordPress database.','breeze')?></label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="data5" class="breeze_tool_tip" ><?php _e('Trackbacks and pingbacks','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('trackbacks').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data5" name="clean[]" class="clean-data" value="trackbacks"/>
            <label class="breeze_tool_tip"><?php _e('Cleanup Transients and Pingbacks from the WordPress database.','breeze')?></label>
        </td>
    </tr>
    <tr>
        <td>
            <label for="data6" class="breeze_tool_tip" ><?php _e('Transient options','breeze')?><?php echo "&nbsp(".(int)Breeze_Configuration::getElementToClean('transient').")"; ?></label>
        </td>
        <td>
            <input type="checkbox" id="data6" name="clean[]" class="clean-data" value="transient"/>
            <label class="breeze_tool_tip"><?php _e('Cleanup expired and active Transients from WordPress.','breeze')?></label>
        </td>
    </tr>
</table>