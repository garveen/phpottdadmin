<?php

$TCP_ADMIN_H = <<<'TCP_ADMIN_H'
enum PacketAdminType {
	ADMIN_PACKET_ADMIN_JOIN,             ///< The admin announces and authenticates itself to the server.
	ADMIN_PACKET_ADMIN_QUIT,             ///< The admin tells the server that it is quitting.
	ADMIN_PACKET_ADMIN_UPDATE_FREQUENCY, ///< The admin tells the server the update frequency of a particular piece of information.
	ADMIN_PACKET_ADMIN_POLL,             ///< The admin explicitly polls for a piece of information.
	ADMIN_PACKET_ADMIN_CHAT,             ///< The admin sends a chat message to be distributed.
	ADMIN_PACKET_ADMIN_RCON,             ///< The admin sends a remote console command.
	ADMIN_PACKET_ADMIN_GAMESCRIPT,       ///< The admin sends a JSON string for the GameScript.
	ADMIN_PACKET_ADMIN_PING,             ///< The admin sends a ping to the server, expecting a ping-reply (PONG) packet.

	ADMIN_PACKET_SERVER_FULL = 100,      ///< The server tells the admin it cannot accept the admin.
	ADMIN_PACKET_SERVER_BANNED,          ///< The server tells the admin it is banned.
	ADMIN_PACKET_SERVER_ERROR,           ///< The server tells the admin an error has occurred.
	ADMIN_PACKET_SERVER_PROTOCOL,        ///< The server tells the admin its protocol version.
	ADMIN_PACKET_SERVER_WELCOME,         ///< The server welcomes the admin to a game.
	ADMIN_PACKET_SERVER_NEWGAME,         ///< The server tells the admin its going to start a new game.
	ADMIN_PACKET_SERVER_SHUTDOWN,        ///< The server tells the admin its shutting down.

	ADMIN_PACKET_SERVER_DATE,            ///< The server tells the admin what the current game date is.
	ADMIN_PACKET_SERVER_CLIENT_JOIN,     ///< The server tells the admin that a client has joined.
	ADMIN_PACKET_SERVER_CLIENT_INFO,     ///< The server gives the admin information about a client.
	ADMIN_PACKET_SERVER_CLIENT_UPDATE,   ///< The server gives the admin an information update on a client.
	ADMIN_PACKET_SERVER_CLIENT_QUIT,     ///< The server tells the admin that a client quit.
	ADMIN_PACKET_SERVER_CLIENT_ERROR,    ///< The server tells the admin that a client caused an error.
	ADMIN_PACKET_SERVER_COMPANY_NEW,     ///< The server tells the admin that a new company has started.
	ADMIN_PACKET_SERVER_COMPANY_INFO,    ///< The server gives the admin information about a company.
	ADMIN_PACKET_SERVER_COMPANY_UPDATE,  ///< The server gives the admin an information update on a company.
	ADMIN_PACKET_SERVER_COMPANY_REMOVE,  ///< The server tells the admin that a company was removed.
	ADMIN_PACKET_SERVER_COMPANY_ECONOMY, ///< The server gives the admin some economy related company information.
	ADMIN_PACKET_SERVER_COMPANY_STATS,   ///< The server gives the admin some statistics about a company.
	ADMIN_PACKET_SERVER_CHAT,            ///< The server received a chat message and relays it.
	ADMIN_PACKET_SERVER_RCON,            ///< The server's reply to a remove console command.
	ADMIN_PACKET_SERVER_CONSOLE,         ///< The server gives the admin the data that got printed to its console.
	ADMIN_PACKET_SERVER_CMD_NAMES,       ///< The server sends out the names of the DoCommands to the admins.
	ADMIN_PACKET_SERVER_CMD_LOGGING,     ///< The server gives the admin copies of incoming command packets.
	ADMIN_PACKET_SERVER_GAMESCRIPT,      ///< The server gives the admin information from the GameScript in JSON.
	ADMIN_PACKET_SERVER_RCON_END,        ///< The server indicates that the remote console command has completed.
	ADMIN_PACKET_SERVER_PONG,            ///< The server replies to a ping request from the admin.

	INVALID_ADMIN_PACKET = 0xFF,         ///< An invalid marker for admin packets.
};

/** Status of an admin. */
enum AdminStatus {
	ADMIN_STATUS_INACTIVE,      ///< The admin is not connected nor active.
	ADMIN_STATUS_ACTIVE,        ///< The admin is active.
	ADMIN_STATUS_END,           ///< Must ALWAYS be on the end of this list!! (period)
};

