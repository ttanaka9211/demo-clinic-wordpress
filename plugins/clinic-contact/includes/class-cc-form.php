<?php
/**
 * 問い合わせフォーム [clinic_contact]
 *
 * @package Clinic_Contact
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * フォームの描画と受信処理。
 */
final class CC_Form {

	private const NONCE   = 'cc_submit';
	private const HONEY   = 'cc_website';
	private const MIN_SEC = 3;

	/**
	 * 画面に出すメッセージ。
	 *
	 * @var array{type: string, text: string}|null
	 */
	private static ?array $notice = null;

	/**
	 * 直前の入力。検証で弾いたときにフォームへ戻すために持つ。
	 *
	 * @var array<string, string>
	 */
	private static array $input = array(
		'name'    => '',
		'email'   => '',
		'tel'     => '',
		'message' => '',
	);

	/**
	 * フック登録。
	 */
	public static function init(): void {
		add_shortcode( 'clinic_contact', array( __CLASS__, 'render' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * アセット登録。
	 */
	public static function register_assets(): void {
		wp_register_style( 'clinic-contact', CC_URL . 'assets/contact.css', array(), CC_VERSION );
	}

	/**
	 * 送信の受け取り。POST は描画前に処理してリダイレクトしない。
	 * PRG にすると入力内容を戻せないので、同一 URL で結果を出し、
	 * 弾いたときは self::$input をフォームへ書き戻す。
	 */
	public static function handle(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}
		if ( ! isset( $_POST['cc_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['cc_nonce'] ) ), self::NONCE ) ) {
			self::$notice = array( 'type' => 'error', 'text' => __( '送信の有効期限が切れました。お手数ですが、もう一度お試しください。', 'clinic-contact' ) );
			return;
		}

		// ハニーポット：人には見えない欄に入力があれば機械とみなす。
		if ( '' !== trim( (string) ( $_POST[ self::HONEY ] ?? '' ) ) ) {
			self::$notice = array( 'type' => 'ok', 'text' => __( '送信しました。', 'clinic-contact' ) );
			return;
		}

		// 時間トラップ：表示から極端に短い送信は機械とみなす。
		$started = absint( $_POST['cc_started'] ?? 0 );
		if ( $started > 0 && ( time() - $started ) < self::MIN_SEC ) {
			self::$notice = array( 'type' => 'ok', 'text' => __( '送信しました。', 'clinic-contact' ) );
			return;
		}

		$name    = sanitize_text_field( wp_unslash( (string) ( $_POST['cc_name'] ?? '' ) ) );
		$email   = sanitize_email( wp_unslash( (string) ( $_POST['cc_email'] ?? '' ) ) );
		$tel     = sanitize_text_field( wp_unslash( (string) ( $_POST['cc_tel'] ?? '' ) ) );
		$message = sanitize_textarea_field( wp_unslash( (string) ( $_POST['cc_message'] ?? '' ) ) );

		// 検証で弾いたときに書き直させないよう、入力をこの時点で控える。
		self::$input = array(
			'name'    => $name,
			'email'   => $email,
			'tel'     => $tel,
			'message' => $message,
		);

		$errors = array();
		if ( '' === $name ) {
			$errors[] = __( 'お名前をご記入ください。', 'clinic-contact' );
		}
		if ( '' === $email || ! is_email( $email ) ) {
			$errors[] = __( 'メールアドレスをご確認ください。', 'clinic-contact' );
		}
		if ( mb_strlen( $message ) < 5 ) {
			$errors[] = __( 'ご相談内容を5文字以上でご記入ください。', 'clinic-contact' );
		}
		if ( mb_strlen( $message ) > 2000 ) {
			$errors[] = __( 'ご相談内容が長すぎます。2000文字以内でお願いします。', 'clinic-contact' );
		}

		if ( array() !== $errors ) {
			self::$notice = array( 'type' => 'error', 'text' => implode( ' ', $errors ) );
			return;
		}

		self::$notice = self::send( $name, $email, $tel, $message );

		// 送れたら控えを捨てる。残すと同じ内容を二重送信させてしまう。
		if ( 'ok' === self::$notice['type'] ) {
			self::$input = array( 'name' => '', 'email' => '', 'tel' => '', 'message' => '' );
		}
	}

	/**
	 * 送信。宛先はサイト管理者。返信先を相談者にする。
	 *
	 * @return array{type: string, text: string}
	 */
	private static function send( string $name, string $email, string $tel, string $message ): array {
		if ( ! CC_Mailer::is_configured() ) {
			error_log( '[clinic-contact] SMTP constants are missing; refusing to send via mail().' );
			return array(
				'type' => 'error',
				'text' => __( '現在、送信の設定に問題があります。お手数ですがお電話でご連絡ください。', 'clinic-contact' ),
			);
		}

		$to      = (string) get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: %s: サイト名 */
			__( '[%s] サイトからのご相談', 'clinic-contact' ),
			(string) get_bloginfo( 'name' )
		);

		$body = implode(
			"\n",
			array(
				__( 'サイトのフォームから相談が届きました。', 'clinic-contact' ),
				'',
				__( 'お名前: ', 'clinic-contact' ) . $name,
				__( 'メール: ', 'clinic-contact' ) . $email,
				__( '電話: ', 'clinic-contact' ) . ( '' !== $tel ? $tel : '—' ),
				'',
				__( '── ご相談内容 ──', 'clinic-contact' ),
				$message,
				'',
				'──',
				__( '送信元: ', 'clinic-contact' ) . home_url( '/' ),
				__( '受信日時: ', 'clinic-contact' ) . wp_date( 'Y-m-d H:i' ),
			)
		);

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $name, $email ),
		);

		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( ! $sent ) {
			return array(
				'type' => 'error',
				'text' => __( '送信できませんでした。お手数ですがお電話でご連絡ください。', 'clinic-contact' ),
			);
		}

		return array(
			'type' => 'ok',
			'text' => __( 'ご相談を承りました。折り返しご連絡いたします。', 'clinic-contact' ),
		);
	}

	/**
	 * フォームの描画。
	 *
	 * @param array<string, string>|string $atts 属性。
	 */
	public static function render( $atts = array() ): string {
		wp_enqueue_style( 'clinic-contact' );

		ob_start();
		?>
		<div class="cc">
			<?php if ( null !== self::$notice ) : ?>
				<p class="cc__notice cc__notice--<?php echo esc_attr( self::$notice['type'] ); ?>" role="status">
					<?php echo esc_html( self::$notice['text'] ); ?>
				</p>
			<?php endif; ?>

			<?php
			/*
			 * action は空にして、いま表示している URL へそのまま送る。
			 * get_permalink() はフロントページが投稿一覧のとき
			 * ループ中の投稿の URL を返してしまい、別ページへ飛ぶ。
			 */
			?>
			<form class="cc__form" method="post">
				<?php wp_nonce_field( self::NONCE, 'cc_nonce' ); ?>
				<input type="hidden" name="cc_started" value="<?php echo esc_attr( (string) time() ); ?>">

				<p class="cc__row cc__row--trap" aria-hidden="true">
					<label for="<?php echo esc_attr( self::HONEY ); ?>"><?php esc_html_e( 'この欄は入力しないでください', 'clinic-contact' ); ?></label>
					<input type="text" name="<?php echo esc_attr( self::HONEY ); ?>" id="<?php echo esc_attr( self::HONEY ); ?>" tabindex="-1" autocomplete="off">
				</p>

				<p class="cc__row">
					<label for="cc_name"><?php esc_html_e( 'お名前', 'clinic-contact' ); ?> <span class="cc__req"><?php esc_html_e( '必須', 'clinic-contact' ); ?></span></label>
					<input type="text" name="cc_name" id="cc_name" required autocomplete="name" maxlength="100" value="<?php echo esc_attr( self::$input['name'] ); ?>">
				</p>
				<p class="cc__row">
					<label for="cc_email"><?php esc_html_e( 'メールアドレス', 'clinic-contact' ); ?> <span class="cc__req"><?php esc_html_e( '必須', 'clinic-contact' ); ?></span></label>
					<input type="email" name="cc_email" id="cc_email" required autocomplete="email" maxlength="254" value="<?php echo esc_attr( self::$input['email'] ); ?>">
				</p>
				<p class="cc__row">
					<label for="cc_tel"><?php esc_html_e( '電話番号（任意）', 'clinic-contact' ); ?></label>
					<input type="tel" name="cc_tel" id="cc_tel" autocomplete="tel" maxlength="30" value="<?php echo esc_attr( self::$input['tel'] ); ?>">
				</p>
				<p class="cc__row">
					<label for="cc_message"><?php esc_html_e( 'ご相談内容', 'clinic-contact' ); ?> <span class="cc__req"><?php esc_html_e( '必須', 'clinic-contact' ); ?></span></label>
					<textarea name="cc_message" id="cc_message" rows="5" required maxlength="2000"><?php echo esc_textarea( self::$input['message'] ); ?></textarea>
				</p>
				<p class="cc__actions">
					<button type="submit" class="cc__submit"><?php esc_html_e( '送信する', 'clinic-contact' ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
