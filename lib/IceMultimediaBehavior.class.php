<?php

class IceMultimediaBehavior
{
  protected static
    $_multimedia = array(),
    $_counts = array();

  public function postSave(BaseObject $object)
  {
    return true;
  }

  /**
   * Get the primary image
   *
   * @param  BaseObject  $object
   * @param  string  $mode
   *
   * @return iceModelMultimedia
   */
  public function getPrimaryImage(BaseObject $object, $mode = Propel::CONNECTION_READ)
  {
    $multimedia = array_merge(self::$_multimedia, (array) $object->_multimedia);

    $key = md5(serialize(array(get_class($object), $object->getId(), 1, 'image', true)));
    if (!array_key_exists($key, $multimedia) || $mode === Propel::CONNECTION_WRITE)
    {
      // Trying to avoid a MySQL query here
      if ($this->getMultimediaCount($object, 'image', $mode) > 0)
      {
        $multimedia[$key] = $this->getMultimedia($object, 1, 'image', true, $mode);
      }
      else
      {
        $multimedia[$key] = null;
      }
    }

    return self::$_multimedia[$key] = $multimedia[$key];
  }

  /**
   * A proxy method to iceModelMultimediaPeer::createMultimediaFromFile()
   *
   * @see iceModelMultimediaPeer::createMultimediaFromFile()
   *
   * @param  BaseObject  $object
   * @param  string|sfValidatedFile  $file
   * @param  array  $options
   *
   * @return iceModelMultimedia
   */
  public function setPrimaryImage(BaseObject $object, $file, $options = array())
  {
    // First we need to set unset the primary status
    // from the rest of the multimedia records
    if (( $_multimedia = iceModelMultimediaPeer::retrieveByModel($object, 0, 'image') ))
    {
      foreach ($_multimedia as $m)
      {
        $m->setIsPrimary(false);
      }

      $_multimedia->save();
    }

    // Clear the static variables
    $this->clearStaticCache($object);

    $key = md5(serialize(array(get_class($object), $object->getId(), 1, 'image', true)));
    if (( self::$_multimedia[$key] = iceModelMultimediaPeer::createMultimediaFromFile($object, $file, $options) ))
    {
      self::$_multimedia[$key]->setIsPrimary(true);
      self::$_multimedia[$key]->save();
    }

    return self::$_multimedia[$key];
  }

  /**
   * Proxy method for iceModelMultimediaPeer::retrieveByModel()
   *
   * @see iceModelMultimediaPeer::createMultimediaFromFile()
   * @see iceModelMultimediaPeer::createMultimediaFromUrl()
   *
   * @param  BaseObject  $object
   * @param  string|sfValidatedFile  $file
   * @param  array  $options
   *
   * @return iceModelMultimedia
   */
  public function addMultimedia(BaseObject $object, $file, $options = array())
  {
    if (is_string($file) && IceWebBrowser::isUrl($file))
    {
      $_multimedia = iceModelMultimediaPeer::createMultimediaFromUrl($object, $file, $options);
    }
    else
    {
      $_multimedia = iceModelMultimediaPeer::createMultimediaFromFile($object, $file, $options);
    }

    // Clear the static variables
    $this->clearStaticCache($object);

    return $_multimedia;
  }

