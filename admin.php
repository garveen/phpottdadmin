<?php
require('define.php');


$admin = new ottd_admin('password', '192.168.1.1', 3977);
$admin->connect();
$admin->join();

$admin->ping();

$admin->poll(ADMIN_UPDATE_CLIENT_INFO, 0xFFFFFFFF);
$admin->poll(ADMIN_UPDATE_COMPANY_INFO, 0xFFFFFFFF);
$admin->poll(ADMIN_UPDATE_COMPANY_ECONOMY, 0xFFFFFFFF);

$admin->loop();

$admin->flush();
// $admin->ping();
// $admin->chat();
// $admin->loop();
// $admin->logout();
// $admin->send();




class ottd_admin {
	public $password = '';
	public $ip = '';
	public $port = 3977;
	public $sock = null;
	public $packetToSend = array();
	public $packetToRecv = array();
	public $game = null;
	public function __construct($password, $ip, $port = 3977) {
		$this->password = $password;
		$this->ip = $ip;
		$this->port = $port;
		$this->game = new stdClass();
	}

	public function connect() {
		$this->sock = socket_create(AF_INET, SOCK_STREAM ,SOL_TCP);
		$ret = socket_connect($this->sock, $this->ip, $this->port);
		socket_set_nonblock($this->sock);
	}

	public function receive() {
		while(1) {
			$buf = socket_read($this->sock, 2);
			if(!$buf) return;
			socket_set_block($this->sock);
			if(strlen($buf) == 1) {
				$buf .= socket_read($this->sock, 1);
			}

			$len = ord($buf[0]) + (ord($buf[1]) << 8);
			$buf .= socket_read($this->sock, $len - 2);
			$p = new packet($buf);
			$p->pos = 2;
			array_push($this->packetToRecv, $p);
			// var_dump('received');
			socket_set_nonblock($this->sock);
		}









	}


	public function send() {
		while($p = array_shift($this->packetToSend)) {
			// var_dump('sent');
			$buffer = $p->getBuffer();
			$sent = socket_send($this->sock, $buffer, strlen($buffer), MSG_OOB);
		}

	}

	public function loop() {
		$i = 0;
		while($i++ < 2) {
			$this->send();
			$this->receive();
			$this->process();
			sleep(1);
		}
	}


	public function process() {
		while($p = array_shift($this->packetToRecv)) {
			$type = $p->Recv_uint8();
			switch($type) {
				case ADMIN_PACKET_SERVER_WELCOME:
					$this->game->server_name = $p->Recv_string();
					$this->game->_openttd_revision = $p->Recv_string();
					$this->game->_network_dedicated = $p->Recv_bool();

					$this->game->map_name = $p->Recv_string();
					$this->game->generation_seed = $p->Recv_uint32();
					$this->game->landscape = $p->Recv_uint8();
					$this->game->starting_year = $p->Recv_uint32();
					$this->game->MapSizeX = $p->Recv_uint16();
					$this->game->MapSizeY = $p->Recv_uint16();
					break;

				case ADMIN_PACKET_SERVER_PROTOCOL:
					$admin_version = $p->Recv_uint8();
					while($p->Recv_bool()) {
						$_admin_update_type_frequencies[$p->Recv_uint16()] = $p->Recv_uint16();
					}
					// var_dump($admin_version);
					// var_dump($_admin_update_type_frequencies);
					break;

				case ADMIN_PACKET_SERVER_DATE:
					$date = $p->Recv_uint32();
					var_dump('date: ' . $date);
					break;

				case ADMIN_PACKET_SERVER_CLIENT_INFO:
					$client = new stdClass();
					$client->id = $p->Recv_uint32();
					$client->hostname = $p->Recv_string();
					$client->client_name = $p->Recv_string();
					$client->client_lang = $p->Recv_uint8();
					$client->join_date = $p->Recv_uint32();
					$client->client_playas = $p->Recv_uint8();
					$this->game->clients[$client->id] = $client;
					break;

				case ADMIN_PACKET_SERVER_COMPANY_INFO:
					$company = new stdClass();
					$company->id = $p->Recv_uint8();
					$company->name = $p->Recv_string();
					$company->manager = $p->Recv_string();
					$company->colour = $p->Recv_uint8();
					$company->has_password = $p->Recv_bool();
					$company->inaugurated_year = $p->Recv_uint32();
					$company->is_ai = $p->Recv_bool();
					$company->quarters_of_bankruptcy = $p->Recv_uint8();
					$buflen = strlen($p->buffer);
					while($buflen > $p->pos) {
						$company->share_owners[] = $p->Recv_uint8();
					}
					$this->game->companies[$company->id] = $company;
					break;

				case ADMIN_PACKET_SERVER_COMPANY_ECONOMY:
					$company_id = $p->Recv_uint8();
					$company = $this->game->companies[$company_id];
					$company->money = $p->Recv_uint64();
					$company->current_loan = $p->Recv_uint64();
					$company->income = $p->Recv_uint64();
					$company->delivered_cargo = $p->Recv_uint16();
					for($i = 0; $i < 2; $i++) {
						$quarter = new stdClass();
						$quarter->company_value = $p->Recv_uint64();
						$quarter->performance_history = $p->Recv_uint16();
						$quarter->delivered_cargo = $p->Recv_uint16();
						$company->quarters[] = $quarter;
					}
					break;


				case ADMIN_PACKET_SERVER_PONG:
					$data = $p->Recv_uint32();
					// var_dump("pong: $data");
					break;

				default:
					var_dump('unknown type: ' . $type);
			}

		}
	}

