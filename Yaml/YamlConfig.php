<?php
namespace ITF\YamlConfigBundle\Yaml;

use JMS\Serializer\Exception\LogicException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlConfig
{
	private $container;
	private $config;
	private $db = array();
	private $dump;
	private $accessor;

	/**
	 * YamlConfig constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $this->container->getParameter('yaml_config');
		$this->accessor = new PropertyAccessor();

		// if file does not exist, create
		if (!is_file($this->getConfigFilePath())) {
			$fs = new Filesystem();
			$fs->touch($this->getConfigFilePath());
		}

		// get
		$this->parse();
	}

	/**
	 * set config file manually
	 *
	 * @param $file_path
	 * @param bool|false $force
	 */
	public function setConfigFilePath($file_path, $force = false)
	{
		// check if file exists
		if (!is_file($file_path)) {
			if (!$force) {
				throw new NotFoundHttpException(sprintf('Configuration file "%s" does not exist.', $file_path));
			}

			// create file
			$fs = new Filesystem();
			$fs->touch($file_path);
		}

		// check file extension
		$file_info = new \SplFileInfo($file_path);
		if ($file_info->getExtension() !== 'yml') {
			throw new LogicException(
				sprintf('Configuration file "%s" is not in needed format .yml, %s is given', $file_path, $file_info->getExtension())
			);
		}

		// set and parse
		$this->config['file_path'] = $file_path;
		$this->parse();
	}


	/**
	 * @return string
	 */
	protected function getConfigFilePath()
	{
		return $this->config['file_path'];
	}

	/**
	 * @return string
	 */
	public function getConfigFileName()
	{
		return str_replace($this->container->getParameter('kernel.root_dir'), '', $this->getConfigFilePath());
	}


	/**
	 * @return array
	 */
	protected function parse()
	{
		$this->db = $this->getParse();
		return $this->db;
	}


	/**
	 * get parsed yaml as array
	 * @return array
	 */
	public function getParse()
	{
		return Yaml::parse(file_get_contents($this->getConfigFilePath()));
	}


	/**
	 * returns yaml format of array
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	protected function dump($array = array())
	{
		if (empty($array)) $array = $this->db;

		$this->dump = $this->getDump($array);

		return $this->dump;
	}


	/**
	 * get dump of yml from array
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public function getDump($array = array())
	{
		if (empty($array)) $array = $this->db;

		return Yaml::dump($array);
	}


	/**
	 * saves dump to file
	 *
	 * @param null $dump
	 *
	 * @return bool
	 */
	public function save($dump = NULL)
	{
		if (empty($dump)) $dump = $this->dump;

		return file_put_contents($this->getConfigFilePath(), $dump) !== false;
	}

	/**
	 * save array as yml
	 *
	 * @param $array
	 *
	 * @return bool
	 */
	public function saveArray($array)
	{
		$dump = $this->getDump($array);
		return $this->save($dump);
	}


	/**
	 * get all config
	 *
	 * @param bool|true $parse_first
	 *
	 * @return array
	 */
	public function getAll($parse_first = true)
	{
		if ($parse_first === true) $this->parse();

		return $this->db;
	}


	/**
	 * Rewrites attr.attr2 -> [attr][attr2]
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	protected function rewriteGetAttr($attr)
	{
		return '['.str_replace('.', '][', $attr).']';
	}


	/**
	 * Get attributes value
	 *
	 * @param $attr
	 * @param bool $not_empty
	 * @param bool|false $parse_first
	 *
	 * @return mixed
	 */
	public function get($attr, $not_empty = true, $parse_first = false)
	{
		if ($parse_first === true) $this->parse();
		$attr_raw = $attr;
		$attr = $this->rewriteGetAttr($attr);

		$value = $this->accessor->getValue($this->db, $attr);

		if ($not_empty === true && strlen($value) == 0) {
			throw new LogicException(sprintf('Requested attribute %s should not be empty', $attr_raw));
		}

		return $value;
	}


	/**
	 * Sets a value in a nested array based on path
	 * See http://stackoverflow.com/a/9628276/419887
	 *
	 * @param array $array The array to modify
	 * @param string $path The path in the array
	 * @param mixed $value The value to set
	 * @param string $delimiter The separator for the path
	 * @return mixed The previous value
	 */
	protected function set_nested_array_value(&$array, $path, &$value, $delimiter = '/') {
		$pathParts = explode($delimiter, $path);

		$current = &$array;
		foreach($pathParts as $key) {
			$current = &$current[$key];
		}

		$backup = $current;
		$current = $value;

		return $backup;
	}


	/**
	 * Sets attributes value
	 *
	 * @param $attr
	 * @param $value
	 * @param bool $force
	 *
	 * @return $this|bool
	 */
	public function set($attr, $value, $force = false)
	{
		// rewrite attr
		$attr_raw = $attr;
		$attr = $this->rewriteGetAttr($attr);

		// check if attribute exists
		if ($this->accessor->getValue($this->db, $attr) === NULL) {
			if ($force === true) {
				$null = NULL;
				$this->set_nested_array_value($this->db, $attr_raw, $null, '.');
			} else {
				throw new Exception(sprintf('attribute "%s" does not exist. You have to set it first or add force as parameter.', $attr_raw));
			}
		}

		// set value
		$this->accessor->setValue($this->db, $attr, $value);

		// save
		$this->dump();
		return $this->save();
	}
}