<?php
/**
 * ショートコード [clinic_simulator]
 *
 * JavaScript が動かない環境でも、初期値の計算結果が表示される。
 * JS はそこに即時更新を足すだけの位置づけ（progressive enhancement）。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * ショートコードの登録と描画。
 */
final class Clinic_Fee_Simulator_Shortcode {

	/**
	 * フック登録。
	 */
	public static function init(): void {
		add_shortcode( 'clinic_simulator', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * アセットを登録する。実際の読み込みはショートコードが使われたときだけ。
	 */
	public static function register_assets(): void {
		wp_register_style( 'cfs-simulator', CLINIC_FEE_SIMULATOR_URL . 'assets/simulator.css', array(), CLINIC_FEE_SIMULATOR_VERSION );
		wp_register_script( 'cfs-simulator', CLINIC_FEE_SIMULATOR_URL . 'assets/simulator.js', array(), CLINIC_FEE_SIMULATOR_VERSION, true );
	}

	/**
	 * 描画。
	 */
	public static function render(): string {
		wp_enqueue_style( 'cfs-simulator' );
		wp_enqueue_script( 'cfs-simulator' );

		wp_localize_script(
			'cfs-simulator',
			'cfsConfig',
			array(
				'endpoint' => esc_url_raw( rest_url( Clinic_Fee_Simulator_Rest::NAMESPACE . '/estimate' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		$settings = Clinic_Fee_Simulator_Settings::get();
		$initial  = Clinic_Fee_Simulator_Calculator::estimate(
			array(
				'insurance' => 'jibaiseki',
				'frequency' => 1,
			)
		);

		ob_start();
		?>
		<div class="cfs" data-cfs>
			<form class="cfs__form" novalidate>
				<fieldset class="cfs__group">
					<legend class="cfs__legend"><?php esc_html_e( 'STEP 1　気になる症状（複数選択できます）', 'clinic-fee-simulator' ); ?></legend>
					<div class="cfs__chips">
						<?php foreach ( Clinic_Fee_Simulator_Calculator::symptoms() as $key => $label ) : ?>
							<label class="cfs__chip">
								<input type="checkbox" name="symptoms[]" value="<?php echo esc_attr( $key ); ?>">
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<fieldset class="cfs__group">
					<legend class="cfs__legend"><?php esc_html_e( 'STEP 2　保険の種類', 'clinic-fee-simulator' ); ?></legend>
					<div class="cfs__chips cfs__chips--3">
						<?php foreach ( Clinic_Fee_Simulator_Calculator::insurances() as $key => $label ) : ?>
							<label class="cfs__chip">
								<input type="radio" name="insurance" value="<?php echo esc_attr( $key ); ?>" <?php checked( 'jibaiseki', $key ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<fieldset class="cfs__group">
					<legend class="cfs__legend"><?php esc_html_e( 'STEP 3　通院の頻度', 'clinic-fee-simulator' ); ?></legend>
					<div class="cfs__chips cfs__chips--3">
						<?php foreach ( Clinic_Fee_Simulator_Calculator::frequencies() as $key => $label ) : ?>
							<label class="cfs__chip">
								<input type="radio" name="frequency" value="<?php echo esc_attr( (string) $key ); ?>" <?php checked( 1, $key ); ?>>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<noscript>
					<button type="submit" class="cfs__submit"><?php esc_html_e( '計算する', 'clinic-fee-simulator' ); ?></button>
				</noscript>
			</form>

			<div class="cfs__result" aria-live="polite">
				<p class="cfs__result-title"><?php esc_html_e( '費用の目安', 'clinic-fee-simulator' ); ?></p>
				<div class="cfs__figures">
					<div class="cfs__figure">
						<span class="cfs__figure-key"><?php esc_html_e( '1回あたり', 'clinic-fee-simulator' ); ?></span>
						<span class="cfs__figure-val" data-cfs-per-visit><?php echo esc_html( self::yen( $initial['per_visit'] ) ); ?></span>
					</div>
					<div class="cfs__figure">
						<span class="cfs__figure-key"><?php esc_html_e( '1か月の施術料', 'clinic-fee-simulator' ); ?></span>
						<span class="cfs__figure-val" data-cfs-monthly><?php echo esc_html( self::yen( $initial['monthly'] ) ); ?></span>
					</div>
					<div class="cfs__figure cfs__figure--main">
						<span class="cfs__figure-key"><?php esc_html_e( 'お支払いの目安', 'clinic-fee-simulator' ); ?></span>
						<span class="cfs__figure-val" data-cfs-self-pay><?php echo esc_html( self::yen( $initial['self_pay'] ) ); ?></span>
					</div>
				</div>
				<p class="cfs__note" data-cfs-note><?php echo esc_html( $initial['note'] ); ?></p>
				<p class="cfs__disclaimer"><?php echo esc_html( (string) $settings['disclaimer'] ); ?></p>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * 円表記に整える。
	 *
	 * @param int $value 金額。
	 */
	private static function yen( int $value ): string {
		return '¥' . number_format_i18n( $value );
	}
}
