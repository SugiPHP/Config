<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config\Test;

use SugiPHP\Config\LoaderInterface;
use SQLite3;

class Db1Loader implements LoaderInterface
{
	private $db;

	public function __construct()
	{
		$this->db = new SQLite3(":memory:");
		$this->db->exec("CREATE TABLE config (key VARCHAR NOT NULL PRIMARY KEY, host VARCHAR(255), debug INTEGER)");
		$this->db->exec("INSERT INTO config VALUES ('development', 'localhost', 1)");
		$this->db->exec("INSERT INTO config VALUES ('production', 'example.com', null)");
	}

	public function load($resource)
	{
		$key = $this->db->escapeString($resource);
		$result = $this->db->query("SELECT * FROM config WHERE key = '$key'");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		if ($array === false) {
			return null;
		}
		unset($array["key"]);

		return $array;
	}
}
