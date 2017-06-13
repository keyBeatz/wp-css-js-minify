<?php

namespace CJM\Admin;

defined( 'ABSPATH' ) || exit();

class Minify extends AdminBase
{

	public $all_minified_reg_handles;

	function __construct() {
		parent::__construct();

		$this->all_minified_reg_handles = cjm_get_all_minified_parts();

		add_action( 'admin_menu', array( $this, 'menu' ) );
   }

	public function menu() {
		add_submenu_page(
			'options-general.php',
			'CSS & JS Minify',
			'CSS & JS Minify',
			'administrator',
			'css-js-minify-settings',
			array( $this, 'render_view' )
		);
	}

	public function render_view() {
?>

<div class="wrap">
	<h2><?php esc_html_e( 'CSS & JS minification & concat settings', 'css-js-minify' ); ?></h2>
	<div id="cjm_minify" class="cjm">
		<div id="tabs" class="cjm-admin-wrap">
			<ul>
				<li><a href="#tab-css">CSS</a></li>
				<li><a href="#tab-js">JS</a></li>
			</ul>
			<div id="tab-css">
				<!-- SORTABLE WRAPPER -->
				<div class="cjm-sortable-wrapper">

					<!-- CONTROL PANEL -->
					<?php $this->filePanel( 'css' ); ?>
					<!-- /CONTROL PANEL -->

					<!-- SORTABLE FIRST BLOCK, DEFAULT -->
					<?php $this->loadInitBlock( 'css' ); ?>
					<!-- /SORTABLE FIRST BLOCK, DEFAULT -->

					<!-- ADD INITIAL BLOCK OR LOAD EXISTING BLOCKS -->
					<?php
						if( !empty( $this->getMinifiedCss() ) ) {
							foreach( $this->getMinifiedCss() as $key => $file )
								$this->loadBlock( 'css', $file, $key );
						}
						else
							$this->newBlockHtml( 'css' );
					?>
					<!-- /ADD INITIAL BLOCK OR LOAD EXISTING BLOCKS -->

					<!-- ADD BLOCK BUTTON -->
					<?php $this->addBlock(); ?>
					<!-- /ADD BLOCK BUTTON -->
				</div>
				<!-- /SORTABLE WRAPPER -->
			</div>
			<div id="tab-js">
				<!-- SORTABLE WRAPPER -->
				<div class="cjm-sortable-wrapper">

					<!-- CONTROL PANEL -->
					<?php $this->filePanel( 'js' ); ?>
					<!-- /CONTROL PANEL -->

					<!-- SORTABLE FIRST BLOCK, DEFAULT -->
					<?php $this->loadInitBlock( 'js' ); ?>
					<!-- /SORTABLE FIRST BLOCK, DEFAULT -->

					<!-- ADD INITIAL BLOCK OR LOAD EXISTING BLOCKS -->
					<?php
						if( !empty( $this->getMinifiedJs() ) ) {
							foreach( $this->getMinifiedJs() as $key => $file )
								$this->loadBlock( 'js', $file, $key );
						}
						else
							$this->newBlockHtml( 'js' );
					?>
					<!-- /ADD INITIAL BLOCK OR LOAD EXISTING BLOCKS -->

					<!-- ADD BLOCK BUTTON -->
					<?php $this->addBlock(); ?>
					<!-- /ADD BLOCK BUTTON -->
				</div>
				<!-- /SORTABLE WRAPPER -->
			</div>
		</div>
		<?php $this->guideHtml(); ?>
	</div>
</div>
<script>
	(function($) {
		$( document ).ready(function() {
			$( "#tabs" ).tabs({
			    beforeActivate: function (event, ui) {
			        window.location.hash = ui.newPanel.selector;
			    }
			});

			var css = new CssJsMinify( 'css', '<?php echo cjm_render_as_string( array( $this, 'newBlockHtml' ), true, array( 'css' ) ); ?>' );
			css.init();
			var js = new CssJsMinify( 'js', '<?php echo cjm_render_as_string( array( $this, 'newBlockHtml' ), true, array( 'js' ) ); ?>' );
			js.init();

		});
	})(jQuery);
</script>
<?php
	}

