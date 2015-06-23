<?php
/* Shortcodes */
function patrol_list_func($arg){
	$patrol_list = ptn_scouttroop_get_patrol_list();
	$return_string .= '<ul class="ptn_scouttroop_patrol_list">';
	foreach($patrol_list as $patrol){
		if ($arg == 'ARRAY'){
			$return_array[] = $patrol;
		}else{
			$return_string .= '<li class="ptn_scouttroop_patrol">'.$patrol.'</li>';
		}
	}
	if ($arg != 'ARRAY'){
		$return_string .= '</ul>';
		return $return_string;
	}
	return $return_array;
}
add_shortcode('patrol_list', 'patrol_list_func');

function scouttroop_name_by_rank(){	
	 $data = get_users( array('role'=>'Scout', 'fields' => array('ID', 'display_name'), 'order_by' => 'display_name'));			       
     $data = json_decode(json_encode($data), true);
     $i=0;
     $k=0;
     foreach ($data as $single_user){
     	$data[$i++]['meta_value'] = get_user_meta($single_user['ID'],'rank', true);         	
     	$rank = get_user_meta($single_user['ID'],'rank', true);         	
     	$rank_name_list[$rank][]= array($single_user['display_name'], $single_user['ID']);
	 }		       

	$rank_table_ui = '<table class="ptn_scouttroop_rank_table"><tr>';

	$ranks_order = array("","Scout", "Tenderfoot", "Second Class", "First Class", "Star", "Life", "Eagle");
	$max_i = 0;
	foreach ($ranks_order as $rank){
		// build header row here
		//$rank_table_ui .= '<th class="ptn_scouttroop_rank_table_rank" >'.$rank.'</th>';
		$rank_img = plugins_url('scouttroop').'/assets\/'.strtolower(str_replace(' ','_',$rank)).'-small.png';
		$rank_table_ui .= '<th class="ptn_scouttroop_rank_table_rank" >';
		if (!empty($rank)){
			$rank_table_ui .= '<img src='.$rank_img.' /></th>';
		}else{
			$rank_table_ui .= '</th>';
		}
		
		
		$i = 0;
		if (is_array($rank_name_list[$rank])){
			foreach ($rank_name_list[$rank] as $name_rank){
				$rank_table[$rank][$i++] = $name_rank['display_name'];
				if ($max_i < $i){
					$max_i = $i;
				}
			}
		}
	} 
	$rank_table_ui .= '</tr>';
	
	for ($i=0; $i<$max_i; $i++){
		foreach ($ranks_order as $rank){
			if ($rank == ''){
				$rank_table_ui .= '<tr>';
			}
			// build detail rows here
			if (! empty($rank_name_list[$rank][$i])){
				if (is_user_logged_in()){
					$rank_table_ui .= '<td class="ptn_scouttroop_rank_table_name" >'.$rank_name_list[$rank][$i][0].'</td>';
				}else{
					$rank_table_ui .= '<td class="ptn_scouttroop_rank_table_name" >'.first_last_init($rank_name_list[$rank][$i][1]).'</td>';
				}
			}else{
				$rank_table_ui .= '<td></td>';
			}
			if($rank == 'Eagle'){
				$rank_table_ui .= '</tr>';
			}
		}
	}
	$rank_table_ui .= '</table>';
	return $rank_table_ui;
}
add_shortcode( 'scoutbyrank', 'scouttroop_name_by_rank');

function scouttroop_patrol_directory(){
	$patrol = $_SERVER['QUERY_STRING']; 
	if (wp_is_mobile()){ 
		$img_size = 'mobile' ;
	} else { 
		$img_size = 'small' ;
	} 
	$table_hdr = '<table class="ptn_scouttroop_patrol_table">';	
 ?>
 <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
 	<header class="entry-header">
 		<?php $the_patrol = $_SERVER['QUERY_STRING']; ?>
 		<h2><?php echo $the_patrol; ?></h2>
 	</header><!-- .entry-header -->
 	<div class="entry-content">
   
     <?php 
     	echo $table_hdr;
	 // Assumption here is that patrol are only for role of Scout;  Therefore no adults 'should' appear on this list and are not specifically excluded.	 	
     	$query = array('meta_key' => 'patrol', 'meta_value' => $patrol);
     	$user_query = new WP_User_Query($query);
    
     	if (is_array($user_query->results)){
     		foreach($user_query->results as $scout){ ?>
  			<tr>
  				<?php if(empty($scout->rank)){
  						echo '<td></td>'; 
  					  }else{
						$rank=plugins_url('scouttroop').'/assets\/'.strtolower(str_replace(' ','_',$scout->rank)).'-'.$img_size.'.png'; ?>
					<td class="ptn_scouttroop_patrol_table_rank_img" ><img class="directory-rank" src= <?php echo $rank;?> /></td>
  				<?php } ?>
  			
  				<td class="ptn_scouttroop_patrol_table_name" ><?php if (is_user_logged_in()){
  						print $scout->user_firstname.' '.$scout->user_lastname; 
  					}else{
  						print $scout->user_firstname.' '.$scout->user_lastname[0];
  					}
  				?></td>
  				<td class="ptn_scouttroop_patrol_table_phone" ><?php if ( !empty($scout->phone)){echo antispambot(format_telephone($scout->phone)); }?></td>  				
				<?php $scout_leadership = get_user_meta($scout->ID, 'leadership');?>
  				<td class="ptn_scouttroop_patrol_table_leadership" >
  				<?php if(is_array($scout->leadership)){foreach($scout->leadership as $role){echo $role.', ';}}?></td>
  			</tr>
  	<?php }} ?>
  	</table>

 	</div><!-- .entry-content -->

    	<!--footer class="entry-meta">--><?php	
}
add_shortcode( 'patroldirectory', 'scouttroop_patrol_directory');

