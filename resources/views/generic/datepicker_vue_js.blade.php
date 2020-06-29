<script type="text/x-template" id="date-template">
    <input type="text" class="form-control form-control-sm">
</script>

<script>
    Vue.component('datepicker', {

        template: '#date-template',
        mounted: function mounted() {
            var vm = this;

            $(this.$el).daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD-MM-YYYY'
                }

            })
                .on('apply.daterangepicker', function(ev, picker) {

                    vm.$emit('input', this.value);
                });


        }

    });
</script>