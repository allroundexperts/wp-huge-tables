<?php

class All_Timelines_Custom_Tables_Shortcode_Hooks {

    protected $attributes;
    protected $query;
    protected $columns;
    protected $images;

    public function __construct($attributes) {
        $this->attributes = $attributes;
        $this->columns = $this->format_columns($attributes['columns']);
        $this->images = [];
        $this->setQuery();
    }

    public function setQuery(){

        $filter_by = $this->attributes['filter_by'];

        $post_type = $this->attributes['post_type'];

        $currentterm = get_term_by( 'slug', $this->attributes['term'], $this->attributes['taxonomy'] );

        $term = $currentterm->term_id;

        $filter_term = isset($_GET[$filter_by]) && count($_GET[$filter_by]) > 0 ? $_GET[$filter_by] : false;

        $sort = isset($_GET['sort']) ? $_GET['sort'] : $this->attributes['sort'];
        $order = isset($_GET['order']) ? $_GET['order'] : $this->attributes['order'];

        $per_page = isset($_GET['per_page']) ? $_GET['per_page'] : $this->attributes['per_page'];

        $tax_query = array(array('taxonomy' => 'timeline', 'field' => 'term_id', 'terms' => $term ));
        
        if($filter_term !== false) {
            $tax_query[] =  array('taxonomy' => $filter_by, 'field' => 'term_id', 'terms' => $filter_term);
            $tax_query['relation'] = '\AND';
        }
        
        $args = array('post_type' => $post_type, 'order' => $order, 'posts_per_page' => $per_page, 'paged' => (get_query_var('paged')) ? get_query_var('paged'): 1, 'tax_query' => $tax_query);
        $position = array_search($sort,array_column($this->columns,'name'));

        if($position !== false){
            $col = $this->columns[$position];
            switch($col['type']){
                case 'cf':
                    $args['orderby'] = $sort === 'chronologicalorder' ? 'meta_value_num': 'meta_value'; 
                    $args['meta_key'] = $col['name'];
                    break;
                case 'default':
                    $args['orderby'] = $col['name'];
                    break;
            }
        }
		
        $this->query = new WP_Query($args);
    }

    public function renderTableRows(){
		$tr = '';
        while ( $this->query->have_posts() ) : $this->query->the_post();
            $td = '';
            $count = count($this->columns);
            if($count > 0){
                $id = "atctp-".get_the_ID();
                foreach($this->columns as $key => $value){
                    $title = $this->get_value($value);
                    if($value['name'] === 'title'){
                        $image_link = get_the_post_thumbnail_url('','large');
                        $this->images[] = ['id' => get_the_ID(), 'link' => $image_link];
                        $title = "<div class='atctp-title'><a href='".get_permalink()."'>".$title."</a> <span onclick=atctp_show_details('$id','$image_link')>+</span></div>";
                    }
                    $td .= '<td class="col-'.strtolower($value['name']).' priority-'.$this->attributes['priorities'][$key].'">'.$title.'</td>';
                }
            }
            $term = end(get_the_terms(get_the_ID(), $this->attributes['filter_by']));
            $tr .= "<tr class='post-type-timeline_item row-highlight-{$term->slug}'>".$td."</tr>";
            $tr .= "<tr id='{$id}' class='atctp-collapsible-row' style='display:none;'><td colspan='{$count}'><div class='atctp-collapsible'><span class='atctp-collapsible-image'></span>".get_field('summary')."</div></td></tr>";
            
        endwhile;
        return $tr;     
    }

    public function renderTable(){
        $header = $this->renderTableHeader();
        $body = $this->renderTableRows();
        $pagination = $this->paginate();
        $filters = $this->filters();
        wp_reset_query();
        return "{$filters}<br/><table class='atctp-table'><thead>{$header}</thead><tbody>{$body}</table>{$pagination}";

    }

    public function renderTableHeader(){
        $th = '';

        if(count($this->columns) > 0){
            foreach($this->columns as $key => $value){
                $sort = isset($_GET['sort']) ? $_GET['sort'] : $this->attributes['sort'];
                $order = isset($_GET['order']) ? $_GET['order'] : $this->attributes['order'];
                if($value['type'] === 'cf' || $value['type'] === 'default'){
                    $args = $_GET;
                    $args['sort'] = $value['name'];
                    if($value['name'] === $sort){
                        $args['order'] = $order === 'asc' ? 'desc': 'asc';
                        $class = 'atctp-order-'.$order;
                    }else{
                        $args['order'] = 'desc';
                        $class = 'atctp-order-both';
                    }
                    $args['paged'] = 1;
                    $title = '<a class="atctp-link" href="'.add_query_arg($args).'">'.$value['alias'].'</a>';
                }else{
                    $class = '';
                    $title = $value['alias'];
                }

                $th .= '<th class="col-'.$value['name'].' '.$class.' sorting priority-'.$this->attributes['priorities'][$key].'">'.$title.'</th>';
            }
        }

        return '<tr>'.$th.'</tr>';
    }

