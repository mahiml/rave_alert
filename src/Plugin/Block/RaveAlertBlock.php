<?php
/**
 * @file
 * Contains \Drupal\rave_alert\Plugin\Block\RaveAlertBlock.
 */
namespace Drupal\rave_alert\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Created by PhpStorm.
 * User: mahim
 * Date: 4/5/17
 * Time: 9:30 AM
 */
/**
 * Provides a 'RaveAlert' block.
 *
 * @Block(
 * id = "raveBlock",
 * admin_label = @Translation("raveBlock"),
 * )
 */
class RaveAlertBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $build = [];
        // This placeholder is just a unique string, it has no other requirements
        // other than being injected in the places than where you want to have the
        // #lazy_builder take over.
        $placeholder = '';

        // Makes sure the placeholder will be replaced by a rave alert markup if available
        // by attaching a lazy builder that will make the rest of this block cacheable.
        $build['rave_alert'] = array(
            '#lazy_builder' => ['rave_alert_injected:DisplayCampusAlert', array()],
            '#create_placeholder' => TRUE
        );

        // This is really intensive to calculate, so that's why we're caching the
        // entire block and having the lazy builder take care of the uncacheable
        // part of the block.
        $build['#markup'] ='';
        $build['#cache']['max-age'] = 0;
        \Drupal::service('page_cache_kill_switch')->trigger();
        // Returns the renderable array with attached placeholder.
        return $build;
    }
}