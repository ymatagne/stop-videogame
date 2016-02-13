<?php
// select taking records for an exam
function watu_takings() {
	global $wpdb;
	
	// select exam
	$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATU_EXAMS." WHERE ID=%d", $_GET['exam_id']));
	$grades = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATU_GRADES." WHERE  exam_id=%d order by gtitle ", $exam->ID) );
	
	// delete a taking
	if(!empty($_GET['del_taking'])) {
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATU_TAKINGS." WHERE ID=%d", $_GET['id']));
		watu_redirect("admin.php?page=watu_takings&exam_id=".$exam->ID);
	}
	
	// mass cleanup
	if(!empty($_POST['delete_all_takings'])) {
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATU_TAKINGS." WHERE exam_id=%d", $exam->ID));
	}
	
	// select taking records
	$ob = empty($_GET['ob'])?"tT.id":$_GET['ob'];
	$dir = !empty($_GET['dir'])?$_GET['dir']:"DESC";
	$odir = ($dir=='ASC')?'DESC':'ASC';
	$offset = empty($_GET['offset'])?0:intval($_GET['offset']);
	$limit_sql = empty($_GET['watu_export']) ? "Limit $offset, 10" : "";
	
	// filter / search?
	$filters = $joins = array();	
	$filter_sql = $left_join_sql = $role_join_sql = $group_join_sql = $left_join = "";
	$join_sql="LEFT JOIN {$wpdb->users} tU ON tU.ID=tT.user_id";
	
	// display name
	if(!empty($_GET['dn'])) {
		switch($_GET['dnf']) {
			case 'contains': $like="%$_GET[dn]%"; break;
			case 'starts': $like="$_GET[dn]%"; break;
			case 'ends': $like="%$_GET[dn]"; break;
			case 'equals':
			default: $like=$_GET['dn']; break;			
		}
		
		$joins[]= " display_name LIKE '$like' ";
	}
	
	// email
	if(!empty($_GET['email'])) {
		switch($_GET['emailf']) {
			case 'contains': $like="%$_GET[email]%"; break;
			case 'starts': $like="$_GET[email]%"; break;
			case 'ends': $like="%$_GET[email]"; break;
			case 'equals':
			default: $like=$_GET['email']; break;			
		}
		
		$joins[]=$wpdb->prepare(" user_email LIKE %s ", $like);
		$filters[]=$wpdb->prepare(" ((user_id=0 AND email LIKE %s) OR (user_id!=0 AND user_email LIKE %s)) ", $like, $like);
		$left_join = 'LEFT'; // when email is selected, do left join because it might be without logged user
	}
	
	// IP
	if(!empty($_GET['ip'])) {
		switch($_GET['ipf']) {
			case 'contains': $like="%$_GET[ip]%"; break;
			case 'starts': $like="$_GET[ip]%"; break;
			case 'ends': $like="%$_GET[ip]"; break;
			case 'equals':
			default: $like=$_GET['ip']; break;			
		}
		
		$filters[]=$wpdb->prepare(" ip LIKE %s ", $like);
	}
	
	// Date
	if(!empty($_GET['date'])) {
		switch($_GET['datef']) {
			case 'after': $filters[]=$wpdb->prepare(" date>%s ", $_GET['date']); break;
			case 'before': $filters[]=$wpdb->prepare(" date<%s ", $_GET['date']); break;
			case 'equals':
			default: $filters[]=$wpdb->prepare(" date=%s ", $_GET['date']); break;
		}
	}
	
	// Points
	if(!empty($_GET['points'])) {
		switch($_GET['pointsf']) {
			case 'less': $filters[]=$wpdb->prepare(" points<%d ", $_GET['points']); break;
			case 'more': $filters[]=$wpdb->prepare(" points>%d ", $_GET['points']); break;
			case 'equals':
			default: $filters[]=$wpdb->prepare(" points=%d ", $_GET['points']); break;
		}
	}
	
	// grade
	if(!empty($_GET['grade_id'])) {
		$filters[]=$wpdb->prepare(" grade_id=%d ", $_GET['grade_id']);
	}
		
	// construct filter & join SQLs
	if(sizeof($filters)) {
		$filter_sql=" AND ".implode(" AND ", $filters);
	}
	
	if(sizeof($joins)) {
		$join_sql=" $left_join JOIN {$wpdb->users} tU ON tU.ID=tT.user_id AND "
			.implode(" AND ", $joins);
	}
	
	$takings = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tT.*, tU.user_login as user_login 
		FROM ".WATU_TAKINGS." tT $join_sql
		WHERE exam_id={$exam->ID} $filter_sql 
		ORDER BY $ob $dir $limit_sql");
			
	$count=$wpdb->get_var("SELECT FOUND_ROWS()");	
		
	// export CSV
	if(!empty($_GET['watu_export'])) {
		$newline=watu_define_newline();		
		
		$rows=array();
		$rows[]=__("User or IP;Date;Points;Result/Grade", 'watu');
		foreach($takings as $taking) {
			$row = ($taking->user_id ? $taking->user_login : $taking->ip).";".date(get_option('date_format'), strtotime($taking->date)).";".
				$taking->points.";".$taking->result;
			$rows[] = $row;		
		} // end foreach taking
		$csv=implode($newline,$rows);		
		
		$now = gmdate('D, d M Y H:i:s') . ' GMT';	
		$filename = 'exam-'.$exam->ID.'-results.csv';	
		header('Content-Type: ' . watu_get_mime_type());
		header('Expires: ' . $now);
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Pragma: no-cache');
		echo $csv;
		exit;
	}	
	
		// this var will be added to links at the view
	$filters_url="dn=".@$_GET['dn']."&dnf=".@$_GET['dnf']."&email=".@$_GET['email']."&emailf=".
		@$_GET['emailf']."&ip=".@$_GET['ip']."&ipf=".@$_GET['ipf']."&date=".@$_GET['date'].
		"&datef=".@$_GET['datef']."&points=".@$_GET['points']."&pointsf=".@$_GET['pointsf'].
		"&grade_id=".@$_GET['grade_id'];			
		
	$display_filters=(!sizeof($filters) and !sizeof($joins)) ? false : true;	
	
	wp_enqueue_script('thickbox',null,array('jquery'));
	wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	if(@file_exists(get_stylesheet_directory().'/watu/takings.php')) include get_stylesheet_directory().'/watu/takings.php';
	else include(WATU_PATH . '/views/takings.php');
}

