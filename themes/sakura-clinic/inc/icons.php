<?php
/**
 * SVG 線画アイコン
 *
 * 絵文字は環境ごとに描画が変わりサイズも揃わないため使わない。
 * currentColor で塗るので配色は CSS 側から制御する。
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

/**
 * アイコンのパス定義。
 *
 * @return array<string, string>
 */
function sakura_clinic_icon_paths(): array {
	return array(
		'hands'    => '<path d="M8 13V5.5a1.5 1.5 0 0 1 3 0V12"/><path d="M11 12V4.5a1.5 1.5 0 0 1 3 0V12"/><path d="M14 12V6.5a1.5 1.5 0 0 1 3 0V13"/><path d="M17 8.5a1.5 1.5 0 0 1 3 0V16a5 5 0 0 1-5 5h-2a7 7 0 0 1-7-7v-3.5a1.5 1.5 0 0 1 3 0"/>',
		'car'      => '<path d="M5 17h14"/><path d="M6.5 17v2.5"/><path d="M17.5 17v2.5"/><path d="M3 13l1.6-4.8A2 2 0 0 1 6.5 7h11a2 2 0 0 1 1.9 1.2L21 13v4H3z"/><circle cx="7.5" cy="14.5" r="1"/><circle cx="16.5" cy="14.5" r="1"/>',
		'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h4"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'train'    => '<rect x="6" y="3" width="12" height="13" rx="3"/><path d="M6 10h12"/><circle cx="9.5" cy="13" r=".8"/><circle cx="14.5" cy="13" r=".8"/><path d="M8.5 16l-2 4"/><path d="M15.5 16l2 4"/>',
		'phone'    => '<path d="M6.5 2h3l1.5 4-2 1.5a12 12 0 0 0 5.5 5.5L16 11l4 1.5v3a1.5 1.5 0 0 1-1.6 1.5C10 16.4 1.6 8 2 -0.4"/>',
		'check'    => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
		'question' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.4 2.3c-.6.3-.9.8-.9 1.4v.4"/><path d="M12 17h.01"/>',
		'pin'      => '<path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'yen'      => '<circle cx="12" cy="12" r="9"/><path d="M9 8.5l3 3.5 3-3.5"/><path d="M9.5 13h5"/><path d="M9.5 15.5h5"/><path d="M12 12v4.5"/>',
	);
}

/**
 * アイコンを出力する。装飾目的なので aria-hidden にする。
 *
 * @param string $name       アイコン名。
 * @param string $class_name 付与する class 属性。
 */
function sakura_clinic_icon( string $name, string $class_name = '' ): string {
	$paths = sakura_clinic_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class_name ),
		$paths[ $name ]
	);
}
