jQuery(document).ready(function ($) {

	var slowUpdateInterval = 5000;
	var fastUpdateInterval = 2000;

	var $downloadLink = $('#db-snapshot-download-link');

	$("button#archive-inactive-plugins").click(function (event) {
		var $button = $(this);
		$button.attr('disabled', 'disabled');
		event.preventDefault();
		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{ 'action':'take_plugin_snapshot' },
			function (data) {
				var pluginSnapshots = $('#plugin-snapshots');
				pluginSnapshots.html(data);
				collapseDetails('#plugin-snapshots');
				pluginSnapshots.find('div.collapsible').first().next().toggle('fast');
				$button.removeAttr('disabled');
			}
		);

		return false;
	});

	$('div.smash-container').on('click', '.smash-panel div.collapsible', function () {
		$(this).next().toggle('fast');
		event.preventDefault();
		return false;
	});

	$('#plugin-snapshots').on('click', '.delete-snapshot', function (event) {
		var $this = $(this);
		var snapshotId = $this.attr('data-snapshot-id');
		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{
				'action':'delete_plugin_snapshot',
				'snapshot_id':snapshotId
			},
			function (data) {
				$this.parents('.smash-panel').remove();
			});
		event.preventDefault();
		return false;
	});

	var collapseDetails = function (container) {
		var $container = typeof(container) == 'string' ? $(container) : container;
		$container.find('div.collapsible').next().hide();
	};

	$("button#take-database-snapshot").click(function (event) {
		event.preventDefault();

		var $button = $(this);
		var $spinner = $('#db-snapshot-spinner');
		var $snapshotProgress = $("#db-snapshot-progress");
		$button.attr('disabled', 'disabled');

		$spinner.css('display', 'inline-block');
		$('#db-snapshot-progress').hide();
		$downloadLink.hide();

		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{
				'action':'make_database_backup'
			},
			function (data) {
				if (data.failed) {
					$snapshotProgress.html( 'Backup failed...' + data.message);
				}
			},
			'json'
		);

		return false;
	});

	$("button#save-s3-settings").click(function(event){
		event.preventDefault();

		var $spinner = $('#s3-settings-spinner');
		var $button = $(this);

		var settings = {
			'action': 'save_s3_backup_settings',
			'aws-access-key': $('#aws-access-key').val(),
			'aws-secret-key': $('#aws-secret-key').val(),
			's3-bucket': $('#s3-bucket').val(),
			's3-rsync-bucket': $('#s3-rsync-bucket').val(),
			's3-copies': $('#s3-copies').val(),
			's3-backup-databases': $('#s3-backup-databases').val(),
			's3-push-folders': $('#s3-push-folders').val(),
			's3-rsync-folders': $('#s3-rsync-folders').val()
		};

		$spinner.css('display', 'inline-block');
		$button.attr('disabled', 'disabled');

		$.post(
			SynthesisSoftwareMonitor.ajaxUrl,
			settings,
			function(response) {
				$('#s3-backup-settings').html(response);
				$spinner.hide();
				$button.removeAttr('disabled');
			},
			'text'
		);
		return false;
	});

	var $dbSnapshotProgress = $('#db-snapshot-progress');

	$dbSnapshotProgress.on('click', '.restore-table', function(event){
		event.preventDefault();

		var $this = $(this);
		var tableName = $this.attr('data-table-name');
		var backupId = $this.attr('data-backup-id');

		$this.parent().find('.backup-restore-spinner').css('display', 'inline-block');

		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{
				action: 'restore_table_backup',
				table_name: tableName,
				backup_id: backupId
			}, function(response){
			}, 'json'
		);

		return false;
	});

	$dbSnapshotProgress.on("click", ".cancel-restore", function(event){
		event.preventDefault();

		console.log('clicking');
		var $this = $(this);
		if ( !$this.attr('data-ignore') ) {
			console.log('canceling');
			var tableName = $this.attr("data-table-name");
			var backupId = $this.attr("data-backup-id");

			$this.parent().find('.backup-restore-spinner').css('display', 'inline-block');

			$this.attr('data-ignore', 'true');

			$.post(SynthesisSoftwareMonitor.ajaxUrl,
				{
					action: "cancel_table_restore",
					table_name: tableName,
					backup_id: backupId
				}, function(response){
				}, 'json'
			);
		}

		return false;
	});

	$dbSnapshotProgress.on("click", ".restore-all-tables", function(event){
		event.preventDefault();
		$('.restore-table').click();
		return false;
	});

	$dbSnapshotProgress.on("click", ".cancel-all-restores", function(event){
		event.preventDefault();
		$('.cancel-restore').click();
		return false;
	});

	var updateDatabaseStatus = function () {
		var $spinner = $("#db-snapshot-spinner");
		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{
				action:"get_database_backup_data"
			},
			function (data) {
				var $button = $("button#take-database-snapshot");
				// Disable the backup button if we detect a backup is running
				if (data.running || data.restore_running) {
					setDatabaseStatusUpdateInterval(fastUpdateInterval);
					$button.attr('disabled', 'disabled');
					$spinner.css('display', 'inline-block');
				} else {
					setDatabaseStatusUpdateInterval(slowUpdateInterval);
					$button.removeAttr('disabled');
					$spinner.css('display', 'none');
				}

				if (data.url) {
					$downloadLink.show().attr('href', data.url);
				} else {
					$downloadLink.hide();
				}

				var $backupContainer = $("#db-snapshot-progress");
				$backupContainer.show();
				var detailHidden = $backupContainer.children().first().children(".db-backup-details").is(":hidden");

				// Update our panel with the latest
				$backupContainer.html(data.markup);

				// Hide the detail view if it was hidden when we started
				if (detailHidden) {
					collapseDetails("#db-snapshot-progress")
				}
			},
			"json"
		);
	};

	var setDatabaseStatusUpdateInterval = (function(timer) {
		return function(interval) {
			clearInterval(timer);
			timer = setInterval(updateDatabaseStatus, interval);
		}
	})(setInterval(updateDatabaseStatus, slowUpdateInterval));
	
	var scribeForm = $('#synthesis-scribe-form');

	scribeForm.submit(function(event){
		event.preventDefault();

		var key = $('#synthesis-scribe-api-key').val();

		var $spinner = $('#synthesis-scribe-save-spinner');
		$spinner.css('display', 'inline-block');

		$.post(SynthesisSoftwareMonitor.ajaxUrl,
			{
				action: 'save_synthesis_scribe_api_key',
				key: key
			}, function(response){
				if ( response.error ) {
					$spinner.css('display', 'none');
					alert( response.error );
				} else {
					scribeForm.html('<div class="updated"><p>Synthesis Scribe API Key Saved</p></div>');
				}
			},
			'json'
		);

		return false;
	});

	updateDatabaseStatus();
	collapseDetails('#plugin-snapshots');
	collapseDetails("#db-snapshot-progress");
});

