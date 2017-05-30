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
		add_menu_page(
			'Optimalizace',
			'Optimalizace',
			'manage_options',
			'cjm-settings.php',
			array( $this, 'render_view' ),
			'dashicons-performance',
			6
		);
	}

	public function render_view() {
?>

<div class="wrap">
	<h2>Nastavení minifikace a spojování souborů CSS & JS</h2>
	<div id="cjm_minify" class="cjm">
		<div id="tabs">
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
				<button data-for="move-left" data-toggle="tooltip" title="Posunout doleva"><?php echo cjm_img( 'arrow-left' ); ?></button>
			</li>
			<li>
				<button data-for="move-right" data-toggle="tooltip" title="Posunout doprava"><?php echo cjm_img( 'arrow-right' ); ?></button>
			</li>
		</ul>
		<ul style="float:right;">
			<li>
				<button data-for="settings" data-toggle="tooltip" title="Zobrazit nastavení"><?php echo cjm_img( 'settings' ); ?></button>
			</li>
			<li>
				<button data-for="delete" data-toggle="tooltip" title="Smazat blok"><?php echo cjm_img( 'delete' ); ?></button>
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
			<?php foreach( $file['files'] as $handle ) : if( isset( $registered{$handle} ) ) : ?>
				<?php
					$priority = array_search( $handle, array_keys( $registered ) );
					$this->blockItem( $registered, $handle, $mode, ++$priority );
				?>
			<?php endif; endforeach; ?>
			<?php $this->blockMessage( 'activated', true ); ?>
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
					<button data-for="close-settings" data-toggle="tooltip" title="Skrýt nastavení"><?php echo cjm_img( 'cancel' ); ?></button>
				</li>
			</ul>
		</nav>
		<ul>
		<?php if( $mode == 'css' ) : ?>
			<li>
				<label for="<?php echo esc_attr( "cjm_css_media_{$key}" ); ?>">Media</label>
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
				<label for="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>">Načítat asynchronně</label>
				<input type="checkbox" id="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_css_async_{$key}" ); ?>" class="cjm_css_async" <?php echo $file['async'] === 'async' ? 'checked="checked"' : ""; ?>>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>">Priorita</label>
				<input type="number" id="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_css_priority_{$key}" ); ?>" value="<?php echo esc_attr( !empty( $file['priority'] ) && is_numeric( $file['priority'] ) ? $file['priority'] : $this->getDefaultPriority() ); ?>" class="cjm_css_priority" min="1" max="99999">
			</li>
		<?php elseif( $mode == 'js' ) : ?>
			<li>
				<label for="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>">V patičce</label>
				<input type="checkbox" id="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_in_footer_{$key}" ); ?>" class="cjm_js_in_footer" <?php echo $file['in_footer'] === true ? 'checked="checked"' : ""; ?>>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_js_async_{$key}" ); ?>">Načítat asynchronně</label>
				<select id="async <?php echo esc_attr( "cjm_js_async_{$key}" ); ?>" name="<?php echo esc_attr( "cjm_js_async_{$key}" ); ?>" class="cjm_js_async" >
					<?php
						$available_async = array( "async", "defer" );
					?>
					<option value="false" <?php echo ( empty( $file['async'] ) ) ? 'selected' : ''; ?>>klasicky</option>
					<?php foreach( $available_async as $val ) : ?>
						<option value="<?php echo $val; ?>" <?php echo $val == $file['async'] ? 'selected' : ''; ?>><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<label for="<?php echo esc_attr( "cjm_js_priority_{$key}" ); ?>">Priorita</label>
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
						<?php echo $mode == 'css' ? $registered{$handle}->args : ( $mode == 'js' ? 'V patičce' : '' ); ?>
					</span>
				<?php endif; ?>
				<?php if( !empty( $registered{$handle}->in_footer ) ) : ?>
					<span class="cjm_bracket in_footer">
						V patičce
					</span>
				<?php endif; ?>
				<?php if( !empty( $registered{$handle}->ver ) ) : ?>
					<span class="cjm_bracket ver"><?php echo $registered{$handle}->ver; ?></span>
				<?php endif; ?>
			</div>
			<div class="sBot sSettings" style="display:none;">
				<ul>
					<li class="url">
						Url:
						<?php echo cjm_strip_site_from_url( $registered{$handle}->src, 'wp-content' ); ?>
					</li>
					<li class="deps">
						<?php if( !empty( $registered{$handle}->deps ) ) : ?>
							Závislosti:
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
		$msg['activated'] 		= "Přetáhněte boxíky sem";
		$msg['not_activated'] 	= 'První je třeba načíst soubory, navštivte prosím <a href="'. esc_url( site_url() ) .'" target="_blank">stránku webu</a>.';
		$msg['empty'] 			= "Všechny položky byly rozděleny";

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
					<button data-for="generate" data-nonce="<?php echo wp_create_nonce( 'generate_minified_files' ); ?>" title="Generovat soubory">
						<?php echo cjm_img( 'floppy', 24 ); ?>
						Uložit
					</button>
				</li>
				<li>
					<button data-for="flush" title="Vyčistit soubory">
						<?php echo cjm_img( 'delete', 24 ); ?>
						Smazat vše
					</button>
				</li>
				<li>
					<button data-for="guide" title="Zobrazit nápovědu">
						<?php echo cjm_img( 'info', 24 ); ?>
						Nápověda
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
					<label class="cjm switch" data-toggle="tooltip" title="<?php echo esc_attr( "Zapnout/vypnout ({$mode})" ); ?>">
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
		<button class="cjm-sortable-add-block">
			<img src="<?php echo cjm_img( 'plus', '64', true ); ?>" alt="Přidat blok" title="Kliknutím vytvoříte další blok">
			Přidat blok
		</button>
	</div>
	<?php
	}

	public function guideHtml() {
	?>
	<div id="cjm_help" title="Nápověda pro minifikátor" style="display:none;">
		<h2 class="sTitle">Záložky</h2>
		<p>
			 Záložky slouží pro oddělení jednotlivých sekcí, z níchž každá se nastavuje zvlášť.
			 <ul>
				<li><strong>CSS:</strong> slouží pro sestavovaní minifikovaných souborů pro styly</li>
				<li><strong>JS:</strong> slouží pro sestavovaní minifikovaných souborů pro skripty</li>
			 </ul>
		</p>
		<h2 class="sTitle">Tlačítka</h2>
		<p>
			Tlačítka reprezentují vždy určité akce, které se vážou buďto k dané záložce, nebo bloku.
			<ul>
			  <li><strong>Uložit:</strong> vygeneruje minifikované soubory dle naskládaných bloků a jejich boxů</li>
			  <li><strong>Smazat vše:</strong> smaže všechny bloky dané záložky, ale pro trvalý efekt je třeba ještě uložit</li>
			  <li><strong>Nápověda:</strong> zobrazí tuto nápovědu</li>
			  <li><strong>Přepínač:</strong> je umístěný úplně vpravo v hlavičce záložky a slouží pro vypnutí a zapnutí minifikátoru (vždy pro záložku zvlášť, takže lze mít zaplé css zatímco js je vyplé)</li>
			</ul>
		</p>
		<h2 class="sTitle">Bloky</h2>
		<p>
			Blok reprezentuje jeden soubor.
			První blok (oranžový), obsahuje výchozí registrované styly, které jde volně rozřazovat do bloků.
			Boxy v oranžovém bloku jsou načteny dle svého původního nastavení a nevystupují nijak v minifikátoru.
			Každý (šedý) blok může obsahovat libovolný počet boxů.
			Na pořadí bloků záleží, jelikož určují prioritu načítání (lze měnit).
			Tlačítka bloků mají tyto funkce:
			<ul>
			  <li><strong>Šipka vlevo/vpravo:</strong> posouvání bloku určeným směrem, takto můžete vybírat prioritu načítání (první blok vlevo se bude načítat jako první)</li>
			  <li><strong>Ozubené kolo:</strong> nastavení pro daný blok (většinou se nastavují atributy, pro CSS media a pro JS např. to, jestli se má blok načítat v patičce nebo v hlavičce dokumentu)</li>
			  <li><strong>Koš:</strong> smaže blok (pro úplné smazání je třeba uložit)</li>
			</ul>
		</p>
		<h2 class="sTitle">Boxy</h2>
		<p>
			Box reprezentuje jeden soubor, který byl načtený WordPressem.
			Ve výchozím stavu jsou načteny všechny vlevo v oranžovém boxu, kde jsou "deaktivované" pro minifikátor a načítají se dle svého původního nastavení.
			Číslo vedle názvu značí prioritu v originálním načítání, na toto by se měl brát vždy ohled, jelikož soubory na sobě bývají buď závislé nebo se navzájem přepisují.
			Pod číslem označující prioritu a názvem se nachází štítky s příznaky, pro CSS zelené značí media atribut a pro JS červené značí, jestli se originálně načítají v patičce.
			Šedý štítek pak znamená verzi souboru.
			Na pravé straně boxu se nachází šipka, která rozklikne další informace o souboru, které mohou být užitečné při identifikaci či rozhodování kam soubor zařadit.
			<strong>Pořadí v boxech značí prioritu, ve které budou vkládány do minifikátoru, vždy by se měl při tom dávat důraz na původní číslo priority.</strong>
		</p>

	</div>
	<?php
	}
}

