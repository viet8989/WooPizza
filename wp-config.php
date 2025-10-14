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
define( 'DB_NAME', 'u717264561_JvMrl' );

/** Database username */
define( 'DB_USER', 'u717264561_iqVBL' );

/** Database password */
define( 'DB_PASSWORD', 'hCFfxaNB2I' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',          'i@JEL3t;YN`(>EW$g< :K#8lQ+-Wsug({Z|d$+}|Zydpodxh$ r-]XFaZzubT,!m' );
define( 'SECURE_AUTH_KEY',   'p@{/kXMx0w#DOR&Gb%FNWuz[Jm`G41/aR4y]8sOm{hex;Ux}J+h.?h|Y??A%E.*&' );
define( 'LOGGED_IN_KEY',     'ObR_L:?6.ldGNf;?2T]3b4E_6nWV4)d$m%[)#Dtv/La+ H@54MIxY!$|rH8Po;f9' );
define( 'NONCE_KEY',         '.Qz?>XR=+&inM4HiXZ<%Or%}*7Xwj>7pf}QfiawSBh~Z/qkLy:/M69dvV;OM6M_L' );
define( 'AUTH_SALT',         '3mk~W`sf(~6J#c4a!U=-3Ldw?B%BE3.m5N7rki3/zB7kUbf39!E~$@ez4f%TGs)F' );
define( 'SECURE_AUTH_SALT',  'b=!c(zWZm?+RN5[DK>N5@&0[#+uM^puVQFM{D,3hK|N/V:gtr{aN^~BHZ_6*+Quc' );
define( 'LOGGED_IN_SALT',    '?+3kR1z,yg#(8ZTmWCbgD:haK%p^B%NaGblZ J/K8g/./%OPA1?jU^7h3n;p+>f ' );
define( 'NONCE_SALT',        '8m0G(B6QLTT~1rH,I|^kNF*n,A3JZ4=_>uT)iEiqkTY$D}B1PFOr%qnPxQk/(C p' );
define( 'WP_CACHE_KEY_SALT', '.BPR,F)&}wZ-^@|`I@msAML1uI42g[. 0;xYQ[ :V:sr_?{Tv,OFk<VnQ_brC6*V' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'R6vhU_';


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

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', 'fb044b5f4721cc763a2999528fb3a847' );
define( 'WP_AUTO_UPDATE_CORE', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';