<?php
namespace Mihdan\Clockwork;

use Clockwork\Clockwork;
use Clockwork\DataSource\PhpDataSource;
use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;

final class Core {

	const EMERGENCY = 'emergency';
	const ALERT     = 'alert';
	const CRITICAL  = 'critical';
	const ERROR     = 'error';
	const WARNING   = 'warning';
	const NOTICE    = 'notice';
	const INFO      = 'info';
	const DEBUG     = 'debug';


	/**
	 * @var Clockwork
	 */
	private $clockwork;
	private $start;
	private $end;
	private $version;
	protected static $instance = null;

	private function __construct() {
		$this->setup();
		$this->hooks();
	}

	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function setup() {
		$this->start     = null;
		$this->end       = null;
		$this->clockwork = new Clockwork();
		$this->version   = Clockwork::VERSION;
	}

	public function init() {
		define( 'SAVEQUERIES', true );

		$this->start = microtime( true );

		$this->clockwork->addDataSource( new PhpDataSource() );
		$this->clockwork->setStorage( new FileStorage( MIHDAN_CLOCKWORK_PATH . '/tmp' ) );
		$this->clockwork->notice( __( 'Application Started', 'mihdan-clockwork' ) );
	}

	public function send_headers() {
		header( 'X-Clockwork-Id: ' . $this->clockwork->getRequest()->id );
		header( 'X-Clockwork-Version: ' . $this->version );
	}

	public function shutdown() {
		global $wpdb;

		$this->end = microtime( true );
		$request   = $this->clockwork->getRequest();

		if ( is_countable( $wpdb->queries ) && count( $wpdb->queries ) ) {
			foreach ( $wpdb->queries as $query ) {
				$request->addDatabaseQuery( $query[0], [], $query[1] );
			}
		}

		//$request->addEvent()
		$request->timelineData['total'] = [
			'start'       => $this->start,
			'end'         => $this->end,
			'duration'    => $this->end - $this->start,
			'description' => __( 'Total execution time.', 'mihdan-clockwork' ),
		];

		$this->clockwork->notice( __( 'Application Shutdown', 'mihdan-clockwork' ) );
		//$this->clockwork->error( $this );
		$this->clockwork->resolveRequest();
		$this->clockwork->storeRequest();
	}

	public function clear_cache() {
		array_map( 'unlink', glob( MIHDAN_CLOCKWORK_PATH . '/tmp/*.json' ) );
	}

	public function url_handler() {
		$request = $_SERVER['REQUEST_URI'];

		if ( preg_match( '/\/__clockwork\/.*/', $request ) ) {
			$request = explode( '/', $request );
			$storage = new FileStorage( MIHDAN_CLOCKWORK_PATH . '/tmp' );

			/** @var Request $data */
			$data = $storage->find( $request[2] );

			echo $data->toJson();

			$this->clear_cache();

			exit();
		}
	}

	public function hooks() {

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'send_headers', array( $this, 'send_headers' ) );
			add_action( 'admin_init', array( $this, 'send_headers' ) );
			add_action( 'shutdown', array( $this, 'shutdown' ) );
			add_action( 'parse_request', array( $this, 'url_handler' ) );

			foreach ( $this->get_levels() as $level ) {
				add_action( "mc/{$level}", array( $this, $level ), 10, 2 );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}
	}
	public function admin_notice() {
		?>
		<div class="notice notice-info">
			<p><?php _e( 'You have the <b>Mihdan: Clockwork</b> plugin enabled but need to turn debug mode on to make it work.', 'mihdan-clockwork' ); ?></p>
		</div>
		<?php
	}

	public function emergency( $message, array $context = array() ) {
		return $this->clockwork->emergency( $message, $context );
	}

	public function alert( $message, array $context = array() ) {
		return $this->clockwork->alert( $message, $context );
	}

	public function critical( $message, array $context = array() ) {
		return $this->clockwork->critical( $message, $context );
	}

	public function error( $message, array $context = array() ) {
		return $this->clockwork->error( $message, $context );
	}

	public function warning( $message, array $context = array() ) {
		return $this->clockwork->warning( $message, $context );
	}

	public function notice( $message, array $context = array() ) {
		return $this->clockwork->notice( $message, $context );
	}

	public function info( $message, array $context = array() ) {
		return $this->clockwork->info( $message, $context );
	}

	public function debug( $message, array $context = array() ) {
		return $this->clockwork->debug( $message, $context );
	}

	public function get_levels() {
		return array(
			self::EMERGENCY,
			self::ALERT,
			self::CRITICAL,
			self::ERROR,
			self::WARNING,
			self::NOTICE,
			self::INFO,
			self::DEBUG,
		);
	}
}

//eof;