	public function flush() {
		extract((array)$this->game);
		require('templates/info.php');
		// var_dump($this->game);

	}




	public function join() {
		$p = new packet(ADMIN_PACKET_ADMIN_JOIN);
		$p->send('string', $this->password);
		//admin name
		$p->send('string', 'php');
		//admin version
		$p->send('string', '1');
		array_push($this->packetToSend, $p);
	}

	public function logout() {
		$p = new packet(ADMIN_PACKET_ADMIN_QUIT);
		array_push($this->packetToSend, $p);
	}

	public function chat() {
		$p = new packet(ADMIN_PACKET_ADMIN_CHAT);
		$p->send('uint8', 3);
		$p->send('uint8', 0);
		$p->send('uint32', 0);
		$p->send('string', 'hello [sent from php程序]');
		array_push($this->packetToSend, $p);

	}

	public function poll($type = ADMIN_UPDATE_DATE, $d1 = 0) {

		$p = new packet(ADMIN_PACKET_ADMIN_POLL);

		$p->send('uint8', $type);
		$p->send('uint32', $d1);
		array_push($this->packetToSend, $p);
	}

	public function ping() {
		$p = new packet(ADMIN_PACKET_ADMIN_PING);
		$p->send('uint32', 123456);
		// var_dump("ping: 123456");
		array_push($this->packetToSend, $p);

	}




}

function GB($input, $n) {
	return chr(((int)$input >> ($n * 8)) & 0xFF);
}


class packet {
	public $buffer = '';
	public $size = 0;
	public function __construct($param) {
		if(is_string($param)) {
			$this->buffer = $param;
		}
		elseif(is_int($param)) {
			$this->buffer = chr($param);
		}
	}

	public function Recv_bool() {
		return $this->Recv_uint8() ? true : false;
	}
	public function Recv_uint8() {
		return ord($this->buffer[$this->pos++]);
	}
	public function Recv_uint16() {
		$val = 0;
		$val += ord($this->buffer[$this->pos++]) << 0;
		$val += ord($this->buffer[$this->pos++]) << 8;
		return $val;
	}
	public function Recv_uint32() {
		$val = 0;
		$val += ord($this->buffer[$this->pos++]) << 0;
		$val += ord($this->buffer[$this->pos++]) << 8;
		$val += ord($this->buffer[$this->pos++]) << 16;
		$val += ord($this->buffer[$this->pos++]) << 24;
		return $val;
	}
	public function Recv_uint64() {
		$val = 0;
		$val += ord($this->buffer[$this->pos++]) << 0;
		$val += ord($this->buffer[$this->pos++]) << 8;
		$val += ord($this->buffer[$this->pos++]) << 16;
		$val += ord($this->buffer[$this->pos++]) << 24;
		$val += ord($this->buffer[$this->pos++]) << 32;
		$val += ord($this->buffer[$this->pos++]) << 40;
		$val += ord($this->buffer[$this->pos++]) << 48;
		$val += ord($this->buffer[$this->pos++]) << 56;
		return $val;

	}
	public function Recv_string() {
		$str = '';
		 while(1) {
			$chr = $this->buffer[$this->pos++];
			if(ord($chr) === 0) {
				break;
			} else {
				$str .= $chr;
			}
		};
		return $str;
	}


	public function getBuffer() {
		$size = strlen($this->buffer) + 2;
		$this->buffer = GB($size, 0) . GB($size, 1) . $this->buffer . chr(0);
		return $this->buffer;
	}
	public function Send_bool($data) {
		$this->Send_uint8($data ? 1 : 0);
	}
	public function Send_uint8($data) {
		$this->buffer .= GB($data, 0);
	}
	public function Send_uint16($data) {
		$this->buffer .= GB($data, 0);
		$this->buffer .= GB($data, 1);
	}
	public function Send_uint32($data) {
		$this->buffer .= GB($data, 0);
		$this->buffer .= GB($data, 1);
		$this->buffer .= GB($data, 2);
		$this->buffer .= GB($data, 3);
	}
	public function Send_uint64($data) {
		throw new Exception("can't process 64bit number");

		$this->buffer .= GB($data, 0);
		$this->buffer .= GB($data, 1);
		$this->buffer .= GB($data, 2);
		$this->buffer .= GB($data, 3);
		$this->buffer .= GB($data, 4);
		$this->buffer .= GB($data, 5);
		$this->buffer .= GB($data, 6);
		$this->buffer .= GB($data, 7);
	}
	public function Send_string($data) {
		$this->buffer .= $data . chr(0);
	}

	public function send($type, $data) {
		$func = 'Send_' . $type;
		$ret = call_user_func_array(array($this, $func), array($data));
		assert(strlen($this->buffer) + 2 < SEND_MTU);
		return $ret;
	}

	public function dump() {
		$len = strlen($this->buffer);
		for($i = 0; $i < $len; $i++) {
			var_dump(array($this->buffer[$i], ord($this->buffer[$i])));
		}
	}
}
