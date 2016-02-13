<?php
/*
 * This file contains code specific for former PRO version of the plugin.
 *
 */

class vasthtml_pro {

	function vasthtml_pro() {
//		add_action("plugins_loaded", array(&$this, "load_wpf_topics_widget"));
	}

	// Remember to flush_rules() when adding rules
	function do_flush_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	// Adding a new rule
	function set_rewrite_rules($rules) {
		$newrules = array();
		$forum_path = trim(str_replace(get_bloginfo('url'), "", get_permalink($this->page_id)), "/");
		$newrules["(".$forum_path.")(/[-/0-9a-zA-Z]+)?/(.*)$"] = 'index.php?pagename=$matches[1]&page=$matches[2]';

		return $newrules + $rules;
	}

	// Adding the id var so that WP recognizes it
	function set_rewrite_qvars($vars) {
		array_push($vars, 'id');
		return $vars;
	}

	function get_seo_query(){
		$forum = str_replace(get_bloginfo('url'), "", get_permalink($this->page_id));
		$full_uri = $_SERVER['REQUEST_URI'];
		$uri = trim(str_replace($forum, "", $full_uri), "/");
		$uri = explode("/", $uri);
		if (array_count_values($uri)) {
			$puri = end($uri);
			preg_match("/.*-(g|f|t)(\d*(\.?\d+)?)$/", $puri, $matches);
		}
		if (!empty($matches)) {
			$result = array(
				'action' => $matches[1],
				'id' => $matches[2],
			);
		}
		return $result;
	}
	
	function load_wpf_topics_widget() {
		if (!function_exists('register_sidebar_widget')) {
			return;
		}

		if ((float) get_bloginfo('version') >= 2.8) {
			wp_register_sidebar_widget('latest_topics', __("Forums Latest Topics", "vasthtml"), array(&$this, "widget_wpf_topics"));
			wp_register_widget_control('latest_topics', "Forums Latest Topics", array(&$this, "widget_wpf_topics_control"));
		} else {
			register_sidebar_widget(__("Forums Latest Topics", "vasthtml"), array(&$this, "widget_wpf_topics"));
			register_widget_control("Forums Latest Topics", array(&$this, "widget_wpf_topics_control"));
		}

	}

	function widget_wpf_topics($args){
		global $wpdb;
		$this->setup_links();
		$widget_option = get_option("wpf_topic_widget");

		$topics = $wpdb->get_results("SELECT * FROM $this->t_threads ORDER BY `date` DESC LIMIT ".$widget_option["wpf_topic_num"]);

		echo $args['before_widget'];
		echo $args['before_title'] . $widget_option["wpf_topic_title"] . $args['after_title'];

		echo "<ul>";
		foreach($topics as $topic){
			$user = get_userdata($topic->starter);
			echo "<li><a href='".$this->thread_link."$topic->id.0'>".$this->output_filter($topic->subject)."</a> ".__("by:", "vasthtml")." ".$this->profile_link($topic->starter)."<br /><small>".$this->format_date($topic->date)."</small></li>";
		}
		echo "</ul>";
		echo $args['after_widget'];
	}

	function widget_wpf_topics_control(){
		if ( $_POST["wpf_submit"] ) {

    		$name = strip_tags(stripslashes($_POST["wpf_topic_title"]));
    		$num = strip_tags(stripslashes($_POST["wpf_topic_num"]));

    		$widget_option["wpf_topic_title"] = $name;
			$widget_option["wpf_topic_num"] = $num;
    		update_option("wpf_topic_widget", $widget_option);
 		}
 			$widget_option = get_option("wpf_topic_widget");

		echo "<p><label for='wpf_topic_title'>".__("Title to display in the sidebar:", "vasthtml")."
				<input style='width: 250px;' id='wpf_topic_title' name='wpf_topic_title' type='text' value='{$widget_option['wpf_topic_title']}' /></label></p>";


		echo "<p><label for='wpf_topic_num'>".__("How many items would you like to display?", "vasthtml");
		echo "<select name='wpf_topic_num'>";
		for($i = 1; $i < 21; ++$i){
			if($widget_option["wpf_topic_num"] == $i)
				$selected = "selected = 'selected'";
			else
				$selected = "";
			echo "<option value='$i' $selected>$i</option>";
		}
		echo "</select>";
			echo "</label></p>
				<input type='hidden' id='wpf_submit' name='wpf_submit' value='1' />";
		
	}

	

