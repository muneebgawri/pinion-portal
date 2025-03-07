;(function($) {
'use strict';

Vue.component('field-address', {
    template: '#tmpl-wpuf-field-address',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    data: function () {
        return {
            default_country: this.editing_form_field.address.country_select.value,
            show_details: {
                street_address:  false,
                street_address2: false,
                city_name:       false,
                state:           false,
                zip:             false,
                country_select:  false,
            },
        };
    },

    computed: {
        countries: function () {
            return wpuf_form_builder.countries;
        },

        visibility_buttons: function () {
            return [
                { name: 'all', title: this.i18n.show_all },
                { name: 'hide', title: this.i18n.hide_these },
                { name: 'show', title: this.i18n.only_show_these }
            ];
        },

        active_visibility: function () {
            return this.editing_form_field.address.country_select.country_list_visibility_opt_name;
        },

        country_in_hide_list: function () {
            return this.editing_form_field.address.country_select.country_select_hide_list;
        },

        country_in_show_list: function () {
            return this.editing_form_field.address.country_select.country_select_show_list;
        }
    },

    mounted: function () {
        this.bind_selectize();
    },

    methods: {
        toggle_address_checked: function(field) {
            this.editing_form_field.address[field].checked = this.editing_form_field.address[field].checked ? '' : 'checked';
        },

        toggle_address_required: function(field) {
            this.editing_form_field.address[field].required = this.editing_form_field.address[field].required ? '' : 'checked';
        },

        toggle_show_details: function (field) {
            this.show_details[field] = !this.show_details[field];
        },

        bind_selectize: function () {
            var self = this;

            $(this.$el).find('.default-country').selectize({
                plugins: ['remove_button'],

            }).on('change', function () {
                var value = $(this).val();

                self.default_country = value;
                self.update_country_select('value', value);
            });

            $(this.$el).find('select.country-list-selector').selectize({
                plugins: ['remove_button'],
                placeholder: this.i18n.select_countries

            }).on('change', function (e) {
                var select      = $(this),
                    visibility  = e.target.dataset.visibility,
                    value       = select.val(),
                    list        = '';

                switch(visibility) {
                    case 'hide':
                        list = 'country_select_hide_list';
                        break;

                    case 'show':
                        list = 'country_select_show_list';
                        break;
                }

                if (!value) {
                    value = [];
                }

                self.update_country_select(list, value);

            });
        },

        update_country_select: function (prop, value) {
            var address = $.extend(true, {}, this.editing_form_field.address);

            address.country_select[prop] = value;

            this.update_value('address', address);
        },

        set_visibility: function(visibility) {
            this.update_country_select('country_list_visibility_opt_name', visibility);
        },
    }
});

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

Vue.component('field-country-list', {
    template: '#tmpl-wpuf-field-country-list',

    mixins: [
        wpuf_mixins.option_field_mixin,
    ],

    data: function () {
        return {
            default_country: this.editing_form_field.country_list.name
        };
    },

    computed: {
        countries: function () {
            return wpuf_form_builder.countries;
        },

        visibility_buttons: function () {
            return [
                { name: 'all', title: this.i18n.show_all },
                { name: 'hide', title: this.i18n.hide_these },
                { name: 'show', title: this.i18n.only_show_these }
            ];
        },

        active_visibility: function () {
            return this.editing_form_field.country_list.country_list_visibility_opt_name;
        },

        country_in_hide_list: function () {
            return this.editing_form_field.country_list.country_select_hide_list;
        },

        country_in_show_list: function () {
            return this.editing_form_field.country_list.country_select_show_list;
        },

    },

    mounted: function () {
        this.bind_selectize();
    },

    methods: {
        bind_selectize: function () {
            var self = this;

            $(this.$el).find('.default-country').selectize({
                plugins: ['remove_button'],

            }).on('change', function () {
                var value = $(this).val();

                self.default_country = value;
                self.update_country_list('name', value);
            });

            $(this.$el).find('select.country-list-selector').selectize({
                plugins: ['remove_button'],
                placeholder: this.i18n.select_countries

            }).on('change', function (e) {
                var select      = $(this),
                    visibility  = e.target.dataset.visibility,
                    value       = select.val(),
                    list        = '';

                switch(visibility) {
                    case 'hide':
                        list = 'country_select_hide_list';
                        break;

                    case 'show':
                        list = 'country_select_show_list';
                        break;
                }

                if (!value) {
                    value = [];
                }

                self.update_country_list(list, value);

            });
        },

        update_country_list: function (prop, value) {
            var country_list = $.extend(true, {}, this.editing_form_field.country_list);

            country_list[prop] = value;

            this.update_value('country_list', country_list);
        },

        set_visibility: function(visibility) {
            this.update_country_list('country_list_visibility_opt_name', visibility);
        },
    }
});

Vue.component('field-gmap-set-position', {
    template: '#tmpl-wpuf-field-gmap-set-position',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    /* global google */
    mounted: function () {
        var self = this,
            default_pos = self.editing_form_field.default_pos,
            default_zoom = self.editing_form_field.zoom;

        var default_latLng = default_pos.split(',');

        if (2 === default_latLng.length && isFinite(default_latLng[0]) && isFinite(default_latLng[1])) {
            default_pos = {lat: parseFloat(default_latLng[0]), lng: parseFloat(default_latLng[1])};
        } else {
            default_pos = {lat: 40.7143528, lng: -74.0059731};
        }

        var map = new google.maps.Map($(this.$el).find('.wpuf-field-google-map').get(0), {
            center: default_pos,
            zoom: parseInt(default_zoom) || 12,
            mapTypeId: 'roadmap',
            streetViewControl: false,
        });

        var geocoder = new google.maps.Geocoder();

        // Create the search box and link it to the UI element.
        var input = $(this.$el).find('.wpuf-google-map-search').get(0);
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        var markers = [];

        set_marker(self.editing_form_field.default_pos);

        function set_marker(address) {
            geocoder.geocode({'address': address}, function(results, status) {
                if (status === 'OK') {
                    // Clear out the old markers.
                    _.each(markers, function (marker) {
                        marker.setMap(null);
                    });

                    markers = [];

                    // Create a marker for each place.
                    markers.push(new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    }));

                    map.setCenter(results[0].geometry.location);

                    self.set_default_pos(results[0].geometry.location);
                }
            });
        }

        // when input latitude and longitude like "40.7143528,-74.0059731"
        input.addEventListener('input', function () {
            var address = this.value;

            var latLng = address.split(',');

            if (2 === latLng.length && isFinite(latLng[0]) && isFinite(latLng[1])) {
                set_marker(address);
            }
        });



        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length === 0) {
                return;
            }

            // Clear out the old markers.
            _.each(markers, function (marker) {
                marker.setMap(null);
            });

            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();

            _.each(places, function (place) {
                if (!place.geometry) {
                    console.log('Returned place contains no geometry');

                    return;
                }

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    position: place.geometry.location
                }));

                self.set_default_pos(place.geometry.location);

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);

                } else {
                    bounds.extend(place.geometry.location);
                }
            });

            map.fitBounds(bounds);
        });

        map.addListener('click', function(e) {
            var latLng = e.latLng;

            // Clear out the old markers.
            _.each(markers, function (marker) {
                marker.setMap(null);
            });

            markers = [];

            markers.push(new google.maps.Marker({
                position: latLng,
                map: map
            }));

            self.set_default_pos(latLng);

            map.panTo(latLng);
        });

        map.addListener('zoom_changed', function () {
            var zoom = map.getZoom();

            self.update_value('zoom', zoom);

            wpuf_form_builder.event_hub.$emit('wpuf-update-map-zoom-' + self.editing_form_field.id, zoom);
        });
    },

    methods: {
        toggle_checkbox_field: function (field) {
            this.editing_form_field[field] = ('yes' === this.editing_form_field[field]) ? 'no' : 'yes';
        },

        set_default_pos: function (latLng) {
            latLng = latLng.toJSON();

            this.update_value('default_pos', latLng.lat + ',' + latLng.lng);
        }
    }
});