function scouttroop_committee_directory(){
	// Not my best work - but functional for v1
 ?>
 <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
 	<div class="entry-content">
     <table class="ptn_scouttroop_committee_table_hdr" >
    
     <?php 
  		$query = array('meta_key' => 'adult', 'query_where' => 'meta_value like REGEXP "Committee Chair|Treasurer|Outdoor Chair|Friends of Scouting|New Family Coordinator|Equipment Coordinator"');
  		$user_query = new WP_User_Query($query);	
     	if (is_array($user_query->results)){
     		foreach($user_query->results as $committee){ 
				$roles = get_user_meta($committee->ID, 'adult');
				if (((!in_array("Scoutmaster",$roles[0]))) && (!empty($roles[0])))
				{			
				?>
  			<tr>
  				<td class="ptn_scouttroop_committee_table_name" ><?php if (is_user_logged_in()){
  						print $committee->user_firstname.' '.$committee->user_lastname; 
  					}else{
  						print $committee->user_firstname.' '.$committee->user_lastname[0];
  					}
  				?></td>		
				<?php 
	     					if (!empty($committee->phone)){
	     						$display_phone_number = antispambot(format_telephone($committee->phone));
	     					}else{
	     						$display_phone_number = "";
	     					} 
	     				?>
				<?php $committee_leadership = get_user_meta($committee->ID, 'adult');?>
  				<td class="ptn_scouttroop_committee_table_leadership" ><?php if(is_array($committee_leadership)){foreach($committee_leadership[0] as $role){echo $role.', ';}}?></td>
<td class="ptn_scouttroop_committee_table_phone"><a href="tel:+<?php echo $display_phone_number;?>"><?php echo $display_phone_number; ?></a></td>

				<td  class="ptn_scouttroop_committee_table_email" ><a href="mailto:<?php echo antispambot($committee->user_email); ?>"> <?php echo antispambot( $committee->user_email ); ?></a></td>	
  			</tr>
  	<?php }}} ?>
  	</table>
 	</div><!-- .entry-content -->
    	<!--footer class="entry-meta">--><?php	
}
add_shortcode ('committeedirectory', 'scouttroop_committee_directory');

function scouttroop_scoutmaster_directory(){
	// Not my best work - but functional for v1
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
    	<div class="entry-content">
        <table class="ptn_scouttroop_sm_table" >
    
        <?php 
		$query = array('meta_key' => 'adult');
	 	$user_query = new WP_User_Query($query);
		
        	if (is_array($user_query->results)){
        		foreach($user_query->results as $sm){ 
					$roles = get_user_meta($sm->ID, 'adult');
					if (in_array("Scoutmaster",$roles[0]))
					{
					?>
     			<tr>
     				<td class="ptn_scouttroop_sm_table_name" ><?php if (is_user_logged_in()){
     						print $sm->user_firstname.' '.$sm->user_lastname; 
     					}else{
     						print $sm->user_firstname.' '.$sm->user_lastname[0];
     					}
     				?></td>
					<?php 
		     					if (!empty($sm->phone)){
		     						$display_phone_number = antispambot(format_telephone($sm->phone));
		     					}else{
		     						$display_phone_number = "";
		     					} 
		     				?>
<td class="ptn_scouttroop_sm_table_phone"><a href="tel:+<?php echo $display_phone_number;?>"><?php echo $display_phone_number; ?></a></td>
  				<td  class="ptn_scouttroop_sm_table_email" ><a href="mailto:<?php echo antispambot($sm->user_email); ?>"> <?php echo antispambot( $sm->user_email ); ?></a></td>	
     			</tr>
     	<?php }}} ?>
     	</table>
    	</div><!-- .entry-content -->
    	<!--footer class="entry-meta">--><?php	
}
add_shortcode ('scoutmasterdirectory', 'scouttroop_scoutmaster_directory');

function scouttroop_adult_directory(){
	// Not my best work - but functional for v1
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
    	<div class="entry-content">
        <table class="ptn_scouttroop_adult_table" >
    
        <?php 
		$query = array('role' => 'adult');
	 	$user_query = new WP_User_Query($query);
        	if (is_array($user_query->results)){
        		foreach($user_query->results as $sm){ 
					?>
     			<tr>
     				<td class="ptn_scouttroop_adult_table_name" ><?php if (is_user_logged_in()){
     						print $sm->user_firstname.' '.$sm->user_lastname; 
     					}else{
     						print $sm->user_firstname.' '.$sm->user_lastname[0];
     					}
     				?></td>
     				<?php 
     					if (!empty($sm->phone)){
     						$display_phone_number = antispambot(format_telephone($sm->phone));
     					}else{
     						$display_phone_number = "";
     					} 
     				?>
				<td class="ptn_scouttroop_adult_table_phone"><a href="tel:+<?php echo $display_phone_number;?>"><?php echo $display_phone_number; ?></a></td>
  				<td  class="ptn_scouttroop_adult_table_email" ><a href="mailto:<?php echo antispambot($sm->user_email); ?>"> <?php echo antispambot( $sm->user_email ); ?></a></td>	
     			</tr>
     	<?php }} ?>
     	</table>
    	</div><!-- .entry-content -->
    	<!--footer class="entry-meta">--><?php	
}
add_shortcode ('adultdirectory', 'scouttroop_adult_directory');


function format_telephone($phone_number)
{
    $cleaned = preg_replace('/[^[:digit:]]/', '', $phone_number);
    preg_match('/(\d{3})(\d{3})(\d{4})/', $cleaned, $matches);
    return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
}


?>