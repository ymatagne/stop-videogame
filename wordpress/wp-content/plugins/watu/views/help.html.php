<div class="wrap">
	<h1><?php _e('Watu - Help and Support', 'watu');?></h1>
	
	<div class="postbox-container" style="width:73%;margin-right:2%;">
		<h2><?php _e('Creating Quizzes', 'watu');?></h2>
		
		<p><?php _e('Go to Watu Quizzes and click on "Create new quiz". Each quiz has title, description and various settings. The quiz also consists of questions and grades / results.', 'watu');?><br>
		<?php _e('Questions are mandatory - you cannot have a quiz without at least one question. Grades are optional - for example if you want to use quizzes as surveys you might not need grades. But the regular quizzes will have grades because this is the whole purpose of a quiz - to calculate a result based on the collected points.', 'watu');?><br>
		<?php _e('Make sure to calculate how many points the user could collect if they answer all questions correctly or all wrong so your grades cover both edges of min/max collected points.', 'watu');?></p>
		
		<h2><?php _e('Frequently Asked Questions', 'watu');?></h2>
		
		<p><?php printf(__('Please check them <a href="%s" target="_blank">online</a>.', 'watu'), 'https://wordpress.org/plugins/watu/faq/');?></p>
		
		<h2><?php _e('Requesting Help', 'watu');?></h2>
		
		<p><b><?php printf(__('When opening a <a href="%s" target="_blank">support thread</a> please provide URL (link) where we can see your problem.', 'watu'), 'https://wordpress.org/support/plugin/watu');?></b></p>
		
		<h2><?php _e('PRO Inquiries', 'watu');?></h2>
		
		<p><?php printf(__('If you have pre-sales or support questions about WatuPRO please send them using the contact details on the <a href="%s" target="_blank">official WatuPRO site</a>. Do not use the wordpress.org forum for this - it allows only free plugin discussions.', 'watu'), 'http://calendarscripts.info/watupro/support.html');?></p>
		
		<h2><?php _e('MailChimp Integration', 'watu');?></h2>
		
		<p><?php printf(__('You can integrate your quizzes with MailChimp so when someone completes a chosen quiz with a given result, they can be subscribed in a mailing list. You will need <a href="%s" target="_blank">this free bridge</a> to do that.', 'watu'), 'https://wordpress.org/plugins/watu-bridge-to-mailchimp/');?></p>
	</div>	
	<div id="watu-sidebar">
			<?php include(WATU_PATH."/views/sidebar.php");?>
	</div>
</div>