<?php
namespace Mihdan\Clockwork;

use Clockwork\Clockwork;
use Clockwork\DataSource\PhpDataSource;
use Clockwork\Storage\FileStorage;

class Clockwork_Main {

	/**
	 * @var Clockwork
	 */
	private $clockwork;
	private $start;
	private $end;
	private $version;

	public function __construct() {
		$this->setup();
		$this->hooks();
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

		header( 'X-Clockwork-Id: ' . $this->clockwork->getRequest()->id );
		header( 'X-Clockwork-Version: ' . $this->version );

		$this->clockwork->addDataSource( new PhpDataSource() );
		$this->clockwork->setStorage( new FileStorage( MIHDAN_CLOCKWORK_PATH . '/tmp' ) );

		$this->clockwork->notice( __( 'Application Started', 'mihdan-clockwork' ) );

	}

	public function shutdown() {
		global $wpdb;

		$this->end = microtime( true );
		$request   = $this->clockwork->getRequest();
		$queries   = array();

		foreach ( $wpdb->queries as $query ) {
			$queries[] = [
				'query'    => $query[0],
				'duration' => $query[1],
			];
		}

		$request->databaseQueries       = $queries;
		$request->timelineData['total'] = [
			'start'       => $this->start,
			'end'         => $this->end,
			'duration'    => $this->end - $this->start,
			'description' => __( 'Total execution time.', 'mihdan-clockwork' ),
		];

		$this->clockwork->notice( __( 'Application Shutdown', 'mihdan-clockwork' ) );
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

			$data = $storage->retrieve( $request[2] );

			echo $data->toJson();

			$this->clear_cache();

			exit();
		}
	}

	public function hooks() {
		if ( WP_DEBUG ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'shutdown', array( $this, 'shutdown' ) );
			add_action( 'parse_request', array( $this, 'url_handler' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}
	}
	public function admin_notice() {
		?>
		<div class="notice notice-info">
			<p><?php _e( 'You have the Mihdan: Clockwork plugin enabled but need to turn debug mode on to make it work.', 'mihdan-clockwork' ); ?></p>
		</div>
		<?php
	}
}

//eof;
