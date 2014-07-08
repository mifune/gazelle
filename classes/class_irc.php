<?php
class IRC_DB extends DB_MYSQL
{
    public function halt($Msg)
    {
        global $Bot;
        $Bot->send_to($Bot->get_channel(),'The database is currently unavailable try again later');
    }
}

abstract class IRC_BOT
{
    abstract protected function connect_events();
    abstract protected function channel_events();
    abstract protected function query_events();
    abstract protected function listener_events();

    protected $Debug = false;
    protected $Socket = false;
    protected $Data = false;
    protected $Whois = false;
    protected $Identified = array();
    protected $Channels = array();
    protected $Messages = array();
    protected $LastChan = false;
    protected $ListenSocket =false;
    protected $Listened = false;
    protected $State = 1; //Drones live
    public $Restart = 0; //Die by default

    public function __construct()
    {
        restore_error_handler(); //Avoid PHP error logging
        set_time_limit(0);
    }

    public function connect()
    {
        //Open a socket to the IRC server
        $this->Socket = fsockopen(BOT_SERVER, BOT_PORT);
        stream_set_blocking($this->Socket, 0);

        //create a socket to listen on
        $this->ListenSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        //socket_set_option($this->ListenSocket, SOL_TCP, SO_REUSEADDR, 1);
        socket_set_option($this->ListenSocket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->ListenSocket, SOCKET_LISTEN_ADDRESS, SOCKET_LISTEN_PORT);
        socket_listen($this->ListenSocket);
        socket_set_nonblock($this->ListenSocket);

        $this->Debug = $Debug;
        fwrite($this->Socket, "NICK ".BOT_NICK."Init\n");
        fwrite($this->Socket, "USER ".BOT_NICK." * * :IRC Bot\n");
        $this->listen();
    }

    public function disconnect()
    {
        socket_close($this->ListenSocket);
        $this->State = 0; //Drones dead
    }

    public function get_channel()
    {
        preg_match('/.+ PRIVMSG ([^:]+) :.+/', $this->Data, $Channel);
        if (preg_match('/#.+/',$Channel[1])) {
            return $Channel[1];
        } else {
            return false;
        }
    }

    public function get_nick()
    {
        preg_match('/:([^!:]+)!.+@[^\s]+ PRIVMSG [^:]+ :.+/', $this->Data, $Nick);

        return $Nick[1];
    }

    protected function get_message()
    {
        preg_match('/:.+ PRIVMSG [^:]+ :(.+)/', $this->Data, $Msg);

        return trim($Msg[1]);
    }

    protected function get_host()
    {
        preg_match('/:[^!:]+!.+@([^\s]+) PRIVMSG [^:]+ :.+/', $this->Data, $Host);

        return trim($Host[1]);
    }

    protected function get_word($Select=1)
    {
        preg_match('/:.+ PRIVMSG [^:]+ :(.+)/', $this->Data, $Word);
        $Word = split(' ',$Word[1]);

        return trim($Word[$Select]);
    }

    protected function get_action()
    {
        preg_match('/:.+ PRIVMSG [^:]+ :!(\S+)/', $this->Data, $Action);

        return strtoupper($Action[1]);
    }

    protected function send_raw($Text)
    {
        fwrite($this->Socket, $Text."\n");
    }

    public function send_to($Channel, $Text)
    {
        fwrite($this->Socket, "PRIVMSG $Channel :$Text\n");
    }

    protected function whois($Nick)
    {
        $this->Whois = $Nick;
        $this->send_raw("WHOIS $Nick");
    }

    protected function listen()
    {
        global $Cache,$DB;
        stream_set_timeout($this->Socket, 10000000000);
        while ($this->State == 1) {
            if ($this->Data = fgets($this->Socket, 256)) {
                if ($this->Debug === true) {
                    $this->send_to(BOT_DEBUG_CHAN, $this->Data);
                }

                if ($this->Whois !== false) {
                    $Exp = explode(' ',$this->Data);
                    if ($Exp[1] == '307') {
                        $this->Identified[$this->Whois] = 1;
                        $this->send_to($this->LastChan, "$this->Whois correctly identified as a real person!");
                        $this->Whois = false;
                        $this->LastChan = false;
                    } elseif ($Exp[6] == '/WHOIS') {
                        $this->Whois = false;
                    }
                }

                if (preg_match("/:([^!]+)![^\s]* QUIT.* /", $this->Data, $Nick)) {
                    if (isset($this->Identified[$Nick[1]])) {
                        unset($this->Identified[$Nick[1]]);
                    }
                }

                if (preg_match("/End of message of the day./", $this->Data)) {
                    $this->connect_events();
                }

                if (preg_match('/PING :(.+)/', $this->Data, $Ping)) {
                    $this->send_raw('PONG :'.$Ping[1]);
                }

                if (preg_match('/.*PRIVMSG #.*/',$this->Data)) {
                    $this->channel_events();
                }

                if (preg_match("/.* PRIVMSG ".BOT_NICK." .*/",$this->Data)) {
                    $this->query_events();
                }
            }

            if ($this->Listened = @socket_accept($this->ListenSocket)) {
                $this->listener_events();
            }

            $DB->LinkID = false;
            $DB->Queries = array();
            usleep(5000);
        }
    }
}
