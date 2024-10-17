<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '>_(x_}L=yp[%Rp|s6pe}<M<P`(lT^>W@.{i!HibVD?b>*D?w@tR,u7>7-uXQ/,5g' );
define( 'SECURE_AUTH_KEY',   'g0K%0%,AnXGh]kJyZyg&@|vS/tV8&`r],bR<)PpYGB41E$U;ku*/<]vC&0b#cLW|' );
define( 'LOGGED_IN_KEY',     'S0yEo|h{X oyQ,VRVs~C%*vB;&l0Q=`2}r%jP*XP!zaY<LmnexYBgtv`-(=yg[2*' );
define( 'NONCE_KEY',         '1eI&W[@N-#z~GN,9K2R==~W*jnU.Io#0NQqbva@=q7>*S}(-._S/yJ$:r4w[3ch)' );
define( 'AUTH_SALT',         'x)E. 32md1|$unWB.0f$}+}g_CjkA!.h0b&}CyH6~YtUm!XVy~*ph<Fwmt*{ee /' );
define( 'SECURE_AUTH_SALT',  'id]C2VfgSUr%]jG(me^x:Cf?dxYWJqr:|XHG@v~t&He7k;A3o%@on;tR0Squd$Y!' );
define( 'LOGGED_IN_SALT',    'dGydFk2y},@&?hLxcn]3)Mvyoe^RCy]^(k~{u+CaZSY0XB-0=kflnu>j0[ziO~6i' );
define( 'NONCE_SALT',        'K,$x(BS3mjUZb:j?${b;Dvv,PvRzxRC;fvA#3YfgTK;+0tU}Xq[>Csr{5~~v8_=P' );
define( 'WP_CACHE_KEY_SALT', '+Zzi0fQl6 iuAZiP%6L,qn6[=Dw:`M%Avc7[Q]atwn>42#.uYa?WNBct2Ztoh*Zn' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
