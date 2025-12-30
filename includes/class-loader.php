<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package Peanut_Booker
 * @since   1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register all actions and filters for the plugin.
 */
class Peanut_Booker_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @var array
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @var array
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress action.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      Optional. Priority. Default 10.
     * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress filter.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      Optional. Priority. Default 10.
     * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * A utility function that is used to register the actions and hooks into a single collection.
     *
     * @param array  $hooks         The collection of hooks to add to.
     * @param string $hook          The name of the WordPress hook.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      The priority.
     * @param int    $accepted_args The number of arguments.
     * @return array
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ( $this->actions as $hook ) {
            add_action(
                $hook['hook'],
                array( $hook['component'], $hook['callback'] ),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
