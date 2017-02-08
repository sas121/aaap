<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><?php esc_html_e('Account Status', 'scribeseo'); ?></th>
			<td>
				<?php echo ( $account->accountStatus ? esc_html__( 'Active', 'scribeseo' ) : esc_html__( 'Inactive', 'scribeseo' ) ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e('Account Type', 'scribeseo'); ?></th>
			<td>
				<?php echo ( $account->accountType ? esc_html__( 'Professional', 'scribeseo' ) : esc_html__( 'Developer', 'scribeseo' ) ); ?>
			</td>
		</tr>
		
		<?php 
		$current_timestamp = current_time('timestamp');
		$current_datetime = current_time('mysql');
		$current_date = date('F j, Y', $current_timestamp);
		$next_month_timestamp = strtotime($current_datetime . ' + 1 month');
		$next_month_first_timestamp = mktime(0, 0, 0, date('n', $next_month_timestamp), 1, date('Y', $next_month_timestamp));
		$next_month_date = date('F j, Y \a\t g:i A', $next_month_first_timestamp + ( get_option( 'gmt_offset' ) * 3600 ));
		
		foreach( $account->evaluations as $account_evaluation_data ) {

			if ( 'ContentAnalysis' == $account_evaluation_data->type )
				$ca_evaluation_data = $account_evaluation_data;
			elseif ( 'KeywordIdeaResearch' == $account_evaluation_data->type )
				$kw_evaluation_data = $account_evaluation_data;

		}
		?>
		
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Evaluations', 'scribeseo' ); ?></th>
			<td>
				<?php printf( esc_html__( '%1$s Evaluations Per Month (1 Evaluation = 1 SEO Analysis)', 'scribeseo' ), number_format_i18n( $ca_evaluation_data->total ) ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Evaluations Left', 'scribeseo' ); ?></th>
			<td>
				<?php printf( esc_html__( '%1$s Evaluations as of %2$s', 'scribeseo' ), number_format_i18n( $ca_evaluation_data->remaining ), esc_html( $current_date ) ); ?><br />
				<?php printf( esc_html__( 'Monthly evaluations will be reset at %1$s', 'scribeseo' ), esc_html( $next_month_date ) ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Keyword Evaluations', 'scribeseo' ); ?></th>
			<td>
				<?php printf( esc_html__( '%1$s Evaluations Per Month (1 Evaluation = 1 Keyword Research)', 'scribeseo' ), number_format_i18n( $kw_evaluation_data->total ) ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Keyword Evaluations Left', 'scribeseo' ); ?></th>
			<td>
				<?php printf( esc_html__( '%1$s Evaluations as of %2$s', 'scribeseo' ), number_format_i18n( $kw_evaluation_data->remaining ), esc_html( $current_date ) ); ?><br />
				<?php printf( esc_html__( 'Monthly evaluations will be reset at %1$s', 'scribeseo' ), esc_html( $next_month_date ) ); ?>
			</td>
		</tr>
	</tbody>
</table>