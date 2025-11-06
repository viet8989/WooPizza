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
define( 'DB_NAME', 'ter75991_db' );

/** Database username */
define( 'DB_USER', 'ter75991_db' );

/** Database password */
define( 'DB_PASSWORD', 'v39$aY8HdE*krrrh' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

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
define('AUTH_KEY', ':S9[Kw1|!W96g197W;GRoO9;h@w-s&%CVK_:3-oe2c(Tewv5@9LM04kj78nRYP9o');
define('SECURE_AUTH_KEY', '&UgkPXpu&Mq9aS5wY#h3)y2A9n[*Ng9#z_obYfUX4&S8Y;!SS2[1O%t74tD2rs37');
define('LOGGED_IN_KEY', '3o)+UH47)-u2w|[k~|M%&kt1!4_ebY37-kPxu6B9ZmF62p5J8C[j76xG@1mpr6i~');
define('NONCE_KEY', 'PZuC84*i2-G_7qJbth]G@x2@dIhz2fTZP||0404;:p5([QrK_]Dfe1Od4M6R:L76');
define('AUTH_SALT', 'Z(:jo3yRQ6[W387zC5z/2Y+l949xCf4%c1o9lgVB@-Pig[RSSLQVqk/+s:hN6254');
define('SECURE_AUTH_SALT', '30ww8V5v;:Dz;0[SL@OpE-6zl|LG%AVJ)7-@3dKPz-36*W9~AaJ2%4Fvyty[x5v;');
define('LOGGED_IN_SALT', 'b9&y0y|%/:Qt#qDfkdF3:-7MRu4wyZ0/+_fp3u;6zr_yhr!0mww2kB4]ei5Pl4+p');
define('NONCE_SALT', 't()*P]rD_F44*qN832|hWYD|*z1b#0il:G_g|RdRY@3i0mQX%PGD10dXef~ixX9X');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'R6vhU_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
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
	define( 'WP_DEBUG', true );
	define( 'WP_DEBUG_LOG', true );
	define( 'WP_DEBUG_DISPLAY', false );
}

define( 'DISABLE_WP_CRON', true );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
