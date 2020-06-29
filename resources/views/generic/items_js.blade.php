<script type="text/x-template" id="select2-template">
    <select multiple>
        <slot v-if="slotPassed"></slot>
    </select>
</script>

<script>
    function _toConsumableArray(arr) {
        if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

    Vue.component('select2Multiple', {
        props: ['options', 'value'],
        template: '#select2-template',

        mounted: function mounted() {
            var vm = this;
            $(this.$el)
            // init select2
                .select2({ data: this.options, theme: "bootstrap" }).val(this.value).trigger('change')
            // emit event on change.
                .on('change', function () {

                    vm.$emit('input', $(this).val());

                });
        },
        watch: {
            value: function value(_value) {
                if ([].concat(_toConsumableArray(_value)).sort().join(",") !== [].concat(_toConsumableArray($(this.$el).val())).sort().join(",")) $(this.$el).val(_value).trigger('change');
            },
            options: function options(_options) {
                // update options
                $(this.$el).select2({ data: _options });
            }
        },
        computed: {
            slotPassed : function slotPassed() {


            }
        },
        destroyed: function destroyed() {
            $(this.$el).off().select2('destroy');
        }
    });






    vm = new Vue({

        el: '#sales',
        components: {

        },
        data: {
            rows: <?php echo json_encode(old_set('items', [],$rec)) ?>,
            discount_total : <?php echo old_set('discount_total', 0 ,$rec) ?>,
            discount_method_id : <?php echo (old_set('discount_method_id', NULL ,$rec)) ? old_set('discount_method_id', NULL ,$rec) : '""' ?>,
            discount_rate : <?php echo old_set('discount_rate', 0 ,$rec) ?>,
            sub_total: <?php echo old_set('sub_total', 0 ,$rec) ?>,
            adjustment : <?php echo old_set('adjustment', 0 ,$rec) ?>,
            total: <?php echo old_set('total', 0 ,$rec) ?>,
            discount_method_percentage : {{ DISCOUNT_METHOD_PERCENTAGE }},
            discount_method_fixed : {{ DISCOUNT_METHOD_FIXED }},
            discount_type_before_tax : {{ DISCOUNT_TYPE_BEFORE_TAX }},
            discount_type_after_tax : {{ DISCOUNT_TYPE_AFTER_TAX }},
            unbilledTasks : [], // For Invoice
            taxRows : [],
            
            options: <?php echo json_encode($data['tax_id_list']); ?>
        },
        filters: {
            formatNumber: function(value) {
                return accounting.formatNumber(value);
            }
        },
        // Fetches data when the component is created.
        created: function created() {
            @if(!isset($rec->id))
            //this.addRow();
            @endif

        },
        mounted: function mounted() {

        },
        computed: {
            runOperation : function runOperation() {
                this.calculateTotal();
            }

        },
        methods: {
            customerSelected : function customerSelected($customerId){
                $scope = this;
                if($customerId)
                {
                    $.post( "{{ route('get_unbilled_tasks_by_customer_id') }}", {  "_token": "{{ csrf_token() }}", customer_id : $customerId })
                      .done(function( response ) {
                        $scope.unbilledTasks = response.data;
                        
                    });
                }
                else
                {
                    $scope.unbilledTasks = [];
                }       

            },
            itemHasValidationError: function hasValidationError($inputName, $rowIndex) {

                $scope = this;

                if($scope.rows.length > 0)
                {
                    if($scope.rows[$rowIndex].hasOwnProperty('validation_error'))
                    {
                        return ($scope.rows[$rowIndex]['validation_error'].hasOwnProperty($inputName))? true : false;
                    }

                }
                return false;
            },
            removeItem: function removeItem($index) {

                this.rows.splice($index, 1);

            },
            addRow: function addRow($event) {
                if($event)
                {
                    $event.preventDefault();
                }

                this.rows.push({
                    "id":"",
                    "description":"",
                    "long_description":"",
                    "quantity":1,
                    "unit": "",
                    "rate": "",
                    "tax_id": "",
                    "sub_total" :""
                });
            },
            discountMethodChanged: function discountMethodChanged($title, $value, $event) {
                $event.preventDefault();
                this.discount_method_id     = $value;
                $('.discount_method_btn').text($title);

            },
            addProductUsingJquery: function addProductUsingJquery(productObject) {

                var $tax = [];
                if(productObject.tax_id_1)
                {
                    $tax.push(productObject.tax_id_1);
                }
                if(productObject.tax_id_2)
                {
                    $tax.push(productObject.tax_id_2);
                }


                this.rows.push({
                    "id"                : "",
                    "description"       : productObject.name,
                    "long_description"  : productObject.description,
                    "quantity"          :1,
                    "unit"              : productObject.unit,
                    "rate"              : productObject.rate,
                    "tax_id"            : $tax,
                    "sub_total"         : (productObject.rate * 1)
                });

            },
            add_unbilled_task_to_invoice : function add_unbilled_task_to_invoice(item, index){

                if(item)
                {
                    this.rows.push({
                        "id"                : "",
                        "description"       : item.description,
                        "long_description"  : item.long_description,
                        "quantity"          : item.quantity,
                        "unit"              : item.unit,
                        "rate"              : item.rate,
                        "tax_id"            : [],
                        "sub_total"         : item.sub_total,
                        'component_number'  : item.component_number,
                        'component_id'      : item.component_id,
                    });
                    $("input[name=show_quantity_as][value=Hours]").prop('checked', true);
                    //$("input[name=show_quantity_as]").val("Hours");
                    this.unbilledTasks.splice(index, 1);
                }
            },
            isNumber: function(evt) {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                
                var previous_input_value = evt.target.value;
                if ((charCode == 46) && previous_input_value && previous_input_value.indexOf('.') > -1)                  
                {
                    evt.preventDefault();
                }

                if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                    evt.preventDefault();
                } else {
                    return true;
                }
            },
            getTaxRowKey: function ($array, $key, $value) {

                var valueToReturn = false;

                if($array.length > 0)
                {
                    $.each($array , function( i, r ) {

                        if(r[$key] == $value)
                        {
                            valueToReturn = i;
                            return valueToReturn;
                        }

                    });
                    return valueToReturn;
                }
                return valueToReturn;

            },
            getTaxObject: function ($array, $key, $value) {

                var valueToReturn = false;

                if($array.length > 0)
                {
                    $.each($array , function( i, r ) {

                        if(r[$key] == $value)
                        {
                            valueToReturn = r;
                        }

                    });
                    return valueToReturn;
                }

                return valueToReturn;
            },
            calculateTotal: function calculateTotal() {

                $scope = this;
                var total = 0;
                var total_tax = 0;
                var sub_total = 0;
                var discount_type_id = $('select[name=discount_type_id]').val();
                $scope.taxRows = [];
                tax_rows = [];


                $.each($scope.rows , function( index, value ) {
                    value.sub_total = value.quantity * value.rate ;

                    sub_total = value.sub_total;
                    total += sub_total;

                    // Calculate Tax
                    if(value.tax_id && (value.tax_id.length > 0) )
                    {
                        if(typeof value.tax_id =='string')
                        {
                            value.tax_id = JSON.parse(value.tax_id);
                        }
                        
                        $.each(value.tax_id , function( i, taxId ) {

                            var taxObject = $scope.getTaxObject($scope.options, 'id', taxId);

                            if(taxObject)
                            {
                                var taxAmount = parseFloat(((sub_total * taxObject.rate) / 100 ).toFixed(2)) ;

                                $taxRowKey = $scope.getTaxRowKey(tax_rows, 'id', taxId);
                  
                                if($taxRowKey || $taxRowKey === 0)
                                {
                                    tax_rows[$taxRowKey].amount = taxObject.amount + taxAmount;
                                    total_tax += taxAmount;
                                   
                                }
                                else
                                {
                                    taxObject.amount = taxAmount;
                                    total_tax += taxAmount;
                                    tax_rows.push(taxObject);
                                   
                                }

                            }
                            else
                            {
                                

                            }


                        });
                    }


                });

                $scope.taxRows = tax_rows;



                $scope.sub_total = total.toFixed(2);

                // Calculate Discount
                if(discount_type_id)
                {
                    // Flat Fee
                    if($scope.discount_method_id == $scope.discount_method_fixed)
                    {
                        // Set Discount Total
                        $scope.discount_total = $scope.discount_rate;
                        // Before Tax
                        if($scope.discount_type_before_tax == discount_type_id)
                        {
                            total = (total - $scope.discount_total) + total_tax
                        }
                        else if($scope.discount_type_after_tax == discount_type_id)
                        {
                            total = (total_tax + total) - $scope.discount_rate
                        }
                    }
                    else
                    {
                        // Discount Method Percentage
                        if($scope.discount_type_before_tax == discount_type_id)
                        {
                            // Calculate Discount Total
                            $scope.discount_total = (($scope.discount_rate * total) / 100 ).toFixed(2) ;

                            total = ( total - $scope.discount_total) + total_tax
                        }
                        else if($scope.discount_type_after_tax == discount_type_id)
                        {
                            total = (total_tax + total);

                            // Calculate Discount Total
                            $scope.discount_total = (($scope.discount_rate * total) / 100 ).toFixed(2) ;

                            total = total - $scope.discount_total ;

                        }

                    }
                }
                else
                {
                    // No Discount
                    $scope.discount_rate = 0;
                    $scope.discount_total = 0;
                    total =  total + total_tax;
                }


                // Adjustment
                if($scope.adjustment && (!isNaN($scope.adjustment)))
                {
                    total = total + parseFloat($scope.adjustment);
                }

                if($scope.adjustment && isNaN($scope.adjustment) && $scope.adjustment != '.')
                {
                   $scope.adjustment = 0; 
                }


                $scope.total = total.toFixed(2);

            }




        }

    });





    // Jquery Stuffs

    $(function () {


        $('input[type=radio][name=show_quantity_as]').change(function () {
            $('.quantity').html( $(this).val() );
        });


        $('select[name=discount_type_id]').change(function () {

            vm.calculateTotal();
        });


        var item_id = $( ".item_id" );

        item_id.select2( {
            theme: "bootstrap",
            minimumInputLength: 2,
            maximumSelectionSize: 6,
            placeholder: "{{ __('form.search_item') }}",
            allowClear: true,

            ajax: {
                url: '{{ route("proposal_search_product") }}',
                data: function (params) {
                    return {
                        search: params.term

                    }


                },
                dataType: 'json',
                processResults: function (data) {
                    //params.page = params.page || 1;
                    // Tranforms the top-level key of the response object from 'items' to 'results'
                    return {
                        results: data.results
                        // pagination: {
                        //     more: (params.page * 10) < data.count_filtered
                        // }
                    };
                }




            },

            templateResult: function (obj) {

                return obj.name;
            },
            templateSelection: function (obj) {

                return obj.name ||  obj.text
            }

        } ).on('select2:select', function (e) {
            var data = e.params.data;

            $(item_id).val(null).trigger("change");

            if(data && data.name)
            {
                vm.addProductUsingJquery(data);
            }
        });



    });

</script>