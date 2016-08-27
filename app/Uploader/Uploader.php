<?php

namespace App\Uploader;
use Gregwar\Image\Image;

Class Uploader
{
	protected $uploadPath = null;
	protected $required = true;
	protected $fieldName = 'files';
	protected $maxSize = 10000000;
	protected $maxWidth = 0;
	protected $maxHeight = 0;
	protected $minWidth = 0;
	protected $minHeight = 0;
	protected $fileExt = '';
	protected $errorMsg = array();
	protected $successMsg = array();
	protected $detectMime = true;
	protected $fileMime = null;
	protected $allowedExt = '*';
	protected $createThumb = false;

	public function __construct($config = [])
	{
		foreach($config as $key => $value)
		{
			if(property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		$this->setAllowedExt($this->allowedExt);
	}

	public function multiUpload()
	{
		if(!$this->validateUploadPath())
		{
			// errors will already be set by validate_upload_path() so just return false
			return $this;
		}

		if(!is_array($_FILES[$this->fieldName]['name']))
		{
			$this->setError('misc', 'Please use singleUpload() method for single file upload');
			return $this;
		}

		for($index = 0; $index < count($_FILES[$this->fieldName]['name']); $index++)
		{
			$name = $_FILES[$this->fieldName]['name'][$index];
			$type = $_FILES[$this->fieldName]['type'][$index];
			$tmp_name = $_FILES[$this->fieldName]['tmp_name'][$index];
			$error = $_FILES[$this->fieldName]['error'][$index];
			$size = $_FILES[$this->fieldName]['size'][$index];

			if(!$this->getUploadError($error, $this->fieldName.'_'.$index))
			{
				continue;
			}

			if($this->getRequiredRule($index) && $error === 4)
			{
				$this->setError($this->fieldName.'_'.$index, 'Please select file for '.$this->fieldName.' '.$index.' field');
				continue;
			}

			if($tmp_name == '')
			{
				continue;
			}

			if($size > $this->maxSize)
			{
				$this->setError($this->fieldName.'_'.$index, 'Exceed limit file size for '.$this->fieldName.' '.$index.' field');
				continue;
			}

			if(!is_uploaded_file($tmp_name))
			{
				$this->setError($this->fieldName.'_'.$index, 'Uploaded file for '.$this->fieldName.' '.$index.' field was not found in server');
				continue;
			}

			$this->setFileExtension($name);
			$this->setFileMime($type);

			if(!$this->isAllowedDimensions($tmp_name))
			{
				$this->setError($this->fieldName.'_'.$index, 'Uploaded file dimension for '.$this->fieldName.' '.$index.' not match with dimension requirement');
				continue;
			}

			// Skip MIME type detection?
			if ($this->detectMime !== false)
			{
				$this->detectFileMime($tmp_name);
			}

			$this->fileMime = preg_replace('/^(.+?);.*$/', '\\1', $this->fileMime);
			$this->fileMime = strtolower(trim(stripslashes($this->fileMime), '"'));

			if(!$this->isAllowedExt($tmp_name))
			{
				$this->setError($this->fieldName.'_'.$index, 'File extension for '.$this->fieldName.' '.$index.' not was allowed');
				continue;
			}

			$prefix = time();
			$saveFile = $prefix.$this->fileExt;

			if(@copy($tmp_name, $this->uploadPath.$saveFile))
			{
				if($this->createThumb && $this->isFileImage())
				{
					Image::open($this->uploadPath.$saveFile)
						->resize(100, 100)
						->save($this->uploadPath.'thumb_'.$saveFile);
				}
				Image::open($this->uploadPath.$saveFile)
					->scaleResize(600, 400)
					->save($this->uploadPath.$saveFile);
			}
			else
			{
				if(@move_uploaded_file($tmp_name, $saveFile))
				{
					if($this->createThumb && $this->isFileImage())
					{
						Image::open($this->uploadPath.$saveFile)
							->resize(100, 100)
							->save($this->uploadPath.'thumb_'.$saveFile);
					}
					Image::open($this->uploadPath.$saveFile)
						->scaleResize(600, 400)
						->save($this->uploadPath.$saveFile);
				}
				else
				{
					$this->setError($this->fieldName.'_'.$index, 'Unknown error trigger for '.$this->fieldName.' '.$index);
					return $this;
				}
			}
			$this->successMsg[$this->fieldName.'_'.$index] = ['saveFile'=>$saveFile,'savePath'=>$this->uploadPath];
		}
		return $this;
	}

	public function singleUpload()
	{
		if(!$this->validateUploadPath())
		{
			// errors will already be set by validate_upload_path() so just return false
			return $this;
		}

		$name = isset($_FILES[$this->fieldName]['name']) ? $_FILES[$this->fieldName]['name'] : '';
		$type = isset($_FILES[$this->fieldName]['type']) ? $_FILES[$this->fieldName]['name'] : '';
		$tmp_name = isset($_FILES[$this->fieldName]['tmp_name']) ? $_FILES[$this->fieldName]['tmp_name'] : '';
		$error = isset($_FILES[$this->fieldName]['error']) ? $_FILES[$this->fieldName]['error'] : 4;
		$size = isset($_FILES[$this->fieldName]['size']) ? $_FILES[$this->fieldName]['size'] : 0;

		if(!$this->getUploadError($error, $this->fieldName))
		{
			return $this;
		}

		if($this->getRequiredRule() && $error === 4)
		{
			$this->setError($this->fieldName, 'Please select file for '.$this->fieldName.' field');
			return $this;
		}

		if(isset($_FILES[$this->fieldName]['name']))
		{
			if(is_array($_FILES[$this->fieldName]['name']))
			{
				$this->setError('misc', 'Please use multiUpload() method for multiple files upload');
				return $this;
			}

			if($tmp_name == '')
			{
				return $this;
			}

			if($size > $this->maxSize)
			{
				$this->setError($this->fieldName, 'Exceed limit file size for '.$this->fieldName.' field');
				return $this;
			}

			if(!is_uploaded_file($tmp_name))
			{
				$this->setError($this->fieldName, 'Uploaded file for '.$this->fieldName.' field was not found in server');
				return $this;
			}

			$this->setFileExtension($name);
			$this->setFileMime($type);

			if(!$this->isAllowedDimensions($tmp_name))
			{
				$this->setError($this->fieldName, 'Uploaded file dimension for '.$this->fieldName.' not match with dimension requirement');
				return $this;
			}

			// Skip MIME type detection?
			if ($this->detectMime !== false)
			{
				$this->detectFileMime($tmp_name);
			}

			$this->fileMime = preg_replace('/^(.+?);.*$/', '\\1', $this->fileMime);
			$this->fileMime = strtolower(trim(stripslashes($this->fileMime), '"'));

			if(!$this->isAllowedExt($tmp_name))
			{
				$this->setError($this->fieldName, 'File extension for '.$this->fieldName.' was not allowed');
				return $this;
			}

			$prefix = time();
			$saveFile = $prefix.$this->fileExt;

			if(@copy($tmp_name, $this->uploadPath.$saveFile))
			{
				if($this->createThumb && $this->isFileImage())
				{
					Image::open($this->uploadPath.$saveFile)
						->resize(100, 100)
						->save($this->uploadPath.'thumb_'.$saveFile);
				}
				Image::open($this->uploadPath.$saveFile)
					->scaleResize(600, 400)
					->save($this->uploadPath.$saveFile);
			}
			else
			{
				if(@move_uploaded_file($tmp_name, $saveFile))
				{
					if($this->createThumb && $this->isFileImage())
					{
						Image::open($this->uploadPath.$saveFile)
							->resize(100, 100)
							->save($this->uploadPath.'thumb_'.$saveFile);
					}
					Image::open($this->uploadPath.$saveFile)
						->scaleResize(600, 400)
						->save($this->uploadPath.$saveFile);
				}
				else
				{
					$this->setError($this->fieldName, 'Unknown error trigger for '.$this->fieldName);
					return $this;
				}
			}
			$this->successMsg[$this->fieldName] = ['saveFile'=>$saveFile,'savePath'=>$this->uploadPath];
		}
		return $this;
	}

	protected function getUploadError($Status, $field = '')
	{
		switch ($Status) {
			case 1:
				$this->setError($field, 'The uploaded file exceeds the upload_max_filesize directive in php.ini');
				return false;
				break;
			case 2:
				$this->setError($field, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
				return false;
				break;
			case 3:
				$this->setError($field, 'The uploaded file was only partially uploaded');
				return false;
				break;
			case 6:
				$this->setError($field, 'Missing a temporary folder');
				return false;
				break;
			case 7:
				$this->setError($field, 'Failed to write file to disk');
				return false;
				break;
			default:
				return true;
		}
		return true;
	}

	protected function getRequiredRule($index = null)
	{
		if(is_bool($this->required))
			return $this->required;

		if(is_array($this->required) && $index !== null)
		{
			if(is_bool($this->required[$index]))
				return $this->required[$index];
			else
				$this->setError($this->fieldName.'_'.$index, 'Invalid require rule for'.$this->fieldName.' '.$index.']');
		}
		else
			$this->setError('misc', 'Invalid require rule');
		return true;
	}

	protected function detectFileMime($file)
	{
		// We'll need this to validate the MIME info string (e.g. text/plain; charset=us-ascii)
		$regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';

		/* Fileinfo extension - most reliable method
		 *
		 * Unfortunately, prior to PHP 5.3 - it's only available as a PECL extension and the
		 * more convenient FILEINFO_MIME_TYPE flag doesn't exist.
		 */
		if (function_exists('finfo_file'))
		{
			$finfo = @finfo_open(FILEINFO_MIME);
			if (is_resource($finfo)) // It is possible that a false value is returned, if there is no magic MIME database file found on the system
			{
				$mime = @finfo_file($finfo, $file);
				finfo_close($finfo);

				/* According to the comments section of the PHP manual page,
				 * it is possible that this function returns an empty string
				 * for some files (e.g. if they don't exist in the magic MIME database)
				 */
				if (is_string($mime) && preg_match($regexp, $mime, $matches))
				{
					$this->fileMime = $matches[1];
					return;
				}
			}
		}

		/* This is an ugly hack, but UNIX-type systems provide a "native" way to detect the file type,
		 * which is still more secure than depending on the value of $_FILES[$field]['type'], and as it
		 * was reported in issue #750 (https://github.com/EllisLab/CodeIgniter/issues/750) - it's better
		 * than mime_content_type() as well, hence the attempts to try calling the command line with
		 * three different functions.
		 *
		 * Notes:
		 *	- the DIRECTORY_SEPARATOR comparison ensures that we're not on a Windows system
		 *	- many system admins would disable the exec(), shell_exec(), popen() and similar functions
		 *	  due to security concerns, hence the function_usable() checks
		 */
		if (DIRECTORY_SEPARATOR !== '\\')
		{
			$cmd = function_exists('escapeshellarg')
				? 'file --brief --mime '.escapeshellarg($file).' 2>&1'
				: 'file --brief --mime '.$file.' 2>&1';

			if (function_usable('exec'))
			{
				/* This might look confusing, as $mime is being populated with all of the output when set in the second parameter.
				 * However, we only need the last line, which is the actual return value of exec(), and as such - it overwrites
				 * anything that could already be set for $mime previously. This effectively makes the second parameter a dummy
				 * value, which is only put to allow us to get the return status code.
				 */
				$mime = @exec($cmd, $mime, $return_status);
				if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches))
				{
					$this->fileMime = $matches[1];
					return;
				}
			}

			if ( ! ini_get('safe_mode') && function_usable('shell_exec'))
			{
				$mime = @shell_exec($cmd);
				if (strlen($mime) > 0)
				{
					$mime = explode("\n", trim($mime));
					if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
					{
						$this->fileMime = $matches[1];
						return;
					}
				}
			}

			if (function_usable('popen'))
			{
				$proc = @popen($cmd, 'r');
				if (is_resource($proc))
				{
					$mime = @fread($proc, 512);
					@pclose($proc);
					if ($mime !== false)
					{
						$mime = explode("\n", trim($mime));
						if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
						{
							$this->fileMime = $matches[1];
							return;
						}
					}
				}
			}
		}

		// Fall back to the deprecated mime_content_type(), if available (still better than $_FILES[$field]['type'])
		if (function_exists('mime_content_type'))
		{
			$this->fileMime = @mime_content_type($file);
			if (strlen($this->fileMime) > 0) // It's possible that mime_content_type() returns false or an empty string
			{
				return;
			}
		}
	}

	protected function setFileMime($type)
	{
		$this->fileMime = $type;
	}

	protected function setFileExtension($filename)
	{
		$x = explode('.', $filename);

		if (count($x) === 1)
		{
			return '';
		}

		$ext = strtolower(end($x));
		$this->fileExt = '.'.$ext;
	}

	public function isFileImage()
	{
		// IE will sometimes return odd mime-types during upload, so here we just standardize all
		// jpegs or pngs to the same file type.

		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($this->fileMime, $png_mimes))
		{
			$this->fileMime = 'image/png';
		}
		elseif (in_array($this->fileMime, $jpeg_mimes))
		{
			$this->fileMime = 'image/jpeg';
		}

		$img_mimes = array('image/gif',	'image/jpeg', 'image/png');

		return in_array($this->fileMime, $img_mimes, true);
	}

	public function isAllowedDimensions($tmp_name)
	{
		if(!$this->isFileImage())
		{
			return true;
		}

		if (function_exists('getimagesize'))
		{
			$D = @getimagesize($tmp_name);

			if ($this->maxWidth > 0 && $D[0] > $this->maxWidth)
			{
				return false;
			}

			if ($this->maxHeight > 0 && $D[1] > $this->maxHeight)
			{
				return false;
			}

			if ($this->minWidth > 0 && $D[0] < $this->minWidth)
			{
				return false;
			}

			if ($this->minHeight > 0 && $D[1] < $this->minHeight)
			{
				return false;
			}
		}

		return true;
	}

	protected function isAllowedExt($tmp_name)
	{
		if ($this->allowedExt === '*')
		{
			return true;
		}

		if (empty($this->allowedExt) OR ! is_array($this->allowedExt))
		{
			$this->setError('misc', 'File extension is not allowed');
			return false;
		}

		$ext = strtolower(ltrim($this->fileExt, '.'));

		if (in_array($ext, $this->allowedExt, true))
		{
			return true;
		}

		$ext = strtolower(ltrim($this->fileExt, '.'));

		if (in_array($ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), true) && @getimagesize($tmp_name) === false)
		{
			return true;
		}

		return false;
	}

	protected function setError($field, $value)
	{
		$this->errorMsg[$field] = $value;
	}

	public function uploadError()
	{
		return (count($this->errorMsg) > 0 ? true : false);
	}

	public function getError()
	{
		return $this->errorMsg;
	}

	public function getSuccess()
	{
		return $this->successMsg;
	}

	protected function validateUploadPath()
	{
		if ($this->uploadPath === '')
		{
			$this->setError('misc', 'Missing save path destination');
			return false;
		}

		if (realpath($this->uploadPath) !== false)
		{
			$this->uploadPath = str_replace('\\', '/', realpath($this->uploadPath));
		}

		if ( ! is_dir($this->uploadPath))
		{
			$this->setError('misc', 'Invalid save path directory');
			return false;
		}

		if ( ! $this->isReallyWritable($this->uploadPath))
		{
			$this->setError('misc', 'Save pth destination is unwritable');
			return false;
		}

		$this->uploadPath = preg_replace('/(.+?)\/*$/', '\\1/',  $this->uploadPath);
		return true;
	}

	public function isReallyWritable($file)
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' && ($this->is_php('5.4') OR ! ini_get('safe_mode')))
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/').'/'.md5(mt_rand());
			if (($fp = @fopen($file, 'ab')) === false)
			{
				return false;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);
			return true;
		}
		elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === false)
		{
			return false;
		}

		fclose($fp);
		return true;
	}

	public function setAllowedExt($types)
	{
		$this->allowedExt = (is_array($types) OR $types === '*')
			? $types
			: explode('|', $types);
		return $this;
	}

	public function is_php($version)
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
	}
}
