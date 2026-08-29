<?php
/**
 * 送信経路
 *
 * このドメインは SPF を `-all`、DMARC を `p=reject` で運用している。
 * Web サーバから PHP の mail() で直送すると送信元 IP が SPF に含まれず
 * 受信側で reject されるため、必ず認証つき SMTP を経由させる。
 *
 * 認証情報はコードに書かず wp-config.php の定数から読む。
 *
 * @package Clinic_Contact
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * PHPMailer を SMTP に切り替える。
 */
final class Clinic_Contact_Mailer {

	/**
	 * フック登録。
	 */
	public static function init(): void {
		add_action( 'phpmailer_init', array( __CLASS__, 'configure' ) );
		add_filter( 'wp_mail_from', array( __CLASS__, 'from_address' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
		add_action( 'wp_mail_failed', array( __CLASS__, 'log_failure' ) );
	}

	/**
	 * 設定が揃っているか。
	 */
	public static function is_configured(): bool {
		return defined( 'CLINIC_SMTP_HOST' )
			&& defined( 'CLINIC_SMTP_USER' )
			&& defined( 'CLINIC_SMTP_PASS' )
			&& '' !== (string) constant( 'CLINIC_SMTP_PASS' );
	}

	/**
	 * PHPMailer を SMTP に設定する。
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer メーラー。
	 */
	public static function configure( $phpmailer ): void {
		if ( ! self::is_configured() ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = (string) constant( 'CLINIC_SMTP_HOST' );
		$phpmailer->Port       = defined( 'CLINIC_SMTP_PORT' ) ? (int) constant( 'CLINIC_SMTP_PORT' ) : 465;
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = (string) constant( 'CLINIC_SMTP_USER' );
		$phpmailer->Password   = (string) constant( 'CLINIC_SMTP_PASS' );
		$phpmailer->SMTPSecure = 465 === $phpmailer->Port ? 'ssl' : 'tls';
		$phpmailer->CharSet    = 'UTF-8';
		$phpmailer->Timeout    = 15;
	}

	/**
	 * 送信元アドレス。SPF に含まれるドメインで送る。
	 *
	 * @param string $from WordPress が渡す既定の差出人。
	 */
	public static function from_address( string $from ): string {
		// SMTP AUTH のログインIDと差出人アドレスは別物。
		// メールサーバーによってはログインIDがローカル部だけ（例: 'info'）で、
		// 差出人は SPF/DKIM を通すために完全なアドレスである必要がある。
		// そのため CLINIC_SMTP_USER と CLINIC_SMTP_FROM を分けている。
		if ( defined( 'CLINIC_SMTP_FROM' ) ) {
			return (string) constant( 'CLINIC_SMTP_FROM' );
		}
		return $from;
	}

	/**
	 * 送信者名。サイト名を使い、空ならもとの値を残す。
	 *
	 * @param string $name WordPress が渡す既定の送信者名。
	 */
	public static function from_name( string $name ): string {
		$blogname = trim( (string) get_bloginfo( 'name' ) );

		return '' !== $blogname ? $blogname : $name;
	}

	/**
	 * 失敗をログに残す。理由が分からないまま握り潰さない。
	 *
	 * @param WP_Error $error エラー。
	 */
	public static function log_failure( $error ): void {
		if ( $error instanceof WP_Error ) {
			// 送信の失敗は握り潰さない。管理画面に出す先が無いのでサーバのログに残す。
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[clinic-contact] wp_mail failed: ' . $error->get_error_message() );
		}
	}
}
