<?php 
namespace Evincemage\OrderImage\Plugin\Adminhtml;

class AddImage
{
	
	public function __construct
	(

	)
	{
		# code...
	}

	public function afterGetcolumns($items, $result)
	{
		if(is_array($result))
		{
			$newResult['image'] = 'Image';
			foreach ($result as $key => $value) 
			{
				$newResult[$key] = $value;
			}
			$result =$newResult;
		}
		return $result;
	}	
}