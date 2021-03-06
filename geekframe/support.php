<?php
/**
 * 功能支持模块
 */
//支持缩略图

add_theme_support('post-thumbnails');
//链接支持
add_filter('pre_option_link_manager_enabled', '__return_true');

if (function_exists('add_theme_support')) {
//开启导航菜单主题支持
    add_theme_support('top-nav-menus');
//注册一个导航菜单
    register_nav_menus(array(
        'header_menu' => '顶部导航菜单',
        'footer_menu' => '底部导航菜单',
    ));
}
//侧边栏注册
function geekpress_sidebar_reg()
{
    register_sidebar(array(
        'id' => 'index_sidebar',
        'name' => '首页边栏',
        'before_title' => '<h2 class="widget-title">',
        'before_widget' => '<div class="aside-box">',
        'after_widget' => '</div>'
    ));
    register_sidebar(array(
        'id' => 'post_sidebar',
        'name' => '文章边栏',
        'before_title' => '<h2 class="widget-title">',
        'before_widget' => '<div class="aside-box">',
        'after_widget' => '</div>'
    ));
    register_sidebar(array(
        'id' => 'footer_widget',
        'name' => '底部小工具1',
        'before_title' => '<h2 class="footer-widget-title">',
        'before_widget' => '<div class="footer-aside-box">',
        'after_widget' => '</div>'
    ));
}

//移除菜单多余css
add_filter('nav_menu_css_class', 'corePress_css_attributes_filter', 100, 1);
//add_filter('nav_menu_item_id', 'corePress_css_attributes_filter', 100, 1);
//add_filter('page_css_class', 'corePress_css_attributes_filter', 100, 1);
function corePress_css_attributes_filter($classes)
{
    if ($classes) {
        $unset_classes = array('menu-item-type-post_type', 'menu-item-object-page', 'menu-item-object-category', 'menu-item-type-taxonomy', 'menu-item-object-custom', 'menu-item-type-custom', 'page_item', 'menu-item-home');
        foreach ($classes as $k => $class) {
            if (in_array($class, $unset_classes)) unset($classes[$k]);
        }
    }
    return $classes;
}

add_action('widgets_init', 'geekpress_sidebar_reg');

//设置页面注册
add_action('admin_menu', 'geekpress_add_menu');
function geekpress_add_menu()
{
    add_menu_page('主题设置', '主题设置', 'administrator', 'geekpress_setting', 'geekpress_page_setting', 'dashicons-buddicons-topics');
}

function geekpress_page_setting()
{
    require_once FRAMEWORK_PATH . "//page-setting.php";
}


//使用字体图标
function corePress_get_dashicons()
{
    wp_enqueue_style('dashicons');
}

add_action('wp_enqueue_scripts', 'corePress_get_dashicons');

//禁止转义引号字符
remove_filter('the_content', 'wptexturize'); // 禁止英文引号转义为中文引号
remove_filter('the_content', 'balanceTags'); //禁止对标签自动校正


if ($set['optimization']['removeversion'] === 1) {
    //移除版本号
    remove_action('wp_head', 'wp_generator');
}

if ($set['optimization']['removednsprefetch'] === 1) {
    //去除头部加载dns-prefetch
    remove_action('wp_head', 'wp_resource_hints', 2);
}
if ($set['optimization']['removejson'] === 1) {
//去除json连接
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('template_redirect', 'rest_output_link_header', 11);
}
if ($set['optimization']['removemeta'] === 1) {
    //移除前后文、第一篇文章、主页meta信息
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}
if ($set['optimization']['removefeed'] === 1) {
    //移除feed
    remove_action('wp_head', 'feed_links', 2);//文章和评论feed
    remove_action('wp_head', 'feed_links_extra', 3); //分类等feed
}
if ($set['optimization']['removewpblock'] === 1) {
    //WordPress 5.0+移除 block-library CSS
    add_action('wp_enqueue_scripts', 'fanly_remove_block_library_css', 100);
}

