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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'GS(dMX0N)jtRQUhg,cz+Z:Fy}@*e,k0|BV;U|o@hg]Tl~}|mK@_$kJ$v&{aXm}x' );
define( 'SECURE_AUTH_KEY',  'oy-$J/fVNyo>4UWbF)(+eiA5S#d!k3*G@[=E@W>jV~P.cL/[Yy{^mxnT~:rkD.O!' );
define( 'LOGGED_IN_KEY',    'X#lHo(i@ij&.5LB-+}lf,)r:9<}-@AXM>SFy;#.p,^pMa%^=$4~H-/u[p>LYk-k:' );
define( 'NONCE_KEY',        'vJg@{r#@|IQRU4_P5QE?N7-nk6-sI<~<2sAe_d-9S4{v[z3A[^Ht2:#F_Y*Vx.Ig' );
define( 'AUTH_SALT',        'ym>sS]@+B3x?}D+o8oYmH._-V.;L5}eP*wOjc&^@%H<j:qr$+~u 7y*-|&m#+=f0' );
define( 'SECURE_AUTH_SALT', 'G6cJsF*J.JfJ#n(/l/dfs|8|3Z/Bx/A$_$%|[I@`-w1g=4]._z,xpT8|tZd&o8&5' );
define( 'LOGGED_IN_SALT',   'i}6$U<P$;g|8aAB9~cz&NN#AV{oa$1{;I.M4p%UR]&S.X.0-.MIZ?K~|z9vu@R!j' );
define( 'NONCE_SALT',       'M=5=*>xrS4-e-Zh:@aZo$<&ZCWg,a2>vNI-w_Dd1D.EY3fJf-?F7lBc>SgE5?A>M' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */

// For development environment, skip database connection
define('WP_SETUP_CONFIG', true);
define('WP_DEBUG_DISPLAY', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';