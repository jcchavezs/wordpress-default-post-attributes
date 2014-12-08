<?php

/*
Plugin Name: Default Post Attributes
Version: 0.1
Plugin URI: https://github.com/jcchavezs/wordpress-default-post-attributes
Description: Sets default attributes for new posts.
Author: José Carlos Chávez <jcchavezs@gmail.com>
Author URI: http://github.com/jcchavezs
*/

class default_post_attributes
{

    const PREFIX = 'default_post_attributes_';
    const PAGENAME = 'defaultpostattributes';
    const TEXT_DOMAIN = 'defaultpostattributes';

    protected $options = array();

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'admin_activation'));

        add_filter('default_title', array($this, 'return_default_title'));
        add_filter('default_content', array($this, 'return_default_content'));

        add_filter('plugin_action_links', array($this, 'action_links'), 10, 2);

        add_action('init', array($this, 'init'));

        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function init() {
        $this->options = get_option(self::PREFIX . 'options', $this->get_defaults());
    }

    private function get_defaults() {
        return array('title' => '', 'content' => '', 'post_types' => array());
    }

    function activation() {
        $defaults = $this->get_defaults();

        add_option(self::PREFIX . 'options', $defaults);
    }

    function admin_init() {
        register_setting(self::PREFIX . 'options', self::PREFIX . 'options', array($this, 'admin_validate'));
        add_settings_section(self::PREFIX . 'main', __('Settings', self::TEXT_DOMAIN), array($this, 'admin_section'), self::PAGENAME);
        add_settings_field('post_types', __('Post types: ', self::TEXT_DOMAIN), array($this, 'admin_post_types'), self::PAGENAME, self::PREFIX . 'main');
        add_settings_field('title', __('Default Title: ', self::TEXT_DOMAIN), array($this, 'admin_title'), self::PAGENAME, self::PREFIX . 'main');
        add_settings_field('content', __('Default Content: ', self::TEXT_DOMAIN), array($this, 'admin_content'), self::PAGENAME, self::PREFIX . 'main');
    }

    function admin_section() {
        echo '<p>' . __('Please enter your default post attribute values.', self::TEXT_DOMAIN) . '</p>';
    }

    function admin_menu() {
        add_submenu_page('options-general.php', __('Default Post Attributes Settings', self::TEXT_DOMAIN), __('Default Post Attributes', self::TEXT_DOMAIN), 'manage_options', self::PAGENAME, array($this, 'options_page'));
    }

    function options_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not allowed to access this part of the site', self::TEXT_DOMAIN));
        }
?>
    <div class="wrap">
    <h2><?php
        _e('Default Settings', self::TEXT_DOMAIN); ?></h2>

        <form action="options.php" method="post">
            <?php
        settings_fields(self::PREFIX . 'options'); ?>
            <?php
        do_settings_sections(self::TEXT_DOMAIN); ?>
            <p class="submit">
                <input name="submit" type="submit" class="button-primary" value="<?php _e('Save Changes', self::TEXT_DOMAIN); ?>" />
            </p>
        </form>
    </div>
    <?php
    }

    function admin_title() {
        echo "<input id='title' name='" . self::PREFIX . "options[title]' type='text' class='regular-text' value='{$this->options['title']}' />";
    }

    function admin_content() {
        echo "<textarea id='content' name='" . self::PREFIX . "options[content]' rows='5' cols='60'/>{$this->options['content']}</textarea>";
    }

    function admin_post_types() {
        echo "<select multiple name='" . self::PREFIX . "options[post_types][]'>";

        $post_types = get_post_types(array('public' => true), 'names');

        foreach ($post_types as $post_type) {
            $selected = in_array($post_type, $this->options['post_types']) ? 'selected' : '';
            echo "<option {$selected} value='{$post_type}'>{$post_type}</option>";
        }

        echo "</select>";
    }

    function admin_validate($input) {
        $input['title'] = strip_tags($input['title'], '');

        return $input;
    }

    function return_default_title($title) {
        global $post_type;

        if (in_array($post_type, $this->options['post_types'])) {
            $title = $this->options['title'];
        }

        return $title;
    }

    function return_default_content($content) {
        global $post_type;

        if (in_array($post_type, $this->options['post_types'])) {
            $content = $this->options['content'];
        }

        return $content;
    }

    function action_links($links, $file) {
        if ($file != plugin_basename(__FILE__)) return $links;

        $settings_link = sprintf('<a href="options-general.php?page=' . self::PAGENAME . '">%s</a>', __('Settings', self::TEXT_DOMAIN));

        array_unshift($links, $settings_link);

        return $links;
    }
}

new default_post_attributes();
