<?php
/**
 * 料金計算のテスト。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;

/**
 * Clinic_Fee_Simulator_Calculator の検証。
 */
final class CalculatorTest extends TestCase {

	/**
	 * 既定の料金表を入れておく。
	 */
	protected function setUp(): void {
		$GLOBALS['test_options']         = array( CLINIC_FEE_SIMULATOR_OPTION => Clinic_Fee_Simulator_Settings::defaults() );
		$GLOBALS['test_settings_errors'] = array();
	}

	/**
	 * 想定外の保険種別は自賠責へ丸める。
	 */
	public function test_normalize_rejects_unknown_insurance(): void {
		$out = Clinic_Fee_Simulator_Calculator::normalize( array( 'insurance' => 'HACK' ) );
		$this->assertSame( 'jibaiseki', $out['insurance'] );
	}

	/**
	 * 範囲外の頻度は週1回へ丸める。
	 *
	 * @dataProvider provide_bad_frequencies
	 * @param mixed $input 入力。
	 */
	public function test_normalize_rejects_bad_frequency( $input ): void {
		$out = Clinic_Fee_Simulator_Calculator::normalize( array( 'frequency' => $input ) );
		$this->assertSame( 1, $out['frequency'] );
	}

	/**
	 * @return array<string, array{mixed}>
	 */
	public static function provide_bad_frequencies(): array {
		return array(
			'0'      => array( 0 ),
			'4'      => array( 4 ),
			'999'    => array( 999 ),
			'負数'   => array( -2 ),
			'文字列' => array( 'abc' ),
		);
	}

	/**
	 * 症状は許可された ID だけを残す。
	 */
	public function test_normalize_keeps_only_allowed_symptoms(): void {
		$out = Clinic_Fee_Simulator_Calculator::normalize(
			array( 'symptoms' => array( 'neck', '<script>', 'unknown', 'back' ) )
		);
		$this->assertSame( array( 'neck', 'back' ), $out['symptoms'] );
	}

	/**
	 * symptoms が配列でなければ空にする。
	 */
	public function test_normalize_handles_non_array_symptoms(): void {
		$out = Clinic_Fee_Simulator_Calculator::normalize( array( 'symptoms' => 'notarray' ) );
		$this->assertSame( array(), $out['symptoms'] );
	}

	/**
	 * 保険3種 × 頻度3種 × 症状0〜5個 の全54通りを、
	 * 独立して立てた式と突き合わせる。
	 */
	public function test_estimate_matches_independent_formula(): void {
		$symptoms = array_keys( Clinic_Fee_Simulator_Calculator::symptoms() );
		$checked  = 0;

		foreach ( array( 'jibaiseki', 'kenpo', 'jihi' ) as $insurance ) {
			foreach ( array( 1, 2, 3 ) as $frequency ) {
				for ( $n = 0; $n <= 5; $n++ ) {
					$got = Clinic_Fee_Simulator_Calculator::estimate(
						array(
							'insurance' => $insurance,
							'frequency' => $frequency,
							'symptoms'  => array_slice( $symptoms, 0, $n ),
						)
					);

					$expected_per_visit = 3500 + ( $n >= 3 ? 500 : 0 );
					$expected_monthly   = $expected_per_visit * $frequency * 4;
					$expected_self_pay  = match ( $insurance ) {
						'jibaiseki' => 0,
						'kenpo'     => (int) floor( $expected_monthly * 0.3 ),
						default     => $expected_monthly,
					};

					$label = "{$insurance} / 週{$frequency}回 / 症状{$n}個";
					$this->assertSame( $expected_per_visit, $got['per_visit'], $label );
					$this->assertSame( $expected_monthly, $got['monthly'], $label );
					$this->assertSame( $expected_self_pay, $got['self_pay'], $label );
					++$checked;
				}
			}
		}

		$this->assertSame( 54, $checked );
	}

	/**
	 * 端数は切り捨てる。利用者に多く見せないため。
	 */
	public function test_kenpo_self_pay_is_floored(): void {
		$GLOBALS['test_options'][ CLINIC_FEE_SIMULATOR_OPTION ]['base_price'] = 3333;

		$got = Clinic_Fee_Simulator_Calculator::estimate(
			array( 'insurance' => 'kenpo', 'frequency' => 1, 'symptoms' => array() )
		);

		// 3333 * 1 * 4 = 13332、その 30% は 3999.6。
		$this->assertSame( 13332, $got['monthly'] );
		$this->assertSame( 3999, $got['self_pay'] );
	}

	/**
	 * 注記に出す割合は、実際に計算した割合と一致しなければならない。
	 */
	public function test_kenpo_note_matches_the_rate_actually_used(): void {
		$GLOBALS['test_options'][ CLINIC_FEE_SIMULATOR_OPTION ]['kenpo_rate'] = 25;

		$got = Clinic_Fee_Simulator_Calculator::estimate(
			array( 'insurance' => 'kenpo', 'frequency' => 1, 'symptoms' => array() )
		);

		// 25% で計算しているのに「3割」と書いたら、患者に誤った額を案内することになる。
		$this->assertSame( (int) floor( 14000 * 0.25 ), $got['self_pay'] );
		$this->assertStringNotContainsString( '3 割', $got['note'] );
	}
}
