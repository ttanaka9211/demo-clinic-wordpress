<?php
/**
 * 汎用テンプレート（固定ページ・投稿・アーカイブのフォールバック）
 *
 * @package Sakura_Clinic
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container container--sm page-body">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
			<?php
		endwhile;
		?>
	<?php else : ?>
		<h1><?php esc_html_e( 'ページが見つかりませんでした', 'sakura-clinic' ); ?></h1>
		<p><?php esc_html_e( 'お探しのページは移動または削除された可能性があります。', 'sakura-clinic' ); ?></p>
		<p><a class="btn btn--green" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'トップページへ', 'sakura-clinic' ); ?></a></p>
	<?php endif; ?>
</div>

<?php
get_footer();
