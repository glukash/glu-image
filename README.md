GluImage
=========

`glukash\glu-image` is a **PHP image manipulation** helper library.

The package includes ServiceProvider and Facade for easy **Laravel 4** integration.

##Purpose

The package supports two image manipulation methods: resize and crop.
The main purpose is to easly resize and crop jpg, png, and gif images.
The sweetness is that it supports animated gif using GD Library. (No Imagick, Gmagick is needed).

## Uses

The package uses:

- [Intervention/image](https://github.com/Intervention/image)
- [glukash/GifCreator](https://github.com/glukash/GifCreator/tree/patch-1) forked version of [Sybio/GifCreator](https://github.com/Sybio/GifCreator)
- [Sybio/GifFrameExtractor](https://github.com/Sybio/GifFrameExtractor)

## Requirements

- PHP >=5.3
- GD Library (>=2.0)

## Quick Installation

To install through composer, put the following in your `composer.json` file:

```json
{
	"repositories": [
	    {
	        "type": "git",
	        "url": "https://github.com/glukash/GifCreator"
	    }
	],
	"require": {
		"glukash/glu-image": "0.*"
	}
}
```

Add `Intervention\Image` and `Glukash\GluImage` service providers in `app/config/app.php`.

```php
'providers' => array(

	// ...
	'Intervention\Image\ImageServiceProvider',
	'Glukash\GluImage\GluImageServiceProvider',
),
```

Add `GluImage` alias in `app/config/app.php`.

```php
'aliases' => array(

	// ...

	'GluImage' => 'Glukash\GluImage\Facades\GluImage',
),
```

If you want to use `Intervention\Image` package directly, add `InterImage` alias in `app/config/app.php`.
This is not a necessary step for using `GluImage`.

```php
'aliases' => array(

	// ...

	'InterImage' => 'Intervention\Image\Facades\Image',
),
```

## Code Examples

```php
GluImage::get( public_path().'/img/01.jpg' )
	->resize(540,360)
	->save( public_path().'/img/01-resized1.jpg' )
	->resize(360,220)
	->save( public_path().'/img/01-resized2.jpg' );

GluImage::get( public_path().'/img/01.gif' )
	->resize(540,360)
	->save( public_path().'/img/01-resized.gif' )
	->crop(360,220)
	->save( public_path().'/img/01-resized-and-cropped.gif' );

$img = GluImage::get( public_path().'/img/01.jpg' );
$img->resize(540,360);
$img->save( public_path().'/img/01-resized.jpg' );
$img->crop(220,220);
$img->save( public_path().'/img/01-cropped.jpg' );
```


## License

GluImage is licensed under the [MIT License](http://opensource.org/licenses/MIT).

Copyright 2014 [Lukasz Gaszyna](http://glukash.net/)
