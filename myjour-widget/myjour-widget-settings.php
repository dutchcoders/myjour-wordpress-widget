<?php
	global $wpdb;    
    $pageTitle = 'Myjour Widget settings';
    $action = 'updatesettings';
?>
<div class="wrap">
    <h2><?php echo $pageTitle; ?></h2>  
    
    <?php
        // Sabic_improve::showMessage($_GET['msg'], $_GET['msgtype']);
    ?>
        
    <form name="frmmyjoursettings" method="post" action="<?php echo $thisPageURL; ?>" autocomplete="off">
       <input type="hidden" name="processval" value="<?php echo $action;?>" />
       
        <table>
             <tr>
                <td align="left" style="padding: 5px 5px 5px 5px;"><b>Related article selector (CSS)</b></td>
                <td align="left" style="padding: 5px 5px 5px 5px;"><input type="text" name="sb_data_like_ref" id="sb_data_like_ref" value="<?php echo stripslashes($optval_arr['data-like-ref']); ?>" style="width: 300px;" maxlength='50' /> (e.g. <b>.itemFullText</b>)</td> 
             </tr> 
             <tr>
                <td align="left" style="padding: 5px 5px 5px 5px;"><b>Channel</b></td>
                <td align="left" style="padding: 5px 5px 5px 5px;"><input type="text" name="sb_data_channel" id="sb_data_channel" value="<?php echo stripslashes($optval_arr['data-channel']); ?>" style="width: 300px;" maxlength='50' /> (e.g. <b>myjour</b>)</td> 
             </tr> 
             
             <tr>
                <td align="left" style="padding: 5px 5px 5px 5px;"><b>Count</b></td>
                <td align="left" style="padding: 5px 5px 5px 5px;"><input type="text" name="sb_data_size" id="sb_data_size" value="<?php echo $optval_arr['data-size']; ?>" style="width: 50px;" maxlength='3' /> (default value as <b>3</b>)</td> 
             </tr> 
             
             <tr>
                <td align="left" style="padding: 5px 5px 5px 5px;" valign="top"><b>Use Post Meta Box</b></td>
                <td align="left" style="padding: 5px 5px 5px 5px;" valign="top"><input type="checkbox" name="sb_data_box_active" id="sb_data_box_active" value="1" <?php if( isset($optval_arr['auto_show_in_post']) && intval($optval_arr['auto_show_in_post']) == 1 ){ echo 'checked="checked"'; } ?> /><br />
                If this is enabled then Myjour Widget is auto append on each post/page or custom post type.<br />
                </td> 
             </tr> 
             
             <tr>
                <td align="left" style="padding: 5px 5px 5px 5px;" valign="top"><b>Inline CSS</b></td>
                <td align="left" style="padding: 5px 5px 5px 5px;" valign="top">
                    <textarea name="sb_inline_css" id="sb_inline_css" style="width: 300px; height: 150px"><?php echo stripslashes( $optval_arr['css'] ); ?></textarea>
                    <br />
                    Note: it will be display when you use below shortcode (below point no. 1)
                </td> 
             </tr> 
             
              <tr>
                 <td align="left" style="padding: 5px 5px 5px 5px;" colspan="2"><input type="submit" name="submit" value="SAVE" /></td> 
             </tr>
        </table>
    </form>
    
    <br /><br />
    <h2>How to use</h2>
    <div>
        <h3>1. Auto shows article when checked above input checkbox ' Auto show articles via Post Meta Box ':</h3>
        <div>
            MyJour articles auto append on end of post description of Post, Page or Custom post type using above Default Settings when this checkbox enable <br />
            And also a Meta box 'Myjour Articles Settings' on right sidebar of Edit Section of Post, Page or Custom Post Type.
        </div>
        <br />
        
        <h3>2. AND / OR Copy & paste below shortcode on post / page or any article of wordpress:</h3>
        <div style="background: #cccccc; padding: 5px; color:#000;">[myjour-widget]</div>
        <br />
        <b>OR</b>
        <br /><br />
        <div style="background: #cccccc; padding: 5px; color:#000;">[myjour-widget data_size=1]</div>
        <br />
        <b>OR</b>
        <br /><br />
        <div style="background: #cccccc; padding: 5px; color:#000;">[myjour-widget data_like_ref='.itemFullText' data_channel='myjour' data_size=3 css='li{ width: 200px; }']</div>
        <br />
        <br />
        <div>Where as, <b>[myjour-widget]</b> is shortcode of editor text, if you use <b>without attributes</b> ( <b>data_like_ref</b> and/or <b>data_channel</b> and/or <b>data_size</b> and/or <b>css</b> ) then related values would be taken from above form/input values.</div>
    </div>  
    
    <div>
        <br />
        <h3>3. AND / OR You can also shown articles on siderbar of pages as below:</h3>
        <div>Go <b>Appearance -> Widgets</b> submenu via left or top menu bar <b>OR</b> <a href="<?php echo get_admin_url().'widgets.php'; ?>">click here</a></div>
        <br />
        <div>Then you can find '<b>Myjour Widget</b>' and set this widget on as per your preference sidebar.</div>
    </div>
    
</div>
