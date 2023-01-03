<?php

namespace App\Command\Ssh;

use Minicli\Input;
use Minicli\Command\CommandController;

class RegisterController extends CommandController
{
    private $connection = [
        'connection_name' => 'Local',
        'connection_id' => 'local',
        'type' => 'public_key',
        'private_key' => '',
        'public_key' => '',
        'password' => '',
        'host' => '127.0.0.1',
        'port' => '22',
        'user' => 'root',
    ];
    protected $global = [];
    protected $home;
    public function handle(): void
    {
        $this->home = getenv('HOME');
        if(!is_file( $this->home . '/sshcm.json') ) {
            fopen( $this->home . '/sshcm.json', 'w' );
        }
        $this->global = json_decode( file_get_contents( $this->home . '/sshcm.json' ), true ) ?? [];
        $this->getPrinter()->display("Register new connection!");

        $this->askConnectionName();
        $this->askHost();
        $this->askPort();
        $this->askType();
        $this->askPassword();
        $this->askUser();

        $this->global[] = $this->connection;
        file_put_contents( $this->home . '/sshcm.json', json_encode( $this->global ) );
        $this->getPrinter()->display("Connection ". $this->connection['connection_name'] ." successfully registered!");
        return;
    }

    function askConnectionName()
    {
        $name = $this->connection['connection_name'];
        $input = new Input('Connection Name: [' . $name . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $name;
        }
        $connId = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $read)));
        
        if( array_search($connId, array_column($this->global, 'connection_id')) !== false ) {
            $this->getPrinter()->display("Connection name already exists!");
            $this->askConnectionName();
            return;
        }

        $this->connection['connection_name'] = $read;
        $this->connection['connection_id'] = $connId;
        return;
    }
    function askType()
    {
        $type = $this->connection['type'];
        $input = new Input('Auth Type: [' . $type . '/password] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $type;
        }
        $this->connection['type'] = $read;

        if ($read === 'public_key') {
            $this->askPublicKey();
        }

        return;
    }

    function askPublicKey()
    {
        $home = $this->home;
        $keys = scandir($home . '/.ssh/');
        $matchedKeys = [];
        foreach ($keys as $key) {
            if ($key !== '.' && $key !== '..' && substr($key, -4) === '.pub') {
                $matchedKeys[] = $key;
            }
        }

        $input = new Input('Public Key: [' . implode($home . '/.ssh/', $matchedKeys) . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = current($matchedKeys) ?? '';
        }
        if (!preg_match('/^\S.*\s.*\S$/', $read)) {
            $save = file_get_contents($home . '/.ssh/' . $read);
        } else {
            $save = $read;
        }
        $this->connection['public_key'] = $home . '/.sshcm/' . $this->connection['connection_id'] . '.pub';
        
        if (!is_dir($home . '/.sshcm')) {
            mkdir($home . '/.sshcm', 0755);
        }
        if (!is_file($this->connection['public_key'])) {
            fopen($this->connection['public_key'], 'w');
        }
        file_put_contents($this->connection['public_key'], $save);

        chmod($this->connection['public_key'], 0755);
        $this->askPrivateKey($read);

        return;
    }

    function askPrivateKey($publicKey = null)
    {
        $home = $this->home;
        $keys = scandir($home . '/.ssh/');
        $matchedKeys = [];
        foreach ($keys as $key) {
            if ($publicKey && $key !== '.' && $key !== '..' && $key === $publicKey) {
                $matchedKeys = [substr($key, 0, -4)];
                break;
            } elseif ($key !== '.' && $key !== '..' && substr($key, 0, 3) === 'id_' && substr($key, -4) !== '.pub') {
                $matchedKeys[] = $key;
            }
        }

        $input = new Input('Private Key: [' . implode($home . '/.ssh/', $matchedKeys) . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = current($matchedKeys) ?? '';
        }
        if (!preg_match('/^\S.*\s.*\S$/', $read)) {
            $save = file_get_contents($home . '/.ssh/' . $read);
        } else {
            $save = $read;
        }

        $this->connection['private_key'] = $home . '/.sshcm/' . $this->connection['connection_id'];

        if (!is_dir($home . '/.sshcm')) {
            mkdir($home . '/.sshcm', 0755);
        }
        if (!is_file( $this->connection['private_key'] )) {
            fopen($this->connection['private_key'], 'w');
        }
        file_put_contents($this->connection['private_key'], $save);
        chmod($this->connection['private_key'], 0600);
        return;
    }

    function askPassword()
    {
        $input = new Input(($this->connection['type'] === 'public_key' ? 'Passphrase' : 'Password') . ' =>');
        $read = $input->read();

        $this->connection['password'] = $read;

        return;
    }


    function askHost()
    {
        $host = $this->connection['host'];
        $input = new Input('Host: [' . $host . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $host;
        }
        $this->connection['host'] = $read;
        return;
    }
    function askPort()
    {
        $port = $this->connection['port'];
        $input = new Input('Port: [' . $port . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $port;
        }
        $this->connection['port'] = $read;
        return;
    }
    function askUser()
    {
        $user = get_current_user();
        $input = new Input('User: [' . $user . '] =>');
        $read = $input->read();
        if ($read === '') {
            $read = $user;
        }
        $this->connection['user'] = $read;
        return;
    }
}
