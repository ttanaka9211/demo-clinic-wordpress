/**
 * 料金シミュレーター
 *
 * 画面側でも即時に計算して見せるが、表示する数字はサーバの計算結果で
 * 上書きする。JS が動かない場合はサーバが描画した初期値がそのまま残る。
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-cfs]' );
	if ( ! root || typeof window.cfsConfig === 'undefined' ) {
		return;
	}

	var form = root.querySelector( '.cfs__form' );
	var out = {
		perVisit: root.querySelector( '[data-cfs-per-visit]' ),
		monthly: root.querySelector( '[data-cfs-monthly]' ),
		selfPay: root.querySelector( '[data-cfs-self-pay]' ),
		note: root.querySelector( '[data-cfs-note]' )
	};

	var timer = null;
	var controller = null;

	function collect() {
		var data = new FormData( form );
		return {
			insurance: data.get( 'insurance' ) || 'jibaiseki',
			frequency: parseInt( data.get( 'frequency' ) || '1', 10 ),
			symptoms: data.getAll( 'symptoms[]' )
		};
	}

	function yen( value ) {
		return '¥' + Number( value ).toLocaleString( 'ja-JP' );
	}

	function paint( result ) {
		if ( out.perVisit ) { out.perVisit.textContent = yen( result.per_visit ); }
		if ( out.monthly ) { out.monthly.textContent = yen( result.monthly ); }
		if ( out.selfPay ) { out.selfPay.textContent = yen( result.self_pay ); }
		if ( out.note ) { out.note.textContent = result.note; }
		root.classList.remove( 'is-loading' );
	}

	function request() {
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();
		root.classList.add( 'is-loading' );

		fetch( window.cfsConfig.endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.cfsConfig.nonce
			},
			body: JSON.stringify( collect() ),
			signal: controller.signal
		} )
			.then( function ( res ) {
				if ( ! res.ok ) {
					throw new Error( 'request failed: ' + res.status );
				}
				return res.json();
			} )
			.then( paint )
			.catch( function ( err ) {
				if ( err.name === 'AbortError' ) {
					return;
				}
				// 通信できなくても画面は壊さない。直前の表示を残す。
				root.classList.remove( 'is-loading' );
			} );
	}

	function schedule() {
		window.clearTimeout( timer );
		timer = window.setTimeout( request, 180 );
	}

	form.addEventListener( 'change', schedule );
	form.addEventListener( 'submit', function ( event ) {
		event.preventDefault();
		request();
	} );
}() );
