<?php
/**
 * Created by PhpStorm.
 * User: onysko
 * Date: 19.11.2014
 * Time: 19:04
 */
namespace samsonphp\fs;

use samson\core\CompressableService;
use samsonframework\core\ConfigurableInterface;
use samsonphp\event\Event;

/**
 * File system module controller
 * @package samsonphp\fs
 */
class FileService extends CompressableService implements IFileSystem, ConfigurableInterface
{
    /** @var string Module identifier */
    protected $id = 'fs';

    /** @var string Configurable file service class name */
    public $fileServiceClassName = 'samsonphp\fs\LocalFileService';

    /** @var array Collection of configuration parameters */
    public $configuration = array();

    /** @var \samsonphp\fs\AbstractFileService Pointer to file system adapter */
    protected $fileService;

    /**
     * Initialize module
     * @param array $params Collection of module parameters
     * @return bool True if module successfully initialized
     */
    public function init(array $params = array())
    {
        if (!$this->loadExternalService($this->fileServiceClassName)) {

            // Signal error
            Event::fire(
                'error',
                array(
                    $this,
                    'Cannot initialize file system adapter[' . $this->fileServiceClassName . ']'
                )
            );
        }

        // Call parent initialization
        return parent::init($params);
    }

    /**
     * Load external file service instance
     * @param string $serviceClassName File service class name for loading
     * @return bool True if external service instance has been created
     */
    public function loadExternalService($serviceClassName)
    {
        // If defined file service is supported
        if (class_exists($serviceClassName)) {
            /** @var \samsonphp\fs\AbstractFileService Create file service instance */
            $this->fileService = new $serviceClassName();

            // Set nested file service instance parameters
            foreach ($this->configuration as $key => $value) {
                $this->fileService->$key = $value;
            }

            // Initialize file service
            $this->fileService->initialize();

            return true;
        }

        return false;
    }

    /**
     * @param mixed $entityConfiguration current instance for configuration
     * @return boolean False if something went wrong otherwise true
     */
    public function configure($entityConfiguration)
    {
        // Convert object to array
        $this->configuration = (array)$entityConfiguration;
        // Set fileServiceClassName parameter from config
        if (!empty($this->configuration['fileServiceClassName']{0})) {
	        $this->fileServiceClassName = $this->configuration['fileServiceClassName'];
        }
    }

    /**
     * Write data to a specific relative location
     *
     * @param mixed $data Data to be written
     * @param string $filename File name
     * @param string $uploadDir Relative file path
     * @return string|boolean Relative path to created file, false if there were errors
     */
    public function write($data, $filename = '', $uploadDir = '')
    {
        return $this->fileService->write($data, $filename, $uploadDir);
    }

    /**
     * Check existing current file in current file system
     * @param $filename string Filename
     * @return boolean File exists or not
     */
    public function exists($filename)
    {
        return $this->fileService->exists($filename);
    }

    /**
     * Read the file from current file system
     * @param $filePath string Full path to file
     * @param $filename string File name
     * @return string File data
     */
    public function read($filePath, $filename = null)
    {
        return $this->fileService->read($filePath, $filename);
    }

    /**
     * Delete file from current file system
     * @param $filename string File for deleting
     * @return mixed
     */
    public function delete($filename)
    {
        return $this->fileService->delete($filename);
    }

    /**
     * Get file extension in current file system
     * @param $filePath string Path
     * @return string|bool false if extension not found, otherwise file extension
     */
    public function extension($filePath)
    {
        return $this->fileService->extension($filePath);
    }

    /**
     * Define if $filePath is directory
     * @param string $filePath Path
     * @return boolean Is $path a directory or not
     */
    public function isDir($filePath)
    {
        return $this->fileService->isDir($filePath);
    }

    /**
     * Get recursive $path listing collection
     * @param string $path Path for listing contents
     * @param array $restrict Collection of restricted paths
     * @param array $result   Collection of restricted paths
     * @return array $path recursive directory listing
     */
    public function dir($path, $restrict = array(), & $result = array())
    {
        return $this->fileService->dir($path, $restrict, $result);
    }

    /**
     * Get file mime type in current file system
     * @param $filePath string Path to file
     * @return false|integer|string false if mime not found, otherwise file mime type
     */
    public function mime($filePath)
    {
        return $this->fileService->mime($filePath);
    }

    /**
     * Get relative path from $path
     * @param string $fullPath Full file path
     * @param string $fileName File name
     * @param string $basePath Base path, must end WITHOUT '/', if not passed
     *                          $fullPath one level top directory is used.
     * @return string Relative path to file
     */
    public function relativePath($fullPath, $fileName, $basePath = null)
    {
        return $this->fileService->relativePath($fullPath, $fileName, $basePath);
    }

    /**
     * Copy file/folder to selected location.
     * Copy can be performed from file($filePath) to file($newPath),
     * also copy can be performed from folder($filePath) to folder($newPath),
     * currently copying from file($filePath) to folder($newPath) is not supported.
     *
     * @param string $filePath      Source path or file path
     * @param string $newPath       New path or file path
     * @return boolean False if failed otherwise true if file/folder has been copied
     */
    public function copyPath($filePath, $newPath)
    {
        return $this->fileService->copyPath($filePath, $newPath);
    }

    /**
     * Create catalog in selected location
     * @param string    $path   Path for new catalog
     * @return boolean  Result of catalog creating
     */
    public function mkDir($path)
    {
        return $this->fileService->mkDir($path);
    }

    /** Обработчик сериализации объекта */
    public function __sleep()
    {
        // Remove all unnecessary fields from serialization
        return array_diff(array_keys(get_object_vars($this)), array('fileService'));
    }
}
