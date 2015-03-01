<?php
/**
 * Plugin Name: Hotdeal - Catch the Tags
 * Plugin URI:  http://blog.hotdeal.vn/
 * Description: Retrieve the tags from main website
 * Version:     1.0.0
 * Author:      ...
 * Author URI:  ...
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || exit;

define( 'CTTs_URL', plugins_url( __FILE__ ) );
define( 'CTTs_DIR', plugin_dir_path( __FILE__ ) );

add_action('admin_menu', 'add_tools_menu');

function add_tools_menu(){
    add_submenu_page( 'tools.php', __( 'Catch the Tags Page', 'hotdealblog' ), __( 'Catch the Tags', 'hotdealblog' ), 'manage_options', 'catch-the-tags', 'callback_catch_the_tag');
}

function callback_catch_the_tag( ) {
    ?>
    <div class="wrap">
        <h2><?php echo __( 'Catch the Tags', 'hotdealblog' ); ?></h2>
        <?php
        echo "<pre>";
            print_r(get_terms(array('post_tag'), array('hide_empty' => false)));
        echo "</pre>";
        ?>
        <form method="post" action="" novalidate="novalidate">
            <input type="hidden" name="option_page" value="catch-the-tags">
            <input type="hidden" name="action" value="update">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="ctt-url"><?php echo __( 'The Url to Retrieve Tags', 'hotdealblog' ); ?></label></th>
                    <td>
                        <input name="ctt-url" type="text" id="ctt-url" value="<?php echo ( !empty($_POST['ctt-url'] ) ) ? $_POST['ctt-url'] : ''; ?>" class="regular-text" placeholder="http://domain.com/get-tags">
                        <p class="description"><?php echo __( 'The url address to retrieve tags', 'hotdealblog' ); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Get Tags">
            </p>
        </form>
    <div class="result_retrieve">
        <ul>
        <?php
        if( isset($_POST['submit']) && $_POST['submit']='Get Tags' ) {
            if( !empty($_POST['ctt-url']) ) {
                $str_json = wp_remote_get($_POST['ctt-url']);
                $arr_obj_tags = json_decode($str_json['body']);

                foreach( $arr_obj_tags as $obj_tag ) {
                    $flag_exists = term_exists( $obj_tag->slug, 'post_tag' );
                    if( isset($flag_exists) && !empty($flag_exists) ) {
                        $notice = $obj_tag->name . '::'. $obj_tag->slug . ' is exists';
                    }else{
                        $flag_insert = wp_insert_term(
                            $obj_tag->name,
                            'post_tag',
                            array(
                                'slug'          => $obj_tag->slug,
                                'description'   => $obj_tag->description,
                            )
                        );

                        if( !empty($flag_insert) ) {
                            $notice = $obj_tag->name . '::'. $obj_tag->slug . ' is inserted successful';
                        }else{
                            $notice = $obj_tag->name . '::'. $obj_tag->slug . ' is inserted fails';
                        }
                    }
                    echo "<li>" . $notice . "</li>";
                }
            }
        }
        ?>
            </ul>
        </div>
    </div>
    <style type="text/css">
        .result_retrieve {
            float: left;
        }

        .result_retrieve ul {
            display: block;
            max-height: 450px;
            overflow: scroll;
        }

        .result_retrieve ul li {
            opacity: 1.5;
            -webkit-transition: all 1s ease;
            -moz-transition: all 1s ease;
            -ms-transition: all 1s ease;
            -o-transition: all 1s ease;
            transition: all 1s ease;
        }
    </style>
<?php
}

add_filter( 'wp_insert_post_data' , 'modify_post_title' , '99', 2 );

function modify_post_title( $data , $postarr ){

    $tag_terms = get_terms(array('post_tag'), array('hide_empty' => false));

    $content = $data['post_content'];

    foreach ($tag_terms as $term) {
        $content = str_replace( trim($term->name), '<a href="' . $term->description . '" title="' . $term->name . '" target="_blank">'. $term->name .'</a>',  $content );
    }

    $data['post_content'] = $content;


    return $data;
}

