<?php
/**
 * ヘッダー
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( '本文へスキップ', 'sakura-clinic' ); ?></a>

<header class="site-header">
	<div class="container container--lg site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php echo sakura_clinic_brand_mark( 'site-header__mark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="site-header__name">
				<?php bloginfo( 'name' ); ?>
				<span class="site-header__sub"><?php echo esc_html( sakura_clinic_get( 'clinic_sub' ) ); ?></span>
			</span>
		</a>

		<a class="site-header__tel" href="<?php echo esc_url( sakura_clinic_tel_href() ); ?>">
			<?php echo esc_html( sakura_clinic_get( 'clinic_tel' ) ); ?>
		</a>

		<a class="btn btn--primary btn--sm" href="#contact">
			<?php echo sakura_clinic_icon( 'phone', 'btn__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php esc_html_e( '相談する', 'sakura-clinic' ); ?>
		</a>
	</div>
</header>

<main id="main">
