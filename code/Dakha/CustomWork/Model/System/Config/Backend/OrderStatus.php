<?php
 
namespace Dakha\CustomWork\Model\System\Config\Backend;
 
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
 
 
/**
 * Class OrderStatus
 * @package Dakha\CustomWork\Model\System\Config\Backend
 */
class OrderStatus extends ConfigValue
{
 
    /**
     * @var SerializerInterface
     */
    protected $serializer;
 
 
    /**
     * OrderStatus constructor.
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
 
 
    /**
     * @return ConfigValue|void
     */
    public function beforeSave()
    {
        /** @var array $value */
        $value = $this->getValue();
        unset($value['__empty']);
        $encodedValue = $this->serializer->serialize($value);
 
        $this->setValue($encodedValue);
    }
 
 
    /**
     * @return ConfigValue|void
     */
    protected function _afterLoad()
    {
        /** @var string $value */
        $value = $this->getValue();
        if ($value) {
            $decodedValue = $this->serializer->unserialize($value);
            $this->setValue($decodedValue);
        }
    }
}