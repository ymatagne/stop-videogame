<?php
add_action('wp_enqueue_scripts', 'wl_removeScripts' , 20);
function wl_removeScripts() {
//De-Queuing Styles sheet 
wp_dequeue_style( 'default',get_template_directory_uri() .'/css/default.css'); 
//EN-Queing Style sheet 
wp_enqueue_style('wlorange', get_stylesheet_directory_uri() . '/wl-orange.css');
}?>
<?php
function wl_add_editor_styles() {
    add_editor_style( 'custom-editor-style.css' );
}
add_action( 'after_setup_theme', 'wl_add_editor_styles' );
?>