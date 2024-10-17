<?php

namespace WeDevs\Wpuf\Pro\Admin;

/**
 * Taxonomy Restriction Class
 *
 * @since 2.7
 *
 * @package WP User Frontend
 */

class Taxonomy_Restriction {

    private static $_instance;

    public function __construct() {
        // add a tab to add subscription form
        add_action( 'wpuf_admin_subs_nav_tab', array( $this, 'nav_tab_func' ), 9, 1 );
        add_action( 'wpuf_admin_subs_nav_content', array( $this, 'nav_tab_content_func' ), 9, 1);
        add_action( 'wpuf_after_update_subscription_pack_meta', [ $this, 'save_func_meta' ], 10, 2 );
        add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue' ) );
        add_action( 'admin_print_styles-post.php', array( $this, 'enqueue' ) );

        add_filter( 'wpuf_taxonomy_checklist_args', array( $this, 'get_allowed_term_metas' ) );
        add_filter( 'wpuf_subscription_section_advanced', [ $this, 'add_taxonomy_restriction_section' ] );
        add_filter( 'wpuf_subscriptions_fields', [ $this, 'add_taxonomy_restriction_fields' ], 11 );
        add_filter( 'wpuf_subscription_payment_fields', [ $this, 'add_recurring_fields' ], 11 );
    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function nav_tab_func() {
        echo '<li><a href="#taxonomy-restriction"><span class="dashicons dashicons-image-filter"></span> ' . __( 'Taxonomy Restriction', 'wpuf-pro' ) . '</a></li>';
    }

    public function nav_tab_content_func() {
        global $pagenow;

        $allowed_tax_id_arr = array();
        $allowed_tax_id_arr = get_post_meta( get_the_ID() , '_sub_allowed_term_ids', true );
        if ( ! $allowed_tax_id_arr ) {
            $allowed_tax_id_arr = array();
        }
        $allowed_tax_ids    = $allowed_tax_id_arr ? implode( ', ', $allowed_tax_id_arr ) : '';
        ?>
        <section id="taxonomy-restriction">
            <table class='form-table' method='post'>
            <tr><?php _e( 'Choose the taxonomy terms you want to enable for this pack:', 'wpuf-pro' ); ?></tr>
                <tr>
                    <td>
                        <?php
                        $cts = get_taxonomies(array('_builtin'=>true), 'objects'); ?>
                        <?php foreach ($cts as $ct) {
                            if ( is_taxonomy_hierarchical( $ct->name ) ) { ?>
                            <div class="metabox-holder" style="float:left; padding:5px;">
                                <div class="postbox">
                                    <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                    <div class="inside" style="padding:0 10px;">
                                        <div class="taxonomydiv">
                                            <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                <?php
                                                $tax_terms = get_terms ( array(
                                                    'taxonomy' => $ct->name,
                                                    'hide_empty' => false,
                                                ) );
                                                foreach ($tax_terms as $tax_term) {
                                                    $selected[] = $tax_term;
                                                ?>
                                                <ul class="categorychecklist form-no-clear">
                                                    <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>"> <?php echo $tax_term->name; ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <p style="padding-left:10px;">
                                            <?php
                                            if ( ! isset( $selected ) || ! is_array( $selected ) ) {
                                                $selected = [];
                                            }
                                            ?>
                                            <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                            <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" > Select All
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } ?>
                    </td>

                    <?php
                    $cts = get_taxonomies(array('_builtin'=>false), 'objects'); ?>
                    <?php foreach ($cts as $ct) {
                        if ( is_taxonomy_hierarchical( $ct->name ) ) {
                            $selected = array();
                            ?>
                        <td>
                            <div class="metabox-holder" style="float:left; padding:5px;">
                                <div class="postbox">
                                    <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                    <div class="inside" style="padding:0 10px;">
                                        <div class="taxonomydiv">
                                            <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                <?php
                                                $tax_terms = get_terms ( array(
                                                    'taxonomy' => $ct->name,
                                                    'hide_empty' => false,
                                                ) );
                                                foreach ($tax_terms as $tax_term) {
                                                    $selected[] = $tax_term;
                                                    ?>
                                                <ul class="categorychecklist form-no-clear">
                                                    <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>"> <?php echo $tax_term->name; ?>
                                                </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <p style="padding-left:10px;">
                                            <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                            <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" > Select All
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php }
                    } ?>
                </tr>
            </table>
        </section>

    <?php
    }

    /**
     * Save allowed term metas to subscription pack post meta
     *
     * @return void
     */
    public function save_func_meta( $id, $request ) {
        if (
            empty( $request['subscription'] )
            && ! isset( $request['subscription']['meta_value'] )
            && ! isset( $request['subscription']['meta_value']['_sub_allowed_term_ids'] )
        ) {
            return;
        }

        update_post_meta( $id, '_sub_allowed_term_ids', wp_unslash( $request['subscription']['meta_value']['_sub_allowed_term_ids'] ) );
    }

    /**
     * Hook to get allowed term metas
     *
     * @return integer
     */
    public function get_allowed_term_metas( $tax_args ) {
        $current_user       = get_current_user_id();
        $pack               = get_user_meta( $current_user , '_wpuf_subscription_pack', true );

        if ( $pack && ( $pack !== 'Cancel' || $pack !== 'cancel' ) && isset( $pack['pack_id'] ) ) {
            $allowed_tax_id_arr = get_post_meta( $pack['pack_id'] , '_sub_allowed_term_ids', true );

            if ( !empty( $allowed_tax_id_arr ) ) {
                $allowed_tax_ids = array();

                foreach ( $allowed_tax_id_arr as $taxonomy_id ) {
                    $term = get_term_by( 'id', $taxonomy_id, $tax_args['taxonomy'] );

                    if ( $term ) {
                        $allowed_tax_ids[] = $taxonomy_id;
                    }
                }

                $allowed_tax_ids     = implode( ', ', $allowed_tax_ids );
                $tax_args['include'] = $allowed_tax_ids;
            }
        }

        return $tax_args;
    }

    public function enqueue() {
        wp_enqueue_script(  'taxonomy-restriction-box', WPUF_PRO_ASSET_URI . '/js/taxonomy-restriction.js'  );
    }

    /**
     * Add taxonomy restriction options
     *
     * @since 4.0.11
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_taxonomy_restriction_section( $sections ) {
        $sections['advanced_configuration'][] = [
            'id'        => 'taxonomy_restriction',
            'label'     => __( 'Taxonomy Access', 'wpuf-pro' ),
            'sub_label' => __( '(Control user access to specific taxonomies)', 'wpuf-pro' ),
        ];

        return $sections;
    }

    /**
     * Add taxonomy restriction fields
     *
     * @since 4.0.11
     *
     * @param array $fields
     *
     * @return array
     */
    public function add_taxonomy_restriction_fields( $fields ) {
        $cts         = get_taxonomies( [], 'objects' );
        $term_fields = [];

        foreach ( $cts as $ct ) {
            if ( ! is_taxonomy_hierarchical( $ct->name ) ) {
                continue;
            }

            $tax_terms = get_terms(
                [
                    'taxonomy'   => $ct->name,
                    'hide_empty' => false,
                ]
            );
            foreach ( $tax_terms as $tax_term ) {
                $term_fields[ $tax_term->taxonomy ][] = [
                    'value' => $tax_term->term_id,
                    'label' => $tax_term->name,
                ];
            }

            if ( empty( $term_fields[ $ct->name ] ) ) {
                continue;
            }

            $fields['advanced_configuration']['taxonomy_restriction'][ $ct->name ] = [
                'id'          => $ct->name,
                'name'        => $ct->name,
                'type'        => 'multi-select',
                'db_key'      => '_sub_allowed_term_ids',
                'db_type'     => 'meta',
                'label'       => $ct->label,
                'term_fields' => $term_fields[ $ct->name ],
            ];
        }

        return $fields;
    }

    /**
     * Add recurring fields that comes with our pro version
     *
     * @param array $fields
     *
     * @return array
     */
    public function add_recurring_fields( $fields ) {
        $fields['payment_details']['enable_recurring'] = [
            'id'      => 'cycle-period',
            'name'    => 'cycle-period',
            'db_key'  => '_recurring_pay',
            'db_type' => 'meta',
            'type'    => 'switcher',
            'label'   => __( 'Enable Recurring Payment', 'wpuf-pro' ),
            'tooltip' => __(
                'Enable recurring payments for this subscription. Users will be charged automatically at the end of each billing cycle until the subscription is canceled',
                'wpuf-pro'
            ),
            'default' => false,
        ];
        $fields['payment_details']['payment_cycle']    = [
            'id'          => 'payment-cycle',
            'name'        => 'payment-cycle',
            'type'        => 'inline',
            'fields'      => [
                'payment_cycle_value' => [
                    'id'      => 'payment-cycle-value',
                    'name'    => 'payment-cycle-value',
                    'type'    => 'input-number',
                    'db_key'  => '_billing_cycle_number',
                    'db_type' => 'meta',
                    'default' => '-1',
                ],
                'payment_cycle_unit'  => [
                    'id'      => 'payment-cycle-unit',
                    'name'    => 'payment-cycle-unit',
                    'type'    => 'select',
                    'options' => [
                        'day'   => __( 'Day(s)', 'wpuf-pro' ),
                        'week'  => __( 'Week(s)', 'wpuf-pro' ),
                        'month' => __( 'Month(s)', 'wpuf-pro' ),
                        'year'  => __( 'Year(s)', 'wpuf-pro' ),
                    ],
                    'db_key'  => '_cycle_period',
                    'db_type' => 'meta',
                    'default' => 'day',
                ],
            ],
            'key_id'      => 'payment_cycle',
            'label'       => __( 'Payment Cycle', 'wpuf-pro' ),
            'tooltip'     => __(
                'Specify the number of payment cycles this subscription will be active for. Enter -1 for unlimited',
                'wpuf-pro'
            ),
            'placeholder' => __( 'enter duration', 'wpuf-pro' ),
            'default'     => - 1,
        ];
        $fields['payment_details']['stop_cycle']       = [
            'id'      => 'stop-cycle',
            'name'    => 'stop-cycle',
            'db_key'  => '_enable_billing_limit',
            'db_type' => 'meta',
            'type'    => 'switcher',
            'label'   => __( 'Stop Billing Cycle', 'wpuf-pro' ),
            'tooltip' => __( 'Stop billing cycle after a certain number', 'wpuf-pro' ),
            'default' => false,
        ];
        $fields['payment_details']['billing_limit']    = [
            'id'      => 'billing-limit',
            'name'    => 'billing-limit',
            'db_key'  => '_billing_limit',
            'db_type' => 'meta',
            'type'    => 'input-number',
            'label'   => __( 'Number of Billing Cycles', 'wpuf-pro' ),
            'tooltip' => __( 'After how many times the billing should stop? Enter -1 for unlimited', 'wpuf-pro' ),
            'default' => -1,
        ];
        $fields['payment_details']['trial']            = [
            'id'      => 'trial',
            'name'    => 'trial',
            'db_key'  => '_trial_status',
            'db_type' => 'meta',
            'type'    => 'switcher',
            'label'   => __( 'Enable Trial', 'wpuf-pro' ),
            'tooltip' => __(
                'If enabled, users will have the option to access the subscription for a trial period before the actual billing cycle begins',
                'wpuf-pro'
            ),
            'default' => false,
        ];
        $fields['payment_details']['trial_period']     = [
            'id'      => 'trial-period',
            'name'    => 'trial-period',
            'type'    => 'inline',
            'fields'  => [
                'trial_period_value' => [
                    'id'      => 'trial-period-value',
                    'name'    => 'trial-period-value',
                    'type'    => 'input-number',
                    'db_key'  => '_trial_duration',
                    'db_type' => 'meta',
                    'default' => '-1',
                ],
                'trial_period_unit'  => [
                    'id'      => 'trial-period-unit',
                    'name'    => 'trial-period-unit',
                    'type'    => 'select',
                    'options' => [
                        'day'   => __( 'Day(s)', 'wpuf-pro' ),
                        'week'  => __( 'Week(s)', 'wpuf-pro' ),
                        'month' => __( 'Month(s)', 'wpuf-pro' ),
                        'year'  => __( 'Year(s)', 'wpuf-pro' ),
                    ],
                    'db_key'  => '_trial_duration_type',
                    'db_type' => 'meta',
                    'default' => 'day',
                ],
            ],
            'key_id'  => 'trial_period',
            'label'   => __( 'Trial Period', 'wpuf-pro' ),
            'tooltip' => __(
                'Enter the duration of the trial period for this subscription. Enter -1 for unlimited', 'wpuf-pro'
            ),
            'default' => -1,
        ];

        return $fields;
    }
}
