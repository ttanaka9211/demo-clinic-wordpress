<?php
/**
 * REST エンドポイント
 *
 * 画面側の JavaScript が出した数字をそのまま信用せず、
 * ここで計算し直した値を正とする。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * 費用の目安を返す REST ルート。
 */
final class Clinic_Fee_Simulator_Rest {

	public const NAMESPACE = 'clinic-simulator/v1';

	/**
	 * フック登録。
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * ルート登録。
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/estimate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle' ),
				// 費用の目安は公開情報なので誰でも呼べる。
				// 書き込みを伴わないため nonce は必須にしないが、
				// 送られてきた場合はログイン利用者として扱われる。
				'permission_callback' => '__return_true',
				'args'                => array(
					'insurance' => array(
						'type'              => 'string',
						'required'          => false,
						'enum'              => Clinic_Fee_Simulator_Calculator::INSURANCE_TYPES,
						'sanitize_callback' => 'sanitize_key',
					),
					'frequency' => array(
						'type'              => 'integer',
						'required'          => false,
						'minimum'           => 1,
						'maximum'           => 3,
						'sanitize_callback' => 'absint',
					),
					'symptoms'  => array(
						'type'     => 'array',
						'required' => false,
						'items'    => array( 'type' => 'string' ),
					),
				),
			)
		);
	}

	/**
	 * 実処理。
	 *
	 * @param WP_REST_Request $request リクエスト。
	 */
	public static function handle( WP_REST_Request $request ): WP_REST_Response {
		$result = Clinic_Fee_Simulator_Calculator::estimate(
			array(
				'insurance' => $request->get_param( 'insurance' ),
				'frequency' => $request->get_param( 'frequency' ),
				'symptoms'  => $request->get_param( 'symptoms' ),
			)
		);

		$response = new WP_REST_Response( $result, 200 );
		// 目安の計算なので短時間ならキャッシュしてよい。
		$response->header( 'Cache-Control', 'public, max-age=60' );

		return $response;
	}
}