Vue.component('field-math-captcha', {
    template: '#tmpl-wpuf-field-math-captcha',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],
});

Vue.component('field-repeater-columns', {
    template: '#tmpl-wpuf-field-repeater-columns',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    mounted: function () {
        var self = this;

        $(this.$el).find('.repeater-columns').sortable({
            items: '.repeater-single-column',
            handle: '.sort-handler',
            update: function (e, ui) {
                var item        = ui.item[0],
                    data        = item.dataset,
                    toIndex     = parseInt($(ui.item).index()),
                    fromIndex   = parseInt(data.index);

                var columns = $.extend(true, [], self.editing_form_field.columns);

                columns.swap(fromIndex, toIndex);

                self.update_value('columns', columns);
            }
        }).disableSelection();
    },

    methods: {
        add_column: function () {
            var count       = this.editing_form_field.columns.length,
                new_column  = this.i18n.column + ' ' + (count + 1);

            this.editing_form_field.columns.push(new_column);
        },

        delete_column: function (index) {
            if (this.editing_form_field.columns.length === 1) {
                this.warn({
                    text: this.i18n.last_column_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.editing_form_field.columns.splice(index, 1);
        }
    },

    watch: {

    }
});

Vue.component('field-step-start', {
    template: '#tmpl-wpuf-field-step-start',

    mixins: [
        wpuf_mixins.option_field_mixin,
    ],
});

/**
 * Field template: Action Hook
 */
Vue.component('form-action_hook', {
    template: '#tmpl-wpuf-form-action_hook',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Address Field
 */
Vue.component('form-address_field', {
    template: '#tmpl-wpuf-form-address_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        countries: function () {
            var countries   = wpuf_form_builder.countries,
                visibility  = this.field.address.country_select.country_list_visibility_opt_name,
                hide_list   = this.field.address.country_select.country_select_hide_list,
                show_list   = this.field.address.country_select.country_select_show_list;

            if ('hide' === visibility && hide_list && hide_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(hide_list, country.code) < 0);
                });

            } else if ('show' === visibility && show_list && show_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(show_list, country.code) >= 0);
                });
            }

            return countries;
        },

        default_country: function () {
            return this.field.address.country_select.value;
        }
    }
});

