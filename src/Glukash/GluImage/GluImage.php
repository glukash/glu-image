<?php namespace Glukash\GluImage;

use GifFrameExtractor\GifFrameExtc xractor;
use GifCreator\GifCreator;
use Intervention\Image\ImageManager as InterImage;

class GluImage {

	/**
	 * image file resource
	 * @var [type]
	 */
	protected $imgFileRes;

	/**
	 * $isAnimatedGif flag
	 * @var [type]
	 */
	protected $isAnimatedGif;

	/**
	 * animated gif frames
	 * @var array
	 */
	protected $frames = array();

	/**
	 * GifCreator instance
	 * @var [type]
	 */
	protected $gifCreator;

	/**
	 * GifFrameExtractor instance
	 * @var [type]
	 */
	protected $gifFrameExtractor;

	/**
	 * Intervention\Image instance
	 * @var [type]
	 */
	protected $interImage;

	/**
	 * constructor
	 * dependency injection
	 * @param InterImage        $interImage        [description]
	 * @param GifFrameExtractor $gifFrameExtractor [description]
	 * @param GifCreator        $gifCreator        [description]
	 */
	public function __construct(InterImage $interImage, GifFrameExtractor $gifFrameExtractor, GifCreator $gifCreator)
	{
		$this->interImage = $interImage;
		$this->gifFrameExtractor = $gifFrameExtractor;
		$this->gifCreator = $gifCreator;
	}

	/**
	 * extracting animated gif frames
	 * @param  [type] $imgFilePath [description]
	 * @return [type]              [description]
	 */
	protected function _getAnimatedGif($imgFilePath)
	{
		return $this->gifFrameExtractor->extract($imgFilePath);
	}

	/**
	 * get image file
	 * @param  [type] $imgFile [description]
	 * @return [type]          [description]
	 */
	public function get($imgFile)
	{
		$this->isAnimatedGif = GifFrameExtractor::isAnimatedGif($imgFile);

		if ( $this->isAnimatedGif )
		{
			$this->frames = $this->_getAnimatedGif($imgFile);
		}
		else
		{
			$this->imgFileRes = $this->interImage->make($imgFile);
		}

		return $this;
	}

	/**
	 * resize image
	 * @param  [type]  $width         [description]
	 * @param  [type]  $height        [description]
	 * @param  boolean $aspectRatio   [description]
	 * @param  boolean $preventUpsize [description]
	 * @return [type]                 [description]
	 */
	protected function _resize($width=null, $height=null, $aspectRatio=true, $preventUpsize=true )
	{
		$this->imgFileRes->resize($width, $height, 
			function ($constraint) use ($aspectRatio, $preventUpsize) 
			{
				if ($aspectRatio) $constraint->aspectRatio();
				if ($preventUpsize) $constraint->upsize();
			}
		);
	}

	/**
	 * resize animated gif's frames
	 * @param  [type] $width  [description]
	 * @param  [type] $height [description]
	 * @return [type]         [description]
	 */
	protected function _resizeAnimated($width=null, $height=null)
	{

		$framesProcessed = array();
		foreach ($this->frames as $frame) {
			$this->imgFileRes = $this->interImage->make($frame['image']);
			$this->_resize($width,$height);

			$frameProcessed=array(
				'image'=>$this->imgFileRes->getCore(),
				'duration'=>$frame['duration']
			);

			$framesProcessed[] = $frameProcessed;
		}

		$this->frames = $framesProcessed;
	}

	/**
	 * crop image
	 * @param  [type] $width  [description]
	 * @param  [type] $height [description]
	 * @param  [type] $x      [description]
	 * @param  [type] $y      [description]
	 * @return [type]         [description]
	 */
	protected function _crop($width, $height, $x=null, $y=null)
	{
		$this->imgFileRes->crop($width, $height, $x, $y);
	}

	/**
	 * crom animated gif's frames
	 * @param  [type] $width  [description]
	 * @param  [type] $height [description]
	 * @param  [type] $x      [description]
	 * @param  [type] $y      [description]
	 * @return [type]         [description]
	 */
	protected function _cropAnimated($width, $height, $x=null, $y=null)
	{
		$framesProcessed = array();
		foreach ($this->frames as $frame) {
			$this->imgFileRes = $this->interImage->make($frame['image']);
			$this->_crop($width, $height, $x, $y);

			$frameProcessed=array(
				'image'=>$this->imgFileRes->getCore(),
				'duration'=>$frame['duration']
			);

			$framesProcessed[] = $frameProcessed;
		}
		$this->frames = $framesProcessed;
	}

	/**
	 * dave image file
	 * @param  [type] $path    [description]
	 * @param  [type] $quality [description]
	 * @return [type]          [description]
	 */
	public function save($path = null, $quality = null)
	{
		if ($this->isAnimatedGif)
		{
			$framesProcessed = array();
			foreach ($this->frames as $frame)
			{
				$framesProcessed[] = $frame['image'];
			}

			$this->gifCreator->create($framesProcessed, $this->gifFrameExtractor->getFrameDurations(), 0);

			$gifBinary = $this->gifCreator->getGif();
			$this->gifCreator->reset();
			file_put_contents($path, $gifBinary);
		}
		else
		{
			$this->imgFileRes->save($path, $quality);
		}

		return $this;
	}
	
	/**
	 * method caller
	 * @param  [type] $methodName [description]
	 * @param  [type] $arguments  [description]
	 * @return [type]             [description]
	 */
	public function __call($methodName, $arguments)
	{

		if ($this->isAnimatedGif)
		{
			$mName = '_'.$methodName.'Animated';
		}
		else
		{
			$mName = '_'.$methodName;
		}

		if ( method_exists ($this, $mName) )
		{
			call_user_func_array( array($this, $mName), $arguments );
		}
		else
		{
			throw new \BadMethodCallException('There is no method called: '.$mName.' in class: '.__CLASS__.'');
		}

		return $this;
	}
}