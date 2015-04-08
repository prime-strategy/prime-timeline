<?php
/**
 * @package prime_timeline package
 * @version 1.00
 */

class ps_timeline_hook {

public $hooks = array();
public $st = array();
public $c;

	function __construct( &$c ) {
		$this->c = $c;
	}

	function __call( $name, $arg ) {
	//echo "---$name--";
	//var_dump($arg);
		$start = microtime( true );
		$i = str_replace( 'stop_', '', $name );
		if ( is_numeric( $i ) ) {
			list( $tag, $file, $line, $st )	= $this->st[$i];
			unset( $this->st[$i] );
			$this->hooks[$tag][$file][$line]['time'] += ( $start - $st );
			//echo "---$tag---$name---";
			//if ($tag != 'get_header') {
			remove_action( $tag, array( &$this, $name ), 999999 );
			//}
		}
		$diff = microtime( true ) - $start;
		$this->c->checkpoint += $diff;
		$this->c->exectime += $diff;
		return $arg[0];
	}

} // class end

