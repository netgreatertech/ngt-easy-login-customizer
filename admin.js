/**
 * NGT Easy Login Customizer - Main Administration Component[cite: 2]
 * Engineered by Net Greater Technologies, LLP
 * Website: https://netgreater.com/
 */
(function($) {
    'use strict';

    const NgtAdminControls = {
        
        /**
         * Core initialization loop
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.initLazyColorPickers();
            this.setupInteractivePreviews();
        },

        /**
         * Map DOM caching boundaries safely using distinct NGT namespaces
         */
        cacheElements: function() {
            this.$body             = $('body');
            this.$redirectRows     = $('#ngt-redirect-rows');
            this.$bgRadioGroup     = $('.ngt-bg-type-selector');
            this.$logoInput        = $('#ngt_portal_logo');
            this.$bgInput          = $('#ngt_portal_bg_image');
            this.$logoPreviewZone  = $('#ngt-logo-preview');
            this.$bgPreviewZone    = $('#ngt-bg-preview');
            this.$removeLogoBtn    = $('#ngt-remove-logo-action');
            this.$removeBgBtn      = $('#ngt-remove-bg-action');
        },

        /**
         * Register event listeners across elements
         */
        bindEvents: function() {
            const self = this;

            // 1. Dynamic Repeater Configuration Engine
            if (this.$redirectRows.length) {
                let redirectIndex = $('.ngt-redirect-row').length;
                
                $('#ngt-add-redirect-trigger').on('click', function(e) {
                    e.preventDefault();
                    self.addNewRedirectRow(redirectIndex);
                    redirectIndex++;
                });

                this.$redirectRows.on('click', '.ngt-remove-redirect-trigger', function(e) {
                    e.preventDefault();
                    self.removeRedirectRow($(this));
                });
            }

            // 2. Media Gallery Handlers
            $('#ngt-upload-logo-trigger').on('click', function(e) { e.preventDefault(); self.openMediaUploader('logo'); });
            $('#ngt-upload-bg-trigger').on('click', function(e) { e.preventDefault(); self.openMediaUploader('bg'); });
            this.$removeLogoBtn.on('click', function(e) { e.preventDefault(); self.clearMediaSelection('logo'); });
            this.$removeBgBtn.on('click', function(e) { e.preventDefault(); self.clearMediaSelection('bg'); });

            // 3. Conditional Visibility Engine
            this.$bgRadioGroup.on('change', function() {
                self.toggleBackgroundContextFields($(this).val());
            });

            // 4. Security Trigger Callback
            $(document).on('click', '.ngt-generate-token-action', function() {
                self.triggerSecurityAlert();
            });
        },

        /**
         * Lazy-loaded Color Pickers with Visibility Interception
         */
        initLazyColorPickers: function() {
            $('.ngt-color-picker:visible').each(function() {
                const $picker = $(this);
                if (!$picker.data('ngtPickerActive')) {
                    $picker.data('ngtPickerActive', true).wpColorPicker({
                        change: function(event, ui) {
                            if (wp && wp.customize) {
                                wp.customize($picker.data('customize-setting-link')).set(ui.color.toString());
                            }
                        }
                    });
                }
            });
        },

        /**
         * Native Password Visibility Toggle Eye Integration
         */
        setupInteractivePreviews: function() {
            if ($('.login-password').length || $('#wp-submit').length) {
                $('.login-password input[type="password"]').after('<span class="ngt-toggle-pass-eye" style="cursor:pointer; position:absolute; right:12px; top:38px; color:#64748b;">👁️</span>');
                
                this.$body.on('click', '.ngt-toggle-pass-eye', function() {
                    const $input = $(this).siblings('input');
                    const isPass = $input.attr('type') === 'password';
                    $input.attr('type', isPass ? 'text' : 'password');
                    $(this).text(isPass ? '🔒' : '👁️');
                });
            }
        },

        /**
         * Array Matrix Logic for Clean Rule Repeater Copies
         */
        addNewRedirectRow: function(index) {
            const $firstRow = this.$redirectRows.find('.ngt-redirect-row').first();
            if (!$firstRow.length) return;

            const $clonedRow = $firstRow.clone();
            $clonedRow.find('input').val('');
            $clonedRow.find('select').prop('selectedIndex', 0);
            
            $clonedRow.find('select, input').each(function() {
                const currentName = $(this).attr('name');
                if (currentName) {
                    $(this).attr('name', currentName.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });

            this.$redirectRows.append($clonedRow);
        },

        removeRedirectRow: function($triggerElement) {
            const $rows = this.$redirectRows.find('.ngt-redirect-row');
            if ($rows.length > 1) {
                $triggerElement.closest('.ngt-redirect-row').remove();
            } else {
                $rows.find('input').val('');
            }
        },

        /**
         * WP Core Media Modal Interceptor Pipeline
         */
        openMediaUploader: function(targetContext) {
            const isLogo = targetContext === 'logo';
            
            let ngtMediaFrame = wp.media({
                title: isLogo ? 'Select Corporate Logo Identity' : 'Select Portal Canvas Backdrop',
                button: { text: 'Apply Asset' },
                multiple: false,
                library: { type: 'image' }
            });

            ngtMediaFrame.on('select', function() {
                const attachment = ngtMediaFrame.state().get('selection').first().toJSON();
                if (isLogo) {
                    NgtAdminControls.$logoInput.val(attachment.url);
                    NgtAdminControls.$logoPreviewZone.html(`<img src="${attachment.url}" style="max-width:280px; max-height:80px; margin-bottom:12px; border-radius:6px; display:block;">`);
                    NgtAdminControls.$removeLogoBtn.show();
                } else {
                    NgtAdminControls.$bgInput.val(attachment.url);
                    NgtAdminControls.$bgPreviewZone.html(`<img src="${attachment.url}" style="max-width:360px; max-height:160px; margin-bottom:12px; border-radius:8px; display:block;">`);
                    NgtAdminControls.$removeBgBtn.show();
                }
            });

            ngtMediaFrame.open();
        },

        clearMediaSelection: function(targetContext) {
            if (targetContext === 'logo') {
                this.$logoInput.val('');
                this.$logoPreviewZone.html('');
                this.$removeLogoBtn.hide();
            } else {
                this.$bgInput.val('');
                this.$bgPreviewZone.html('');
                this.$removeBgBtn.hide();
            }
        },

        toggleBackgroundContextFields: function(activeType) {
            $('.ngt-bg-context-row').hide();
            $(`.ngt-bg-context-row[data-bg-type="${activeType}"]`).show();
            this.initLazyColorPickers();
        },

        triggerSecurityAlert: function() {
            console.log('NGT Security System: New encryption sequence instantiated.');
        }
    };

    $(document).ready(function() {
        NgtAdminControls.init();
    });

})(jQuery);