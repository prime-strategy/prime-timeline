<p><?php echo sprintf( __( 'Total execution time (%.2f msec)', 'prime-timeline' ), $exec ); ?></p>
<table>
	<tbody>
		<tr id='ps_tr_head'>
			<th width='5%'><?php _e( 'No.', 'prime-timeline' ); ?></th>
			<th width='5%'><?php _e( 'Total', 'prime-timeline' ); ?><br />(msec)</th>
			<th width='10%'><?php _e( 'Type', 'prime-timeline' ); ?></th>
			<th width='5%'><?php _e( 'File', 'prime-timeline' ); ?></th>
			<th width='30%'><?php _e( 'Hook/SQL', 'prime-timeline' ); ?></th>
			<th width='30%'><?php _e( 'Detail', 'prime-timeline' ); ?></th>
			<th width='5%'><?php _e( 'Count', 'prime-timeline' ); ?></th>
			<th width='5%'><?php _e( 'Diff', 'prime-timeline' ); ?><br />(msec)</th>
			<th width='5%'><?php _e( 'This', 'prime-timeline' ); ?><br />(msec)</th>
		</tr>
