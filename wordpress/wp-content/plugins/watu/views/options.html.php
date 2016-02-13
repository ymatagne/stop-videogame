<div class="wrap">

	<h2><?php _e("Watu Settings", 'watu'); ?></h2>

	<div class="postbox-container" style="width:73%;margin-right:2%;">	
	
	<p><?php _e('Go to', 'watu')?> <a href="admin.php?page=watu_exams"><?php _e('Manage Your Exams', 'watu')?></a>
	&nbsp;|&nbsp; <a href="admin.php?page=watu_social_sharing"><?php _e('Social Sharing Options', 'watu');?></a></p>
	
	<form name="post" action="" method="post" id="post">
	<div>
		<div class="postarea">
			<div class="postbox">
			<h3 class="hndle">&nbsp;<span><?php _e('Default Answer Type', 'watu') ?></span></h3>
			<div class="inside" style="padding:8px">
			<?php 
				$single = $multi = '';
				if( get_option('watu_answer_type') =='radio') $single='checked="checked"';
				else $multi = 'checked="checked"';
			?>
			<label>&nbsp;<input type='radio' name='answer_type' <?php print $single?> id="answer_type_r" value='radio' /> <?php _e('Single Answer', 'watu')?> </label>
			&nbsp;&nbsp;&nbsp;
			<label>&nbsp;<input type='radio' name='answer_type' <?php print $multi?> id="answer_type_c" value='checkbox' /> <?php _e('Multiple Answers', 'watu')?></label>
			</div></div>
			
	
	<div class="postbox wp-admin" style="padding:5px;">
	<h3><?php _e('Question based captcha', 'watu')?></h3>
	
	<p><?php _e("You can use a simple text-based captcha. We have loaded 3 basic questions but you can edit them and load your own. Make sure to enter only one question per line and use = to separate question from answer.", 'watu')?></p>
	
	<p><textarea name="text_captcha" rows="10" cols="70"><?php echo stripslashes($text_captcha);?></textarea></p>
	<div class="help"><?php _e('This question-based captcha can be enabled individually by selecting a checkbox in the quiz settings form. If you do not check the checkbox, the captcha question will not be generated.', 'watu');?></div>	
</div>		
	
	<div class="postbox">
		<h3 class="hndle">&nbsp;<span><?php _e('Other settings', 'watu') ?></span></h3>
		<div class="inside" style="padding:8px">
			<label>&nbsp;<input type='checkbox' value="1" name='use_the_content' <?php if(get_option('watu_use_the_content')) echo 'checked'?>  />&nbsp;<?php _e('Use "the_content" instead of our custom content filter (do not select this unless adviced so)', 'watu')?> </label>
		</div>
	</div>
	
	<div class="postbox">
		<h3 class="hndle">&nbsp;<span><?php _e('Database Option', 'watu') ?></span></h3>
		<div class="inside" style="padding:8px">
		<?php 
			$check = get_option('watu_delete_db');
		?>
		<label>&nbsp;<input type='checkbox' value="1" name='delete_db' <?php if($delete_db) echo 'checked'?> onclick="this.checked ? jQuery('#deleteDBConfirm').show() : jQuery('#deleteDBConfirm').hide();" />&nbsp;<?php _e('Delete stored Watu data when deinstalling the plugin.', 'watu')?> </label>
		
			<span id="deleteDBConfirm" style="display: <?php echo empty($delete_db) ? 'none' : 'inline';?>">
				<?php _e('Please confirm by typing "yes" in the box:', 'watu')?> <input type="text" name="delete_db_confirm" value="<?php echo get_option('watu_delete_db_confirm')?>">		
			</span>
		</div>
	</div>
		
	<p class="submit">
	<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<span id="autosave"></span>
	<input type="submit" name="submit" value="<?php _e('Save Options', 'watu') ?>"  class="button-primary" />
	</p>
	
	</div>
</div>
<?php wp_nonce_field('watu_options'); ?>
	</form>
	
	
	<div class="wrap">
		<div class="postbox">
			<div class="inside">
				<h2><?php _e('Ajax in quizzes', 'watu'); ?></h2>
				
				<p><?php _e('Here you can select quizzes which will be submitted by regular post submit rather than using Ajax. You may want to do this mostly for the following reason:', 'watu')?></p>
				
				<ul>				
					<li><?php _e('To embed in the "Final screen" content from plugins that do not normally work well in Ajax mode.', 'watu')?></li>
				</ul>
				
				<p><b><?php _e('The selected quizzes will NOT use Ajax when users submit them.', 'watu')?></b></p>
				
				<form name="post" action="" method="post" id="post">
				<div>
					<div class="postarea">
						<?php foreach($quizzes as $quiz):?>
							<input type="checkbox" name="no_ajax[]" value="<?php echo $quiz->ID?>" <?php if(!empty($quiz->no_ajax)) echo 'checked'?>> <?php echo stripslashes($quiz->name);?><br>
						<?php endforeach;?>
					</div>
										
					<p><input type="submit" name="save_ajax_settings" value="<?php _e('Save Ajax Related Settings', 'watupro')?>" class="button-primary"></p>
				</div>
				<?php wp_nonce_field('watu_ajax_options'); ?>
				</form>
			</div>	
		</div>
	</div>

	</div>
	<div id="watu-sidebar">
			<?php include(WATU_PATH."/views/sidebar.php");?>
	</div>
</div>	
