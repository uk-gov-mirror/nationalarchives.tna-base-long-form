<?php
// Edit as required
function tnatheme_globals() {
    global $pre_path;
    global $pre_crumbs;
    $headers = apache_request_headers();
    if ( isset($_SERVER['HTTP_X_NGINX_PROXY']) && isset($headers['X-Host-Type']) && $headers['X-Host-Type'] == 'public' ) {
        $pre_crumbs = array(
            'First World War' => '/first-world-war/',
            'Fighting talk: First World War telecommunications' => '/first-world-war/telecommunications-in-war/'
        );
        $pre_path = '';
    } elseif (substr($_SERVER['REMOTE_ADDR'], 0, 3) === '10.') {
        $pre_path = '';
        $pre_crumbs = array(
            'Fighting talk: First World War telecommunications' => '/first-world-war/telecommunications-in-war/'
        );
    } else {
        $pre_crumbs = array(
            'First World War' => '/first-world-war/',
            'Fighting talk: First World War telecommunications' => '/first-world-war/telecommunications-in-war/'
        );
        $pre_path = '';
    }
}
// For live environment
tnatheme_globals();
function dequeue_parent_style() {
    wp_dequeue_style('tna-styles');
    wp_deregister_style('tna-styles');
}
add_action( 'wp_enqueue_scripts', 'dequeue_parent_style', 9999 );
add_action( 'wp_head', 'dequeue_parent_style', 9999 );


// Enqueue styles & scripts
function tna_child_styles() {
    wp_register_style( 'tna-parent-styles', get_template_directory_uri() . '/css/base-sass.min.css', array(), EDD_VERSION, 'all' );
    wp_register_style( 'tna-child-styles', get_stylesheet_directory_uri() . '/css/style.css', array(), '0.1', 'all' );
    wp_deregister_script('jquery');
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js', '1.6.1', true);

    wp_register_script('long-form', get_stylesheet_directory_uri(). '/js/long-form.js','','1.1', true);
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'tna-parent-styles' );
    wp_enqueue_style( 'tna-child-styles' );

    wp_enqueue_script( 'long-form' );
}
add_action( 'wp_enqueue_scripts', 'tna_child_styles' );

// Hooks your functions into the correct filters
function image_align() {
    // check user permissions
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return;
    }
    // check if WYSIWYG is enabled
    if ( 'true' == get_user_option( 'rich_editing' ) ) {
        add_filter( 'mce_external_plugins', 'my_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'my_register_mce_button' );
    }
}


function remove_width_attribute( $html ) {
        $html = preg_replace( '/(width|height)=("|\')\d*(|px)("|\')\s/', "", $html );
        return $html;
}
add_filter( 'the_content', 'remove_width_attribute', 10 );


/* Change the name of posts */
function post_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'Long Form Sections';
    $submenu['edit.php'][5][0] = 'Sections';
    $submenu['edit.php'][10][0] = 'Add Sections';
    $submenu['edit.php'][16][0] = 'Section Tags';
    echo '';
}
add_action( 'admin_menu', 'post_label' );
/* Adding Menu Order to Posts*/
function menu_order()
{
    add_post_type_support( 'post', 'page-attributes' );
}
add_action( 'admin_init', 'menu_order' );
/* END Adding Menu Order to Posts*/


function img_responsive($content){
    return str_replace('<img class="','<img class="img-responsive ',$content);
}
add_filter('the_content','img_responsive');
/* END Adds a class to every image element */


function sub_heading_get_meta( $value ) {
    global $post;

    $field = get_post_meta( $post->ID, $value, true );
    if ( ! empty( $field ) ) {
        return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
    } else {
        return false;
    }
}

function sub_heading_add_meta_box() {
    add_meta_box(
        'sub_heading-sub-heading',
        __( 'Text field before footer', 'sub_heading' ),
        'sub_heading_html',
        'page',
        'normal',
        'core'
    );
}
add_action( 'add_meta_boxes', 'sub_heading_add_meta_box' );


function sub_heading_html( $post) {
    wp_nonce_field( '_sub_heading_nonce', 'sub_heading_nonce' ); ?>

    <p>
    <input class="widefat" type="text" name="sub_heading_sub_heading" id="sub_heading_sub_heading" value="<?php echo sub_heading_get_meta( 'sub_heading_sub_heading' ); ?>">
    </p>
    <?php
}
function sub_heading_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['sub_heading_nonce'] ) || ! wp_verify_nonce( $_POST['sub_heading_nonce'], '_sub_heading_nonce' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['sub_heading_sub_heading'] ) )
        update_post_meta( $post_id, 'sub_heading_sub_heading', esc_attr( $_POST['sub_heading_sub_heading'] ) );
}
add_action( 'save_post', 'sub_heading_save' );