/** Update types an admin can register a frequency for */
enum AdminUpdateType {
	ADMIN_UPDATE_DATE,            ///< Updates about the date of the game.
	ADMIN_UPDATE_CLIENT_INFO,     ///< Updates about the information of clients.
	ADMIN_UPDATE_COMPANY_INFO,    ///< Updates about the generic information of companies.
	ADMIN_UPDATE_COMPANY_ECONOMY, ///< Updates about the economy of companies.
	ADMIN_UPDATE_COMPANY_STATS,   ///< Updates about the statistics of companies.
	ADMIN_UPDATE_CHAT,            ///< The admin would like to have chat messages.
	ADMIN_UPDATE_CONSOLE,         ///< The admin would like to have console messages.
	ADMIN_UPDATE_CMD_NAMES,       ///< The admin would like a list of all DoCommand names.
	ADMIN_UPDATE_CMD_LOGGING,     ///< The admin would like to have DoCommand information.
	ADMIN_UPDATE_GAMESCRIPT,      ///< The admin would like to have gamescript messages.
	ADMIN_UPDATE_END,             ///< Must ALWAYS be on the end of this list!! (period)
};

/** Update frequencies an admin can register. */
enum AdminUpdateFrequency {
	ADMIN_FREQUENCY_POLL      = 0x01, ///< The admin can poll this.
	ADMIN_FREQUENCY_DAILY     = 0x02, ///< The admin gets information about this on a daily basis.
	ADMIN_FREQUENCY_WEEKLY    = 0x04, ///< The admin gets information about this on a weekly basis.
	ADMIN_FREQUENCY_MONTHLY   = 0x08, ///< The admin gets information about this on a monthly basis.
	ADMIN_FREQUENCY_QUARTERLY = 0x10, ///< The admin gets information about this on a quarterly basis.
	ADMIN_FREQUENCY_ANUALLY   = 0x20, ///< The admin gets information about this on a yearly basis.
	ADMIN_FREQUENCY_AUTOMATIC = 0x40, ///< The admin gets information about this when it changes.
};
DECLARE_ENUM_AS_BIT_SET(AdminUpdateFrequency)

/** Reasons for removing a company - communicated to admins. */
enum AdminCompanyRemoveReason {
	ADMIN_CRR_MANUAL,    ///< The company is manually removed.
	ADMIN_CRR_AUTOCLEAN, ///< The company is removed due to autoclean.
	ADMIN_CRR_BANKRUPT,  ///< The company went belly-up.

	ADMIN_CRR_END,       ///< Sentinel for end.
};
TCP_ADMIN_H;

preg_match_all('#enum ([a-zA-Z]*) {([^}]*)#', $TCP_ADMIN_H, $enums, PREG_SET_ORDER);
// var_dump($enums);
foreach($enums as $enum) {
	preg_match_all('#^\s*([a-zA-Z_]*)\s*(?:=\s*([^,]*))?#m', $enum[2], $defines, PREG_SET_ORDER);
	$i = 0;
	// var_dump($defines);
	// exit;
	foreach($defines as $define) {
		// var_dump($define[1]);
		if(isset($define[2])) {
			if($define[2][0] === '0') {
				switch(@substr($define[2], 1, 1)) {
					case 'x':
					case 'X':
						$val = hexdec($define[2]);
						break;
					case 'b':
					case 'B':
						$val = bindec($define[2]);
						break;
					default:
						$val = octdec($define[2]);
				}
			} else {
				$val = $define[2];
			}
			define($define[1], $val);
			$i = $val + 1;
		} else {
			define($define[1], $i);
			$i++;
		}
	}
}

$CONFIG_H = <<<'CONFIG_H'

static const uint16 NETWORK_MASTER_SERVER_PORT    = 3978;         ///< The default port of the master server (UDP)
static const uint16 NETWORK_CONTENT_SERVER_PORT   = 3978;         ///< The default port of the content server (TCP)
static const uint16 NETWORK_CONTENT_MIRROR_PORT   =   80;         ///< The default port of the content mirror (TCP)
static const uint16 NETWORK_DEFAULT_PORT          = 3979;         ///< The default port of the game server (TCP & UDP)
static const uint16 NETWORK_ADMIN_PORT            = 3977;         ///< The default port for admin network
static const uint16 NETWORK_DEFAULT_DEBUGLOG_PORT = 3982;         ///< The default port debug-log is sent to (TCP)

static const uint16 SEND_MTU                      = 1460;         ///< Number of bytes we can pack in a single packet

static const byte NETWORK_GAME_ADMIN_VERSION      =    1;         ///< What version of the admin network do we use?
static const byte NETWORK_GAME_INFO_VERSION       =    4;         ///< What version of game-info do we use?
static const byte NETWORK_COMPANY_INFO_VERSION    =    6;         ///< What version of company info is this?
static const byte NETWORK_MASTER_SERVER_VERSION   =    2;         ///< What version of master-server-protocol do we use?

