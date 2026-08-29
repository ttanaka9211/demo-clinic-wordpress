<?php
/**
 * 計算ロジック
 *
 * 表示用の計算は JavaScript でも行うが、正となる値はこのクラスが返す。
 * 画面側の値を信用せず、必ずサーバ側で検算する。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * 費用の目安を計算する。
 */
final class Clinic_Fee_Simulator_Calculator {

	public const INSURANCE_TYPES = array( 'jibaiseki', 'kenpo', 'jihi' );

	/**
	 * 選べる症状。
	 *
	 * @return array<string, string>
	 */
	public static function symptoms(): array {
		return array(
			'neck'     => __( '首の痛み', 'clinic-fee-simulator' ),
			'back'     => __( '腰の痛み', 'clinic-fee-simulator' ),
			'shoulder' => __( '肩のこり', 'clinic-fee-simulator' ),
			'head'     => __( '頭の重さ', 'clinic-fee-simulator' ),
			'numb'     => __( '手足のしびれ', 'clinic-fee-simulator' ),
		);
	}

	/**
	 * 保険の選択肢。
	 *
	 * @return array<string, string>
	 */
	public static function insurances(): array {
		return array(
			'jibaiseki' => __( '自賠責保険', 'clinic-fee-simulator' ),
			'kenpo'     => __( '健康保険', 'clinic-fee-simulator' ),
			'jihi'      => __( '自費', 'clinic-fee-simulator' ),
		);
	}

	/**
	 * 通院頻度の選択肢。
	 *
	 * @return array<int, string>
	 */
	public static function frequencies(): array {
		return array(
			1 => __( '週1回', 'clinic-fee-simulator' ),
			2 => __( '週2回', 'clinic-fee-simulator' ),
			3 => __( '週3回', 'clinic-fee-simulator' ),
		);
	}

	/**
	 * 入力を正規化する。想定外の値は既定へ丸める。
	 *
	 * @param array<string, mixed> $raw 入力。
	 * @return array{insurance: string, frequency: int, symptoms: string[]}
	 */
	public static function normalize( array $raw ): array {
		$insurance = isset( $raw['insurance'] ) ? sanitize_key( (string) $raw['insurance'] ) : '';
		if ( ! in_array( $insurance, self::INSURANCE_TYPES, true ) ) {
			$insurance = 'jibaiseki';
		}

		/*
		 * absint() を使わない。負数の符号を落としてしまうため、
		 * -2 が 2 になり「週2回」として通っていた。
		 * 選択肢に無い値は既定へ丸めるが、丸める前に別の値へ化けさせない。
		 */
		$frequency = 1;
		if ( isset( $raw['frequency'] ) && is_numeric( $raw['frequency'] ) && (float) $raw['frequency'] >= 0 ) {
			$frequency = (int) $raw['frequency'];
		}
		if ( ! array_key_exists( $frequency, self::frequencies() ) ) {
			$frequency = 1;
		}

		$symptoms = array();
		if ( isset( $raw['symptoms'] ) && is_array( $raw['symptoms'] ) ) {
			$allowed  = array_keys( self::symptoms() );
			$symptoms = array_values(
				array_intersect(
					array_map( 'sanitize_key', array_map( 'strval', $raw['symptoms'] ) ),
					$allowed
				)
			);
		}

		return array(
			'insurance' => $insurance,
			'frequency' => $frequency,
			'symptoms'  => $symptoms,
		);
	}

	/**
	 * 費用の目安を返す。
	 *
	 * @param array<string, mixed> $raw 入力。
	 * @return array{per_visit: int, monthly: int, self_pay: int, note: string, insurance: string, frequency: int, symptoms: string[]}
	 */
	public static function estimate( array $raw ): array {
		$in       = self::normalize( $raw );
		$settings = Clinic_Fee_Simulator_Settings::get();

		$base      = (int) $settings['base_price'];
		$surcharge = count( $in['symptoms'] ) >= (int) $settings['symptom_thresh']
			? (int) $settings['symptom_surchg']
			: 0;

		$per_visit = $base + $surcharge;
		$monthly   = $per_visit * $in['frequency'] * (int) $settings['weeks_per_month'];

		switch ( $in['insurance'] ) {
			case 'jibaiseki':
				$self_pay = 0;
				$note     = __( '自賠責保険が適用される場合、窓口でのお支払いは生じないのが一般的です。適用の可否は保険会社の判断によります。', 'clinic-fee-simulator' );
				break;

			case 'kenpo':
				$rate     = max( 0, min( 100, (int) $settings['kenpo_rate'] ) );
				$self_pay = (int) floor( $monthly * $rate / 100 );

				/*
				 * 「割」で言い換えるのは 10 の倍数のときだけにする。
				 * round( $rate / 10 ) だと 25% を「3割」と書いてしまい、
				 * 計算に使った割合と案内する割合が食い違う。
				 * 窓口で示す額の話なので、ここがずれるのは困る。
				 */
				$note = 0 === $rate % 10
					? sprintf(
						/* translators: %d: 自己負担の割合（割） */
						__( '健康保険が適用される場合の目安です（自己負担 %d 割で計算）。', 'clinic-fee-simulator' ),
						intdiv( $rate, 10 )
					)
					: sprintf(
						/* translators: %d: 自己負担の割合（％） */
						__( '健康保険が適用される場合の目安です（自己負担 %d％ で計算）。', 'clinic-fee-simulator' ),
						$rate
					);
				break;

			default:
				$self_pay = $monthly;
				$note     = __( '保険を使わない場合の目安です。', 'clinic-fee-simulator' );
				break;
		}

		return array(
			'per_visit' => $per_visit,
			'monthly'   => $monthly,
			'self_pay'  => $self_pay,
			'note'      => $note,
			'insurance' => $in['insurance'],
			'frequency' => $in['frequency'],
			'symptoms'  => $in['symptoms'],
		);
	}
}