	public function newBlockHtml( $mode = false ) {
	?>
	<div class="cjm_sortable_box">
		<?php $this->blockControls(); ?>
		<ul id="" class="cjm_sortable css_sortable created with_message">
			<?php echo $this->blockMessage(); ?>
		</ul>
		<?php $this->blockSettings( $mode ); ?>
	</div>
	<?php
	}

	public function blockControls() {
	?>
	<nav class="cjm_sortable_header">
		<ul style="float:left;">
			<li>
				<button data-for="move-left" data-toggle="tooltip" title="<?php esc_attr_e( 'Move file block left', 'css-js-minify' ); ?>">
                    <span class="cjm-block-icon dashicons dashicons-arrow-left-alt2"></span>
                </button>
			</li>
			<li>
				<button data-for="move-right" data-toggle="tooltip" title="<?php esc_attr_e( 'Move file block right', 'css-js-minify' ); ?>">
                    <span class="cjm-block-icon dashicons dashicons-arrow-right-alt2"></span>
                </button>
			</li>
		</ul>
		<ul style="float:right;">
			<li>
				<button data-for="settings" data-toggle="tooltip" title="<?php esc_attr_e( 'Show file block settings', 'css-js-minify' ); ?>">
                    <span class="cjm-block-icon dashicons dashicons-admin-generic"></span>
                </button>
			</li>
			<li>
				<button data-for="delete" data-toggle="tooltip" title="<?php esc_attr_e( 'Remove file block', 'css-js-minify' ); ?>">
                    <span class="cjm-block-icon dashicons dashicons-trash"></span>
                </button>
			</li>
		</ul>
	</nav>
	<?php
	}

	public function loadBlock( $mode, $file, $key ) {
		if( empty( $file ) )
			return;

		if( $mode == 'css' ) {
			$registered = $this->getCssLog();
		}
		else if( $mode == 'js' ) {
			$registered = $this->getJsLog();
		}
	?>
	<?php if( !empty( $file['files'] ) && is_array( $file['files'] ) ) : ?>
	<div class="cjm_sortable_box">
		<?php $this->blockControls(); ?>
		<ul id="<?php echo esc_attr( "cjm_sortable" . $mode . "_" . $key ); ?>" class="cjm_sortable <?php echo esc_attr( $mode . "_sortable" ); ?> created with_message">
            <?php $this->blockMessage( 'activated', true ); ?>
            <?php foreach( $file['files'] as $handle ) : if( isset( $registered{$handle} ) ) : ?>
				<?php
					$priority = array_search( $handle, array_keys( $registered ) );
					$this->blockItem( $registered, $handle, $mode, ++$priority );
				?>
			<?php endif; endforeach; ?>
		</ul>
		<?php $this->blockSettings( $mode, $file, $key ); ?>
	</div>
	<?php endif;?>
	<?php
	}

