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
final class Clinic_Contact_Form {

	private const NONCE   = 'cc_submit';
	private const HONEY   = 'cc_website';
	private const MIN_SEC = 3;

	/** 同一の送信元から、この回数までを RL_WINDOW 秒あたりで許す。 */
	private const RL_MAX    = 3;
	private const RL_WINDOW = 600;

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
		wp_register_style( 'clinic-contact', CLINIC_CONTACT_URL . 'assets/contact.css', array(), CLINIC_CONTACT_VERSION );
	}

	/**
	 * 送信の受け取り。POST は描画前に処理してリダイレクトしない。
	 * PRG にすると入力内容を戻せないので、同一 URL で結果を出し、
	 * 弾いたときは self::$input をフォームへ書き戻す。
	 */
	public static function handle(): void {
		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) )
			: '';
		if ( 'POST' !== $method ) {
			return;
		}
		if ( ! isset( $_POST['cc_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['cc_nonce'] ) ), self::NONCE ) ) {
			self::$notice = array(
				'type' => 'error',
				'text' => __( '送信の有効期限が切れました。お手数ですが、もう一度お試しください。', 'clinic-contact' ),
			);
			return;
		}

		// ハニーポット：人には見えない欄に入力があれば機械とみなす。
		$honey = isset( $_POST[ self::HONEY ] )
			? sanitize_text_field( wp_unslash( (string) $_POST[ self::HONEY ] ) )
			: '';
		if ( '' !== trim( $honey ) ) {
			self::$notice = array(
				'type' => 'ok',
				'text' => __( '送信しました。', 'clinic-contact' ),
			);
			return;
		}

		/*
		 * 時間トラップ：表示から極端に短い送信は機械とみなす。
		 *
		 * cc_started が無い送信も同じ扱いにする。以前は $started > 0 を
		 * 条件にしていたため、この欄を送らなければトラップを丸ごと
		 * 回避できた。素朴な機械ほど欄を落とす。
		 *
		 * なお cc_started はただの hidden なので値は偽装できる。
		 * これは素朴な機械を落とすためのもので、これ自体は防御にならない。
		 * 回数の歯止めはレート制限のほうで持つ。
		 */
		$started = absint( $_POST['cc_started'] ?? 0 );
		if ( $started <= 0 || ( time() - $started ) < self::MIN_SEC ) {
			self::$notice = array(
				'type' => 'ok',
				'text' => __( '送信しました。', 'clinic-contact' ),
			);
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

		/*
		 * 長さはサーバ側でも見る。maxlength は入力の案内でしかなく、
		 * POST を直接組み立てれば効かない。
		 * お名前は Reply-To ヘッダに載るので特に縛る。
		 * sanitize_text_field が改行を落とすためヘッダの注入はできないが、
		 * 極端に長い値はヘッダを壊す。
		 */
		$errors = array();
		if ( '' === $name ) {
			$errors[] = __( 'お名前をご記入ください。', 'clinic-contact' );
		} elseif ( mb_strlen( $name ) > 100 ) {
			$errors[] = __( 'お名前が長すぎます。100文字以内でお願いします。', 'clinic-contact' );
		}
		if ( '' === $email || ! is_email( $email ) ) {
			$errors[] = __( 'メールアドレスをご確認ください。', 'clinic-contact' );
		}
		if ( mb_strlen( $tel ) > 30 ) {
			$errors[] = __( '電話番号が長すぎます。', 'clinic-contact' );
		}
		if ( mb_strlen( $message ) < 5 ) {
			$errors[] = __( 'ご相談内容を5文字以上でご記入ください。', 'clinic-contact' );
		}
		if ( mb_strlen( $message ) > 2000 ) {
			$errors[] = __( 'ご相談内容が長すぎます。2000文字以内でお願いします。', 'clinic-contact' );
		}

		if ( array() !== $errors ) {
			self::$notice = array(
				'type' => 'error',
				'text' => implode( ' ', $errors ),
			);
			return;
		}

		// 送信の直前に見る。書き直しのたびに枠を消費させないため、
		// 検証エラーでは数えない。
		if ( self::is_rate_limited() ) {
			self::$notice = array(
				'type' => 'error',
				'text' => __( '短時間に続けて送信されています。しばらく時間をおいてからお試しください。お急ぎの場合はお電話でご連絡ください。', 'clinic-contact' ),
			);
			return;
		}
		self::count_attempt();

		self::$notice = self::send( $name, $email, $tel, $message );

		// 送れたら控えを捨てる。残すと同じ内容を二重送信させてしまう。
		if ( 'ok' === self::$notice['type'] ) {
			self::$input = array(
				'name'    => '',
				'email'   => '',
				'tel'     => '',
				'message' => '',
			);
		}
	}

	/**
	 * 送信元ごとの計数キー。
	 *
	 * REMOTE_ADDR をそのまま使う。このサイトはリバースプロキシや CDN の
	 * 内側にいないため、これが実際の接続元になる。
	 * 将来その前段を挟むなら X-Forwarded-For を見る必要があるが、
	 * 検証せずに信じると誰でも偽装できるので、そのときは信頼できる
	 * プロキシの範囲を決めたうえで扱うこと。
	 */
	private static function rate_key(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) )
			: '';

		// 形の壊れた値でキーを作らない。取れなければ一つの枠にまとめる。
		if ( '' === $ip || false === filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = 'unknown';
		}

		return 'cc_rl_' . md5( $ip );
	}

	/**
	 * 送信が多すぎないか。
	 *
	 * nonce は使い捨てではなく十数時間有効なので、1回取得できれば
	 * 時間トラップを越えたあと何度でも投稿できてしまう。
	 * ハニーポットと時間トラップは素朴な機械を落とすだけで、回数は縛れない。
	 * 実在の SMTP アカウントで送っている以上、踏み台にされると
	 * ドメインの評判に響くため、回数そのものを制限する。
	 */
	private static function is_rate_limited(): bool {
		return (int) get_transient( self::rate_key() ) >= self::RL_MAX;
	}

	/**
	 * 送信の試行を数える。
	 *
	 * set_transient は既存キーに書くと期限も引き直すため、送り続けている間は
	 * 枠が空かない。正規の利用者は 10 分に 3 通も送らないので、
	 * 厳しすぎるより漏らさないほうを選んでいる。
	 */
	private static function count_attempt(): void {
		$key = self::rate_key();
		set_transient( $key, (int) get_transient( $key ) + 1, self::RL_WINDOW );
	}

	/**
	 * 送信。宛先はサイト管理者。返信先を相談者にする。
	 *
	 * @param string $name    お名前。
	 * @param string $email   メールアドレス。
	 * @param string $tel     電話番号。空でもよい。
	 * @param string $message ご相談内容。
	 * @return array{type: string, text: string}
	 */
	private static function send( string $name, string $email, string $tel, string $message ): array {
		if ( ! Clinic_Contact_Mailer::is_configured() ) {
			// 設定漏れに気づけるようログに残す。mail() へ落として黙って劣化させない。
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
	 * フォームの描画。このショートコードは属性を取らない。
	 */
	public static function render(): string {
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
