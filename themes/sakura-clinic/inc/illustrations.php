<?php
/**
 * 線画イラスト
 *
 * ストック写真を使わず自作の線画で構成する。権利関係が明確になり、
 * 外部アセットへの依存も生まれない。写真を入れたい場合は
 * カスタマイザーの画像スロットから差し替えられる。
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

/**
 * ブランドマーク（背骨をモチーフにした記号）。
 *
 * @param string $class_name 付与する class 属性。
 */
function sakura_clinic_brand_mark( string $class_name = '' ): string {
	return sprintf(
		'<svg class="%s" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true" focusable="false">
			<path d="M16 4c-3 0-4.5 2-4.5 4S13 11 16 11s4.5 1 4.5 3-1.5 4-4.5 4-4.5 2-4.5 4 1.5 4 4.5 4"/>
			<path d="M10 7.5h3M19 11h3M10 15h3M19 18.5h3M10 22h3"/>
		</svg>',
		esc_attr( $class_name )
	);
}

/**
 * ヒーローの線画（院の外観）。
 *
 * @param string $class_name 付与する class 属性。
 */
function sakura_clinic_hero_art( string $class_name = '' ): string {
	return sprintf(
		'<svg class="%s" viewBox="0 0 400 300" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="%s" focusable="false">
			<path d="M60 130 L200 58 L340 130" />
			<path d="M84 118v140h232V118" />
			<path d="M60 258h280" stroke-width="1.8" />
			<rect x="120" y="160" width="64" height="52" rx="3" />
			<path d="M152 160v52M120 186h64" />
			<rect x="216" y="160" width="64" height="52" rx="3" />
			<path d="M248 160v52M216 186h64" />
			<path d="M176 258v-36a24 24 0 0 1 48 0v36" />
			<circle cx="212" cy="240" r="2.4" fill="currentColor" stroke="none" />
			<path d="M150 108h100M164 96h72" opacity=".55" />
			<path d="M44 258c0-16 8-28 18-28s18 12 18 28" opacity=".5" />
			<path d="M62 230v28" opacity=".5" />
			<path d="M320 258c0-16 8-28 18-28s18 12 18 28" opacity=".5" />
			<path d="M338 230v28" opacity=".5" />
			<path d="M300 74c6-10 18-10 24 0s-6 18-12 24c-6-6-18-14-12-24z" opacity=".45" />
		</svg>',
		esc_attr( $class_name ),
		esc_attr__( '院の外観のイラスト', 'sakura-clinic' )
	);
}

/**
 * アクセスの略図。
 *
 * 架空の住所なので実地図は載せられない。駅からの位置関係を示す
 * 概念図にすることで、正確でない情報を地図として提示することを避ける。
 *
 * @param string $class_name 付与する class 属性。
 */
function sakura_clinic_access_map( string $class_name = '' ): string {
	return sprintf(
		'<svg class="%s" viewBox="0 0 420 240" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="%s" focusable="false">
			<rect x="1" y="1" width="418" height="238" rx="10" fill="#faf8f4" stroke="#e2ddd2" stroke-width="1"/>
			<path d="M0 148h420" stroke="#e2ddd2" stroke-width="14"/>
			<path d="M0 148h420" stroke="#faf8f4" stroke-width="1.5" stroke-dasharray="10 10"/>
			<path d="M196 0v240" stroke="#e2ddd2" stroke-width="10"/>
			<g stroke="#52796f" stroke-width="1.4">
				<rect x="42" y="96" width="86" height="42" rx="5" fill="#fff"/>
				<path d="M42 116h86"/>
				<circle cx="62" cy="127" r="3"/><circle cx="108" cy="127" r="3"/>
			</g>
			<text x="85" y="86" text-anchor="middle" font-size="12" fill="#33322e" stroke="none" font-weight="700">○○駅</text>
			<text x="85" y="158" text-anchor="middle" font-size="10" fill="#7d7a72" stroke="none">5番出口</text>
			<path d="M128 137 L196 137 L196 74 L286 74" stroke="#c9713f" stroke-width="2.5" stroke-dasharray="7 7"/>
			<text x="205" y="112" font-size="10" fill="#c9713f" stroke="none">徒歩5分</text>
			<g stroke="#2d6a4f" stroke-width="1.6">
				<path d="M286 88V52l24-14 24 14v36z" fill="#fff"/>
				<path d="M300 88V68h20v20"/>
			</g>
			<text x="310" y="106" text-anchor="middle" font-size="12" fill="#2d6a4f" stroke="none" font-weight="700">当院</text>
			<g stroke="#b9b3a6" stroke-width="1.2">
				<rect x="286" y="168" width="58" height="34" rx="4" fill="#fff"/>
				<path d="M296 185h38M305 168v34"/>
			</g>
			<text x="315" y="216" text-anchor="middle" font-size="9" fill="#7d7a72" stroke="none">提携駐車場</text>
		</svg>',
		esc_attr( $class_name ),
		esc_attr__( '駅から当院までの位置関係を示した略図', 'sakura-clinic' )
	);
}
