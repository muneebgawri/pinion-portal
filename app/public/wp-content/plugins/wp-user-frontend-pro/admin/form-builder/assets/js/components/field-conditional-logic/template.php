<div v-if="wpuf_cond && wpuf_cond.condition_status" class="panel-field-opt panel-field-opt-conditional-logic">
    <label>
        <?php _e( 'Conditional Logic', 'wpuf-pro' ); ?>
    </label>

    <ul class="list-inline">
        <li>
            <label><input type="radio" value="yes" v-model="wpuf_cond.condition_status"> <?php _e( 'Yes', 'wpuf-pro' ); ?></label>
        </li>

        <li>
            <label><input type="radio" value="no" v-model="wpuf_cond.condition_status"> <?php _e( 'No', 'wpuf-pro' ); ?></label>
        </li>
    </ul>

    <div v-if="'yes' === wpuf_cond.condition_status" class="condiotional-logic-container">
        <ul class="condiotional-logic-repeater">
            <li v-for="(condition, index) in conditions" class="clearfix">
                <div class="cond-field">
                    <select v-model="condition.name" @change="on_change_cond_field(index, $event)">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="(dep_field, index) in dependencies"
                            :value="dep_field.name"
                            :data-type="dep_field.input_type"
                            :data-field-type="dep_field.type"
                            :key="index"
                        >{{ dep_field.label }}</option>
                    </select>
                </div>

                <div class="cond-operator">
                    <select v-model="condition.operator">
                        <option
                            v-for="(operator, index) in get_cond_operators(condition.input_type)"
                            :value="operator.value"
                            :key="index">
                                {{ operator.label }}
                        </option>
                    </select>
                </div>

                <div class="cond-option">
                    <input
                        v-model="condition.option"
                        v-if="show_textfield(condition.input_type)"
                        style="width: 100%;"
                        type="text"
                        :disabled="is_disabled(condition.operator)"
                    >
                    <select
                        v-model="condition.option"
                        v-if="show_dropdown(condition.input_type)"
                        :disabled="is_disabled(condition.operator)"
                        @change="on_change_options_field(index, $event)"
                    >
                        <option
                            v-for="cond_option in get_cond_options(condition.name)"
                            :value="cond_option.opt_name"
                            :data-option-title="cond_option.opt_title"
                        >
                            {{ cond_option.opt_title }}
                        </option>
                    </select>
                </div>

                <div class="cond-action-btns">
                    <i class="fa fa-plus-circle" @click="add_condition"></i>
                    <i class="fa fa-minus-circle pull-right" @click="delete_condition(index)"></i>
                </div>
            </li>
        </ul>

        <p>
            <?php
                printf(
                    __( 'Show this field when %s of these rules are met', 'wpuf-pro' ),
                    '<select v-model="wpuf_cond.cond_logic"><option value="any">' . __( 'any', 'wpuf-pro' ) . '</option><option value="all">' . __( 'all', 'wpuf-pro' ) . '</option></select>'
                );
            ?>
        </p>
    </div>
</div>
