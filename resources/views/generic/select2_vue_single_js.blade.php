<script type="text/x-template" id="select2-template">
    <select class="form-control form-control-sm">
        <slot></slot>
    </select>
</script>

<script>
    Vue.component('select2', {
        props: ['options', 'value'],
        template: '#select2-template',
        mounted: function mounted() {
            var vm = this;
            $(this.$el)
            // init select2
                .select2({
                    data: this.options,
                    theme: "bootstrap",

                    minimumResultsForSearch: -1,
                    placeholder: function(){
                        $(this).data('placeholder');
                    },

                    maximumSelectionSize: 6 }).val(this.value).trigger('change')
            // emit event on change.
                .on('change', function () {
                    vm.$emit('input', this.value);
                });
        },
        watch: {
            value: function value(_value) {
                // update value
                $(this.$el).val(_value).trigger('change');
            },
            options: function options(_options) {
                // update options
                $(this.$el).empty().select2({ data: _options });
            }
        },
        destroyed: function destroyed() {
            $(this.$el).off().select2('destroy');
        }
    });
</script>


