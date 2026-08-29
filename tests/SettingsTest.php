<?php
/**
 * 設定値の検証のテスト。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;

/**
 * Clinic_Fee_Simulator_Settings::sanitize() の検証。
 */
final class SettingsTest extends TestCase {

	/**
	 * 「前に設定した値」がある状態から始める。既定値とは別の値にしておく。
	 */
	protected function setUp(): void {
		$GLOBALS['test_options']         = array(
			CLINIC_FEE_SIMULATOR_OPTION => array_merge(
				Clinic_Fee_Simulator_Settings::defaults(),
				array( 'base_price' => 5000, 'symptom_thresh' => 4 )
			),
		);
		$GLOBALS['test_settings_errors'] = array();
	}

	/**
	 * 数字でない入力はその項目だけ据え置き、メッセージを出す。
	 *
	 * absint() を使っていたころは 'abc' が 0 になり、
	 * base_price の最小値が 0 のため「範囲内」として保存されていた。
	 * つまり施術料 0 円が警告なしに公開サイトへ出ていた。
	 *
	 * @dataProvider provide_non_numeric
	 * @param string $input 入力。
	 */
	public function test_non_numeric_input_is_rejected( string $input ): void {
		$out = Clinic_Fee_Simulator_Settings::sanitize( array( 'base_price' => $input ) );

		$this->assertSame( 5000, $out['base_price'], '元の値が保たれること' );
		$this->assertNotEmpty( $GLOBALS['test_settings_errors'], 'メッセージが出ること' );
		$this->assertStringContainsString(
			'1回あたりの基本施術料',
			$GLOBALS['test_settings_errors'][0]['message'],
			'内部キーではなく項目名が出ること'
		);
	}

	/**
	 * @return array<string, array{string}>
	 */
	public static function provide_non_numeric(): array {
		return array(
			'文字列'   => array( 'abc' ),
			'負数'     => array( '-500' ),
			'空欄'     => array( '' ),
			'小数'     => array( '35.5' ),
			'全角数字' => array( '３５００' ),
		);
	}

	/**
	 * 範囲外は既定値ではなく、保存済みの値のまま据え置く。
	 *
	 * 既定値へ戻していたころは、ひとつの入力ミスで
	 * 前に設定した値まで工場出荷値に巻き戻っていた。
	 */
	public function test_out_of_range_keeps_the_saved_value_not_the_default(): void {
		$out = Clinic_Fee_Simulator_Settings::sanitize( array( 'symptom_thresh' => '999' ) );

		$this->assertSame( 4, $out['symptom_thresh'], '保存済みの 4 のままであること' );
		$this->assertNotSame( 3, $out['symptom_thresh'], '既定値の 3 に戻っていないこと' );
	}

	/**
	 * ひとつ弾いても、同時に送った正しい項目は保存する。
	 */
	public function test_one_bad_field_does_not_discard_the_good_ones(): void {
		$out = Clinic_Fee_Simulator_Settings::sanitize(
			array( 'base_price' => '4200', 'symptom_thresh' => '999' )
		);

		$this->assertSame( 4200, $out['base_price'], '正しい項目は保存されること' );
		$this->assertSame( 4, $out['symptom_thresh'], '弾いた項目は据え置かれること' );
	}

	/**
	 * 正しい入力はそのまま通り、メッセージも出ない。
	 */
	public function test_valid_input_is_saved(): void {
		$out = Clinic_Fee_Simulator_Settings::sanitize(
			array( 'base_price' => '4200', 'symptom_thresh' => '2', 'kenpo_rate' => '30' )
		);

		$this->assertSame( 4200, $out['base_price'] );
		$this->assertSame( 2, $out['symptom_thresh'] );
		$this->assertSame( array(), $GLOBALS['test_settings_errors'] );
	}

	/**
	 * 配列でない入力は、いま保存されている値をそのまま返す。
	 */
	public function test_non_array_input_returns_current_settings(): void {
		$out = Clinic_Fee_Simulator_Settings::sanitize( 'not an array' );

		$this->assertSame( 5000, $out['base_price'] );
	}
}