static const uint NETWORK_NAME_LENGTH             =   80;         ///< The maximum length of the server name and map name, in bytes including '\0'
static const uint NETWORK_COMPANY_NAME_LENGTH     =  128;         ///< The maximum length of the company name, in bytes including '\0'
static const uint NETWORK_HOSTNAME_LENGTH         =   80;         ///< The maximum length of the host name, in bytes including '\0'
static const uint NETWORK_SERVER_ID_LENGTH        =   33;         ///< The maximum length of the network id of the servers, in bytes including '\0'
static const uint NETWORK_REVISION_LENGTH         =   15;         ///< The maximum length of the revision, in bytes including '\0'
static const uint NETWORK_PASSWORD_LENGTH         =   33;         ///< The maximum length of the password, in bytes including '\0' (must be >= NETWORK_SERVER_ID_LENGTH)
static const uint NETWORK_CLIENTS_LENGTH          =  200;         ///< The maximum length for the list of clients that controls a company, in bytes including '\0'
static const uint NETWORK_CLIENT_NAME_LENGTH      =   25;         ///< The maximum length of a client's name, in bytes including '\0'
static const uint NETWORK_RCONCOMMAND_LENGTH      =  500;         ///< The maximum length of a rconsole command, in bytes including '\0'
static const uint NETWORK_GAMESCRIPT_JSON_LENGTH  = SEND_MTU - 3; ///< The maximum length of a gamescript json string, in bytes including '\0'. Must not be longer than SEND_MTU including header (3 bytes)
static const uint NETWORK_CHAT_LENGTH             =  900;         ///< The maximum length of a chat message, in bytes including '\0'

static const uint NETWORK_GRF_NAME_LENGTH         =   80;         ///< Maximum length of the name of a GRF


CONFIG_H;



define('NETWORK_MASTER_SERVER_PORT', 3978);         ///< The default port of the master server (UDP)
define('NETWORK_CONTENT_SERVER_PORT', 3978);         ///< The default port of the content server (TCP)
define('NETWORK_CONTENT_MIRROR_PORT', 80);         ///< The default port of the content mirror (TCP)
define('NETWORK_DEFAULT_PORT', 3979);         ///< The default port of the game server (TCP & UDP)
define('NETWORK_ADMIN_PORT', 3977);         ///< The default port for admin network
define('NETWORK_DEFAULT_DEBUGLOG_PORT', 3982);         ///< The default port debug-log is sent to (TCP)

define('SEND_MTU', 1460);         ///< Number of bytes we can pack in a single packet

define('NETWORK_GAME_ADMIN_VERSION', 1);         ///< What version of the admin network do we use?
define('NETWORK_GAME_INFO_VERSION', 4);         ///< What version of game-info do we use?
define('NETWORK_COMPANY_INFO_VERSION', 6);         ///< What version of company info is this?
define('NETWORK_MASTER_SERVER_VERSION', 2);         ///< What version of master-server-protocol do we use?

define('NETWORK_NAME_LENGTH', 80);         ///< The maximum length of the server name and map name, in bytes including '\0'
define('NETWORK_COMPANY_NAME_LENGTH', 128);         ///< The maximum length of the company name, in bytes including '\0'
define('NETWORK_HOSTNAME_LENGTH', 80);         ///< The maximum length of the host name, in bytes including '\0'
define('NETWORK_SERVER_ID_LENGTH', 33);         ///< The maximum length of the network id of the servers, in bytes including '\0'
define('NETWORK_REVISION_LENGTH', 15);         ///< The maximum length of the revision, in bytes including '\0'
define('NETWORK_PASSWORD_LENGTH', 33);         ///< The maximum length of the password, in bytes including '\0' (must be >= NETWORK_SERVER_ID_LENGTH)
define('NETWORK_CLIENTS_LENGTH', 200);         ///< The maximum length for the list of clients that controls a company, in bytes including '\0'
define('NETWORK_CLIENT_NAME_LENGTH', 25);         ///< The maximum length of a client's name, in bytes including '\0'
define('NETWORK_RCONCOMMAND_LENGTH', 500);         ///< The maximum length of a rconsole command, in bytes including '\0'
define('NETWORK_GAMESCRIPT_JSON_LENGTH', SEND_MTU - 3); ///< The maximum length of a gamescript json string, in bytes including '\0'. Must not be longer than SEND_MTU including header (3 bytes)
define('NETWORK_CHAT_LENGTH', 900);         ///< The maximum length of a chat message, in bytes including '\0'

define('NETWORK_GRF_NAME_LENGTH', 80);         ///< Maximum length of the name of a GRF




