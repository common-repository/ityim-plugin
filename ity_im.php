<?php
/*
Plugin Name: ity.im wordpress plugin
Plugin URI: http://api.ity.im/wordpress
Description: ity.im Wordpress plugin
Version: 1.4
Author: Enes Music
Author URI: http://ewizz.me
License: GPL2
    Copyright 2010  Enes Music  (email : ew1zz@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
include 'db.php';
include 'ity_class.php';
global $post;
global $ity_im_db_version;
    global $wpdb;
$ity_im_db_version = "1.4";
register_activation_hook(__FILE__,'ity_im_install');
add_action('admin_menu', 'ity_im_menu');
add_action ( 'publish_post', 'crawl_me' );
add_action ( 'publish_page', 'crawl_me' );
add_action('init', 'wp_invoice_init');
 add_filter('the_content', 'display_ity_me');
 add_filter('the_content_more_link', 'gotohell');
add_shortcode('readmore', 'readmemore');
$ity_user = get_option('ity_username');
$ity_passwd = get_option('ity_password');
function crawl_me($post_id){
    $ity_user = get_option('ity_username');
$ity_passwd = get_option('ity_password');
    $selected_now = get_option('ity_im_default_crawl');
    //twice, cached result
        global $wpdb;
    $staje = $wpdb->get_results("SELECT post_type,post_content FROM wp_posts WHERE ID = $post_id");
    if($staje->post_type=="page"){ $ovoje = 2;}if($staje->post_type=="post"){ $ovoje=1;}
    $posttext2 = get_post($post_id);
    $posttext1 = $posttext2->post_content;
    $datum = get_the_time();
    //$posttext1 = '<a href="/intl/bs/about.html">Sve o Googleuf</a>';
  if(($selected_now==$ovoje)||($selected_now=="3")){
    $ityclass=new ityim();
$ityclass->login=$ity_user; //your ity.im login email address
$ityclass->apikey=$ity_passwd; //your ity.im api key
$ityclass->format="xml"; //output format; json or xml
$ityclass->disable_inter="1";
$regular_expression = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
preg_match_all("/$regular_expression/siU", $posttext1, $allpics );
$NumberOfPics = count($allpics[0]);
// Check to see if we have at least 1
if ( $NumberOfPics > 0 )
{
for ( $i=0; $i < $NumberOfPics ; $i++ )
{	//izbaci 'http...'
    $allpics[2][$i] = str_replace("'","",$allpics[2][$i]);
    //pozovi api
    $ityclass->longurl= $allpics[2][$i];
    $ityclass->shrinkurl();
    $newhref= "<a href='".$ityclass->returned->data->url."' rel='".$allpics[2][$i]."' class='websnapr'>".$allpics[2][$i]."</a>";
$posttext1 = str_replace($allpics[0][$i], $newhref, $posttext1); }
}
$checkit2 = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_ity WHERE post_id=$post_id limit 1"));
if($checkit2!=""){
$asddas = $wpdb->update('wp_ity',array( 'post_text' => $posttext1 ), array( 'post_id' => $post_id ),$format = null, $where_format = null);
}
else{
    $wpdb->insert( 'wp_ity', array( 'date' => $datum, 'post_id' => $post_id,'post_text'=> $posttext1 ));
}}}
function wp_invoice_init() {
    wp_enqueue_style('ewizz',get_bloginfo('wpurl').'/wp-content/plugins/ityim-plugin/websnapr.css');
wp_enqueue_script( 'websnaprr',get_bloginfo('wpurl').'/wp-content/plugins/ityim-plugin/websnapr.js');
}
function display_ity_me($content){
    $selected_now = get_option('ity_im_default_crawl');
    if((is_page())&&($selected_now>1)){ return ity_im_get_content($content);}
    if((( get_post_type() == 'post' )&&($selected_now==1))||(( get_post_type() == 'post' )&&($selected_now==3))){
    return ity_im_get_content($content);}
    else { return $content;}
}
function gotohell($linkmemore){
      $ity_user = get_option('ity_username');
$ity_passwd = get_option('ity_password');
$ads = get_option('ity_ads');
$ityclass=new ityim();
$ityclass->login=$ity_user; //your ity.im login email address
$ityclass->apikey=$ity_passwd; //your ity.im api key
$ityclass->format="xml"; //output format; json or xml
$ityclass->disable_inter=$ads;
$offset = strpos($linkmemore, '#more-');
if ($offset) {
$end = strpos($linkmemore, '"',$offset);
}
if ($end) {
$link = substr_replace($linkmemore, '', $offset, $end-$offset);
}
$ityclass->longurl=$link;
$ityclass->shrinkurl();
$returnlink = $ityclass->returned->data->url;
return $link;
}
 function readmemore($atts, $content = null) {
    extract(shortcode_atts(array(
		"readmorelink" => 'Read More...'
	), $atts));
    $perm = get_permalink($post->ID);
    $ity_user = get_option('ity_username');
$ity_passwd = get_option('ity_password');
$ads = get_option('ity_ads');
$ityclass=new ityim();
$ityclass->login=$ity_user; //your ity.im login email address
$ityclass->apikey=$ity_passwd; //your ity.im api key
$ityclass->format="xml"; //output format; json or xml
$ityclass->disable_inter=$ads;
$ityclass->longurl=$perm;
$ityclass->shrinkurl();
$returnlink = $ityclass->returned->data->url;
 return '<a class="asd" href="'.$returnlink.'">'.$readmorelink.'</a>';
}
function set_atts( $obj, $atts){
$vars = array_keys(get_class_vars(get_class($obj)));
foreach($atts as $k=>$v){
if(in_array($k,$vars)){
$obj->$k=$v;
}
}
}
function ity_im_get_content($thisone){
    global $post;
  $ity_user = get_option('ity_username');
$ity_passwd = get_option('ity_password');
$ads = get_option('ity_ads');
if($ads==""){ $ads=1;}
    $posttext1 = $thisone;
     $ityclass=new ityim();
$ityclass->login=$ity_user; //your ity.im login email address
$ityclass->apikey=$ity_passwd; //your ity.im api key
$ityclass->format="xml"; //output format; json or xml
$ityclass->disable_inter=$ads;
$regular_expression = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
preg_match_all("/$regular_expression/siU", $posttext1, $allpics );
$NumberOfPics = count($allpics[0]);
// Check to see if we have at least 1
if ( $NumberOfPics > 0 )
{
for ( $i=0; $i < $NumberOfPics ; $i++ )
{	//izbaci 'http...'
    $allpics[2][$i] = str_replace("'","",$allpics[2][$i]);
    //pozovi api
$position = strpos($allpics[0][$i],"<img ");
if($position===false){
$ext = substr($allpics[2][$i], strrpos($allpics[2][$i], '.') + 1);
$ext = strtolower($ext);
if(($ext!="jpg")||($ext!="png")||($ext!="jpeg")||($ext!="gif")){
    $position2 = strpos($allpics[2][$i],get_bloginfo('url'));
    if($position2===false){
    if($allpics[2][$i]==get_permalink()){
        $ityclass->longurl= get_permalink();
    $ityclass->shrinkurl();
    $longie = $ityclass->returned->data->url;
        $relis = "";
        $imeto = "Read More...";
        $klasa = "";
    }
    else {
    $ityclass->longurl= $allpics[2][$i];
    $ityclass->shrinkurl();
    $longie = $ityclass->returned->data->url;
    $relis = $allpics[2][$i];
    $imeto = $allpics[2][$i];
    $klasa = "websnapr";}
    $newhref= "<a href='".$longie."' rel='".$relis."' class='".$klasa."'>".$imeto."</a>";
$posttext1 = str_replace($allpics[0][$i], $newhref, $posttext1); }}}}
}
 return $posttext1;   
}

function ity_im_install () {
   global $wpdb;
   global $ity_im_db_version;

   $table_name = $wpdb->prefix . "ity";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  post_id bigint(11) DEFAULT '0' NOT NULL,
      page_id bigint(11) DEFAULT '0' NOT NULL,
	  date tinytext NOT NULL,
	  post_text text NOT NULL,
      page_text text NOT NULL,
	  UNIQUE KEY id (id)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      $wpdb->query($sql);

     // $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => "testin", 'text' => "test" ) );
 
      add_option("ity_im_db_version", $ity_im_db_version);
      // 1 = posts 2 = pages 3= both, crawl both by default ^^
      add_option("ity_im_default_crawl", "3");
      add_option("ity_username","username");
      add_option("ity_password","123456");
      add_option("ity_ads","1");
   }
}

function ity_im_menu() {

  add_options_page('Ity.im Options', 'Ity.im plugin', 'manage_options', 'ity_im', 'my_plugin_options');

}

function my_plugin_options() {
    global $wpdb;

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
$selected_now = get_option('ity_im_default_crawl');
  echo '<div class="wrap">';
  if(isset($_POST['ityopt'])){ $temp_value = $_POST['ityopt'];update_option( "ity_im_default_crawl", $temp_value );?><div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test' ); ?></strong></p></div>
<?php }
  echo '<h2>ity.im Plugin Options</h2><br />';
    if(isset($_POST['usr'])){
    update_option( "ity_username", $_POST['usr'] );
     update_option( "ity_password", $_POST['api'] );
    ?>
    <div class="updated"><p><strong><?php _e('Email and API key are saved.', 'menu-test' ); ?></strong></p></div><?php
  }
      if(isset($_POST['ads'])){
    update_option( "ity_ads", $_POST['ads'] );
    ?>
    <div class="updated"><p><strong><?php _e('Ads settings saved.', 'menu-test' ); ?></strong></p></div><?php
  }
  echo '<p>Enter your <a href="http://ity.im/members/profile.php">ity.im API key</a> and email which you used to register to http://ity.im<p />';
  echo '<form name="userandpass" action="" method="post"><label for="usr"><p>Your email:</label><input type="text" name="usr" id="usr" value="'.get_option('ity_username').'"/></p><p><label for="api">Your API:&nbsp;&nbsp;&nbsp;</label><input type="text" name="api" id="api" value="'.get_option('ity_password').'"/></p><input type="submit" value="Save Changes" class="button-primary" name="Submit"><br><br /></form>';
  echo '<p>Please choose from where you want links to be shortened. You can choose pages,posts or both.</p><form name="form1" method="post" action=""><select name="ityopt" id="ityopt"><option value="1" name="1"';if($selected_now==1){echo 'selected';} echo '>From Posts</option><option value="2" name="2" ';if($selected_now==2){echo 'selected';} echo '>From Pages</option><option value="3" name="3" ';if($selected_now==3){echo 'selected';} echo '>Both</option></select><p><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p></form></p>';
  echo '<p>When link is clicked, ity.im will:</p><form action="" method="post"><select name="ads" id="ads"><option name="1" value="1">Show Ads</option><option name="2" value="2">Hide Ads</option></select><br /><p><input type="submit" class="button-primary" value="Save changes"/></p></form>';
  echo '<p>If you want to crawl older posts and pages click this button.This action may take some time, so please do not interrupt proccess. <form method="POST" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=ity_im&update=1"><input type="submit" value="Crawl pages and posts" /></form></p>';
  if(isset($_GET["update"])){
   $temp = $wp_query; /*will need it l8tr */
	$wp_query = null; /* it holds previous data,so null it */
$wp_query = new WP_Query('post_type=post&showposts=-1');
  while ($wp_query->have_posts()) : $wp_query->the_post();
  $id_now = get_the_ID();
  $time_now = get_the_time();/*mysql have it already so need to pass into var. */
  $checkit = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_ity WHERE post_id=$id_now and date='$time_now' limit 1"));
if($checkit!=""){ echo '<strong>Post '.$checkit.' already updated.</strong><br/ >';}
else {
    $filteredtext = ity_im_get_content(get_the_content());
    $wpdb->insert( 'wp_ity', array( 'date' => $time_now, 'post_id' => $id_now,'post_text'=> $filteredtext ));
    echo '<p style="color:#06AC18">Post '.$checkit.' updated successfully.</p>';
}
endwhile;
?><div class="updated"><p><strong><?php _e('All pages and posts are updated.', 'menu-test' ); ?></strong></p></div>
  <?php }
  echo '</div>';

}
?>