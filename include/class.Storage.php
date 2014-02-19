<?php
	class Storage
	{
		/*
			returns storage full local path
		*/
		static function get_storage_path($hash)
		{
			return(sprintf("%s/%s/%s/%s", rtrim(LOCAL_STORAGE_PATH, "/"), substr($hash, 0, 1), substr($hash, 1, 1), $hash));
		}

		/*
			returns thumbnail full local path for the file with specified hash
		*/
		static function get_thumbnail_local_path($hash)
		{
			return(sprintf("%s/%s/%s/%s-thumb.jpg", rtrim(LOCAL_STORAGE_PATH, "/"), substr($hash, 0, 1), substr($hash, 1, 1), $hash));
		}

		/*
			returns thumbnail network path for the file with specified hash
		*/
		static function get_thumbnail_url_path($hash)
		{
			return(sprintf("%s/%s/%s/%s-thumb.jpg", rtrim(WEB_STORAGE_PATH, "/"), substr($hash, 0, 1), substr($hash, 1, 1), $hash));
		}

		static function create_imagick_thumbnail($source_path, $dest_path, $pdf = FALSE)
		{
			if ($pdf == TRUE)
			{
				$image = new Imagick();
				$image->readImage($source_path . '[0]');
				$image = $image->flattenImages();
			}
			else
			{
				ob_clean();
				$image = new Imagick($source_path);
			}
			$maxsize = 400;				
			if($image->getImageHeight() <= $image->getImageWidth())
			{
				if ($image->getImageWidth() >= $maxsize) {
					// Resize image using the lanczos resampling algorithm based on width
					$image->resizeImage($maxsize,0,Imagick::FILTER_LANCZOS,1);
				}
			}
			else
			{
				if ($image->getImageHeight() >= $maxsize)
				{
					// Resize image using the lanczos resampling algorithm based on height
					$image->resizeImage(0,$maxsize,Imagick::FILTER_LANCZOS,1);
				}
			}					
			// Set to use jpeg compression
			$image->setImageCompression(Imagick::COMPRESSION_JPEG);
			// Set compression level (1 lowest quality, 100 highest quality)
			$image->setImageCompressionQuality(75);
			// Strip out unneeded meta data
			$image->stripImage();
			// Writes resultant image to output directory
			$image->writeImage($dest_path);
			// Destroys Imagick object, freeing allocated resources in the process
			$image->destroy();				
			return(file_exists($dest_path));
		}

		/*
			create storage paths default structure
		*/
		static function create_directories()
		{
			if (defined ('LOCAL_STORAGE_PATH'))
			{
				if (! is_dir(LOCAL_STORAGE_PATH))
				{
					if (! mkdir(LOCAL_STORAGE_PATH, 0777, TRUE))
					{
						throw new Exception("Error creating path: " . LOCAL_STORAGE_PATH);
					}
					chmod(LOCAL_STORAGE_PATH, 0770);
				}
				for ($i = 0; $i < 16; $i++)
				{
					for ($j = 0; $j < 16; $j++)
					{
						$dir = sprintf("%s/%x/%x/", rtrim(LOCAL_STORAGE_PATH, "/"), $i, $j);
						if (! is_dir($dir)) {
						    if (! mkdir($dir, 0777, TRUE))
						    {
						    	throw new Exception("Error creating path: " . $dir);
						    }
						    chmod($dir, 0770);
						}
					}
				}			
			}
		}
	}
?>