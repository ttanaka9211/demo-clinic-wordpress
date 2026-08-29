<?php
/**
 * 設定（管理画面から料金表を編集する）
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * 設定画面と保存処理。
 */
final class Clinic_Fee_Simulator_Settings {

	/**
	 * 既定値。
	 *
	 * @return array<string, int|string>
	 */
	public static function defaults(): array {
		return array(
			'base_price'      => 3500,
			'symptom_surchg'  => 500,
			'symptom_thresh'  => 3,
			'weeks_per_month' => 4,
			'kenpo_rate'      => 30,
			'disclaimer'      => 'ここに表示される金額は目安です。実際の費用は保険の適用状況や施術内容によって変わります。詳しくはお電話でご確認ください。',
		);
	}

	/**
	 * 項目の定義。ラベル・種類・範囲をここだけで持つ。
	 *
	 * 以前はラベルが register()、範囲が sanitize()、min/max が render_field()
	 * にそれぞれ散っていた。そのため検証エラーの文面に内部キー
	 * （base_price など）がそのまま出ていて、画面の項目名と結びつかなかった。
	 *
	 * @return array<string, array{label: string, type: string, min?: int, max?: int}>
	 */
	private static function fields(): array {
		return array(
			'base_price'      => array(
				'label' => __( '1回あたりの基本施術料（円）', 'clinic-fee-simulator' ),
				'type'  => 'number',
				'min'   => 0,
				'max'   => 1000000,
			),
			'symptom_surchg'  => array(
				'label' => __( '症状が多い場合の加算（円）', 'clinic-fee-simulator' ),
				'type'  => 'number',
				'min'   => 0,
				'max'   => 1000000,
			),
			'symptom_thresh'  => array(
				'label' => __( '加算の対象となる症状の数', 'clinic-fee-simulator' ),
				'type'  => 'number',
				'min'   => 1,
				'max'   => 20,
			),
			'weeks_per_month' => array(
				'label' => __( '月あたりの週数', 'clinic-fee-simulator' ),
				'type'  => 'number',
				'min'   => 1,
				'max'   => 6,
			),
			'kenpo_rate'      => array(
				'label' => __( '健康保険の自己負担割合（％）', 'clinic-fee-simulator' ),
				'type'  => 'number',
				'min'   => 0,
				'max'   => 100,
			),
			'disclaimer'      => array(
				'label' => __( '注記', 'clinic-fee-simulator' ),
				'type'  => 'textarea',
			),
		);
	}

	/**
	 * 設定の取得。既定値とマージして欠損キーを埋める。
	 *
	 * @return array<string, int|string>
	 */
	public static function get(): array {
		$saved = get_option( CLINIC_FEE_SIMULATOR_OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( self::defaults(), $saved );
	}

	/**
	 * フック登録。
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * 管理メニューに追加。編集権限のある利用者だけに見せる。
	 */
	public static function add_page(): void {
		add_options_page(
			__( '料金シミュレーター', 'clinic-fee-simulator' ),
			__( '料金シミュレーター', 'clinic-fee-simulator' ),
			'manage_options',
			'clinic-fee-simulator',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * 設定の登録。sanitize_callback を必ず指定する。
	 */
	public static function register(): void {
		register_setting(
			'cfs_group',
			CLINIC_FEE_SIMULATOR_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'cfs_prices',
			__( '料金表', 'clinic-fee-simulator' ),
			static function (): void {
				echo '<p>' . esc_html__( 'ここを変更すると、サイト上のシミュレーターにそのまま反映されます。', 'clinic-fee-simulator' ) . '</p>';
			},
			'clinic-fee-simulator'
		);

		foreach ( self::fields() as $key => $field ) {
			add_settings_field(
				$key,
				$field['label'],
				array( __CLASS__, 'render_field' ),
				'clinic-fee-simulator',
				'cfs_prices',
				array( 'key' => $key ) + $field
			);
		}
	}

	/**
	 * 入力値の検証。おかしな値はその項目だけ元のまま据え置く。
	 *
	 * @param mixed $input 送信値。
	 * @return array<string, int|string>
	 */
	public static function sanitize( $input ): array {
		/*
		 * 土台は「いま保存されている値」。既定値ではない。
		 * ひとつの入力ミスで、前に設定した他の項目まで工場出荷値へ
		 * 巻き戻ってしまうため。
		 */
		$out = self::get();

		if ( ! is_array( $input ) ) {
			return $out;
		}

		foreach ( self::fields() as $key => $field ) {
			if ( 'number' !== $field['type'] || ! isset( $input[ $key ] ) ) {
				continue;
			}
			$min = (int) $field['min'];
			$max = (int) $field['max'];

			$raw = trim( (string) $input[ $key ] );

			/*
			 * absint() を使わない。'abc' を 0、'-500' を 500 に変えてしまい、
			 * 入力ミスが黙って別の値として保存される。
			 * base_price は最小値が 0 なので、'abc' が施術料 0 円として
			 * そのまま公開サイトに出ていた。
			 */
			if ( 1 !== preg_match( '/\A[0-9]+\z/', $raw ) ) {
				add_settings_error(
					CLINIC_FEE_SIMULATOR_OPTION,
					'cfs_' . $key,
					sprintf(
						/* translators: %s: 項目名 */
						__( '「%s」は半角数字で入力してください。この項目は変更していません。', 'clinic-fee-simulator' ),
						$field['label']
					),
					'error'
				);
				continue;
			}

			$value = (int) $raw;
			if ( $value < $min || $value > $max ) {
				add_settings_error(
					CLINIC_FEE_SIMULATOR_OPTION,
					'cfs_' . $key,
					sprintf(
						/* translators: 1: 項目名, 2: 最小値, 3: 最大値 */
						__( '「%1$s」は %2$s〜%3$s の範囲で入力してください。この項目は変更していません。', 'clinic-fee-simulator' ),
						$field['label'],
						number_format_i18n( $min ),
						number_format_i18n( $max )
					),
					'error'
				);
				continue;
			}
			$out[ $key ] = $value;
		}

		if ( isset( $input['disclaimer'] ) ) {
			$out['disclaimer'] = sanitize_textarea_field( (string) $input['disclaimer'] );
		}

		return $out;
	}

	/**
	 * 入力欄の描画。
	 *
	 * @param array{key: string, label: string, type: string, min?: int, max?: int} $args 引数。
	 */
	public static function render_field( array $args ): void {
		$settings = self::get();
		$key      = $args['key'];
		$value    = $settings[ $key ] ?? '';
		$name     = sprintf( '%s[%s]', CLINIC_FEE_SIMULATOR_OPTION, $key );

		if ( 'textarea' === $args['type'] ) {
			printf(
				'<textarea name="%s" id="%s" rows="3" class="large-text">%s</textarea>',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_textarea( (string) $value )
			);
			return;
		}

		printf(
			'<input type="number" name="%s" id="%s" value="%s" class="small-text" min="%s" max="%s" step="1">',
			esc_attr( $name ),
			esc_attr( $key ),
			esc_attr( (string) $value ),
			esc_attr( (string) ( $args['min'] ?? 0 ) ),
			esc_attr( (string) ( $args['max'] ?? '' ) )
		);
	}

	/**
	 * 設定ページ本体。
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'このページを表示する権限がありません。', 'clinic-fee-simulator' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p>
				<?php esc_html_e( '設置するには、固定ページや投稿に次のショートコードを書きます。', 'clinic-fee-simulator' ); ?>
				<code>[clinic_simulator]</code>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'cfs_group' );
				do_settings_sections( 'clinic-fee-simulator' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