function fanly_remove_block_library_css()
{
    wp_dequeue_style('wp-block-library');
}

if ($set['optimization']['closerest'] === 1) {
    //屏蔽 REST API
    add_filter('rest_enabled', '__return_false');
    add_filter('rest_jsonp_enabled', '__return_false');
}
if ($set['optimization']['closeupdate'] === 1) {
    // 禁止 WordPress 检查更新
    remove_action('admin_init', '_maybe_update_core');
    remove_action('admin_init', '_maybe_update_plugins');
    remove_action('admin_init', '_maybe_update_themes');
}
if ($set['optimization']['closeemoji'] === 1) {
    //禁止emoji
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('embed_head', 'print_emoji_detection_script');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}


function CorePress_replace_avatar($avatarUrl)
{
    global $set;
    $avatarUrl = str_replace("http://", "https://", $avatarUrl);
    if ($set['optimization']['gravatarsite'] == 'v2ex') {
        //$avatar = preg_replace(["/[0-9]\.gravatar\.com\/avatar/", "/secure.gravatar\.com\/avatar/"], "cdn.v2ex.com/gravatar", $avatarUrl);
        $avatarUrl = str_replace(array("www.gravatar.com/avatar", "0.gravatar.com/avatar", "1.gravatar.com/avatar", "2.gravatar.com/avatar"), "cdn.v2ex.com/gravatar", $avatarUrl);

    } elseif ($set['optimization']['gravatarsite'] == 'geek') {
        $avatarUrl = str_replace(array("www.gravatar.com/avatar", "0.gravatar.com/avatar", "1.gravatar.com/avatar", "2.gravatar.com/avatar"), "sdn.geekzu.org/avatar", $avatarUrl);
    } elseif ($set['optimization']['gravatarsite'] == 'cn') {
        $avatarUrl = str_replace(array("www.gravatar.com/avatar", "0.gravatar.com/avatar", "1.gravatar.com/avatar", "2.gravatar.com/avatar"), "cn.gravatar.com/avatar", $avatarUrl);
    }
    return $avatarUrl;
}

//print_r($set['optimization']['gravatarsite'] );
add_filter('get_avatar', 'CorePress_replace_avatar');
add_filter('get_avatar_url', 'CorePress_replace_avatar');


add_filter('emoji_svg_url', '__return_false');

show_admin_bar(false);

function copay_footer_admin($text)
{
    global $set;
    if ($set['info']['themeupdate'] == 1) {
        corepress_updateTheme();
        if ($set['info']['newversion'] != THEME_VERSION) {
            $url = '，<a href="' . $set['info']['downurl'] . '" target="_blank">立即更新</a>';
        }
    }else
    {
        $url = '，已关闭更新';
    }


    return "{$text}<p>CorePress主题，当前版本：" . THEME_VERSIONNAME . "，最新版本：{$set['info']['newversionname']}{$url}</p>";

}

add_filter('admin_footer_text', 'copay_footer_admin');

function corepress_dashboard_help()
{
    global $set;
    ?>
    <p>感谢使用Corepress主题，这些信息可能对您有帮助</p>
    <p>主题官网：<a href="https://www.lovestu.com" target="_blank">https://www.lovestu.com</a></p>
    <p>当前版本：<span><?php echo THEME_VERSIONNAME ?></span></p>
    <?php
}

function corepress_add_dashboard_widgets()
{
    wp_add_dashboard_widget('corepress_dashboard_help', 'CorePress主题', 'corepress_dashboard_help');
}

add_action('wp_dashboard_setup', 'corepress_add_dashboard_widgets');

function corepress_updateTheme()
{
    global $set;
    $url = 'http://api.lovestu.com/theme/corepress/version.json';
    $request = new WP_Http;
    $result = $request->request($url);
    $json = json_decode($result['body'], true);
    $set['info']['newversionname'] = $json['versionname'];
    $set['info']['newversion'] = $json['version'];
}