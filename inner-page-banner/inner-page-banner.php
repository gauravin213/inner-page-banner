<?php
/*
Plugin Name: Custom Inner Page Banner
Description: This is the InnerPageBanner plugin
Author: Dev
Text Domain: inner-page-banner
*/

//prefix: InnerPageBanner

defined( 'ABSPATH' ) or die();

define( 'InnerPageBanner_VERSION', '1.0.0' );
define( 'InnerPageBanner_URL', plugin_dir_url( __FILE__ ) );
define( 'InnerPageBanner_PATH', plugin_dir_path( __FILE__ ) );

if (!class_exists('InnerPageBanner')) {

  class InnerPageBanner {
    
    function __construct(){  
      
      add_action('init', array($this, 'InnerPageBanner_register_post_type_fun'), 10);

      add_action('add_meta_boxes', array($this, 'TmmLedger_meta_boxes'), 10);

      add_action( 'admin_menu', array($this, 'InnerPageBanner_admin_menu_fun') );

      add_action('save_post', array($this, 'InnerPageBanner_save_post'), 10, 1);

      add_shortcode('InnerPageBannerFrontEndShortcode', array($this, 'InnerPageBannerFrontEndShortcodeFun') );

      add_filter( 'manage_edit-inner_page_banner_columns', array($this, 'InnerPageBannerColumnFun' ) );
      add_action( 'manage_inner_page_banner_posts_custom_column', array($this, 'InnerPageBannerValueFun') , 11);
      
      add_filter( 'posts_join', array($this, 'InnerPageBanner_search_join') );
      add_filter( 'posts_where', array($this, 'InnerPageBanner_search_search_where') );
      add_filter( 'posts_distinct', array($this, 'InnerPageBanner_search_distinct') );

      add_action('admin_enqueue_scripts', array($this, 'InnerPageBanner_admin_enqueue_scripts'), 10, 1);

      add_action( 'wp_ajax_InnerPageBannerSelection_action', array($this, 'InnerPageBannerSelection_action'));
      add_action( 'wp_ajax_nopriv_InnerPageBannerSelection_action', array($this, 'InnerPageBannerSelection_action'));

      add_action( 'wp_ajax_InnerCatBanner_action_select2', array($this, 'InnerCatBanner_action_select2'));
      add_action( 'wp_ajax_nopriv_InnerCatBanner_action_select2', array($this, 'InnerCatBanner_action_select2'));

      add_action( 'wp_ajax_InnerPageBanner_show_selected_pages', array($this, 'InnerPageBanner_show_selected_pages'));
      add_action( 'wp_ajax_nopriv_InnerPageBanner_show_selected_pages', array($this, 'InnerPageBanner_show_selected_pages'));

      add_action('admin_enqueue_scripts', array($this, 'InnerPageBanner_admin_enqueue_scripts_fun'));


    }


    public function InnerPageBanner_admin_enqueue_scripts_fun(){

      wp_enqueue_style('InnerPageBannerAdminStyle', InnerPageBanner_URL.'assets/css/InnerPageBannerAdminStyle.css', array(), '1.0', 'all');
      wp_enqueue_script('InnerPageBannerAdminScript', InnerPageBanner_URL.'assets/js/InnerPageBannerAdminScript.js', array(), '1.0', true);

      $data = array(
          'ajaxurl'=> admin_url( 'admin-ajax.php'),
          'posturl'=> admin_url( 'admin-post.php')
      );
      wp_localize_script( 'InnerPageBannerAdminScript', 'datab', $data );



    }

    public function InnerPageBanner_admin_enqueue_scripts(){

      wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
      wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );

    }


    public function InnerPageBanner_search_join ( $join ) {
        global $pagenow, $wpdb;
        if ( is_admin() && 'edit.php' === $pagenow && 'inner_page_banner' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {    
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        }
        return $join;
    }

    public function InnerPageBanner_search_search_where( $where ) {
        global $pagenow, $wpdb;

        if ( is_admin() && 'edit.php' === $pagenow && 'inner_page_banner' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
        }
        return $where;
    }

    public function InnerPageBanner_search_distinct( $where ){
        global $pagenow, $wpdb;

        if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']=='inner_page_banner' && $_GET['s'] != '') {
        return "DISTINCT";

        }
        return $where;
    }



    public function InnerPageBanner_admin_menu_fun(){

      $parent_slug = "edit.php?post_type=inner_page_banner";

      $page_title = 'Banner Settings';

      add_submenu_page( $parent_slug, $page_title, $page_title, 'manage_options', 'banner-settings-page', array($this, 'InnerPageBannerSettingsFun') );


    }

    public function InnerPageBannerSettingsFun(){

      ?>

       <div class="wrap">
       <h1>Banner Settings</h1>

      <div>
        <div style="font-size: 21px;">Shortcode: <b>[InnerPageBannerFrontEndShortcode]</b></div>
      </div>
    
      <form action="options.php" method="post">
      <?php wp_nonce_field('update-options') ?>

        <table class="form-table"><tbody>
            

            <tr>
              <th scope="row">
                    <label for="InnerPageBanner_switch_on_off"><?php echo __('Enable: ', 'inner-page-banner');?></label>
              </th>
              <td>
              <input type="checkbox" name="InnerPageBanner_switch_on_off" id="InnerPageBanner_switch_on_off" value="1" <?php if (!empty(get_option('InnerPageBanner_switch_on_off'))) { echo "checked"; } ?>>
              </td>
            </tr>



          </tbody>
        </table>

    
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options" value="InnerPageBanner_switch_on_off,category_banner_image,category_banner_image_option,diesel_ads_url_opt, diesel_inner_page_banner_image" />
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
      </form>
   </div>



      <?php
    }


    public function InnerPageBannerColumnFun( $columns ) {
      
      $columns = array(
              'cb' => '<input type="checkbox" />',
              'title' => __( 'Title' ),
              'banner_img' => __( 'Banner' ),
              'selected_page' => __( 'Selected Page' ),
              'date' => __( 'Date' )
          );

      return $columns;
      
      
  }


  public function InnerPageBannerValueFun( $column ) { 

      global $post;

      switch( $column ) {

        case 'banner_img' :

        $attachment_id = get_post_meta( $post->ID, '_thumbnail_id', true );

        //$attachment_id = get_post_meta( $post->ID, 'InnerPageBannerImage', true );

        $image_size = 'thumbnail'; // (thumbnail, medium, large, full or custom size)
          
        $image_attributes_thumbnail = wp_get_attachment_image_src( $attachment_id, $image_size );

        $banner_url = $image_attributes_thumbnail[0];

        echo '<img src="'.$banner_url.'" style="width:100px;">';
          
              break;

        case 'selected_page' :

            
          //
          $InnerPageBannerType = get_post_meta( $post->ID, 'InnerPageBannerType', true ); 
          $InnerPageBannerSelection = get_post_meta( $post->ID, 'InnerPageBannerSelection', true );

          foreach ($InnerPageBannerSelection as $data) {

            $selected_element_id = (int)$data; 

             if ($InnerPageBannerType == 'InnerPageBannerPage' || $InnerPageBannerType == 'InnerPageBannerProduct') {  
                $preview_url = get_permalink($selected_element_id);
                $page_title = get_the_title($selected_element_id);
               ?>
               <div>Page: <a target="_blank" href="<?php echo $preview_url?>"><?php echo $page_title?></a></div>
               <?php

             }else if($InnerPageBannerType == 'InnerPageBannerProductCategory'){ 
                $preview_url = get_term_link( $selected_element_id, 'product_cat' ); 
                $term = get_term($selected_element_id);
                $page_title = $term->name;
                ?>
               <div>Category: <a target="_blank" href="<?php echo $preview_url?>"><?php echo $page_title?></a></div>
               <?php
             }else{
                $preview_url = "";
             }
          }
          //
          
              break;


          default :
                  break;
      }
  }


    public function InnerPageBanner_register_post_type_fun(){

      $labels = array(
          'name'               => _x( 'Banner', 'post type general name' ),
          'singular_name'      => _x( 'Banner', 'post type singular name' ),
          'add_new'            => _x( 'Add New', 'Banner' ),
          'add_new_item'       => __( 'Add New Banner' ),
          'edit_item'          => __( 'Edit Banner' ),
          'new_item'           => __( 'New Banner Items' ),
          'all_items'          => __( 'All Banners' ),
          'view_item'          => __( 'View Banner' ),
          'search_items'       => __( 'Search Banner' ),
          'not_found'          => __( 'No Banner Items found' ),
          'not_found_in_trash' => __( 'No Banner Items found in the Trash' ),
          'parent_item_colon'  => '',
          'menu_name'          => 'Banner'
      );
      $args = array(
          'labels'        => $labels,
          'description'   => 'Banner specific data',
          'public'        => false,
          'show_ui'       => true,
          'show_in_menu'  => true,
          'query_var'     => true,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'rewrite'       => array('slug' => 'inner-page-banner'),
          'capability_type'=> 'post',
          'has_archive'   => true,
          'hierarchical'  => false,
          'menu_position' => 5,
          'supports'            => array( 'title', 'thumbnail'),
          'menu_icon' => 'dashicons-welcome-write-blog'
      );

      register_post_type( 'inner_page_banner', $args );


    }

    public function TmmLedger_meta_boxes(){

      add_meta_box( 'InnerPageBanner', 'Inner Page Banner', array($this, 'InnerPageBannerMetabox'), 'inner_page_banner', 'normal', 'high' );


    }


    public function InnerPageBannerFrontEndShortcodeFun(){

        $InnerPageBanner_switch_on_off = get_option('InnerPageBanner_switch_on_off');

        if ($InnerPageBanner_switch_on_off && !empty($InnerPageBanner_switch_on_off)) {


          if (is_product_category()) {
            $category = get_queried_object();
            $page_id = $category->term_id;
            //echo 'term_id: '.$page_id;
          }else{
            $page_id = get_the_ID();
            //echo 'page_id: '.$page_id;
          }


          $InnerPageBanner_Response = $this->InnerPageBanner_GetPageID($page_id);

          //echo "<pre>"; print_r($InnerPageBanner_Response); echo "</pre>";
         
          if (!empty($InnerPageBanner_Response)) {

            $banner_id = $InnerPageBanner_Response[0]->banner_id;

            $banner_post_status = get_post_status($banner_id);

            if ( $banner_post_status == 'publish') {

              $BannerAttachment = get_post_meta( $banner_id, '_thumbnail_id', true );

              //$BannerAttachment = get_post_meta( $banner_id, 'InnerPageBannerImage', true );

              $InnerPageBannerLink = get_post_meta( $banner_id, 'InnerPageBannerLink', true );

              if (!empty($BannerAttachment)) {

                $image_size = 'full'; // (thumbnail, medium, large, full or custom size)
                  
                $image_attributes_thumbnail = wp_get_attachment_image_src( $BannerAttachment, $image_size );

                $banner_url = $image_attributes_thumbnail[0];
                ?>
                <div class="diesel-ads-image">
                  <a href="<?php echo $InnerPageBannerLink;?>">
                    <img src="<?php echo $banner_url;?>" style="width:100%;">
                  </a>
                </div>
                <?php

              }
             
            }

          }
         
        }
    }


    public function InnerPageBanner_GetPageID($page_id){

      global $wpdb;

      $query =  "SELECT * FROM ".$wpdb->prefix."InnerPageBanner WHERE page_id =".$page_id;

      $results = $wpdb->get_results( $query );

      return $results;

    }


    public function InnerPageBannerMetabox(){
      global $post; 

       $InnerPageBannerSelection = get_post_meta( $post->ID, 'InnerPageBannerSelection', true );

       $banner_id = $post->ID;

       //echo "<pre>"; print_r($InnerPageBannerSelection); echo "</pre>";

       $page_id = $InnerPageBannerSelection;

       $InnerPageBannerImage = get_post_meta( $post->ID, 'InnerPageBannerImage', true );

       $InnerPageBannerLink = get_post_meta( $post->ID, 'InnerPageBannerLink', true );

       $InnerPageBannerLinkVal = '#';
       if ($InnerPageBannerLink && !empty($InnerPageBannerLink)) {
         $InnerPageBannerLinkVal = $InnerPageBannerLink;
       }

       $InnerPageBannerType = get_post_meta( $post->ID, 'InnerPageBannerType', true ); 

      ?>

      <div id="InnerPageBannerOverlayLoader"></div>

      <div>
        <?php
        foreach ($InnerPageBannerSelection as $data) {

          $selected_element_id = (int)$data;

           if ($InnerPageBannerType == 'InnerPageBannerPage' || $InnerPageBannerType == 'InnerPageBannerProduct') {  
              $preview_url = get_permalink($selected_element_id);

             ?>
              <div>
                <label>Preview: </label>
                <a target="_blank" href="<?php echo $preview_url?>"><?php echo $preview_url?></a>
              </div>
             <?php

           }else if($InnerPageBannerType == 'InnerPageBannerProductCategory'){ 
              $preview_url = get_term_link( $selected_element_id, 'product_cat' ); 
              ?>
              <div>
                <label>Preview: </label>
                <a target="_blank" href="<?php echo $preview_url?>"><?php echo $preview_url?></a>
              </div>
             <?php
           }else{
              $preview_url = "";
           }
        }
        ?>


      
        <div>
          <ul style="display: flex;">
            <li>Page<input type="radio" name="InnerPageBannerType" value="InnerPageBannerPage" style="margin-left: 4px;" <?php if ($InnerPageBannerType == 'InnerPageBannerPage') { echo "checked";}?>></li>

            <li>Product Category<input type="radio" name="InnerPageBannerType" value="InnerPageBannerProductCategory" style="margin-left: 4px;" <?php if ($InnerPageBannerType == 'InnerPageBannerProductCategory') { echo "checked";}?>></li>

          </ul>
        </div>


        <div class="tabs">
          <div id="InnerPageBannerPage" class="InnerPageBannerTabLi" style="display: none;">
             <select name='InnerPageBannerSelection[]' id="InnerPageBannerSelection" multiple="multiple">
                <option value='0'><?php _e('Select a Page', 'textdomain'); ?></option>
                <?php $pages = get_pages(); ?>
                <?php foreach( $pages as $page ) { ?>
                    <option value='<?php echo $page->ID; ?>' <?php selected( $InnerPageBannerSelection, $page->ID ); ?> ><?php echo $page->post_title; ?></option>
                <?php }; ?>
            </select>
          </div>

          <div id="InnerPageBannerProductCategory" class="InnerPageBannerTabLi" style="display: none;">
          <select name='InnerCatBanner_action_select2[]' id="InnerCatBanner_action_select2" multiple="multiple">
                <option value='0'><?php _e('Select a Page', 'textdomain'); ?></option>
                <?php $terms = get_terms( 'product_cat' ); ?>
                <?php foreach( $terms as $term ) { ?>
                    <option value='<?php echo $term->term_id; ?>' <?php selected( $InnerPageBannerSelection, $term->term_id ); ?> ><?php echo $term->name; ?></option>
                <?php }; ?>
            </select>

          </div>

        </div>

        <br>

        <!-- <div> -->
        <?php
            //echo $this->inner_page_baaner_image_uploader_field( 'InnerPageBannerImage', $InnerPageBannerImage );
        ?>
       <!--  </div> -->

        <!-- <br><br> -->

        <div>
          <label>
            Banner Url:
          </label>
          <input type="text" name="InnerPageBannerLink" id="InnerPageBannerLink" value="<?php echo $InnerPageBannerLinkVal;?>" style="width:100%;">
        </div>

        <script type="text/javascript">
        jQuery(function($){



          jQuery('#InnerPageBannerOverlayLoader').insertBefore('#InnerPageBanner > .inside');

          /*
          * Radio button type chnage tab
          */
          var InnerPageBannerType = '<?php echo $InnerPageBannerType;?>';
          jQuery('#'+InnerPageBannerType).show();

          jQuery(document).on('change', 'input[name="InnerPageBannerType"]', function(){

            var target = jQuery(this);

            var banner_type = target.val();

            jQuery('.InnerPageBannerTabLi').hide();

            jQuery('#'+banner_type).show();

          });
          /*
          * Radio button type chnage tab
          */


          /*
          * Select Page
          */ 
          jQuery('#InnerPageBannerSelection').select2({
            width: '100%',
            placeholder : "select me" 
          });
          /*
          * Select Page
          */ 


          /*
          * Select Product Category
          */ 
          jQuery('#InnerCatBanner_action_select2').select2({
            ajax: {
              url: '<?php echo admin_url( 'admin-ajax.php');?>', //"https://api.github.com/search/repositories",
              dataType: 'json',
              delay: 250, // delay in ms while typing when to perform a AJAX search
              data: function (params) {  
                  return {
                    q: params.term, // search query
                    action: 'InnerCatBanner_action_select2' // AJAX action for admin-ajax.php
                  };
              },
              processResults: function( data ) {  

                console.log(data);

                jQuery("#InnerPageBannerSelection").select2('val', '0');

                var options = [];
                if ( data ) {
                
                  // data is the array of arrays, and each of them contains ID and the Label of the option
                    jQuery.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
                      options.push( { id: text[0], text: text[1]  } );
                    });


                }
                return {
                  results: options
                };
            },
            cache: true
            },
            minimumInputLength: 3, // the minimum of symbols to input before perform a search
            width: '100%',
            placeholder : "select me" 
          });
          /*
          * Select Product Category
          */ 




          /*
          * Show selected pages
          */
          var InnerPageBannerType = jQuery('input[name="InnerPageBannerType"]:checked').val();
          var banner_id = '<?php echo $banner_id;?>';
          jQuery.ajax({
              url: '<?php echo admin_url( 'admin-ajax.php');?>',
              type: "POST",
              data: {'action': 'InnerPageBanner_show_selected_pages', 'banner_id': banner_id},
              cache: false,
              dataType: 'json',
              beforeSend: function(){
                jQuery('#InnerPageBannerOverlayLoader').addClass('InnerPageBannerOverlayLoaderClass');
              },
              complete: function(){
                jQuery('#InnerPageBannerOverlayLoader').removeClass('InnerPageBannerOverlayLoaderClass');
              },
              success: function (response) { 

                var Values = new Array();
             
                jQuery.each( response['response'], function( key, val ) { 
                    Values.push( val );
                });

                if (InnerPageBannerType == 'InnerPageBannerPage') {
                  jQuery("#InnerPageBannerSelection").val(Values).trigger('change');
                }else if(InnerPageBannerType == 'InnerPageBannerProductCategory'){
                  jQuery("#InnerCatBanner_action_select2").val(Values).trigger('change');
                }else{
                  //alert("Page Note Found");
                }
                //console.log(response['response']);
              }
          });
          /*
          * Show selected pages
          */


          /*
          * Validate selected page
          */
          /*jQuery(document).on('change', '#InnerPageBannerSelection', function(){

            jQuery( "#publish" ).prop( "disabled", true );

            //var page_id = jQuery(this).find(':selected').val(); 
        
            var page_id = jQuery("#InnerPageBannerSelection :selected").map(function(i, el) {
                return jQuery(el).val();
            }).get();

            console.log(page_id);

          });*/
          /*
          * Validate selected page
          */



          /*
           * Select/Upload image(s) event
           */
          jQuery('body').on('click', '.consultant_upload_image_button', function(e){
              e.preventDefault();

                  var button = jQuery(this),
                      custom_uploader = wp.media({
                  title: 'Insert image',
                  library : {
                      // uncomment the next line if you want to attach image to the current post
                      // uploadedTo : wp.media.view.settings.post.id, 
                      type : 'image'
                  },
                  button: {
                      text: 'Use this image' // button label text
                  },
                  multiple: false // for multiple image selection set to true
              }).on('select', function() { // it also has "open" and "close" events 
                  var attachment = custom_uploader.state().get('selection').first().toJSON();
                  jQuery(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:50%;display:block;" />').next().val(attachment.id).next().show();
              })
              .open();
          });

          /*
           * Remove image event
           */
          jQuery('body').on('click', '.misha_remove_image_button', function(){
              jQuery(this).hide().prev().val('').prev().addClass('button').html('Upload image');
              return false;
          });
         
        });
        </script>

      </div>
      <?php
    }






    /*
    * 
    */
    public function InnerPageBanner_show_selected_pages(){

      $banner_id = $_POST['banner_id'];
      $InnerPageBannerSelection = get_post_meta( $banner_id, 'InnerPageBannerSelection', true );
      $myArr = array('response' => $InnerPageBannerSelection, 'banner_id'=>$banner_id);
      $myJSON = json_encode($myArr); 
      echo $myJSON;
      die();
    }




    /*
    * Save post meta
    */
    public function InnerPageBanner_save_post($post_id){

      global $post; global $wpdb;

      $InnerPageBannerType = $_POST['InnerPageBannerType'];




     
      if ($InnerPageBannerType == 'InnerPageBannerPage'){  

        $selection_id = $_POST['InnerPageBannerSelection'];

        update_post_meta( $post_id, 'InnerPageBannerSelection', $selection_id );

        $InnerPageBannerTitle = get_the_title($selection_id);
        update_post_meta( $post_id, 'InnerPageBannerTitle', $InnerPageBannerTitle );

       }

       if ($InnerPageBannerType == 'InnerPageBannerProductCategory'){ 
          
          $selection_id = $_POST['InnerCatBanner_action_select2'];
         
          update_post_meta( $post_id, 'InnerPageBannerSelection', $selection_id );

          $term = get_term($selection_id);
          $category_name = $term->name;
          update_post_meta( $post_id, 'InnerPageBannerTitle', $category_name );

       }


      /*if (isset( $_POST['InnerPageBannerImage'] ) ) {
        $sanitized = wp_filter_post_kses( $_POST['InnerPageBannerImage'] );
        $attachment_id = $sanitized;
        update_post_meta( $post_id, 'InnerPageBannerImage', $sanitized );
      }*/


      if (isset( $_POST['_thumbnail_id'] ) ) {
        $sanitized = wp_filter_post_kses( $_POST['_thumbnail_id'] );
        $attachment_id = $sanitized;
        update_post_meta( $post_id, 'InnerPageBannerImage', $sanitized );
      }


      $InnerPageBannerLink = $_POST['InnerPageBannerLink'];
      $InnerPageBannerLinkVal = '#';
      if ($InnerPageBannerLink && !empty($InnerPageBannerLink)) {
       $InnerPageBannerLinkVal = $InnerPageBannerLink;
      }

      update_post_meta( $post_id, 'InnerPageBannerLink', $InnerPageBannerLinkVal );

      /*if (isset( $_POST['InnerPageBannerLink'] ) ) {
        $sanitized = wp_filter_post_kses( $_POST['InnerPageBannerLink'] );
        update_post_meta( $post_id, 'InnerPageBannerLink', $InnerPageBannerLinkVal );
      }*/


      if (isset( $_POST['InnerPageBannerType'] ) ) {
        $sanitized = wp_filter_post_kses( $_POST['InnerPageBannerType'] );
        update_post_meta( $post_id, 'InnerPageBannerType', $sanitized );
      }


      //die();



      //
      //echo "<pre>"; print_r($selection_id); echo "</pre>";
      foreach ($selection_id as $element_id) {

          $query_up =  "SELECT * FROM ".$wpdb->prefix."InnerPageBanner WHERE page_id=".$element_id;
          $results_up = $wpdb->get_results( $query_up );
          $count = count($results_up); echo "<br>";

          if ($count > 0) {

            $wpdb->update( 
              $wpdb->prefix.'InnerPageBanner', 
              array( 
                'banner_id' => $post_id, 
                'attachment_id' => $attachment_id,
              ), 
              array( 'page_id' => $element_id ), 
              array( 
                '%d', 
                '%d'  
              ),  
              array( '%d' ) 
            );
            
          }else{

            /*$query_de = "DELETE FROM ".$wpdb->prefix."InnerPageBanner WHERE page_id =".$element_id;
            $wpdb->query($query_de); */

            $wpdb->insert( 
              $wpdb->prefix.'InnerPageBanner', 
              array( 
                'banner_id' => $post_id, 
                'page_id' => $element_id,
                'attachment_id' => $attachment_id,
              ), 
              array( 
                '%d', 
                '%d',
                '%d'  
              ) 
            );


          }
              
      }
      //
      //die();




    }





    /*
     * Page Category search ajax
     */
    public function InnerPageBannerSelection_action(){

      global $wpdb;

      $page_id = $_POST['page_id'];

      $count = 0;

      $query =  "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'InnerPageBannerSelection' AND meta_value=".$page_id;

      $results = $wpdb->get_results( $query );

      $count = sizeof($results);

      $myArr = array('response' => $results, 'page_id' => $page_id, 'count' => $count);
      $myJSON = json_encode($myArr); 
      echo $myJSON;
      die();
    }

    public function inner_page_baaner_image_uploader_field( $name, $value = '') {
      $image = ' button">Upload image';
      $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
      $display = 'none'; // display state ot the "Remove image" button

      if( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {
          $image = '"><img src="' . $image_attributes[0] . '" style="max-width:50%;display:block;" />';
          $display = 'inline-block';
      }

      return '
      <div>
          <a href="#" class="consultant_upload_image_button' . $image . '</a>
              <input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" />
              <a href="#" class="button button-primary misha_remove_image_button" style="margin-top: 10px;;display:inline-block;display:' . $display . '">Remove image</a>
          </div>';

    }


   /*
   * Product Category search ajax
   */
    public function InnerCatBanner_action_select2(){
      $return = array();

      $terms = get_terms( 'product_cat', array(
          'name__like' => $_GET['q'],
          'hide_empty' => true  
      ) );
      if ( count($terms) > 0 ){

          foreach ( $terms as $term ) {

             $return[] = array( $term->term_id, $term->name );

          }
      }
      echo json_encode( $return );
      die;
    }


  }

}

new InnerPageBanner();



function create_InnerPageBanner_database_table(){

    global $table_prefix, $wpdb;

    $tblname = 'InnerPageBanner';
    $wp_track_table = $table_prefix . "$tblname ";

    #Check to see if the table exists already, if not, then create it
    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {

        $sql = "CREATE TABLE IF NOT EXISTS {$wp_track_table} (
          id INT AUTO_INCREMENT PRIMARY KEY,
          banner_id VARCHAR(255) NOT NULL,
          page_id VARCHAR(255) NOT NULL,
          attachment_id VARCHAR(255) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}

register_activation_hook( __FILE__, 'create_InnerPageBanner_database_table' );

?>