  /**
   * Proxy method for iceModelMultimediaPeer::retrieveByModel()
   *
   * @see iceModelMultimediaPeer::retrieveByModel()
   */
  public function getMultimedia(
    BaseObject $object,
    $limit = 0,
    $type = null,
    $primary = null,
    $mode = Propel::CONNECTION_READ
  ) {
    $multimedia = array_merge(self::$_multimedia, (array) $object->_multimedia);

    $key = md5(serialize(array(get_class($object), $object->getId(), $limit, $type, $primary)));
    if (!array_key_exists($key, $multimedia) || $mode === Propel::CONNECTION_WRITE)
    {
      $multimedia[$key] = null;

      if ($mode === Propel::CONNECTION_READ && ($element = $object->getEblobElement('multimedia')))
      {
        $collection = new PropelObjectCollection(array());
        $collection->setModel('iceModelMultimedia');

        $_collection = new PropelObjectCollection(array());
        $_collection->setModel('iceModelMultimedia');
        $_collection->fromXML($element->asXml());

        foreach ($_collection as $m)
        {
          $true = true;

          if ($type && $m->getType() != $type)
          {
            $true = false;
          }
          else if ($primary !== null && (bool) $m->getIsPrimary() !== $primary)
          {
            $true = false;
          }

          if ($true)
          {
            $collection->append($m);
          }

          if ($limit > 0 && count($collection) >= $limit)
          {
            break;
          }
        }

        $multimedia[$key] = ($primary === true || $limit == 1)
          ? $collection->getFirst()
          : $collection;
      }

      /**
       * Failback to quering the database before all Model classes implement the eblob behavior
       */
      if (count($multimedia[$key]) == 0)
      {
        $multimedia[$key] = iceModelMultimediaPeer::retrieveByModel(
          $object, $limit, $type, $primary
        );
      }
    }

    $_multimedia = self::$_multimedia[$key] = $multimedia[$key];

    return $_multimedia;
  }

  /**
   * Proxy method for iceModelMultimediaPeer::countByModel()
   *
   * @see iceModelMultimediaPeer::countByModel()
   */
  public function getMultimediaCount(BaseObject $object, $type = null, $mode = Propel::CONNECTION_READ)
  {
    $counts = array_merge(self::$_counts, (array) $object->_counts);

    $key = md5(serialize(array(get_class($object), $object->getId(), $type)));
    if (!array_key_exists($key, $counts) || $mode === Propel::CONNECTION_WRITE)
    {
      $multimedia = $this->getMultimedia($object, 0, $type, null, $mode);
      $counts[$key] = $multimedia instanceof PropelObjectCollection
        ? $multimedia->count()
        : 0;
    }

    return self::$_counts[$key] = $counts[$key];
  }

  /**
   * @param  BaseObject  $object
   */
  public function preDelete(BaseObject $object)
  {
    if (( $_multimedia = $this->getMultimedia($object, 0, null, null, Propel::CONNECTION_WRITE) ))
    {
      foreach ($_multimedia as $m)
      {
        $m->delete();
      }
    }
  }

  /**
   * Clear the internal multimedia object cache
   */
  public function clearStaticCache(BaseObject $object = null)
  {
    // Reset the local multimedia and counts cache
    self::$_multimedia = array();
    self::$_counts = array();

    if ($object !== null)
    {
      $object->_counts = array();
      $object->_multimedia = array();
    }
  }

  /**
   * Retrieve one or many multimedia records based on role
   *
   * @param     BaseObject $object
   * @param     string $role
   * @param     integer $limit
   * @param     string $type
   *
   * @return    iceModelMultimedia|iceModelMultimedia[]|null
   */
  public function getMultimediaByRole(
    BaseObject $object,
    $role = PluginiceModelMultimediaPeer::ROLE_MAIN,
    $limit = 0,
    $type = null
  ) {
    $_multimedia = $this->getMultimedia($object, $limit, $type);

    // if a single object was returned by getMultimedia
    if ($_multimedia instanceof iceModelMultimedia)
    {
      // test for role and return it or null
      return self::_filterByRole($_multimedia, $role)
        ? $_multimedia
        : null;
    }
    elseif (count($_multimedia))
    {
      // getMultimedia returned an object collection, filter it by role

      // First we need to create a new collection object to return
      $collection = new PropelObjectCollection(array());
      $collection->setModel('iceModelMultimedia');

      // and then populate it with the proper data
      $collection->setData(array_filter(
        $_multimedia->getArrayCopy(),
        create_function(
          '$m, $role = "' . $role . '"',
          'return IceMultimediaBehavior::_filterByRole($m, $role);'
        )
      ));

      // return the resulting object collection,
      // or return null if all items were filtered out
      return count($collection)
        ? (1 == count($collection) ? $collection->getFirst() : $collection)
        : null;
    }
    else
    {
      return null;
    }
  }

  /**
   * Filter a multimedia record on role. Used for filtering collections
   *
   * @param     iceModelMultimedia $m
   * @param     string $role
   *
   * @return    boolean
   */
  public static function _filterByRole(iceModelMultimedia $m, $role)
  {
    return $m->getRole() == $role;
  }

}
