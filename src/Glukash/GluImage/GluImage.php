<?php namespace Glukash\GluImage;

use GifFrameExtractor\GifFrameExtractor;
use GifCreator\GifCreator;
use Intervention\Image\ImageManager as InterImage;

class GluImage {
	
	protected $imgFile;
	
	protected $imgFileRes;
	
	protected $isAnimatedGif;
	
	protected $frames = array();
	
	protected $frame = false;
	
	protected $gifFrameExtractor;
	
	protected $gifCreator;
	
	protected $interImage;
	
	public function __construct(InterImage $interImage, GifFrameExtractor $gifFrameExtractor, GifCreator $gifCreator)
	{
		$this->interImage = $interImage;
		$this->gifFrameExtractor = $gifFrameExtractor;
		$this->gifCreator = $gifCreator;
	}
	
	protected function _getAnimatedGif($imgFilePath)
	{
		return $this->gifFrameExtractor->extract($imgFilePath);
	}
	
	public function get($imgFile)
	{
		$this->imgFile = $imgFile;
		
		$this->isAnimatedGif = GifFrameExtractor::isAnimatedGif($this->imgFile);
		
		if ( $this->isAnimatedGif )
		{
			$this->frames = $this->_getAnimatedGif($this->imgFile);
		}
		else
		{
			$this->imgFileRes = $this->interImage->make($this->imgFile);
		}
		
		return $this;
	}
	
	protected function _resize($width=null, $height=null)
	{
		$this->imgFileRes->resize($width, $height, function ($constraint) {
		    $constraint->aspectRatio();
		    $constraint->upsize();
		});
	}
	
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
	
	public function resize($width=null, $height=null)
	{
		if ($this->isAnimatedGif)
		{
			$this->_resizeAnimated($width,$height);
		}
		else
		{
			$this->_resize($width,$height);
		}
		
		return $this;
	}
	
	protected function _crop($width, $height, $x=null, $y=null)
	{
		$this->imgFileRes->crop($width, $height, $x, $y);
	}
	
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
	
	public function crop($width, $height, $x=null, $y=null)
	{
		if ($this->isAnimatedGif)
		{
			$this->_cropAnimated($width,$height,$x,$y);
		}
		else
		{
			$this->_crop($width,$height,$x,$y);
		}
		
		return $this;
	}
	
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
}