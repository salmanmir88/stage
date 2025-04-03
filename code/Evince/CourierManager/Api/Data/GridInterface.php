<?php
namespace Evince\CourierManager\Api\Data;

interface GridInterface
{
    const ENTITY_ID = 'entity_id';
    const CITY = 'city';


   /**
    * Get EntityId.
    *
    * @return int
    */
    public function getEntityId();

   /**
    * Set EntityId.
    */
    public function setEntityId($entityId);

   /**
    * Get City.
    *
    * @return varchar
    */
    public function getCity();

   /**
    * Set Title.
    */
    public function setCity($city);
   
}
