<?php

declare(strict_types=1);

namespace Packagist\WebBundle\Package;

use Exception;

class Zipper
{

	/**
	 * @var string
	 */
	private $rootDir;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * Zipper constructor.
	 * @param string $rootDir
	 * @param string $scheme
	 * @param string $host
	 * @param string $baseUrl
	 * @param string $authUser
	 * @param string $authPass
	 */
	public function __construct($rootDir, $scheme, $host, $baseUrl, $authUser, $authPass)
	{
		$rootDir .= '/../web/zip';
		if (!file_exists($rootDir)) {
			mkdir($rootDir, 0777, true);
		}
		$this->rootDir = realpath($rootDir);
		$this->baseUrl = $scheme . '://' . $authUser . ':' . $authPass . '@' . $host . $baseUrl . '/zip';
	}

	/**
	 * @param string $packageName
	 * @param string $gitUrl
	 * @param string $reference
	 * @param string $version
	 * @return null|string[]
	 * @throws Exception
	 */
	public function createDist($packageName, $gitUrl, $reference, $version)
	{
		if (!$this->shouldBeCreated($version)) {
			return null;
		}

		$file = $this->getDir($packageName) . '/' . $version . '.zip';
		$url = $this->baseUrl . '/' . $packageName . '/' . $version . '.zip';
		$command = 'git archive --remote="' . $gitUrl . '" --format=zip --output="' . $file . '" "' . $version . '"';
		exec($command, $output, $status);
		if ($status !== 0) {
			$output = implode("\n", $output);
			$msg = "Cannot create zipfile:\n" . $command;
			if ($output !== '') {
				$msg .= ":\n" . $output;
			}
			throw new Exception($msg);
		}

		return [
			'type' => 'zip',
			'reference' => $reference,
			'url' => $url,
			'shasum' => '', // TODO
		];
	}

	private function shouldBeCreated($version)
	{
		return preg_match('/^v[0-9]+\.[0-9]+(\.[0-9]+)?$/', $version) === 1;
	}

	private function getDir($packageName) {
		$dir = $this->rootDir . '/' . $packageName;
		if (!file_exists($dir)) {
			if (!mkdir($dir, 0777, true)) {
				throw new Exception('Cannot create directory: ' . $dir);
			}
		} elseif (!is_dir($dir)) {
			throw new Exception('Is not directory: ' . $dir);
		}
		return $dir;
	}

}
