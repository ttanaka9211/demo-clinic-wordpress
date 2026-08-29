<?php
/**
 * OGP / favicon
 *
 * 提案時にURLを渡して開いてもらう前提なので、共有時の見え方を用意する。
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

/**
 * OGP と Twitter Card を出力する。
 */
function sakura_clinic_meta_tags(): void {
	$title = get_bloginfo( 'name' );
	$desc  = sakura_clinic_get( 'hero_lead' );
	$url   = home_url( '/' );

	$image_id  = (int) get_theme_mod( 'hero_image', 0 );
	$image_url = $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, 'full' ) : '';

	printf( '<meta property="og:type" content="website">%s', "\n" );
	printf( '<meta property="og:site_name" content="%s">%s', esc_attr( $title ), "\n" );
	printf( '<meta property="og:title" content="%s">%s', esc_attr( $title ), "\n" );
	printf( '<meta property="og:description" content="%s">%s', esc_attr( $desc ), "\n" );
	printf( '<meta property="og:url" content="%s">%s', esc_url( $url ), "\n" );
	printf( '<meta property="og:locale" content="ja_JP">%s', "\n" );

	if ( '' !== $image_url ) {
		printf( '<meta property="og:image" content="%s">%s', esc_url( $image_url ), "\n" );
		printf( '<meta name="twitter:card" content="summary_large_image">%s', "\n" );
	} else {
		printf( '<meta name="twitter:card" content="summary">%s', "\n" );
	}

	printf( '<meta name="description" content="%s">%s', esc_attr( $desc ), "\n" );
	printf( '<meta name="theme-color" content="#2d6a4f">%s', "\n" );
}
add_action( 'wp_head', 'sakura_clinic_meta_tags', 5 );

/**
 * SVG の favicon をインラインで出す。
 * カスタムアイコンが未設定のときだけ使う。
 */
function sakura_clinic_favicon(): void {
	if ( has_site_icon() ) {
		return;
	}

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">'
		. '<rect width="32" height="32" rx="7" fill="#2d6a4f"/>'
		. '<g fill="none" stroke="#faf8f4" stroke-width="1.8" stroke-linecap="round">'
		. '<path d="M16 7c-2.6 0-3.9 1.7-3.9 3.5S13.4 13.3 16 13.3s3.9.9 3.9 2.6-1.3 3.5-3.9 3.5-3.9 1.7-3.9 3.5S13.4 26 16 26"/>'
		. '<path d="M11 10h2.4M18.6 13.3H21M11 16.4h2.4M18.6 19.5H21M11 22.6h2.4"/>'
		. '</g></svg>';

	printf(
		'<link rel="icon" href="data:image/svg+xml,%s">%s',
		rawurlencode( $svg ),
		"\n"
	);
}
add_action( 'wp_head', 'sakura_clinic_favicon', 6 );
