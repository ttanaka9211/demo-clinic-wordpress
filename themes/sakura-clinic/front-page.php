<?php
/**
 * フロントページ（LP本体）
 *
 * 料金シミュレーターはテーマに実装せず、プラグインが提供する
 * [clinic_simulator] ショートコードを差し込む。
 *
 * 文言は柔道整復の広告ガイドラインを踏まえ、「治療」ではなく「施術」で
 * 統一し、効果を断定する表現・体験談は置かない。
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sakura_clinic_tel_href = sakura_clinic_tel_href();

$sakura_clinic_worries = array(
	__( '事故のあとから首や肩が重い', 'sakura-clinic' ),
	__( '病院で異常なしと言われたが痛みが残る', 'sakura-clinic' ),
	__( '保険会社とのやりとりがよく分からない', 'sakura-clinic' ),
	__( '仕事帰りに通える時間帯がない', 'sakura-clinic' ),
);

$sakura_clinic_features = array(
	array(
		'icon'  => 'car',
		'title' => __( '交通事故に対応', 'sakura-clinic' ),
		'text'  => __( '自賠責保険を使った施術を承っています。', 'sakura-clinic' ),
	),
	array(
		'icon'  => 'document',
		'title' => __( '書類のご相談', 'sakura-clinic' ),
		'text'  => __( '保険会社への連絡や必要書類をご案内します。', 'sakura-clinic' ),
	),
	array(
		'icon'  => 'clock',
		'title' => __( '夜20時まで受付', 'sakura-clinic' ),
		'text'  => __( '平日はお仕事帰りの時間にも通えます。', 'sakura-clinic' ),
	),
	array(
		'icon'  => 'train',
		'title' => __( '駅から徒歩5分', 'sakura-clinic' ),
		'text'  => __( '提携駐車場もご利用いただけます。', 'sakura-clinic' ),
	),
);

$sakura_clinic_flow = array(
	array(
		'title' => __( 'お電話でご予約', 'sakura-clinic' ),
		'text'  => __( '事故の状況と、いまお困りの症状をお聞かせください。', 'sakura-clinic' ),
	),
	array(
		'title' => __( '問診・お身体の確認', 'sakura-clinic' ),
		'text'  => __( '痛みの出かたや動かせる範囲を確認します。', 'sakura-clinic' ),
	),
	array(
		'title' => __( '施術の内容をご説明', 'sakura-clinic' ),
		'text'  => __( '通院の目安と進め方をお伝えしてから始めます。', 'sakura-clinic' ),
	),
	array(
		'title' => __( '施術・次回のご案内', 'sakura-clinic' ),
		'text'  => __( 'ご都合に合わせて次回の予定を決めます。', 'sakura-clinic' ),
	),
);

$sakura_clinic_faq = array(
	array(
		'q' => __( '費用はどのくらいかかりますか。', 'sakura-clinic' ),
		'a' => __( '保険の種類と通院の頻度によって変わります。上の料金シミュレーターで目安をご確認いただけます。', 'sakura-clinic' ),
	),
	array(
		'q' => __( '病院に通いながらでも大丈夫ですか。', 'sakura-clinic' ),
		'a' => __( '併用されている方もいらっしゃいます。通院状況をお聞かせいただければ、進め方をご相談します。', 'sakura-clinic' ),
	),
	array(
		'q' => __( '保険会社への連絡は自分でしますか。', 'sakura-clinic' ),
		'a' => __( 'ご自身でご連絡いただく形が基本ですが、伝え方が分からない場合はご案内しています。', 'sakura-clinic' ),
	),
	array(
		'q' => __( '予約なしでも受けられますか。', 'sakura-clinic' ),
		'a' => __( 'お待たせすることがあるため、お電話でのご予約をおすすめしています。', 'sakura-clinic' ),
	),
);
?>

<section class="hero">
	<div class="container container--lg hero__inner">
		<div class="hero__copy">
			<span class="hero__badge"><?php echo esc_html( sakura_clinic_get( 'hero_badge' ) ); ?></span>
			<h1 class="hero__title"><?php echo sakura_clinic_nl2br_esc( sakura_clinic_get( 'hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="hero__lead"><?php echo esc_html( sakura_clinic_get( 'hero_lead' ) ); ?></p>

			<div class="hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( $sakura_clinic_tel_href ); ?>">
					<?php echo sakura_clinic_icon( 'phone', 'btn__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php esc_html_e( '電話で相談する', 'sakura-clinic' ); ?>
				</a>
				<a class="btn btn--outline" href="#simulator">
					<?php echo sakura_clinic_icon( 'yen', 'btn__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php esc_html_e( '費用の目安を見る', 'sakura-clinic' ); ?>
				</a>
			</div>

			<ul class="hero__facts">
				<li class="hero__fact"><?php echo sakura_clinic_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( '自賠責保険に対応', 'sakura-clinic' ); ?></li>
				<li class="hero__fact"><?php echo sakura_clinic_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( '夜20時まで受付', 'sakura-clinic' ); ?></li>
				<li class="hero__fact"><?php echo sakura_clinic_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( '駅から徒歩5分', 'sakura-clinic' ); ?></li>
			</ul>
		</div>

		<div class="hero__art">
			<?php echo sakura_clinic_hero_visual(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>

<section class="section section--white">
	<div class="container container--md">
		<div class="section__head">
			<span class="eyebrow">TROUBLES</span>
			<h2 class="section__title"><?php esc_html_e( 'こんなことでお困りではありませんか', 'sakura-clinic' ); ?></h2>
			<span class="rule"></span>
		</div>
		<ul class="worries">
			<?php foreach ( $sakura_clinic_worries as $sakura_clinic_worry ) : ?>
				<li class="worry"><?php echo sakura_clinic_icon( 'question' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $sakura_clinic_worry ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section section--soft" id="simulator">
	<div class="container container--sm">
		<div class="section__head">
			<span class="eyebrow">SIMULATOR</span>
			<h2 class="section__title"><span class="mark"><?php esc_html_e( '費用の目安を調べる', 'sakura-clinic' ); ?></span></h2>
			<p class="section__lead"><?php esc_html_e( '保険の種類と通院の頻度を選ぶと、おおよその金額を表示します。', 'sakura-clinic' ); ?></p>
		</div>

		<?php if ( shortcode_exists( 'clinic_simulator' ) ) : ?>
			<?php echo do_shortcode( '[clinic_simulator]' ); ?>
		<?php else : ?>
			<p class="section__lead">
				<?php esc_html_e( '料金シミュレーターを表示するには clinic-fee-simulator プラグインを有効化してください。', 'sakura-clinic' ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<section class="section section--white">
	<div class="container container--lg">
		<div class="section__head">
			<span class="eyebrow">FEATURES</span>
			<h2 class="section__title"><?php esc_html_e( '当院の4つの特徴', 'sakura-clinic' ); ?></h2>
			<span class="rule"></span>
		</div>
		<div class="features">
			<?php foreach ( $sakura_clinic_features as $sakura_clinic_feature ) : ?>
				<div class="feature">
					<?php echo sakura_clinic_icon( $sakura_clinic_feature['icon'], 'feature__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h3 class="feature__title"><?php echo esc_html( $sakura_clinic_feature['title'] ); ?></h3>
					<p class="feature__text"><?php echo esc_html( $sakura_clinic_feature['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--paper2">
	<div class="container container--sm">
		<div class="section__head">
			<span class="eyebrow">FLOW</span>
			<h2 class="section__title"><?php esc_html_e( '初回の流れ', 'sakura-clinic' ); ?></h2>
			<span class="rule"></span>
		</div>
		<ol class="flow">
			<?php foreach ( $sakura_clinic_flow as $sakura_clinic_step ) : ?>
				<li class="flow__item">
					<p class="flow__title"><?php echo esc_html( $sakura_clinic_step['title'] ); ?></p>
					<p class="flow__text"><?php echo esc_html( $sakura_clinic_step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="section section--white">
	<div class="container container--sm">
		<div class="section__head">
			<span class="eyebrow">FAQ</span>
			<h2 class="section__title"><?php esc_html_e( 'よくあるご質問', 'sakura-clinic' ); ?></h2>
			<span class="rule"></span>
		</div>
		<div class="faq">
			<?php foreach ( $sakura_clinic_faq as $sakura_clinic_item ) : ?>
				<details class="faq__item">
					<summary class="faq__q"><span><?php echo esc_html( $sakura_clinic_item['q'] ); ?></span></summary>
					<p class="faq__a"><span><?php echo esc_html( $sakura_clinic_item['a'] ); ?></span></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--paper2">
	<div class="container container--md">
		<div class="section__head">
			<span class="eyebrow">ACCESS</span>
			<h2 class="section__title"><?php esc_html_e( 'アクセス', 'sakura-clinic' ); ?></h2>
			<span class="rule"></span>
		</div>
		<div class="access">
			<div class="access__list">
				<div class="access__row"><span class="access__key"><?php esc_html_e( '所在地', 'sakura-clinic' ); ?></span><span><?php echo esc_html( sakura_clinic_get( 'clinic_addr' ) ); ?></span></div>
				<div class="access__row"><span class="access__key"><?php esc_html_e( '最寄り', 'sakura-clinic' ); ?></span><span><?php echo esc_html( sakura_clinic_get( 'clinic_access' ) ); ?></span></div>
				<div class="access__row"><span class="access__key"><?php esc_html_e( '駐車場', 'sakura-clinic' ); ?></span><span><?php echo esc_html( sakura_clinic_get( 'clinic_park' ) ); ?></span></div>
				<div class="access__row"><span class="access__key"><?php esc_html_e( '受付時間', 'sakura-clinic' ); ?></span><span><?php echo sakura_clinic_nl2br_esc( sakura_clinic_get( 'clinic_hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div>
			</div>
			<div class="access__map">
				<?php echo sakura_clinic_access_map(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="access__caption"><?php esc_html_e( '※ 架空の所在地のため、実地図ではなく位置関係の略図を掲載しています。', 'sakura-clinic' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--white" id="contact">
	<div class="container container--sm cta">
		<h2 class="cta__title"><?php echo esc_html( sakura_clinic_get( 'cta_title' ) ); ?></h2>
		<p class="cta__lead"><?php echo esc_html( sakura_clinic_get( 'cta_lead' ) ); ?></p>
		<a class="cta__tel" href="<?php echo esc_url( $sakura_clinic_tel_href ); ?>">
			<?php echo sakura_clinic_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo esc_html( sakura_clinic_get( 'clinic_tel' ) ); ?>
		</a>
		<p class="cta__hours"><?php echo sakura_clinic_nl2br_esc( sakura_clinic_get( 'clinic_hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>

		<?php if ( shortcode_exists( 'clinic_contact' ) ) : ?>
			<div class="cta__form">
				<p class="cta__form-lead"><?php esc_html_e( 'メールでのご相談はこちらから', 'sakura-clinic' ); ?></p>
				<?php echo do_shortcode( '[clinic_contact]' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