// display taking details by ajax
function watu_taking_details() {
	global $wpdb, $user_ID;
	
	// select taking
	$taking=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATU_TAKINGS."
			WHERE id=%d", $_REQUEST['id']));
			
	// select user
	$student=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} 
		WHERE id=%d", $taking->user_id));

	// make sure I'm admin or that's me
	if(!current_user_can('manage_options') and $student->ID!=$user_ID) {
		wp_die( __('You do not have sufficient permissions to access this page', 'watu') );
	}
			
	// select exam
	$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATU_EXAMS." WHERE id=%d", $taking->exam_id));
				
	if(@file_exists(get_stylesheet_directory().'/watu/taking_details.html.php')) include get_stylesheet_directory().'/watu/taking_details.html.php';
	else include(WATU_PATH . '/views/taking_details.html.php');  			
	exit;			
}

// shortcode for showing the basic barchart included in the core WatuPRO
// call this ONLY in the Final Screen of the quiz
function watu_basic_chart($atts) {
	$taking_id = $GLOBALS['watu_taking_id'];
	$content = watu_barchart($taking_id, $atts);
	return $content;
}

// basic barchart your points vs avg points, your % vs avg %
// this chart will be loaded by variable or shortcode in the Final screen 
// this function uses globals so it will work properly only when called on controllers/show_exam.php or a shortcode on the Final screen
function watu_barchart($taking_id, $atts) {
	global $wpdb, $achieved;
	
	// normalize params
	$show = empty($atts['show']) ? 'both' : $atts['show'];
	if(!in_array($show, array('both', 'points', 'percent'))) $show = 'both';
	$your_color = empty($atts['your_color']) ? "blue" : $atts['your_color'];
	$avg_color = empty($atts['avg_color']) ? "gray" : $atts['avg_color'];
	$your_percent_text = empty($atts['your_percent_text']) ? __('You: %d%% correct', 'watu') : $atts['your_percent_text'];
	$avg_percent_text = empty($atts['avg_percent_text']) ? __('Avg. %d%% correct', 'watu') : $atts['your_percent_text'];
	$your_points_text = empty($atts['your_points_text']) ? __('Your points: %s', 'watu') : $atts['your_points_text'];
	$avg_points_text = empty($atts['avg_points_text']) ? __('Avg. points: %s', 'watu') : $atts['avg_points_text'];
	$step = 2;
	
	// select taking
	$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATU_TAKINGS." WHERE ID=%d", $taking_id));
	
	// get average points
	$all_point_rows = $wpdb->get_results($wpdb->prepare("SELECT points FROM ".WATU_TAKINGS." WHERE exam_id=%d", $taking->exam_id));
	$all_points = 0;
	foreach($all_point_rows as $r) $all_points += $r->points;	
	$all_points += $achieved;			
	$avg_points = round($all_points / ($wpdb->num_rows + 1), 1);
		
	// the points step should rougly make the higher points bar 200px high
	$more_points = ($avg_points > $taking->points) ? $avg_points : $taking->points;
	if(!$more_points) $more_points = 1; // set to non-zero for division
	$points_step = round(200 / $more_points, 2);
	
	// create & return the chart HTML
	$content = '<table class="watu-basic-chart"><tr>';
	
	if($show == 'points' or $show == 'both') {
		$your_points_text = sprintf($your_points_text, $taking->points);
		$avg_points_text = sprintf($avg_points_text, $avg_points);				
		
		// normalize points here, shouldn't be less than zero when calculating the bar height
		if($taking->points < 0) $taking->points = 0;		
		
		$content .= '<td style="vertical-align:bottom;"><table class="watu-basic-chart-points"><tr><td align="center" style="vertical-align:bottom;">';
		$content .= '<div style="background-color:'.$your_color.';width:100px;height:'.round($points_step * $taking->points). 'px;">&nbsp;</div>'; 
		$content .='</td><td align="center" style="vertical-align:bottom;">';
		$content .= '<div style="background-color:'.$avg_color.';width:100px;height:'.round($points_step * $avg_points). 'px;">&nbsp;</div>';
		$content .='</td></tr><tr><td align="center">' . $your_points_text . '</td><td align="center">'. $avg_points_text .'</td></tr>';
		$content .= '</table></td>';			
	}
	$content .= '</tr></table>';
	
	return $content;
}