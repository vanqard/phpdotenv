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

    /**
     * Produces a comparison array to help a site maintainer spot when
     * a repo version of a dotenv file contains new entries.
     *
     * Normally you would use the 'diff' and 'patch' *nix utils for this.
     *
     * Additionally, it will report on entries in the dotenv file that do
     * not appear in the repo "dist" file.
     *
     * USAGE
     * // assuming you have a .env file on the server
     * // and a '.env.dist' file in the repo
     *
     * $dotEnv = new \Dotenv\Dotenv(__DIR__, '.env');
     * $comparison = $dotEnv->compare(__DIR__, '.env.dist')
     *
     * print $comaprison['changes']; // Shows differences
     *
     * @return array $report
     */
    public function compare($path = null, $file = '.env.dist')
    {
      list($thisBucket, $thatBucket) = $this->setupComparisonBuckets($path, $file);

      $missing = array_diff($thatBucket, $thisBucket);
      $surplus = array_diff($thisBucket, $thatBucket);

      return $this->getChangeReport($missing, $surplus);
    }

    /**
     * Sets up the comparison buckets
     *
     * @return array[]
     */
    private function setupComparisonBuckets($distPath, $distFile)
    {
      $this->loader->populateBucket();
      $thisBucket = $this->loader->getBucket();

      if (is_null($distPath)) {
        $distPath = dirname($this->filePath);
      }

      $distFilePath = $this->getFilePath($distPath, $distFile);
      $comparisonLoader = new Loader($distFilePath, true);
      $comparisonLoader->populateBucket();

      $thatBucket = $comparisonLoader->getBucket();

      return [$thisBucket, $thatBucket];
    }

    /**
     * If you're not comfortable using the *nix diff and patch commands
     * this method will provide a list of items that need to be added
     * to your .env file (and optionally, removed from)
     *
     * @param array $missing items
     * @param array $surplus items
     * @return string
     */
    private function getChangeReport($missing, $surplus)
    {
        $changeReport = '# No changes #' . PHP_EOL;

        if (!empty($missing)) {
          $changeReport = '# Add to the file ' . $this->filePath . PHP_EOL . PHP_EOL;

          foreach ($missing as $key=>$value) {
              $changeReport .= "{$key}={$value}" . PHP_EOL;
          }
        }

        if (!empty($surplus)) {
          $changeReport .= PHP_EOL . '# Also, these entries are surplus. Remove them?' . PHP_EOL . PHP_EOL;

          foreach ($surplus as $key=>$value) {
            $changeReport .= "# - {$key}={$value}" . PHP_EOL;
          }
        }

        return $changeReport;
    }
}
