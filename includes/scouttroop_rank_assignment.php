<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class scouttroop_rank_List_Table extends WP_List_Table {

    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'scout',     //singular name of the listed records
            'plural'    => 'scouts',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default($item, $column_name){
        switch($column_name){
        	case 'ID':
			case 'display_name':
            case 'meta_value':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['display_name'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'display_name'     => 'User Name',
            'meta_value'	=> 'Rank'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'display_name'     => array('display_name',true),
            'meta_value'		=> array('meta_value', false)     //true means it's already sorted
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
    	$ptn_scouttroop_ranks = array("Scout","Tenderfoot","Second Class", "First Class", "Star", "Life", "Eagle");
    	foreach ($ptn_scouttroop_ranks as $ptn_scouttroop_rank){
			$actions[$ptn_scouttroop_rank] = $ptn_scouttroop_rank;
		}
        return $actions;
    }

    function process_bulk_action() {       
        if(is_array($_GET["scout"])){
            foreach($_GET["scout"] as $the_scout){
        	$new_rank = $this->current_action();
        	update_user_meta($the_scout, 'rank', $new_rank);
        }
        }
    }

    function prepare_items() {
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 25;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();  
             
        $data = get_users( array('role'=>'Scout', 'fields' => array('ID', 'display_name'), 'orderby' => 'display_name'));			       
        $data = json_decode(json_encode($data), true);
        $i=0;
        foreach ($data as $single_user){
        	$data[$i++]['meta_value'] = get_user_meta($single_user['ID'],'rank', true);         	
        }		          
 
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
//        usort($data, 'usort_reorder');

        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}

function scouttroop_render_rank_page(){
    //Create an instance of our package class...
    $RankTable= new scouttroop_rank_List_Table ();
    //Fetch, prepare, sort, and filter our data...
    $RankTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Rank Table</h2>
        
        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
			<p>Use this page to assign ranks.</p>
        </div>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $RankTable->display() ?>
        </form>
        
    </div>
    <?php
}
?>