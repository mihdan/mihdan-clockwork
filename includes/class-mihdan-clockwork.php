<?php
namespace Mihdan\Clockwork;

use Clockwork\Clockwork;
use Clockwork\DataSource\PhpDataSource;
use Clockwork\Request\Request;
use Clockwork\Storage\FileStorage;

final class Core {

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
		if ( WP_DEBUG ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'send_headers', array( $this, 'send_headers' ) );
			add_action( 'shutdown', array( $this, 'shutdown' ) );
			add_action( 'parse_request', array( $this, 'url_handler' ) );
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

	public function notice( $message ) {
		return $this->clockwork->notice( $message );
	}

	public function error( $message ) {
		return $this->clockwork->error( $message );
	}

	public function alert( $message ) {
		return $this->clockwork->alert( $message );
	}
}

//eof;