	function quick_reply($thread_id) {
		global $user_ID;
		$out = '';
		if($user_ID || $this->allow_unreg()){
			$options = get_option("vasthtml_options");
			$this->current_view = POSTREPLY;
			$thread = $this->check_parms($thread_id);
			$page = $this->curr_page;
		
			$subj = htmlentities($this->get_subject($thread), $quote_style = ENT_QUOTES);
			$out .= "<form action='".WPFURL."wpf-insert.php' name='addform' method='post'>";
			$out .= "<table class='wpf-table' width='100%'>
				<tr>
					<th colspan='2'>".__("Quick Reply", "vasthtml")."</th>
				</tr>
				<tr>	
					<td valign='top'>".__("Message:", "vasthtml")."</td>
					<td>";
							$out .= $this->form_buttons();
	
						$out .= "<br /><textarea cols='80' rows='5' name='message' >".stripslashes($q)."</textarea>";
					$out .= "</td>
				</tr>";
				
					$out .= $this->get_captcha();
	
				$out .= "<tr>
					<td></td>
					<td><input type='submit' name='add_post_submit' value='".__("Submit", "vasthtml")."' /></td>
					<input type='hidden' name='add_post_subject' value='Re: ".$subj."'/>
					<input type='hidden' name='add_post_forumid' value='".$this->check_parms($thread)."'/>
					<input type='hidden' name='add_topic_page' value='".$page."'/>
					<input type='hidden' name='add_topic_plink' value='".get_permalink($this->page_id)."'/>
	
				</tr>
	
				</table></form>";
		}
		return $out;
	}

	
	function get_post_reputation($post_id, $author_id) {
		global $wpdb, $table_prefix, $user_ID;
		$table = $table_prefix .'forum_reputation_posts';
		$sum = $wpdb->get_var("SELECT sum(value) FROM $table WHERE post_author_id = ".(int)$author_id);
		$vote = $wpdb->get_var("SELECT count(*) FROM $table WHERE post_author_id = ".(int)$author_id." and post_id = ".(int)$post_id." and author_id = ".(int)$user_ID);
		$sum = empty($sum) ? 0 : $sum; 
		return $user_ID && !$vote &&  $author_id != $user_ID ? "<br />Reputation: $sum ( <a href='".$this->thread_link.$this->current_thread."&amp;set_post_reputation=1&amp;id=$post_id'> ".__("+", "vasthtml")."</a> / <a href='".$this->thread_link.$this->current_thread."&amp;set_post_reputation=-1&amp;id=$post_id'> ".__("-", "vasthtml")."</a> )" : '<br />Reputation: '.$sum;
		
	}
	function set_post_reputation($post_id, $value) {
		global $user_ID, $wpdb, $table_prefix;
		
		$author_id = $wpdb->get_var("SELECT author_id FROM $this->t_posts WHERE id = ".(int)$post_id);
		$table = $table_prefix .'forum_reputation_posts';
		$vote = $wpdb->get_var("SELECT count(*) FROM $table WHERE post_author_id = ".(int)$author_id." and post_id = ".(int)$post_id." and author_id = ".(int)$user_ID);
		
		if(!$vote){
			$wpdb->query("INSERT INTO $table (post_id, author_id, post_author_id, value, `date`) VALUES(".(int)$post_id.", ".(int)$user_ID.", ".(int)$author_id.", '".mysql_real_escape_string(stripslashes($value))."', NOW())");
		}
	}
}
?>