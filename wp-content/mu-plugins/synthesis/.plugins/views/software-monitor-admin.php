<div class="wrap" id="synthesis-software-monitor">
	<?php screen_icon( 'synthesis-management' ); ?>

	<h2><?php _e( 'Synthesis Software Monitor' ); ?></h2>

	<?php if ( !get_option( Synthesis_Software_Monitor::SCRIBE_API_KEY, false) && !class_exists( 'Scribe_SEO' ) && !class_exists( 'Scribe_Data' ) ) : ?>
	<form action="" method="post" id="synthesis-scribe-form">
		<h3 class="title"><?php _e( 'Synthesis Scribe' ); ?></h3>

		<p>
			<?php printf( __( 'Enter your <a href="%s">Synthesis Scribe API Key</a> to enable Synthesis Scribe' ), esc_url( Synthesis_Software_Monitor::SCRIBE_KEY_URL ) ); ?>
		</p>

		<p>
			<input type="text" class="regular-text" name="synthesis-scribe-api-key" id="synthesis-scribe-api-key" value="" placeholder="<?php esc_attr_e( 'Synthesis Scribe API Key' ); ?>" />
		</p>
		<p>
			<input type="submit" class="button" id="synthesis-scribe-submit" value="Save Scribe Key" />
			<span class="spinner" id="synthesis-scribe-save-spinner" style="float: none; display: none"></span>
		</p>
	</form>
	<?php endif; ?>
	
	<?php Synthesis_Resource_Monitor::disk_quota_markup(); ?>
	<?php Synthesis_Resource_Monitor::bandwidth_quota_markup(); ?>

	<h3 class="title"><?php _e( 'Database Snapshot' ); ?></h3>

	<p>
		<?php _e( 'Take a snapshot of your database before making any major changes, like activating a new theme or plugin' ); ?>
	</p>
	
	<p>
		<?php _e( 'This snapshot targets configurations.  Tables not included are wp_posts, wp_postmeta, wp_comments, and wp_commentmeta.' ); ?>
	</p>

	<p>
		<strong><?php printf( __( "Tables with more than %s rows will not be included in snapshots" ), number_format( Synthesis_DB_Backup::MAX_TABLE_SIZE ) ); ?></strong>
	</p>

	<p>
		<?php
		$upload_dir = wp_upload_dir();
		printf( __( 'All DB backups are stored in %s' ), trailingslashit( $upload_dir['basedir'] ) . Synthesis_DB_Backup::BACKUP_FOLDER . '/' );
		?>
	</p>

	<form action="" method="post">
		<button class="button" id="take-database-snapshot" <?php disabled( false != Synthesis_DB_Backup::is_backup_running() ); ?>>New DB Snapshot</button>
		<span class="spinner" id="db-snapshot-spinner" style="float: none; display: none"></span>
	</form>

	<p>
        <a href="" style="display: none" id="db-snapshot-download-link"><?php _e( 'Download last snapshot' ); ?></a>
	</p>

	<div id="db-snapshot-progress" class="smash-container">
		<?php Synthesis_Software_Monitor::db_snapshots_markup(); ?>
	</div>

	</div>

	<h3 class="title"><?php _e( 'Personal Backups for S3' ); ?></h3>

    <p><?php _e( 'If you have a an Amazon S3 account, you can configure your Personal Backups for S3.'); ?>
	   <?php printf( __( 'Download the User Guide <a href="%s">here</a>' ), Synthesis_S3_Settings::BACKUP_GUIDE_URL ); ?></p>

    <form action="" method="post" >
        <div id="s3-backup-settings">
            <?php Synthesis_S3_Settings::s3_settings_markup(); ?>
        </div>
        <p>
            <button class="button" id="save-s3-settings"><?php _e( 'Save S3 Settings' ); ?></button>
            <span class="spinner" id="s3-settings-spinner" style="float: none; display: none"></span>
        </p>
    </form>

	<h3 class="title"><?php _e( 'Theme Monitor' ); ?></h3>

	<p>
		<?php
		if ( count( $nondefault_themes ) > 0 ) {
			_e( sprintf( 'You have %d inactive theme(s). You should delete them to keep your site secure.', count( $nondefault_themes ) ) );
		} else {
			_e( 'All is well. You have no inactive custom themes' );
		}
		?>
	</p>

	<h3 class="title" style=""><?php _e( 'Plugin Snapshots' ); ?></h3>
	<p>
		<?php _e( 'Take snapshot of your current active and inactive plugins as a reference. This is a report only function and DOES NOT save plugin files or settings.' ); ?>
		<?php printf( __( 'You can store up to %d snapshots.' ), Synthesis_Software_Monitor::SNAPSHOT_LIMIT ); ?>
	</p>

	<form action="" method="post">
		<button class="button" id="archive-inactive-plugins">New Plugin Snapshot</button>
	</form>

	<p>
		<?php _e( 'Your snapshot reports are listed here. Click a snapshot report title to show report details.' ); ?>
	</p>
	<div id="plugin-snapshots" class="smash-container">
		<?php Synthesis_Software_Monitor::plugin_snapshots_markup(); ?>
	</div>