	public function blockSettings( $mode, $file = false, $key = "" ) {
	?>
	<div class="cjm_block_settings" style="display:none;">
		<nav class="cjm_sortable_header">
			<ul>
				<li>
					<button data-for="close-settings" data-toggle="tooltip" title="<?php esc_attr_e( 'Hide settings', 'css-js-minify' ); ?>">
                        <span class="cjm-block-icon cjm-dark dashicons dashicons-no"></span>
                    </button>
				</li>
			</ul>
		</nav>
		<ul>
		<?php if( $mode == 'css' ) : ?>
			<li>
				<label for="<?php echo esc_attr( "cjm_css_media_{$key}" ); ?>"><?php esc_html_e( 'Media', 'css-js-minify' ); ?></label>
				<select id="<?php echo esc_attr( "cjm_css_media_{$key}" ); ?>" for="<?php echo esc_attr( "cjm_css_media_{$key}" ); ?>" class="cjm_css_media">
					<?php
						$available_media = array( "all", "print", "screen", "speech", "aural", "braille", "handheld", "projection", "tty", "tv" );
					?>
					<?php foreach( $available_media as $media_val ) : ?>
						<option value="<?php echo $media_val; ?>" <?php echo $media_val == $file['media'] ? 'selected' : ''; ?>><?php echo $media_val; ?></option>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>"><?php esc_html_e( 'Load asynchronously', 'css-js-minify' ); ?></label>
				<input type="checkbox" id="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>" class="cjm_css_async" <?php echo $file['async'] === 'async' ? 'checked="checked"' : ""; ?>>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>"><?php esc_html_e( 'Priority', 'css-js-minify' ); ?></label>
				<input type="number" id="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>" value="<?php echo esc_attr( !empty( $file['priority'] ) && is_numeric( $file['priority'] ) ? $file['priority'] : $this->getDefaultPriority() ); ?>" class="cjm_css_priority" min="1" max="99999">
			</li>
		<?php elseif( $mode == 'js' ) : ?>
			<li>
				<label for="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>"><?php esc_html_e( 'In footer', 'css-js-minify' ); ?></label>
				<input type="checkbox" id="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>" class="cjm_js_in_footer" <?php echo $file['in_footer'] === true ? 'checked="checked"' : ""; ?>>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_js_async_{$key}" ); ?>"><?php esc_html_e( 'Load asynchronously', 'css-js-minify' ); ?></label>
				<select id="async <?php echo esc_attr( "cjm_js_async_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_js_async_{$key}" ); ?>" class="cjm_js_async" >
					<?php
						$available_async = array( "async", "defer" );
					?>
					<option value="false" <?php echo ( empty( $file['async'] ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Default', 'css-js-minify' ); ?></option>
					<?php foreach( $available_async as $val ) : ?>
						<option value="<?php echo $val; ?>" <?php echo $val == $file['async'] ? 'selected' : ''; ?>><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_js_priority_{$key}" ); ?>"><?php esc_html_e( 'Priority', 'css-js-minify' ); ?></label>
				<input type="number" id="<?php echo esc_attr( "cjm_js_priority_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_js_priority_{$key}" ); ?>" value="<?php echo esc_attr( !empty( $file['priority'] ) && is_numeric( $file['priority'] ) ? $file['priority'] : $this->getDefaultPriority() ); ?>" class="cjm_js_priority" min="1" max="99999">
			</li>
		<?php endif; ?>
		</ul>
	</div>
	<?php
	}

	public function loadInitBlock( $mode ) {
		if( $mode == 'css' ) {
			$registered = $this->getCssLog();
		}
		else if( $mode == 'js' ) {
			$registered = $this->getJsLog();
		}
	?>
	<div id="<?php echo esc_attr( "cjm_sortable_{$mode}_init" ); ?>" class="cjm_sortable_box main">

		<ul class="cjm_sortable <?php echo esc_attr( "{$mode}_sortable" ); ?> main">
			<?php
			$priority 		= 0;
			$printed_files = 0;
			?>
            <li>
			<?php if( !empty( $registered ) ) : ?>
			<?php foreach( $registered as $handle => $file ) : $priority++; if( !in_array( $handle, $this->all_minified_reg_handles[$mode] ) ) : $printed_files++; ?>
				<?php $this->blockItem( $registered, $handle, $mode, $priority ); ?>
			</li>
			<?php endif; endforeach; ?>
			<?php endif;?>
			<?php
			if( !empty( $registered ) )
				$this->blockMessage( 'empty', $hide = $printed_files == 0 ? false : true );
			else
				$this->blockMessage( 'not_activated', $hide = $printed_files == 0 ? false : true );
			?>
		</ul>
	</div>
	<?php
	}

	public function blockItem( $registered, $handle, $mode, $priority ) {
	?>
	<li id="<?php echo $handle; ?>"
		class="ui-state-default cjm_item <?php echo esc_attr( "cjm_item_" . $mode ); ?>"
		data-ver="<?php echo $registered{$handle}->ver; ?>"
		data-src="<?php echo $registered{$handle}->src; ?>"
		data-deps="<?php echo implode( ", ", $registered{$handle}->deps ); ?>"
		data-media="<?php echo $registered{$handle}->args; ?>">
		<div class="sLeft">
			<div class="sTop">
				<span class="cjm_item_priority"><?php echo $priority; ?></span>
				<?php echo $handle; ?>
			</div>
			<div class="sMid">
				<?php if( !empty( $registered{$handle}->args ) && $mode == 'css' ) : ?>
					<span class="cjm_bracket <?php echo $mode == 'css' ? 'media' : ( $mode == 'js' ? 'in_footer' : '' ); ?>">
						<?php echo $mode == 'css' ? $registered{$handle}->args : ( $mode == 'js' ? esc_html__( 'In footer', 'css-js-minify' ) : '' ); ?>
					</span>
				<?php endif; ?>
				<?php if( !empty( $registered{$handle}->in_footer ) ) : ?>
					<span class="cjm_bracket in_footer">
						<?php esc_html_e( 'In footer', 'css-js-minify' ); ?>
					</span>
				<?php endif; ?>
				<?php if( !empty( $registered{$handle}->ver ) ) : ?>
					<span class="cjm_bracket ver"><?php echo $registered{$handle}->ver; ?></span>
				<?php endif; ?>
			</div>
			<div class="sBot sSettings" style="display:none;">
				<ul>
					<li class="url">
						<?php esc_html_e( 'Url', 'css-js-minify' ); ?>:
						<?php echo cjm_strip_site_from_url( $registered{$handle}->src, 'wp-content' ); ?>
					</li>
					<li class="deps">
						<?php if( !empty( $registered{$handle}->deps ) ) : ?>
                            <?php esc_html_e( 'Dependencies', 'css-js-minify' ); ?>:
							<?php foreach( $registered{$handle}->deps as $dep ) : ?>
								<span class="cjm_bracket"><?php echo $dep; ?></span>
							<?php endforeach; ?>
						<?php endif; ?>
					</li>
				</ul>
			</div>
		</div>
		<div class="sRight sOpen">
			<?php echo cjm_img( 'double-arrow' ); ?>
		</div>
	</li>
	<?php
	}

	public function blockMessage( $state = 'activated', $hide = false ) {

		$msg['activated'] 		= esc_html__( 'Move file boxes here', 'css-js-minify' );
		$msg['not_activated'] 	= sprintf(
			'%s <a href="'. esc_url( site_url() ) .'" target="_blank">%s</a>.',
			__( 'First you need to load existing files, please revisit', 'css-js-minify' ),
			__( 'your webpage', 'css-js-minify' )
		);
		$msg['empty'] 			= esc_html__( 'All boxes has been already assigned.', 'css-js-minify' );

		if( isset( $msg[$state] ) ) :
	?>
	<div class="cjm_box_message_wrap <?php echo esc_attr( "state_{$state}" ); ?>" style="<?php echo $hide ? 'display:none;' : ''; ?>">
		<div class="cjm_box_message">
			<?php echo $msg[$state]; ?>
		</div>
	</div>
   <?php
		endif;
	}

	public function filePanel( $mode ) {
	?>
	<header id="file_header_<?php echo esc_attr( $mode ); ?>">
		<nav class="cjm_file_header">
			<ul style="float:left;">
				<li>
					<button data-for="generate" data-toggle="tooltip" data-nonce="<?php echo wp_create_nonce( 'generate_minified_files' ); ?>" title="<?php esc_attr_e( 'Generate files', 'css-js-minify' ); ?>">
                        <span class="cjm-wp-icon dashicons dashicons-welcome-add-page"></span>
                        <?php /*esc_html_e( 'Save', 'css-js-minify' ); */?>
					</button>
				</li>
				<li>
					<button data-for="flush" data-toggle="tooltip" title="<?php esc_attr_e( 'Erase all files', 'css-js-minify' ); ?>">
                        <span class="cjm-wp-icon dashicons dashicons-trash"></span>
                        <?php /*esc_html_e( 'Delete all', 'css-js-minify' ); */?>
					</button>
				</li>
				<li>
					<button data-for="guide" data-toggle="tooltip" title="<?php esc_attr_e( 'Show guidance', 'css-js-minify' ); ?>">
                        <span class="cjm-wp-icon dashicons dashicons-editor-help"></span>
                        <?php /*esc_html_e( 'Help', 'css-js-minify' ); */?>
					</button>
				</li>

				<li>
					<?php echo cjm_ajax_loader(); ?>
				</li>
			</ul>
			<ul style="float:right;">
				<li>
					<!-- SWITCH BUTTON -->
					<?php
						$state = false;
						if( $mode == "css" )
						    $state = $this->isCssOn();
						else if( $mode == "js" )
						    $state = $this->isJsOn();
					?>
					<label class="cjm switch" data-toggle="tooltip" title="<?php printf( esc_attr__( 'Turn on/off %s', 'css-js-minify' ), $mode ); ?>">
						<input class="cjm_main_toggle" data-nonce="<?php echo wp_create_nonce( 'main_toggle' ); ?>" type="checkbox" <?php if( $state === true ) echo 'checked="checked"'; ?>>
						<div class="slider round"></div>
					</label>
					<!-- /SWITCH BUTTON -->
				</li>
			</ul>
		</nav>
	</header>
	<?php
	}

	public function addBlock() {
	?>
	<div class="cjm-sortable-add-block-wrapper">
		<button class="cjm-sortable-add-block" title="<?php esc_attr_e( 'Click to add new file block', 'css-js-minify' ); ?>">
            <span class="cjm-add-block-icon dashicons dashicons-plus"></span>
			<?php esc_html_e( 'Add file block', 'css-js-minify' ); ?>
		</button>
	</div>
	<?php
	}

	public function guideHtml() {
	?>
	<div id="cjm_help" title="<?php esc_attr_e( 'CSS & JS Minify - guide', 'css-js-minify' ); ?>" style="display:none;">

        <nav class="cjm-help-nav">
            <ul>
                <li data-tab="cjm_step_0" class="active"><?php esc_html_e( 'Introduction', 'css-js-minify' ); ?></li>
                <li data-tab="cjm_step_1"><?php esc_html_e( 'Tabs', 'css-js-minify' ); ?></li>
                <li data-tab="cjm_step_2"><?php esc_html_e( 'Buttons', 'css-js-minify' ); ?></li>
                <li data-tab="cjm_step_3"><?php esc_html_e( 'Blocks', 'css-js-minify' ); ?></li>
                <li data-tab="cjm_step_4"><?php esc_html_e( 'Boxes', 'css-js-minify' ); ?></li>
            </ul>
        </nav>

        <div id="cjm_step_0" class="cjm-help-tab active">

            <div class="cjm-help-content">

                <p>
	                <?php esc_html_e( "Welcome to our community plugin guide.", 'css-js-minify' ); ?>
	                <?php esc_html_e( "This guide will guide you through functions, features and terms used in this plugin.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "I belive this plugin provides the most possible user friendly way of defining, minifying and concating your existing CSS & JS files among all existing possibilities (another plugins).", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "However if you encounter any bugs feel free to send report to the author - you will find contact on github or WP plugin page.", 'css-js-minify' ); ?>
                </p>

            </div>

        </div>
        <div id="cjm_step_1" class="cjm-help-tab">

            <div class="cjm-help-content">
                <p>
	                <?php esc_html_e( "Each tab stands for separate section - every section is set independently on others.", 'css-js-minify' ); ?>
                </p>
                <ul>
                    <li>
                        <strong><?php esc_html_e( "CSS", 'css-js-minify' ); ?>:</strong>
                        <?php esc_html_e( "providing minification & concatenation options and settings for your registered cascading styles.", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "JS", 'css-js-minify' ); ?>:</strong>
                        <?php esc_html_e( "providing minification & concatenation options and settings for your registered javascript files.", 'css-js-minify' ); ?>

                    </li>
                </ul>
            </div>

        </div>
        <div id="cjm_step_2" class="cjm-help-tab">

            <div class="cjm-help-content">

                <p>
	                <?php esc_html_e( "Every single button represents certain action over the tab.", 'css-js-minify' ); ?>
                </p>
                <ul>
                    <li>
                        <strong><?php esc_html_e( "Save", 'css-js-minify' ); ?>:</strong>
	                    <?php esc_html_e( "will generate minified files sorted by block positions and positions of their boxes.", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "Delete all", 'css-js-minify' ); ?>:</strong>
	                    <?php esc_html_e( "will delete all existing blocks within current tab. To make effect permanent you need also click Save button", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "Help", 'css-js-minify' ); ?>:</strong>
	                    <?php esc_html_e( "will show this guide.", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "Switch (on/off)", 'css-js-minify' ); ?>:</strong>
	                    <?php esc_html_e( "is placed at the top right corner and is used to turn on/off loading minified files (always set separately for each tab).", 'css-js-minify' ); ?>
                        <p>
	                        <strong>
		                        <?php esc_html_e( "Please note: if you have turned this switch on, logging new files will not be loading - if you need to load new files you must turn off this switch first.", 'css-js-minify' ); ?>
                            </strong>
                        </p>
                    </li>
                </ul>

            </div>

        </div>
        <div id="cjm_step_3" class="cjm-help-tab">

            <div class="cjm-help-content">
                <p>
	                <?php esc_html_e( "Each block represents one minified file.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "First red block is the init base block. It's containing all registered styles/script files and you can sort them from here to your custom defined blocks.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "Boxes in red block are not loaded (when minification switch is turned on) and their default order is based on their original loading priorities in WordPress.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "Every custom block (grey ones) can contain infinite number of boxes.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "The order of blocks matters. Position of block defines loading priority (by default), higher priority on the left. However you can override priorities in block settings.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "Block buttons have these functions", 'css-js-minify' ); ?>:
                </p>
                <ul>
                    <li>
                        <strong><?php esc_html_e( "Arrow left/right", 'css-js-minify' ); ?>: </strong>
	                    <?php esc_html_e( "move block in desired direction - it will determine blocks loading priorities - the most left positioned block has higher priority, you can however change priority manually in block settings..", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "Gear", 'css-js-minify' ); ?>: </strong>
	                    <?php esc_html_e( "this opens the block settings. Settings options differ for CSS and JS. For CSS you can choose media, for JS if file will be included in footer or in header. For both you cas set asynchronous or deferred loading.", 'css-js-minify' ); ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e( "Basket", 'css-js-minify' ); ?>: </strong>
	                    <?php esc_html_e( "will delete the block. To make changes parmanent you must also save everything with Save button.", 'css-js-minify' ); ?>
                    </li>
                </ul>
            </div>

        </div>
        <div id="cjm_step_4" class="cjm-help-tab">

            <div class="cjm-help-content">
                <p>
	                <?php esc_html_e( "One box is representing one file loaded by WordPress.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "In the initial state all boxes are placed in the red block (init base block) in deactivated state and sorted by their default loading priorities.", 'css-js-minify' ); ?>
	                <?php esc_html_e( "If plugin minification is enabled (switch is turned on) boxes placed in red init box will not be loaded by WordPress at all.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "Notice little grey number in top left corner - it's holding original priority order - eg. 3 was loaded before 5.", 'css-js-minify' ); ?>
	                <?php esc_html_e( "You should always take this number seriously and order boxes in your custom blocks by them.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "Under the priority number there are couple of labels helping you to decide where should the box be placed.", 'css-js-minify' ); ?>
	                <?php esc_html_e( "Grey labels stands for file version and is not that important.", 'css-js-minify' ); ?>
	                <strong><?php esc_html_e( "Hint", 'css-js-minify' ); ?>: </strong>
	                <?php esc_html_e( "CSS with same media (green) label and JS with 'in footer' (red) label should always be together - in most cases.", 'css-js-minify' ); ?>
                </p>
                <p>
	                <?php esc_html_e( "On the right you can find arrow which will show additional data like url path.", 'css-js-minify' ); ?>
                </p>
                <p>
                    <strong><?php esc_html_e( "Important", 'css-js-minify' ); ?> :</strong>
	                <?php esc_html_e( "Positions of boxes within their block will determine priority of that same file when they will be concatenated.", 'css-js-minify' ); ?>
	                <?php esc_html_e( "Always decide by priority number (in top left corner) where the box should stand - because earlier loaded files may be (and often are) overriden by later loaded files.", 'css-js-minify' ); ?>
                </p>
            </div>

        </div>

	</div>
	<?php
	}
}

