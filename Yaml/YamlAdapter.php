<?php
namespace ITF\YamlConfigBundle\Yaml;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;

class YamlAdapter
{
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->parser = new Parser();
	}

	public function query()
	{

	}
}