    public function paginate() {
        $big = 9999999;
        $url = add_query_arg($_GET);
        $exploded = explode('?',$url);
        $format = count($exploded) > 1 ? '&paged=%#%': '?paged=%#%';
        return "<div class='atctp-pagination'>".paginate_links( array(
            'base' => str_replace( $big, '%#%', get_pagenum_link( $big, false )  ),
            'format' => $format,
            'current' => max( 1, get_query_var('paged') ),
            'total' => $this->query->max_num_pages
        ))."</div>";
    }

    public function format_columns($raw_columns){
        $data = [];
        $columns = explode(',',$raw_columns);
        if(count($columns) > 0){
            foreach($columns as $key => $value){
                $value = strpos($value,':') ? $value: 'default:'.$value;
                $value = strpos($value,'|') ? $value: $value.'|'.end(explode(':',$value));
                $values = preg_split('/[:,|]/', $value);
                $data[] = ['type' => $values[0], 'name' => $values[1], 'alias' => $values[2]];
            }
        }
        
        return $data;          
    }

    public function get_value($value) {
        $post = get_post();
        switch($value['type']){
            case 'cf':
                return apply_filters('the_content', get_field($value['name']));
                break;
            case 'tax':
                $terms = get_the_terms($post->ID, $value['name']);
                return apply_filters('the_content', $this->generate_links($terms));
                break;
            default:
                return apply_filters('the_content',$post->{'post_'.$value['name']});
        }
    }

    public function generate_links($terms){
        $links = '';
        if(count($terms) > 0) {
            foreach($terms as $key => $term) {
                $links .= "<a href='".get_term_link($term)."'>".$term->name."</a><br/>";
            }
        }
        return $links;
    }

    public function filters(){

        $filter_by = $this->attributes['filter_by'];
        $currentterm = get_term_by( 'slug', $this->attributes['term'], $this->attributes['taxonomy'] );
        $term = $currentterm->term_id;
		$args = array('post_type' => 'timeline_items', 'posts_per_page' => -1, 'tax_query' => array( array('taxonomy' => 'timeline', 'field' => 'term_id', 'terms' => $term )));
        $query = new WP_Query($args);
		$posts = wp_list_pluck($query->posts,'ID');
        $terms = wp_get_object_terms($posts, $filter_by);
        if(count($terms) > 0){
            $options = "<option value=''>All</option>";
            foreach($terms as $key => $term){
                $selected = isset($_GET[$filter_by]) && in_array($term->term_id,$_GET[$filter_by]) ? "selected": null;
                $options .= "<option {$selected} value='{$term->term_id}'>{$term->name}</option>";
            }
        }
        $inputs = '';
        $sort_options = '';
        foreach($this->columns as $key => $column){
            if($column['type'] === 'default' || $column['type'] === 'cf'){
                $selected = isset($_GET['sort']) && $_GET['sort'] === $column['name'] ? "selected": null;
                $sort_options .= '<option '.$selected.' value="'.$column['name'].'">'.$column['alias'].'</option>';
            }
        }

        $page_options = [25,50,75,100];
        $per_page_options = '';
        foreach($page_options as $key => $page){
            $selected = isset($_GET['per_page']) && (int)$_GET['per_page'] === $page ? "selected": null;
            $per_page_options .= '<option '.$selected.' value="'.$page.'">'.$page.'</option>';
        }

        $filter_name = $filter_by."[]";
        $images = json_encode($this->images);

        return "
                <form class='atctp-form' action='' method='GET'>
                    <div class='atctp-formgroup'>
                        <label for='slim-select'>Filter: </label> <select id='slim-select' multiple name={$filter_name}>{$options}</select>
                    </div>
                    <div class='atctp-formgroup'>
                        <label for='slim-sort'>Sort: </label> <select id='slim-sort' name='sort'>".$sort_options."</select>
                    </div>
                    <div class='atctp-formgroup'>                    
                        <label for='slim-pagination'>Show </label> <select id='slim-pagination' name='per_page'>".$per_page_options."</select> entries
                    </div>
                    <div class='atctp-formgroup'>                    
                        <input class='atctp-button' type='submit' value='Apply'/>
                    </div>
                    <div class='atctp-formgroup'>
                        <input id='atctp-toggle' class='atctp-button' type='button' onclick=atctp_show_all('{$images}') value='Expand All'/>
                    </div>
                    <input type='hidden' name='paged' value='1'/>
                </form>
                ";

    }
    
}