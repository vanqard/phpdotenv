<?php

namespace Dotenv;

/**
 * This is the dotenv class.
 *
 * It's responsible for loading a `.env` file in the given directory and
 * setting the environment vars.
 */
class Dotenv
{
    /**
     * The file path.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The loader instance.
     *
     * @var \Dotenv\Loader|null
     */
    protected $loader;

    /**
     * Create a new dotenv instance.
     *
     * @param string $path
     * @param string $file
     *
     * @return void
     */
    public function __construct($path, $file = '.env')
    {
        $this->filePath = $this->getFilePath($path, $file);
        $this->loader = new Loader($this->filePath, true);
    }

    /**
     * Load `.env` file in given directory.
     *
     * @return array
     */
    public function load()
    {
        $this->loader = new Loader($this->filePath, true);

        return $this->loader->load();
    }

    /**
     * Load `.env` file in given directory.
     *
     * @return array
     */
    public function overload()
    {
        $this->loader = new Loader($this->filePath, false);

        return $this->loader->load();
    }

    /**
     * Returns the full path to the file.
     *
     * @param string $path
     * @param string $file
     *
     * @return string
     */
    protected function getFilePath($path, $file)
    {
        if (!is_string($file)) {
            $file = '.env';
        }

        $filePath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

        return $filePath;
    }

    /**
     * Required ensures that the specified variables exist, and returns a new Validator object.
     *
     * @param string|string[] $variable
     *
     * @return \Dotenv\Validator
     */
    public function required($variable)
    {
        return new Validator((array) $variable, $this->loader);
    }

    public function compare($path = null, $file = '.env.dist')
    {
      if (is_null($path)) {
        $path = dirname($this->filePath);
      }

      $distFilePath = $this->getFilePath($path, $file);
      $comparisonLoader = new Loader($this->filePath, true);

      $this->loader->populateBucket();
      $comparisonLoader->populateBucket();

      $thisBucket = $this->loader->getBucket();
      $thatBucket = $comparisonLoader->getBucket();

      return array_diff($thisBucket, $thisBucket);

    }
}
