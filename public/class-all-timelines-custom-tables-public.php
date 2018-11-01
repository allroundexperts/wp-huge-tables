<?php

class All_Timelines_Custom_Tables_Public {
    
    protected $name;
    protected $version;

    public function __construct() {
        
        if (defined('ATCTP_VERSION') && defined('ATCTP_NAME')) {
            $this->version = ATCTP_VERSION;
            $this->name = ATCTP_NAME;
		}else{
            $this->version = '1.0.0';
            $this->name = 'all-timelines-custom-tables';
		}
        add_action('wp_footer',array($this,'enqueue_scripts'));
        add_action('wp_footer',array($this,'enqueue_styles'));
    }

    public function enqueue_styles() {
        $css = file_get_contents(plugin_dir_url( __FILE__ ) . 'styles/style.css');
        $css .= file_get_contents(plugin_dir_url( __FILE__ ) . 'styles/slimselect.min.css');
        wp_register_style( 'ATCTP-styles', false );
        wp_enqueue_style( 'ATCTP-styles' );
        wp_add_inline_style( 'ATCTP-styles', $css );    
    }

	public function enqueue_scripts() {
        wp_enqueue_script( $this->name.'1', plugin_dir_url( __FILE__ ) . 'js/slimselect.min.js', array(), $this->version, true );
		wp_enqueue_script( $this->name.'2', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), $this->version, true );
    }

}