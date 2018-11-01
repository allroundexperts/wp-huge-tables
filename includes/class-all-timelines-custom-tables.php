<?php
class All_Timelines_Custom_Tables {

    public function __construct() {
        $this->load_dependencies();
    }

    public function load_dependencies() {
        require_once plugin_dir_path( __FILE__ ) . 'class-all-timelines-custom-tables-shortcode.php';
    }

    public function run() {
        $shortcode = new All_Timelines_Custom_Tables_Shortcode;
        add_shortcode('all-timelines-custom-tables',array($shortcode,'bootstrap'));
    }
}