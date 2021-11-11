<?php
/**
 *  @copyright 2017  Cloudways  https://www.cloudways.com
 *
 *  Original development of this plugin by JoomUnited https://www.joomunited.com/
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if (!defined('ABSPATH')) {
    exit;
}

class RedisClient {
    /**
     * @var Redis
     */
    private $redis;

    public function __construct($uri) {
        $this->redis = new Redis();
        $url = parse_url($uri);
        $scheme = 'tcp';

        switch ($url['scheme']) {
            case 'unix':
                $this->redis->connect($url['path']);
                break;

            case 'rediss':
                $scheme = 'tls';
                // no break
            default:
                $db = strlen($url['path']) > 1 ? (int) filter_var(substr($url['path'], 1), FILTER_VALIDATE_INT) : 0;
                $db = $db ? $db : 0;
                $host = ($scheme ? $scheme . '://' : '') . $url['host'];
                $port = isset($url['port']) ? $url['port'] : 6379;
                $this->redis->connect($host, $port);
                $this->redis->select($db);
                break;
        }
    }

    public function delete($key) {
        try {
            return $this->redis->eval("local keys = redis.call('keys', ARGV[1]) for i=1,#keys,5000 do redis.call('del', unpack(keys, i, math.min(i+4999, #keys))) end return #keys", [$key], 0);
        } catch (exception $e) {
            throw $this->redis->getLastError();
        }
    }

    public function set($key, $data, $ttl = 0) {
        try {
            if ($ttl) {
                return $this->redis->setEx($key, $ttl, $data);
            }
            return $this->redis->set($key, $data);
        } catch (exception $e) {
            throw $this->redis->getLastError();
        }
    }

    public function get($key) {
        try {
            return $this->redis->get($key);
        } catch (exception $e) {
            throw $this->redis->getLastError();
        }
    }

    /**
     * Singleton instance.
     *
     * @return RedisClient
     */
    public static function factory() {
        static $instance;

        if (!$instance) {
            $config = isset($GLOBALS['breeze_config']['cache_options']['breeze-redis-uri']) ? $GLOBALS['breeze_config']['cache_options']['breeze-redis-uri'] : breeze_get_option('basic_settings')['breeze-redis-uri'];
            $instance = new self($config);
        }

        return $instance;
    }
}
