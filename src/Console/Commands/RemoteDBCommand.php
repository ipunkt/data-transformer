<?php

namespace Ipunkt\DataTransformer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class RemoteDBCommand extends Command
{
    public function connect()
    {
        Config::set('database.connections.remote.host', $this->argument('host'));
        Config::set('database.connections.remote.database', $this->argument('db'));
        Config::set('database.connections.remote.username', $this->argument('username'));
        Config::set('database.connections.remote.password', $this->argument('password'));

        Config::set('database.connections.remote.driver', $this->option('driver'));
        Config::set('database.connections.remote.port', $this->option('port'));
        Config::set('database.connections.remote.unix_socket', $this->option('unix_socket'));
        Config::set('database.connections.remote.charset', $this->option('charset'));
        Config::set('database.connections.remote.collation', $this->option('collation'));
        Config::set('database.connections.remote.strict', $this->option('strict'));
        Config::set('database.connections.remote.engine', $this->option('engine'));
    }
}
