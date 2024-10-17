Vue.component('field-conditional-logic', {
    template: '#tmpl-wpuf-field-conditional-logic',

    mixins: [
        wpuf_mixins.option_field_mixin,
    ],

    data: function () {
        return {
            conditions: [],
            all_conditional_operators: {
                radio: [
                    {
                        label: 'is',
                        value: '=',
                    },
                    {
                        label: 'is not',
                        value: '!=',
                    },
                    {
                        label: 'any selection',
                        value: '!=empty',
                    },
                    {
                        label: 'no selection',
                        value: '==empty',
                    }
                ],
                text: [
                    {
                        label: 'is',
                        value: '=',
                    },
                    {
                        label: 'is not',
                        value: '!=',
                    },
                    {
                        label: 'contains',
                        value: '==contains',
                    },
                    {
                        label: 'has any value',
                        value: '!=empty',
                    },
                    {
                        label: 'has no value',
                        value: '==empty',
                    }
                ],
                number: [
                    {
                        label: 'is',
                        value: '=',
                    },
                    {
                        label: 'is not',
                        value: '!=',
                    },
                    {
                        label: 'contains',
                        value: '==contains',
                    },
                    {
                        label: 'has any value',
                        value: '!=empty',
                    },
                    {
                        label: 'has no value',
                        value: '==empty',
                    },
                    {
                        label: 'value is greater then',
                        value: 'greater',
                    },
                    {
                        label: 'value is less then',
                        value: 'less',
                    }
                ],
                others: [
                    {
                        label: 'has any value',
                        value: '!=empty',
                    },
                    {
                        label: 'has no value',
                        value: '==empty',
                    }
                ]
            }
        };
    },

    computed: {
        wpuf_cond: function () {
            return this.editing_form_field.wpuf_cond;
        },

        hierarchical_taxonomies: function () {
            var hierarchical_taxonomies = [];

            _.each(wpuf_form_builder.wp_post_types, function (taxonomies) {
                _.each(taxonomies, function (tax_props, taxonomy) {
                    if (tax_props.hierarchical) {
                        hierarchical_taxonomies.push(taxonomy);
                    }
                });
            });

            return hierarchical_taxonomies;
        },

        wpuf_cond_supported_fields: function () {
            return wpuf_form_builder.wpuf_cond_supported_fields.concat(this.hierarchical_taxonomies);
        },

        dependencies: function () {
            var self = this,
                dependenciesFields = [],
                i = 0;

            for (i = 0; i < self.$store.state.form_fields.length; i++) {

                var field = self.$store.state.form_fields[i];

                if ('column_field' === field.template) {
                    var innerColumnFields = self.$store.state.form_fields[i].inner_fields;

                    for (const columnFields in innerColumnFields) {
                        if (innerColumnFields.hasOwnProperty(columnFields)) {
                            var columnFieldIndex = 0;

                            while (columnFieldIndex < innerColumnFields[columnFields].length) {
                                var columnInnerField = innerColumnFields[columnFields][columnFieldIndex];

                                if ('taxonomy' !== columnInnerField.template) {
                                    if ( (_.indexOf(self.wpuf_cond_supported_fields, columnInnerField.template) >= 0) &&
                                            columnInnerField.name &&
                                            columnInnerField.label &&
                                            (self.editing_form_field.name !== columnInnerField.name)
                                        )
                                    {
                                        dependenciesFields.push(columnInnerField);
                                    }
                                } else {
                                    if ( (_.indexOf(self.wpuf_cond_supported_fields, columnInnerField.name) >= 0) &&
                                            columnInnerField.label &&
                                            (self.editing_form_field.name !== columnInnerField.name)

                                        )
                                    {
                                        dependenciesFields.push(columnInnerField);
                                    }
                                }

                                columnFieldIndex++;
                            }
                        }
                    }

                } else if ('taxonomy' !== field.template && 'column_field' !== field.template) {

                    if ( (_.indexOf(self.wpuf_cond_supported_fields, field.template) >= 0) &&
                            field.name &&
                            field.label &&
                            (self.editing_form_field.name !== field.name)
                        )
                    {
                        dependenciesFields.push(field);
                    }

                } else {

                    if ( (_.indexOf(self.wpuf_cond_supported_fields, field.name) >= 0) &&
                            field.label &&
                            (self.editing_form_field.name !== field.name)

                        )
                    {
                        dependenciesFields.push(field);
                    }

                }
            }

            return dependenciesFields;
        },

        prev_conditions: function () {
            let self = this,
                prev_fields = {},
                i = 0;

            for (i = 0; i < self.$store.state.form_fields.length; i++) {
                let field = self.$store.state.form_fields[i];
                prev_fields[field.name] = field.input_type;
            }

            return prev_fields;
        }
    },

    created: function () {
        var wpuf_cond = $.extend(true, {}, this.editing_form_field.wpuf_cond),
            prev_conditions = this.prev_conditions,
            self = this;

        _.each(wpuf_cond.cond_field, function (name, i) {

            if (name && wpuf_cond.cond_field[i] && wpuf_cond.cond_operator[i]) {
                const input_types = wpuf_cond.input_type !== undefined ? wpuf_cond.input_type : '';
                const option_title = wpuf_cond.option_title !== undefined && wpuf_cond.option_title[i] !== undefined ? wpuf_cond.option_title[i] : "";
                let input_type = '';
                // for backward compatibility
                if (input_types === '') {
                    input_type = prev_conditions[name] !== undefined ? prev_conditions[name] : '';
                } else {
                    input_type = input_types[i] !== undefined ? input_types[i] : '';
                }
                self.conditions.push({
                    name: name,
                    operator: wpuf_cond.cond_operator[i],
                    option: wpuf_cond.cond_option[i],
                    option_title: option_title,
                    input_type: input_type,
                    field_type: (wpuf_cond.field_type !== undefined) && (wpuf_cond.field_type[i] !== undefined) ? wpuf_cond.field_type[i] : ''
                });
            }

        });

        if (!self.conditions.length) {
            self.conditions = [{
                name: '',
                operator: '',
                option: ''
            }];
        }
    },

    methods: {
        get_cond_options: function (field_name) {
            var options = [];

            if (_.indexOf(this.hierarchical_taxonomies, field_name) < 0) {
                var dep = this.dependencies.filter(function (field) {
                    return field.name === field_name;
                });

                if (dep.length && dep[0].options) {
                    _.each(dep[0].options, function (option_title, option_name) {
                        options.push({opt_name: option_name, opt_title: option_title});
                    });
                }

            } else {
                // NOTE: Two post types cannot have same taxonomy
                // ie: post_type_one and post_type_two cannot have same taxonomy my_taxonomy
                var i;

                for (i in wpuf_form_builder.wp_post_types) {
                    var taxonomies = wpuf_form_builder.wp_post_types[i];

                    if (taxonomies.hasOwnProperty(field_name)) {
                        var tax_field = taxonomies[field_name];

                        if (tax_field.terms && tax_field.terms.length) {
                            var j = 0;

                            for (j = 0; j < tax_field.terms.length; j++) {
                                options.push({opt_name: tax_field.terms[j].term_id, opt_title: tax_field.terms[j].name});
                            }
                        }

                        break;
                    }
                }
            }

            return options;
        },

        get_cond_operators: function (field_type) {
            switch (field_type) {
                case 'select':
                case 'radio':
                case 'category':
                case 'taxonomy':
                case 'checkbox':
                    return this.all_conditional_operators.radio;
                case 'text':
                case 'textarea':
                case 'email':
                case 'url':
                case 'password':
                    return this.all_conditional_operators.text;
                case 'numeric_text':
                    return this.all_conditional_operators.number;
                case null:
                    return [];
                default:
                    return this.all_conditional_operators.others;
            }
        },

        show_dropdown: function(input_type) {
            switch (input_type) {
                case 'select':
                case 'radio':
                case 'category':
                case 'taxonomy':
                case 'checkbox':
                    return true;
                default:
                    return false;
            }
        },

        show_textfield: function(input_type) {
            switch (input_type) {
                case 'select':
                case 'radio':
                case 'category':
                case 'taxonomy':
                case 'checkbox':
                    return false;
                default:
                    return true;
            }
        },

        on_change_cond_field: function (index, event) {
            let current_condition = this.conditions[index];
            current_condition.option = '';
            const the_target = event.target.options[event.target.options.selectedIndex];
            const input_type = the_target.dataset.type;
            const field_type = the_target.dataset.fieldType;
            const opt_name = this.get_cond_options(current_condition.name)[0] !== undefined ? this.get_cond_options(current_condition.name)[0].opt_name : '';
            current_condition.input_type = input_type;
            current_condition.field_type = field_type !== undefined ? field_type : input_type;
            // set the default selected item
            current_condition.operator = this.get_cond_operators(input_type)[0].value;
            current_condition.option = opt_name;
        },

        on_change_options_field: function (index, event) {
            const current_condition = this.conditions[index];
            const the_target = event.target.options[event.target.options.selectedIndex];
            const option_title = the_target.dataset.optionTitle !== undefined ? the_target.dataset.optionTitle : "";

            current_condition.option_title = option_title;
        },

        is_disabled: function(operator) {
            // check if the operator is set to 'has any value' or 'has no value'
            return (operator === '==empty') || (operator === '!=empty');
        },

        add_condition: function () {
            this.conditions.push({
                name: '',
                operator: '',
                option: '',
                option_title: '',
                input_type: '',
                field_type: ''
            });
        },

        delete_condition: function (index) {
            if (this.conditions.length === 1) {
                this.warn({
                    text: this.i18n.last_choice_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.conditions.splice(index, 1);
        }
    },

    watch: {
        conditions: {
            deep: true,
            handler: function (new_conditions) {
                var new_wpuf_cond = $.extend(true, {}, this.editing_form_field.wpuf_cond);

                if (!this.editing_form_field.wpuf_cond) {
                    new_wpuf_cond.condition_status = 'no';
                    new_wpuf_cond.cond_logic = 'all';
                }

                new_wpuf_cond.cond_field = [];
                new_wpuf_cond.cond_operator = [];
                new_wpuf_cond.cond_option = [];
                new_wpuf_cond.option_title = [];
                new_wpuf_cond.input_type = [];
                new_wpuf_cond.field_type = [];

                _.each(new_conditions, function (cond) {
                    new_wpuf_cond.cond_field.push(cond.name);
                    new_wpuf_cond.cond_operator.push(cond.operator);
                    if ((cond.operator === '==empty') || (cond.operator === '!=empty')) {
                        new_wpuf_cond.cond_option.push('');
                    } else {
                        new_wpuf_cond.cond_option.push(cond.option);
                    }
                    new_wpuf_cond.input_type.push(cond.input_type);
                    new_wpuf_cond.field_type.push(cond.field_type);
                    new_wpuf_cond.option_title.push(cond.option_title);
                });

                this.update_value('wpuf_cond', new_wpuf_cond);
            }
        }
    }
});
