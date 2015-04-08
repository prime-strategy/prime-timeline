<?php
/*
Plugin Name: Prime Timeline
Plugin URI: http://www.prime-strategy.co.jp/
Description: This plugin analyze the execution status of WordPress for debugging, performance check, and study.
Author: Kengyu Nakamura ( Prime Strategy Co.,Ltd. )
Version: 1.0.4
Author URI: http://www.prime-strategy.co.jp/
*/

/*	Copyright 2014 Prime Strategy Co.,Ltd.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

class prime_timeline {

	public $hook;
	public $files = array();
	public $sqls = array();
	public $logs = array();
	public $i;
	public $exectime;
	public $starttime;
	public $checkpoint;
	public $content;
	public $ver;

	function __construct() {
		global $timestart;
		if ( ( ! defined( 'WP_USE_THEMES' ) && ! defined( 'WP_ADMIN' ) ) || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) {
			return;
		}
		
		$this->starttime = $this->checkpoint = $timestart;
		$this->exectime = 0;
		$this->i = 0;

		require_once( dirname( __FILE__ ) . '/inc/ps_timeline_hook.php' );
		$this->hook = new ps_timeline_hook( $this );

		if ( ! defined( 'SAVEQUERIES' ) ) {
			define ( 'SAVEQUERIES', true );
		} else {
			$this->check_autoload();
		}

		add_action( 'init',  array( &$this, 'init' ) );
		add_action( 'all',   array( &$this, 'all' ) );
		add_filter( 'query', array( &$this, 'query' ), 9998 );
	}

	function check_autoload() {
		global $wpdb;

		if ( ! isset( $wpdb->queries[0][0] ) ) { return; }

		$this->included_files();
		$id_after_autoload = false;		

		foreach( $this->logs as $key => $val ) {
			if ( basename( $val['val'] ) == 'class-wp-walker.php' ) {
				$id_after_autoload = $key;
				break;
			}
		}

		if ( false == $id_after_autoload ) { return; }

		$log = array( 'type' => 'sql', 'diff' => 0 );
		$wpdb->queries[0][0] .= " -- $id_after_autoload";
		array_splice( $this->logs, $id_after_autoload, 0, 0 );
		$this->logs[$id_after_autoload] = $log;
	}

	function init() {
		if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
			remove_action( 'all',   array( &$this, 'all' ) );
			remove_filter( 'query', array( &$this, 'query' ) );
			return;
		}

		$header = get_file_data( __FILE__, array( 'ver' => 'Version' ) );
		$this->ver = $header['ver'];

		add_action( 'admin_bar_menu',            array( &$this, 'admin_bar_menu' ), 100 );
		add_action( 'wp_after_admin_bar_render', array( &$this, 'panel_render' ) );
		add_action( 'shutdown',                  array( &$this, 'shutdown' ), 0 );

		wp_enqueue_style(  'ps_timeline',	plugins_url( 'css/ps_timeline.css', __FILE__ ) , array(), $this->ver );
		wp_enqueue_script( 'ps_timeline',	plugins_url( 'js/ps_timeline.js',   __FILE__ ) , array('jquery'), $this->ver, true );

		ob_start( array( &$this, 'output' ) );
	}

	function admin_bar_menu( $ab ) {
		$ab->add_node( array( 'id' => 'prime-timeline', 'title' => 'Prime Timeline', 'href' => '#' ) );
	}

	function panel_render() {
		include( dirname( __FILE__ ) . '/tpl/ps_panel.php' );
	}

	function check_point( $now ) {
		$diff = $now - $this->checkpoint;
		$this->checkpoint = $now;
		return $diff;
	}

	function included_files() {
		$if = get_included_files();
		$done = count( $this->files );
		$ifc = count( $if );

		for ( $i = $done; $i < $ifc; $i++ ) {
			$path = str_replace( ABSPATH, '', $if[$i] );
			$this->files[] = $path;
			$this->logs[$this->i] = array( 'type' => 'file', 'val' => $path );
			$this->i++;
		}
	}

	function query( $q ) {
		$start = microtime( true );
		$this->included_files();
		$q .= " -- $this->i";
		$this->logs[$this->i] = array( 'type' => 'sql', 'diff' => $this->check_point( $start ) );
		$this->i++;
		$diff = microtime( true ) - $start;
		$this->checkpoint += $diff;
		$this->exectime += $diff;
		return $q;
	}

	function all( $tag ) {
		global $wp_filter;
		$start = microtime( true );
		$hook_arr = array( 'apply_filters', 'apply_filters_ref_array', 'do_action', 'do_action_ref_array' );

		if ( defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ) {
			$debug = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		} else {
			$debug = debug_backtrace();
		}
		foreach ( $debug as $key => $val ) {
			if ( in_array( $val['function'] , $hook_arr ) ) {
				$file = str_replace( ABSPATH, '', $val['file'] );
				$line = $val['line'];
				$function = $val['function'];
				$hooks =& $this->hook->hooks;
				if ( ! isset( $hooks[$tag][$file][$line] ) ) {
					$hooks[$tag][$file][$line]['count'] = 1;
					$this->included_files();
					$this->logs[$this->i] = array( 
						'type' => 'hook',
						'val' => array(	$tag, $file, $line, $function, $this->check_point( $start ) )
					);
				} else {
					$hooks[$tag][$file][$line]['count']++;
				}

				if ( in_array( $tag, array_keys( $wp_filter ) ) ) {
					foreach ( $wp_filter[$tag] as $priority => $arr ) {
						foreach ( $arr as $key => $func ) {
							$func = $func['function'];
							if ( is_array( $func ) ) {
								if ( is_object( $func[0] ) ) {
									$func = get_class( $func[0] ) . '->' . $func[1];
								} else {
									$func = $func[0] . '::' . $func[1];
								}
							}

							// ex. $func is instance of Closure. PHP 5.3+
							if ( is_object( $func ) ){
								$func = get_class( $func );
							}

							if ( ! isset( $hooks[$tag][$file][$line]['callback'][$priority][$func] ) ) {
								$hooks[$tag][$file][$line]['callback'][$priority][$func] = 1;
							} else {
								$hooks[$tag][$file][$line]['callback'][$priority][$func]++;
							}
						}
					}
					if ( ! isset( $hooks[$tag][$file][$line]['time'] ) ) {
						$hooks[$tag][$file][$line]['time'] = 0;
					}
					$this->hook->st[$this->i] = array( $tag, $file, $line, microtime( true ) );
					add_action( $tag, array( &$this->hook, 'stop_' . $this->i ), 999999 );
					
				}
				break;
			} 
		}
		$this->i++;
		$diff = microtime( true ) - $start;
		$this->checkpoint += $diff;
		$this->exectime += $diff;
	}

	function shutdown() {
		global $wpdb;
		$exec = ( microtime( true ) - $this->starttime - $this->exectime ) * 1000;
		$sqls = array();
		$checksum = 0;
		$j = 0;

		foreach ( $wpdb->queries as $q ) {
			if ( preg_match( '/ -- ([0-9]+)$/', $q[0], $m ) ) {
				$q[0] = preg_replace( '/ -- [0-9]+$/', '', $q[0] );
				$sqls[$m[1]] = $q;
			}
		}

		ob_start();
		include( dirname( __FILE__ ) . '/tpl/ps_table_header.php' );
		foreach ( $this->logs as $i => $val ) {
			$j++;
			if ( $val['type'] == 'hook' ) {
				list( $tag, $file, $line, $function, $diff ) = $val['val'];
				$diff *= 1000;
				$checksum += $diff;
				$data = $this->hook->hooks[$tag][$file][$line];
				if ( isset( $data['time'] ) ) {
					$data['time'] *= 1000;
					if (isset( $data['callback'] ) ) {
						ksort( $data['callback'] );
					} else {
						$data['callback'] = arraY();
					}
				}
			} elseif ( $val['type'] == 'file' ) {
			} elseif ( $val['type'] == 'sql' ) {
				$val['diff'] *= 1000;
				$sqls[$i][1] *= 1000;
				$checksum += $val['diff'];
				$cbs = mb_split( ', ', $sqls[$i][2] );
			}
			include( dirname( __FILE__ ) . '/tpl/ps_tr.php' );
		}
		include( dirname( __FILE__ ) . '/tpl/ps_table_footer.php' );
		$this->content = ob_get_clean();
	}

	function output( $out ) {
		$out .= '<!--out-->';
		if ( preg_match( '/__ps_footer__/', $out ) ) {
			$out = preg_replace( '/__ps_footer__/', $this->content, $out );
		} else {
//		$out .= $str;
		}
		return $out;
	}

} // class end

new prime_timeline;
