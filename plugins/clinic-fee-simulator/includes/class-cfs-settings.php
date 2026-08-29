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
final class CFS_Settings {

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
	 * 設定の取得。既定値とマージして欠損キーを埋める。
	 *
	 * @return array<string, int|string>
	 */
	public static function get(): array {
		$saved = get_option( CFS_OPTION, array() );
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
			CFS_OPTION,
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

		$fields = array(
			'base_price'      => array( __( '1回あたりの基本施術料（円）', 'clinic-fee-simulator' ), 'number' ),
			'symptom_surchg'  => array( __( '症状が多い場合の加算（円）', 'clinic-fee-simulator' ), 'number' ),
			'symptom_thresh'  => array( __( '加算の対象となる症状の数', 'clinic-fee-simulator' ), 'number' ),
			'weeks_per_month' => array( __( '月あたりの週数', 'clinic-fee-simulator' ), 'number' ),
			'kenpo_rate'      => array( __( '健康保険の自己負担割合（％）', 'clinic-fee-simulator' ), 'number' ),
			'disclaimer'      => array( __( '注記', 'clinic-fee-simulator' ), 'textarea' ),
		);

		foreach ( $fields as $key => $field ) {
			add_settings_field(
				$key,
				$field[0],
				array( __CLASS__, 'render_field' ),
				'clinic-fee-simulator',
				'cfs_prices',
				array( 'key' => $key, 'type' => $field[1] )
			);
		}
	}

	/**
	 * 入力値の検証。範囲外は既定値へ戻す。
	 *
	 * @param mixed $input 送信値。
	 * @return array<string, int|string>
	 */
	public static function sanitize( $input ): array {
		$defaults = self::defaults();
		$out      = $defaults;

		if ( ! is_array( $input ) ) {
			return $out;
		}

		$ranges = array(
			'base_price'      => array( 0, 1000000 ),
			'symptom_surchg'  => array( 0, 1000000 ),
			'symptom_thresh'  => array( 1, 20 ),
			'weeks_per_month' => array( 1, 6 ),
			'kenpo_rate'      => array( 0, 100 ),
		);

		foreach ( $ranges as $key => list( $min, $max ) ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}
			$value = absint( $input[ $key ] );
			if ( $value < $min || $value > $max ) {
				add_settings_error(
					CFS_OPTION,
					'cfs_' . $key,
					sprintf(
						/* translators: 1: field key, 2: min, 3: max */
						__( '「%1$s」は %2$d〜%3$d の範囲で入力してください。既定値に戻しました。', 'clinic-fee-simulator' ),
						$key,
						$min,
						$max
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
	 * @param array{key: string, type: string} $args 引数。
	 */
	public static function render_field( array $args ): void {
		$settings = self::get();
		$key      = $args['key'];
		$value    = $settings[ $key ] ?? '';
		$name     = sprintf( '%s[%s]', CFS_OPTION, $key );

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
			'<input type="number" name="%s" id="%s" value="%s" class="small-text" min="0" step="1">',
			esc_attr( $name ),
			esc_attr( $key ),
			esc_attr( (string) $value )
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
