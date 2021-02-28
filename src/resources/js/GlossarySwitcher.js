(function($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.GlossarySwitcher = Garnish.Base.extend({
        $glossarySelect: null,
        $spinner: null,

        init: function() {
            console.log('test');
            this.$glossarySelect = $('#glossary');
            this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$glossarySelect.parent());

            this.addListener(this.$glossarySelect, 'change', 'onGlossaryChange');
        },

        onGlossaryChange: function(ev) {
            this.$spinner.removeClass('hidden');

            Craft.postActionRequest('glossary/term/switch-glossary', Craft.cp.$primaryForm.serialize(), $.proxy(function(response, textStatus) {
                this.$spinner.addClass('hidden');

                if (textStatus === 'success') {
                    var $tabs = $('#tabs');
                    if (response.tabsHtml) {
                        if ($tabs.length) {
                            $tabs.replaceWith(response.tabsHtml);
                        } else {
                            $(response.tabsHtml).insertBefore($('#content'))
                        }
                        Craft.cp.$mainContent.addClass('has-tabs');
                    } else {
                        $tabs.remove();
                        Craft.cp.$mainContent.removeClass('has-tabs');
                    }

                    $('#fields').html(response.fieldsHtml);
                    Craft.initUiElements($('#fields'));
                    Craft.appendHeadHtml(response.headHtml);
                    Craft.appendFootHtml(response.bodyHtml);

                    Craft.cp.initTabs();
                }
            }, this));
        }
    });
})(jQuery);
