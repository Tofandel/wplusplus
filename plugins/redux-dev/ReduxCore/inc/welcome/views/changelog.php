<?php
/**
 * Admin View: Page - Changelog
 *
 * @package Redux Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap about-wrap" style="max-width:initial;margin:10px 20px 0 2px;">
	<h1><?php esc_html_e( 'Redux Framework - Changelog', 'redux-framework' ); ?></h1>
	<div class="about-text">
		<?php esc_html_e( 'Our core mantra at Redux is backwards compatibility. With hundreds of thousands of instances worldwide, you can be assured that we will take care of you and your clients.', 'redux-framework' ); ?>
	</div>
	<div class="redux-badge">
		<i class="el el-redux"></i>
		<span>
			<?php printf( esc_html__( 'Version', 'redux-framework' ) . ' %s', esc_html( ReduxCore::$_version ) ); ?>
		</span>
	</div>

	<?php $this->actions(); ?>
	<?php $this->tabs(); ?>

	<div class="changelog">
		<div class="feature-section">
			<?php echo wp_kses_post( $this->parse_readme() ); ?>
		</div>
	</div>
</div>
