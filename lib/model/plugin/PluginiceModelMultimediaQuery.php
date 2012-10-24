<?php

/**
 * iceModelMultimediaQuery
 *
 */
class PluginiceModelMultimediaQuery extends BaseiceModelMultimediaQuery
{

  /**
   * @param  BaseObject  $model
   * @param  string  $comparison
   *
   * @return iceModelMultimediaQuery
   */
  public function filterByModel($model = null, $comparison = null)
  {
    if ($model instanceof BaseObject && method_exists($model, 'getId'))
    {
      return $this->filterByModel(get_class($model), $comparison)->filterByModelId($model->getId(), $comparison);
    }
    else
    {
      return parent::filterByModel($model, $comparison);
    }
  }

}
