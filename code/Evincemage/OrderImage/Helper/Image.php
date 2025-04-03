<?php 
namespace Evincemage\OrderImage\Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
		
class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $galleryReadHandler;
	protected $imageHelper;


	public function __construct
	(
		GalleryReadHandler $galleryReadHandler,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Helper\Image $imageHelper
	)
	{
		$this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
	}

	public function addGallery($product)
	{
		$this->galleryReadHandler->execute($product);
	}

	public function getGalleryImages(\Magento\Catalog\Api\Data\Productinterface $product)
	{
		$images = $product->getMediaGalleryImages();
		if ($images instanceof \Magento\Framework\Data\Collection) 
		{
			foreach ($images as $image) 
			{
				/*var_dump($image->getFile());*/
				$image->setData(
					'small_image_url',
					$this->imageHelper->init($product,'product_page_image_small')->getUrl()

				);
			}
		}
		return $images;
	}

	public function getPlaceHolderImage($imageType)
	{
		return $this->imageHelper->getDefaultPlaceholderUrl($imageType);
	} 
}