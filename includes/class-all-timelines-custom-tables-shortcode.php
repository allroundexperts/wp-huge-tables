<?php
class All_Timelines_Custom_Tables_Shortcode {

    protected $attributes;

    public function __construct() {
        $this->attributes = array(
                            'taxonomy' => 'timeline',
                            'term' => 'star-wars-canon',
                            'post_type' => 'timeline_items', 
                            'columns' => 'cf:chronologicalorder|#,cf:in-universe_date|Time,cf:amazon_link|Buy,title|Title,tax:media__type|Type,tax:writer|Author,cf:date_published|Released', 
                            'filter_by' => 'media__type', 
                            'per_page' => 25, 
                            'order' => 'asc', 
                            'sort' => 'chronologicalorder',
                            'priorities' => '1,2,3,4,5,1,2',
                        );
    }

    public function load_dependencies() {
        require plugin_dir_path( __FILE__ ).'class-all-timelines-custom-tables-shortcode-hooks.php';
        require plugin_dir_path( __DIR__ ).'public/class-all-timelines-custom-tables-public.php';
        new All_Timelines_Custom_Tables_Public();
        $content = new All_Timelines_Custom_Tables_Shortcode_Hooks($this->attributes);
        $table = $content->renderTable();
        return $table;
    }

    public function bootstrap($attr) {
        $this->attributes =  shortcode_atts($this->attributes, $attr, ATCTP_NAME);
        $this->attributes['priorities'] = explode(',', $this->attributes['priorities']);
        return $this->load_dependencies();
    }

    
}