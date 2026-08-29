<?php
/**
 * Sakura Clinic テーマ
 *
 * 架空クライアント（さくら整骨院）向けのサンプル作品。
 * 料金シミュレーターはテーマに含めず、独立したプラグイン
 * clinic-fee-simulator が提供するショートコードを使う。
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

define( 'SAKURA_CLINIC_VERSION', '2.0.0' );

require_once get_template_directory() . '/inc/icons.php';
require_once get_template_directory() . '/inc/illustrations.php';
require_once get_template_directory() . '/inc/meta.php';

/**
 * テーマがサポートする機能。
 */
function sakura_clinic_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'customize-selective-refresh-widgets' );
	load_theme_textdomain( 'sakura-clinic', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'sakura_clinic_setup' );

/**
 * スタイルの読み込み。ベタ書きせず必ず wp_enqueue_* を通す。
 */
function sakura_clinic_assets(): void {
	/*
	 * 外部の CSS にバージョンを付けない。?ver= を足すと Google 側の
	 * キャッシュから外れるだけで、こちらが更新できるものでもない。
	 */
	wp_enqueue_style(
		'sakura-clinic-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);
	wp_enqueue_style(
		'sakura-clinic',
		get_stylesheet_uri(),
		array( 'sakura-clinic-fonts' ),
		SAKURA_CLINIC_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'sakura_clinic_assets' );

/**
 * Google Fonts への事前接続（表示速度対策）。
 *
 * @param string[] $urls     この関係の URL 一覧。
 * @param string   $relation 関係の種類。
 * @return string[]
 */
function sakura_clinic_resource_hints( array $urls, string $relation ): array {
	if ( 'preconnect' === $relation ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'sakura_clinic_resource_hints', 10, 2 );

/**
 * カスタマイザーで編集できる項目。
 *
 * 「治療」は医療機関を想起させる語のため既定文では使わず「施術」に統一する
 * （柔道整復の広告ガイドラインを踏まえた判断）。
 *
 * @return array<string, array{label: string, default: string, type?: string}>
 */
function sakura_clinic_fields(): array {
	return array(
		'clinic_sub'    => array(
			'label'   => '院名の英字表記',
			'default' => 'SAKURA SEIKOTSUIN',
		),
		'clinic_tel'    => array(
			'label'   => '電話番号',
			'default' => '06-0000-0000',
		),
		'clinic_hours'  => array(
			'label'   => '受付時間',
			'default' => "平日 9:00〜20:00\n土日 9:00〜17:00（木曜休）",
			'type'    => 'textarea',
		),
		'clinic_addr'   => array(
			'label'   => '所在地',
			'default' => '大阪市北区○○ 1-2-3 ○○ビル 2F',
		),
		'clinic_access' => array(
			'label'   => 'アクセス',
			'default' => '各線○○駅 5番出口から徒歩5分',
		),
		'clinic_park'   => array(
			'label'   => '駐車場',
			'default' => '提携駐車場あり（2時間無料）',
		),

		'hero_badge'    => array(
			'label'   => 'ヒーロー：バッジ',
			'default' => '交通事故・自賠責保険に対応',
		),
		'hero_title'    => array(
			'label'   => 'ヒーロー：見出し',
			'default' => "その痛み、\n我慢しなくて大丈夫です。",
			'type'    => 'textarea',
		),
		'hero_lead'     => array(
			'label'   => 'ヒーロー：本文',
			'default' => '交通事故のあとの首や腰の不調に、自賠責保険を使った施術で対応しています。保険会社とのやりとりや書類のご相談も承ります。',
			'type'    => 'textarea',
		),

		'cta_title'     => array(
			'label'   => '最終CTA：見出し',
			'default' => 'まずは、お電話でご相談ください',
		),
		'cta_lead'      => array(
			'label'   => '最終CTA：本文',
			'default' => 'シミュレーターの結果をもとに、通院の頻度や進め方をご案内します。ご相談だけでもかまいません。',
			'type'    => 'textarea',
		),

		'sample_notice' => array(
			'label'   => 'サンプル作品の注記',
			'default' => 'このサイトは架空のクライアント「さくら整骨院」を想定して制作したサンプル作品です。実在の院ではありません。',
			'type'    => 'textarea',
		),
	);
}

/**
 * 設定値の取得ヘルパー。
 *
 * @param string $key 設定キー。
 */
function sakura_clinic_get( string $key ): string {
	$fields = sakura_clinic_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}
	return (string) get_theme_mod( $key, $fields[ $key ]['default'] );
}

/**
 * カスタマイザーへの登録。
 * sanitize_callback は必須（未指定だと WordPress が保存を拒否する）。
 *
 * @param WP_Customize_Manager $wp_customize カスタマイザー。
 */
function sakura_clinic_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'sakura_clinic_basic',
		array(
			'title'       => __( '院の情報・LPの文言', 'sakura-clinic' ),
			'priority'    => 30,
			'description' => __( '電話番号や見出しをここから変更できます。', 'sakura-clinic' ),
		)
	);

	foreach ( sakura_clinic_fields() as $key => $field ) {
		$is_textarea = isset( $field['type'] ) && 'textarea' === $field['type'];

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field['default'],
				'transport'         => 'refresh',
				'sanitize_callback' => $is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'section' => 'sakura_clinic_basic',
				'type'    => $is_textarea ? 'textarea' : 'text',
			)
		);
	}

	// 写真を入れたくなったときの差し替え口。未設定なら線画を使う。
	$wp_customize->add_setting(
		'hero_image',
		array(
			'default'           => '',
			'transport'         => 'refresh',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'hero_image',
			array(
				'label'       => __( 'ヒーローの画像', 'sakura-clinic' ),
				'description' => __( '未設定の場合は線画イラストを表示します。', 'sakura-clinic' ),
				'section'     => 'sakura_clinic_basic',
				'mime_type'   => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'sakura_clinic_customize_register' );

/**
 * 電話番号を tel: リンク用に整形する。
 */
function sakura_clinic_tel_href(): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', sakura_clinic_get( 'clinic_tel' ) );
}

/**
 * 改行を <br> にしつつエスケープする（見出し用）。
 *
 * @param string $text 対象の文字列。
 */
function sakura_clinic_nl2br_esc( string $text ): string {
	return wp_kses( nl2br( esc_html( $text ) ), array( 'br' => array() ) );
}

/**
 * ヒーローのビジュアル。画像が設定されていればそれを、なければ線画を返す。
 */
function sakura_clinic_hero_visual(): string {
	$image_id = (int) get_theme_mod( 'hero_image', 0 );

	if ( $image_id > 0 ) {
		$html = wp_get_attachment_image(
			$image_id,
			'large',
			false,
			array(
				'class'    => 'hero__photo',
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
		if ( '' !== $html ) {
			return $html;
		}
	}

	return sakura_clinic_hero_art( 'hero__art-svg' );
}

/**
 * head からバージョン情報と不要な自動出力を落とす。
 *
 * WordPress のバージョンが分かると、そのバージョン向けの既知の脆弱性を
 * 狙い撃ちされる。攻撃を防ぐわけではないが、機械的な走査の対象からは外れる。
 * xmlrpc は nginx 側で 403 にしてあるので、RSD リンクも出す意味がない。
 */
function sakura_clinic_clean_head(): void {
	remove_action( 'wp_head', 'wp_generator' );                        // <meta name="generator">
	remove_action( 'wp_head', 'rsd_link' );                            // xmlrpc への RSD リンク
	remove_action( 'wp_head', 'wlwmanifest_link' );                    // Windows Live Writer 用（現在は不要）
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );                 // 投稿を使わないサイトなのでフィードは出さない
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'sakura_clinic_clean_head' );

/**
 * 生成される CSS/JS の URL から ?ver=<WPバージョン> を落とす。
 * テーマ側で明示的に付けたバージョンは残す。
 *
 * @param string $src 対象の URL。
 */
function sakura_clinic_strip_core_ver( string $src ): string {
	$wp_version = get_bloginfo( 'version' );

	if ( '' !== $wp_version && str_contains( $src, 'ver=' . $wp_version ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'sakura_clinic_strip_core_ver', 20 );
add_filter( 'script_loader_src', 'sakura_clinic_strip_core_ver', 20 );

/**
 * ログイン失敗時のメッセージを一律にする。
 * 既定では「ユーザー名が違う」「パスワードが違う」を区別して返すため、
 * 総当たりの前段でユーザー名の存在確認に使えてしまう。
 */
function sakura_clinic_generic_login_error(): string {
	return __( 'ログイン情報が正しくありません。', 'sakura-clinic' );
}
add_filter( 'login_errors', 'sakura_clinic_generic_login_error' );

/**
 * 表側では管理バーを出さない。
 *
 * ポートフォリオ一覧がこのサイトを iframe で埋め込んでいるため、
 * 制作者がログインしたまま客先に見せると、プレビューの上端に
 * 管理バーが写り込む。表示を見せる用途のサイトなので常に隠す。
 */
add_filter( 'show_admin_bar', '__return_false' );
