<?php

require_once dirname(__FILE__).'/../../../../test/bootstrap/model.php';
require_once dirname(__FILE__).'/../../lib/model/iceModelMultimediaPeer.php';

$t = new lime_test(17, array('output' => new lime_output_color(), 'error_reporting' => true));

$images = array(
  __DIR__ .'/../../data/test/05620d783231c09402ea1d406d35a58c.jpg',
  __DIR__ .'/../../data/test/787d7bfb4d440de2ef136f097683f426.jpg',
  __DIR__ .'/../../data/test/c71794c2b0bf9fd8a9cd31abda2ed70b.jpg',
  __DIR__ .'/../../data/test/4fbe6dbd481c49c4eab4a2f12e7808f9.jpg',
  __DIR__ .'/../../data/test/38eb5d37b931b5c7c6429bc07600fbb9.jpg',
  __DIR__ .'/../../data/test/collectible-1911.jpg',
  __DIR__ .'/../../data/test/collectible-31.jpg',
  __DIR__ .'/../../data/test/collectible-1357.jpg',
  __DIR__ .'/../../data/test/movie-mark-03-armor-33130.jpg',
);

$t->diag('::getValidContentTypes()');

  $t->is(in_array('jpg', iceModelMultimediaPeer::getValidContentTypes()), true, 'Checking if JPG is supported');

$t->diag('::makeThumb()');

  $thumb = iceModelMultimediaPeer::makeThumb($images[2], '75x75', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(75, 75));

  $thumb = iceModelMultimediaPeer::makeThumb($images[2], '170x230', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(170, 230));

  $thumb = iceModelMultimediaPeer::makeThumb($images[3], '170x230', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(170, 230));

  $thumb = iceModelMultimediaPeer::makeThumb($images[2], '420x1000', 'scale');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(420, 560));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '1024x768', 'scale');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(577, 768));

  $thumb = iceModelMultimediaPeer::makeThumb($images[6], '230x150', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(230, 150));

  $thumb = iceModelMultimediaPeer::makeThumb($images[7], '150x150', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(150, 150));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '620x19:15', 'scale');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(368, 490));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '620!x19:15', 'top');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(620, 490));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '19:15x490!', 'top');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(620, 490));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '620!x0', 'resize');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(620, 824));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '0x490', 'resize');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(368, 490));

  $thumb = iceModelMultimediaPeer::makeThumb($images[5], '150', 'center');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(150, 150));

  $thumb = iceModelMultimediaPeer::makeThumb($images[7], '1024x768', 'resize');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(800, 600));

  $thumb = iceModelMultimediaPeer::makeThumb($images[8], '620!x0', 'resize');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(620, 826));

  $thumb = iceModelMultimediaPeer::makeThumb($images[8], '620x0', 'resize');
  $t->is(array($thumb->getWidth(), $thumb->getHeight()), array(450, 600));