/**
 * Field template: Avatar
 */
Vue.component('form-avatar', {
    template: '#tmpl-wpuf-form-avatar',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Country list
 */
Vue.component('form-country_list_field', {
    template: '#tmpl-wpuf-form-country_list_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        countries: function () {
            var countries   = wpuf_form_builder.countries,
                visibility  = this.field.country_list.country_list_visibility_opt_name,
                hide_list   = this.field.country_list.country_select_hide_list,
                show_list   = this.field.country_list.country_select_show_list;

            if ('hide' === visibility && hide_list && hide_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(hide_list, country.code) < 0);
                });

            } else if ('show' === visibility && show_list && show_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(show_list, country.code) >= 0);
                });
            }

            return countries;
        },

        default_country: function () {
            return this.field.country_list.name;
        }
    }
});

/**
 * Field template: Date
 */
Vue.component('form-date_field', {
    template: '#tmpl-wpuf-form-date_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Display Name
 */
Vue.component('form-display_name', {
    template: '#tmpl-wpuf-form-display_name',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Embed
 */
Vue.component('form-embed', {
    template: '#tmpl-wpuf-form-embed',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});
/**
 * Field template: File upload
 */
Vue.component('form-file_upload', {
    template: '#tmpl-wpuf-form-file_upload',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: First Name
 */
Vue.component('form-first_name', {
    template: '#tmpl-wpuf-form-first_name',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Date
 */
Vue.component('form-google_map', {
    template: '#tmpl-wpuf-form-google_map',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            map: {},
            geocoder: {},
            markers: []
        };
    },

    /* global google */
    mounted: function () {
        if (typeof window.google === 'undefined') {
            return;
        }

        var self = this,
            default_pos = self.field.default_pos,
            default_zoom = self.field.zoom;

        var default_latLng = default_pos.split(',');

        if (2 === default_latLng.length && isFinite(default_latLng[0]) && isFinite(default_latLng[1])) {
            default_pos = {lat: parseFloat(default_latLng[0]), lng: parseFloat(default_latLng[1])};
        } else {
            default_pos = {lat: 40.7143528, lng: -74.0059731};
        }

        self.map = new google.maps.Map($(this.$el).find('.wpuf-form-google-map').get(0), {
            center: default_pos,
            zoom: parseInt(default_zoom) || 12,
            mapTypeId: 'roadmap',
            streetViewControl: false,
        });

        self.geocoder = new google.maps.Geocoder();

        // Create the search box and link it to the UI element.
        var input = $(this.$el).find('.wpuf-google-map-search').get(0);
        var searchBox = new google.maps.places.SearchBox(input);
        self.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        self.map.addListener('bounds_changed', function() {
            searchBox.setBounds(self.map.getBounds());
        });

        self.markers = [];

        self.set_marker(self.field.default_pos);
    },

    methods: {
        set_marker: function (address) {
            var self = this;

            self.geocoder.geocode({'address': address}, function(results, status) {
                if (status === 'OK') {
                    // Clear out the old markers.
                    _.each(self.markers, function (marker) {
                        marker.setMap(null);
                    });

                    self.markers = [];

                    // Create a marker for each place.
                    self.markers.push(new google.maps.Marker({
                        map: self.map,
                        position: results[0].geometry.location
                    }));

                    self.map.setCenter(results[0].geometry.location);
                }
            });
        }
    },

    watch: {
        field: {
            deep: true,
            handler: function (newVal) {
                this.set_marker(newVal.default_pos);
                this.map.setZoom(parseInt(newVal.zoom));
            }
        }
    }
});

/**
 * Field template: Last Name
 */
Vue.component('form-last_name', {
    template: '#tmpl-wpuf-form-last_name',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Math Captcha
 */
Vue.component('form-math_captcha', {
    template: '#tmpl-wpuf-form-math_captcha',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        captcha: () => {
            let operators = [ '+', '-', 'x' ],
                random = Math.floor( Math.random() * operators.length );

            return {
                operandOne: Math.floor( Math.random() * 200 ) + 1,
                operandTwo: Math.floor( Math.random() * 200 ) + 1,
                operator: operators[random]
            };
        }
    },
});

/**
 * Field template: Nickname
 */
Vue.component('form-nickname', {
    template: '#tmpl-wpuf-form-nickname',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

Vue.component('form-numeric_text_field', {
    template: '#tmpl-wpuf-form-numeric_text_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Password
 */
Vue.component('form-password', {
    template: '#tmpl-wpuf-form-password',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

Vue.component('form-phone_field', {
    template: '#tmpl-wpuf-form-phone_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        countries: function () {
            var countries   = wpuf_form_builder.countries,
                visibility  = this.field.country_list.country_list_visibility_opt_name,
                hide_list   = this.field.country_list.country_select_hide_list,
                show_list   = this.field.country_list.country_select_show_list;

            if ('hide' === visibility && hide_list && hide_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(hide_list, country.code) < 0);
                });

            } else if ('show' === visibility && show_list && show_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(show_list, country.code) >= 0);
                });
            }

            return countries;
        },

        default_country: function () {
            return this.field.country_list.name;
        }
    }
});

/**
 * Field template: Ratings
 */
Vue.component('form-ratings', {
    template: '#tmpl-wpuf-form-ratings',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Really Simple Captcha
 */
Vue.component('form-really_simple_captcha', {
    template: '#tmpl-wpuf-form-really_simple_captcha',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        is_rs_captcha_active: function () {
            return wpuf_form_builder.is_rs_captcha_active;
        },

        no_plugin_msg: function () {
            return wpuf_form_builder.field_settings.really_simple_captcha.validator.msg;
        }
    }
});

/**
 * Field template: Repeat
 */
Vue.component('form-repeat_field', {
    template: '#tmpl-wpuf-form-repeat_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: shortcode
 */
Vue.component('form-shortcode', {
    template: '#tmpl-wpuf-form-shortcode',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            raw_html: 'from data'
        };
    }
});

/**
 * Field template: Step Start
 */
Vue.component('form-step_start', {
    template: '#tmpl-wpuf-form-step_start',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Time
 */
Vue.component('form-time_field', {
    template: '#tmpl-wpuf-form-time_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Terms & Conditions
 */
Vue.component('form-toc', {
    template: '#tmpl-wpuf-form-toc',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        content: function () {
            return this.field.description.replace(/\n/g, '<br>');
        }
    }
});

/**
 * Field template: Biographical Info
 */
Vue.component('form-user_bio', {
    template: '#tmpl-wpuf-form-user_bio',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: User Email
 */
Vue.component('form-user_email', {
    template: '#tmpl-wpuf-form-user_email',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Username
 */
Vue.component('form-user_login', {
    template: '#tmpl-wpuf-form-user_login',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

/**
 * Field template: Website
 */
Vue.component('form-user_url', {
    template: '#tmpl-wpuf-form-user_url',

    mixins: [
        wpuf_mixins.form_field_mixin
    ]
});

Vue.component('submit-button-conditional-logics', {
    template: '#tmpl-wpuf-submit-button-conditional-logic',

    data: function() {
        return {
            settings: this.$store.state.settings
        };
    },

    computed: {

        wpuf_cond_supported_fields: function () {
            return wpuf_form_builder.wpuf_cond_supported_fields;
        },

        dependencies: function () {
            var self = this;

            return this.$store.state.form_fields.filter(function (form_field) {
                return (_.indexOf(self.wpuf_cond_supported_fields, form_field.template) >= 0) && form_field.name && form_field.label;
            });
        }
    },

    methods: {
        get_cond_options: function (field_name) {
            var options = [];

            if (_.indexOf(this.wpuf_cond_supported_fields, field_name) < 0) {
                var dep = this.dependencies.filter(function (field) {
                    return field.name === field_name;
                });

                if (dep.length && dep[0].options) {
                    _.each(dep[0].options, function (option_title, option_name) {
                        options.push({opt_name: option_name, opt_title: option_title});
                    });
                }
            }

            return options;
        },

        on_change_cond_field: function (index) {
            this.$store.state.settings.submit_button_cond.conditions[index].option = '';
        },

        add_condition: function () {
            this.$store.state.settings.submit_button_cond.conditions.push({
                name: '',
                operator: '=',
                option: ''
            });
        },

        delete_condition: function (index) {
            if (this.$store.state.settings.submit_button_cond.conditions.length === 1) {
                this.warn({
                    text: this.i18n.last_choice_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.$store.state.settings.submit_button_cond.conditions.splice(index, 1);
        }
    },
});
})(jQuery);
