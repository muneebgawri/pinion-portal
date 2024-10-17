<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.1.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

// Show Parent Page's title on child page (for Buy a Press Release plan pages)
function display_parent_page_title() {
    global $post;
    // Check if the page has a parent
    if ($post->post_parent) {
        // Get the parent page ID
        $parent_id = wp_get_post_parent_id($post->ID);
        
        // Return the parent page title
        return get_the_title($parent_id);
    }
    return ''; // Return empty string if no parent
}
add_shortcode('parent_page_title', 'display_parent_page_title');

// Show Sub-Package to customer
// Function to display the current post's excerpt
function display_post_excerpt() {
    global $post;

    // Check if there's an excerpt for the current post
    if (has_excerpt($post->ID)) {
        // Return the excerpt
        return get_the_excerpt($post->ID);
    } else {
        // Return a default message if no excerpt is found
        return 'No excerpt available for this post.';
    }
}

// Register the shortcode
add_shortcode('post_excerpt', 'display_post_excerpt');

// Generate link for editing the Press Release
// Function to generate the admin edit post URL
function generate_edit_post_link() {
    global $post;

    // Get the site URL
    $site_url = get_site_url();

    // Get the current post ID
    $post_id = get_the_ID();

    // Generate the URL in the required format
    $edit_url = $site_url . '/wp-admin/post.php?post=' . $post_id . '&action=edit';

    // Return the generated URL
    return $edit_url;
}

// Register the shortcode
add_shortcode('edit_post_link', 'generate_edit_post_link');

// Enfore Business Email upon registration
// Function to block common email providers during user registration
function restrict_non_business_emails($errors, $sanitized_user_login, $user_email) {

    // List of blocked email domains (common email providers)
    $blocked_domains = array(
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
        'live.com',
        'aol.com',
        'icloud.com'
        // You can add more common providers as needed
    );

    // Extract the domain from the user's email address
    $email_domain = substr(strrchr($user_email, "@"), 1);

    // Check if the email domain is in the list of blocked domains
    if (in_array($email_domain, $blocked_domains)) {
        // Add an error if the domain is blocked
        $errors->add('invalid_email_domain', __('Only business email addresses are allowed. Please use a corporate email.', 'text-domain'));
    }

    return $errors;
}

// Hook into the registration process to enforce business emails
add_filter('registration_errors', 'restrict_non_business_emails', 10, 3);

// Remove menu from WPUF Dashboard
add_action( 'wpuf_account_content_dashboard', '__return_false', 10 );
add_action( 'wpuf_account_content_press-releases', '__return_false', 10 );
add_action( 'wpuf_account_content_edit-profile', '__return_false', 10 );
add_action( 'wpuf_account_content_subscription', '__return_false', 10 );
add_action( 'wpuf_account_content_billing-address', '__return_false', 10 );
add_action( 'wpuf_account_content_invoices', '__return_false', 10 );
add_action( 'wpuf_account_content_submit-post', '__return_false', 10 );
remove_filter( 'wpuf_account_sections', [ 'WeDevs\Wpuf\Frontend\Frontend_Account', 'add_account_sections' ] );

// Shortcode for displaying WPUF Invoices
function show_invoices_section() {
    // Check if the user is logged in
    if ( is_user_logged_in() ) {
        // Define the sections array
        $sections = wpuf_get_account_sections();
        
        // Define the current section as 'invoices'
        $current_section = 'invoices';
        
        // Use the do_action to trigger the content for the 'invoices' section
        do_action( "wpuf_account_content_invoices", $sections, $current_section );
    } else {
        // Message for non-logged-in users
        echo '<p>You need to be logged in to view your invoices.</p>';
    }
}

// Register the shortcode [show_invoices]
add_shortcode( 'show_invoices', 'show_invoices_section' );

// Disable Admin Bar for all users except Administrator
/* Disable WordPress Admin Bar for all users except administrators */
add_filter( 'show_admin_bar', 'restrict_admin_bar' );
 
function restrict_admin_bar( $show ) {
    return current_user_can( 'administrator' ) ? true : false;
}

// Redirect non-logged-in users to the login page if they try to access any page other than login or register
function redirect_if_not_logged_in() {
    // Custom slugs for login and register pages (can be configured later via admin settings)
    $login_slug = 'login'; // You can change this slug
    $register_slug = 'signup'; // You can change this slug

    // Check if the user is not logged in
    if ( !is_user_logged_in() ) {
        // Get the URLs for the login and register pages dynamically using the custom slugs
        $login_page = home_url( '/' . $login_slug . '/' );
        $register_page = home_url( '/' . $register_slug . '/' );

        // Allow access to the login and register pages
        if ( is_page() && !is_page( array( $login_slug, $register_slug ) ) ) {
            // Redirect to the login page if the current page is not login or register
            wp_redirect( $login_page );
            exit();
        }

        // If the homepage is accessed, redirect to the login page as well
        if ( is_front_page() ) {
            wp_redirect( $login_page );
            exit();
        }
    }
}
add_action( 'template_redirect', 'redirect_if_not_logged_in' );

// Shortcode for Custom Logout URL output
function wpso58817718_add_logout_link( $atts ){
    return wp_logout_url( home_url() );
}
add_shortcode( 'logout_link', 'wpso58817718_add_logout_link' );

// Shortcode for 404 page back link
function custom_404_back_link() {
    // Default link is the home URL
    $home_url = home_url();
    
    // HTML output with a default link to the homepage
    $output = '
    <a id="back-link" href="' . $home_url . '">Go to Home</a>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var referrer = document.referrer;
            var currentUrl = window.location.href;
            var backLink = document.getElementById("back-link");
            
            // Check if the referrer exists and is different from the current page
            if (referrer && referrer !== currentUrl && referrer.indexOf(window.location.hostname) !== -1) {
                backLink.href = referrer;
                backLink.textContent = "Go back to the previous page";
            } else {
                backLink.href = "' . $home_url . '";
                backLink.textContent = "Go to Home";
            }
        });
    </script>';
    
    return $output;
}

add_shortcode( 'back_link_404', 'custom_404_back_link' );
