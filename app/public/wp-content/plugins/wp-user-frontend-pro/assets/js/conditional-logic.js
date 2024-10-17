(function($) {

    var conditional_logic = {

        field_prefix: 'wpuf_',

        init: function() {
            var self = this;

            self.refresh_conditions();

            $( document ).on( 'change', '.wpuf-fields input, .wpuf-fields textarea, .wpuf-fields select', function(){
                self.refresh_conditions();
            });

            $( document ).on( 'wpuf-ajax-fetched-child-categories', function ( e, container_id ) {
                self.refresh_conditions();

                $( 'select', '#' + container_id ).on( 'change', function () {
                    self.refresh_conditions();
                } );
            } );

            $(".wpuf-fields ul.wpuf-attachment-list").bind('DOMSubtreeModified', function() {
                self.refresh_conditions();
            });
        },

        refresh_conditions: function () {
            //need to check radio for default value although its checked but not working
            if ( $( '[data-type="radio"]' ).closest( 'li' ).css( 'display' ) === 'none' || $( '[data-type="radio"]' ).closest( 'tr' ).css( 'display' ) === 'none' ) {
                $( 'input[type="radio"], input[type="checkbox"]' ).each( function () {
                    if ( $(this).attr('checked') === 'checked' ) {
                        this.checked = true;
                    }
                });
            }
            this.apply_logic();

            // refresh pluploads when uploads show based on conditional logic
            if ( typeof wpuf_plupload_items !== 'undefined' && wpuf_plupload_items.length ) {
                for (var i = wpuf_plupload_items.length - 1; i >= 0; i--) {
                    wpuf_plupload_items[i].refresh();
                }
            }
        },

        apply_logic: function() {
            let all = [];

            if ( typeof wpuf_conditional_items === 'undefined' ) {
                return;
            }

            $.each( wpuf_conditional_items, function( k, item ) {
                $.each(item.cond_field, function (key, item_meta) {

                    let input_type = item.input_type !== undefined ? item.input_type[key] : '';

                    // fields with old conditions
                    if (input_type === '') {
                        all = conditional_logic.check_field_without_input_type(item, key, item_meta);
                    } else {
                        switch (input_type) {
                            case 'select':
                                all[key] = conditional_logic.check_select_field(item, key, item_meta);
                                break;
                            case 'taxonomy':
                                all[key] = conditional_logic.check_taxonomy_field(item, key, item_meta);
                                break;
                            case 'radio':
                            case 'checkbox':
                                all[key] = conditional_logic.check_checked_field(item, key, item_meta, input_type);
                                break;
                            case 'text':
                            case 'textarea':
                            case 'email':
                            case 'url':
                            case 'password':
                                all[key] = conditional_logic.check_text_field(item, key, item_meta, input_type);
                                break;
                            case 'numeric_text':
                                all[key] = conditional_logic.check_numeric_field(item, key, item_meta);
                                break;
                            default:
                                all[key] = conditional_logic.check_default_field(item, key, item_meta, input_type);
                                break;
                        }
                    }

                });

                var field_selector = '.' + conditional_logic.field_prefix + item.name + '_' + item.form_id;

                if (item.cond_logic === 'any') {
                    let check = all.indexOf(true);

                    if (check != '-1') {
                        if (item.type === 'address') {
                            $('li.wpuf-el.' + item.name).show();
                        } else {
                            $(field_selector).closest('li').show();
                        }

                    } else {
                        if (item.type === 'address') {
                            $('li.wpuf-el.' + item.name).hide();
                        } else {
                            $(field_selector).closest('li').hide();

                            if (item.type === 'checkbox' || item.type === 'radio' || item.type === 'taxonomy') {
                                $(field_selector).closest('li').find(':input').each(function () {
                                    this.checked = false;
                                });
                            } else if (item.type === 'select') {
                                $(field_selector).closest('li').hide();
                            } else if (item.type === 'submit') {
                                // do nothing
                            } else {
                                // $( field_selector ).closest('li').find(':input').val('');
                                $(field_selector).closest('li').find(':input').show();
                            }
                        }

                    }

                } else {

                    let check = all.indexOf(false);

                    if (check == '-1') {
                        if (item.type === 'address') {
                            $('li.wpuf-el.' + item.name).show();
                        } else {
                            $(field_selector).closest('li').show();
                        }

                    } else {

                        if (item.type === 'address') {
                            $('li.wpuf-el.' + item.name).hide();
                        } else {
                            $(field_selector).closest('li').hide();

                            if (item.type === 'checkbox' || item.type === 'radio' || item.type === 'taxonomy') {
                                $(field_selector).closest('li').find(':input').each(function () {
                                    this.checked = false;
                                });
                            } else if (item.type === 'select') {
                                $(field_selector).closest('li').hide();
                            } else if (item.type === 'submit') {
                                // do nothing
                            } else {
                                // $( field_selector ).closest('li').find(':input').val('');
                                $(field_selector).closest('li').find(':input').show();
                            }
                        }
                    }

                }

                all.length = 0;
            });
        },

        check_taxonomy_field: (item, key, item_meta) => {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;

            let type = $(selector).attr('type');
            if (type === 'checkbox') {
                return conditional_logic.check_checked_field(item, key, item_meta, type);
            } else if (type === 'text') {
                return conditional_logic.check_taxonomy_text_field(item, key, item_meta, type);
            } else {
                return conditional_logic.check_select_field(item, key, item_meta, type);
            }
        },

        check_taxonomy_text_field: (item, key, item_meta, input_type) => {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let cond_value = item.option_title[key];
            let field = $('input[type=text].textfield' + selector);
            let field_value = field.val();
            field_value = field_value.replace(/,\s*$/, "");

            // condition set to 'has no value'
            if (operator === '==empty' && field_value === '') {
                return true;
            }

            // condition set to 'has any value'
            if (operator === '!=empty' && field_value !== '') {
                return true;
            }

            // condition set to check equals
            if (operator === '=' && field_value === cond_value) {
                return true;
            }

            // condition set to check if contains
            if (operator === '==contains' && (field_value).indexOf(cond_value) > -1) {
                return true;
            }

            // condition set to check if is not the same
            return operator === '!=' && field_value !== cond_value;
        },

        check_select_field: function(item, key, item_meta) {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;
            let value = item.cond_option[key];
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let select = $('select' + selector + '>option[value="' + value + '"]');
            let select_fields = $('select' + selector + ' option');

            // condition set to 'has any value'
            if (operator === '!=empty') {
                let has_any_value = false;
                if (select_fields) {
                    $.each(select_fields, (index, select) => {
                        if ($(select).is(':selected') && $(select).val() !== '-1') {
                            has_any_value = true;
                            return false;
                        }
                    });
                }

                return has_any_value;
            }

            // condition set to 'has no value'
            if (operator === '==empty') {
                let has_no_value = true;
                if (select_fields) {
                    $.each(select_fields, (index, select) => {
                        if ($(select).is(':selected') && $(select).val() !== '-1') {
                            has_no_value = false;
                            return false;
                        }
                    });
                }

                return has_no_value;
            }

            if (select.length) {
                let selected_status = select.is(':selected') ? true : false;

                if ( operator === '=' && selected_status ) {
                    return true;
                }

                if ( operator === '!=' && selected_status === false ) {
                    return true;
                }

                return false;
            }
        },

        // checkbox and radio fields
        check_checked_field: function (item, key, item_meta, input_type) {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;
            let value = item.cond_option[key];
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let field;
            let all_fields;

            if (input_type === 'radio') {
                field = $('input[type=radio][value="' + value + '"]' + selector);
                all_fields = $('input[type=radio]' + selector);
            } else {
                field = $('input[type=checkbox][value="' + value + '"]' + selector);
                all_fields = $('input[type=checkbox]' + selector);
            }

            // condition set to 'has any value'
            if (operator === '!=empty') {
                let has_any_value = false;

                if (all_fields) {
                    $.each(all_fields, (index, single_field) => {
                        if ( $(single_field).is(':checked') ) {
                            has_any_value = true;
                            return false;
                        }
                    });
                }

                return has_any_value;
            }

            // condition set to 'has no value'
            if (operator === '==empty') {
                let has_no_value = true;
                if (all_fields) {
                    $.each(all_fields, (index, single_field) => {
                        if ($(single_field).is(':checked') && $(single_field).val() !== '-1') {
                            has_no_value = false;
                            return false;
                        }
                    });
                }

                return has_no_value;
            }

            if (field.length) {
                let field_checked_status = field.is(':checked');

                if (operator === '=' && field_checked_status) {
                    return true;
                }

                if (operator === '!=' && field_checked_status === false) {
                    return true;
                }

                return false;
            }
        },

        check_text_field: function (item, key, item_meta, input_type) {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let cond_value = item.cond_option[key];
            let field;
            let field_value;

            if (input_type === 'text') {
                field = $('input[type=text].textfield' + selector);
                field_value = field.val();
            } else if (input_type === 'textarea') {
                field = $('textarea.textareafield' + selector);
                field_value = field.val();
            } else if (input_type === 'email') {
                field = $('input[type=email]' + selector);
                field_value = field.val();
            } else if (input_type === 'url') {
                field = $('input[type=url].url' + selector);
                field_value = field.val();
            } else if (input_type === 'password') {
                field = $('input[type=password].password' + selector);
                field_value = field.val();
            }

            // condition set to 'has no value'
            if (operator === '==empty' && field_value === '') {
                return true;
            }

            // condition set to 'has any value'
            if (operator === '!=empty' && field_value !== '') {
                return true;
            }

            if (operator === '=' && field_value === cond_value) {
                return true;
            }

            if (operator === '==contains' && (field_value).indexOf(cond_value) > -1) {
                return true;
            }

            return operator === '!=' && field_value !== cond_value;
        },

        check_numeric_field: function (item, key, item_meta) {
            let form_id = '_' + item.form_id;
            let selector = '.' + conditional_logic.field_prefix + item_meta + form_id;
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let cond_value = item.cond_option[key];
            let number = $('input[type=number].textfield' + selector);
            let field_value = number.val();

            // condition set to 'has no value'
            if (operator === '==empty' && field_value === '') {
                return true;
            }

            // condition set to 'has any value'
            if (operator === '!=empty' && field_value !== '') {
                return true;
            }

            if (operator === '==contains' && (field_value).indexOf(cond_value) > -1) {
                return true;
            }

            field_value = parseInt(field_value);
            cond_value = parseInt(cond_value);

            if ( operator === '=' && field_value === cond_value ) {
                return true;
            }

            if ( operator === '!=' && field_value !== cond_value ) {
                return true;
            }

            if ( operator === 'greater' && field_value > cond_value ) {
                return true;
            }

            if ( operator === 'less' && field_value < cond_value ) {
                return true;
            }

            return false;
        },

        check_default_field: function (item, key, item_meta, input_type) {
            let operator = item.cond_operator[key] !== undefined ? item.cond_operator[key] : '';
            let form_id = '_' + item.form_id;
            let field;
            let field_value = null;

            if (input_type === 'map') {
                field = $('input[type=text]#wpuf-map-add-' + item_meta);
                field_value = field.val();
            } else if (input_type === 'file_upload') {
                field = $('a.button.file-selector.' + conditional_logic.field_prefix + item_meta + form_id);
                field_value = field.siblings('ul.wpuf-attachment-list').children().length;
            }

            // condition set to 'has no value'
            if (operator === '==empty' && !field_value) {
                return true;
            }

            // condition set to 'has any value'
            if (operator === '!=empty' && field_value) {
                return true;
            }

            return false;
        },

        // fields with old conditions
        check_field_without_input_type: function(item, key, item_meta) {
            let all = [];
            let form_id     = '_' + item.form_id,
                value       = item.cond_option[key],
                selector    = '.' + conditional_logic.field_prefix + item_meta + form_id,
                operator    = ( item.cond_operator[key] === '=' ) ? true : false,
                checkbox    = $('input[type=checkbox][value="' + value + '"]' + selector),
                radio       = $('input[type=radio][value="' + value+'"]'+ selector),
                select      = $('select' + selector + '>option[value="' + value + '"]');

            if ( select.length ) {
                let select_selected_status = select.is(':selected') ? true : false;

                if ( operator && select_selected_status  ) {
                    all[key] = true;
                } else if ( operator === false && select_selected_status === false ) {
                    all[key] = true;
                } else {
                    all[key] = false;
                }
            } else if ( radio.length ) {
                let radio_checked_status = radio.is(':checked') ? true : false;

                if ( operator && radio_checked_status  ) {
                    all[key] = true;
                } else if ( operator === false && radio_checked_status === false ) {
                    all[key] = true;
                } else {
                    all[key] = false;
                }
            } else if ( checkbox.length ) {
                let checkbox_checked_status = checkbox.is(':checked') ? true : false;

                if( operator && checkbox_checked_status  ) {
                    all[key] = true;
                } else if ( operator === false && checkbox_checked_status === false ) {
                    all[key] = true;
                } else {
                    all[key] = false;
                }
            } else {
                all[key] = false;
            }

            return all;
        }
    };

    conditional_logic.init();
})(jQuery);
