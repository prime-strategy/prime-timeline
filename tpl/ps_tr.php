<?php
	if ( $val['type'] == 'hook' ) :
?>
<?php
		if ( isset( $data['time'] ) ) :
?>
<tr class='ps_hook ps_hook_do'>
	<td><?php echo $j; ?></td>
	<td><?php echo sprintf( '%.2f', $checksum ); ?></td>
	<td><?php echo $function; ?></td>
	<td>*</td>
	<td><?php echo esc_html( $tag ); ?></td>
	<td><?php echo esc_html( $file ); ?> [ line <?php echo $line; ?> ]<br/>
<?php
			foreach ( $data['callback'] as $priority => $functions ) :
?>
			&nbsp;&nbsp;<?php echo $priority; ?> =&gt;
<?php
				foreach ( $functions as $func => $num ) :
?>
&nbsp;<?php echo esc_html( $func ); ?>(<?php echo $num; ?>)
<?php
				endforeach;
?>
			<br />
<?php
			endforeach;
?>
	</td>
	<td>(<?php echo $data['count']; ?>)</td>
	<td class='ps_time'><?php echo sprintf( '%.2f', $diff ); ?></td>
	<td class='ps_time'><?php echo sprintf( '%.2f', $data['time'] ); ?>
<?php
		else :
?>
<tr class='ps_hook ps_hook_pass'>
	<td><?php echo $j; ?></td>
	<td><?php echo sprintf( '%.2f', $checksum ); ?></td>
	<td><?php echo $function; ?></td>
	<td>&nbsp;</td>
	<td><?php echo esc_html( $tag ); ?></td>
	<td><?php echo esc_html( $file ); ?> [ line <?php echo $line; ?> ]</td>
	<td>(<?php echo $data['count']; ?>)</td>
	<td class='ps_time'><?php echo sprintf( '%.2f', $diff ); ?></td>
	<td>&nbsp; 
<?php
		endif;
?>
<?php 
	elseif ( $val['type'] == 'file' ) :
?>
<tr class='ps_file'>
	<td><?php echo $j; ?></td>
	<td>&nbsp;</td>
	<td>load</td>
	<td colspan='6'><?php echo esc_html( $val['val'] ); ?>
<?php 
	elseif ( $val['type'] == 'sql' ) :
?>
<tr class='ps_sql'>
	<td><?php echo $j; ?></td>
	<td><?php echo sprintf( '%.2f', $checksum ); ?></td>
	<td>sql</td>
	<td></td>
	<td><?php echo esc_html( $sqls[$i][0] ); ?></td>
	<td>
<?php
	foreach ( $cbs as $cb ) :
?>
<?php	echo esc_html( $cb ); ?><br />
<?php 
	endforeach;
?>
	</td>
	<td></td>
	<td class='ps_time'><?php echo sprintf( '%.2f', $val['diff'] ); ?></td>
	<td class='ps_time'><?php echo sprintf( '%.2f', $sqls[$i][1] ); ?>
<?php
endif;
?>
	</td>
</tr>
