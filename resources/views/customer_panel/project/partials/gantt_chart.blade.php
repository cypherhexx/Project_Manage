@if(check_customer_project_permission($rec->settings->permissions, 'view_gantt')) 
<style type="text/css">
    .gantt{
        border: 2px solid #eee !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
       /* margin-right: -20px !important;*/
        width: calc(100vh * 1.57);
    }
   .fn-gantt .leftPanel .name {

   font-weight: bold;
    background-color: #717a86 !important;
    border-bottom: 1px solid #f5f5f5;
    height: 23px;

}
.fn-gantt .leftPanel .name .fn-label{
    color: #fff;
    font-size: 13px;
}

.fn-gantt .leftPanel .desc {

   
    background-color: #fff !important;

}
.fn-gantt .leftPanel .desc .fn-label{
   
    font-size: 13px;
}


.fn-gantt .leftPanel {

    width: 35% !important;
    
 

}
.fn-gantt .leftPanel .spacer {
    background-color: #fff !important;
    
}
.gantt_project_name {
    font-weight: 500;
    text-align: center;
    font-size: 16px;
    margin: 0 auto;
    display: block;
    margin-top: 32px;
}
    /*#778461
#D0E4FD;
#78436D*/

</style>


<form class="form-inline">

  <?php 
    $gantt_type_list = [
    'milestone' => __('form.milestone'),
    'status' => __('form.status'),
    'members' => __('form.members'),    
  ];

  ?>
  <?php echo form_dropdown('gantt_type', $gantt_type_list , Request::query('gantt_type') , "class='form-control selectPickerWithoutSearch gantt_type'"); ?> 
                             


</form>

<hr>

<div class="gantt"></div>
</div>

@section('innerPageJS')
    <script>
        $(function() {

            $('.gantt_type').change(function(){

                var url = "{{route('cp_show_project_page', $rec->id) }}?group=gantt";
                var gantt_type = $(this).val();

                if(gantt_type != 'milestone')
                {
                    url =  url + '&gantt_type=' + gantt_type ;
                }
                window.location.href = url;
                
            });

            <?php

                if(Request::query('gantt_type') == 'members')
                {                    
                    $ganttSource = $rec->gantt_chart_data_project_members();
                }
                else if(Request::query('gantt_type') == 'status')
                {
                    $ganttSource = $rec->gantt_chart_data_project_status();
                }
                else
                {
                    $ganttSource = $rec->gantt_chart_data_milestone();
                }
            ?>
            
            $(".gantt").gantt({
                source: <?php echo  $ganttSource ; ?>,
                navigate: "scroll",
               
                itemsPerPage: 25,
                onItemClick: function(data) {
                    if(data)
                    {
                       var url = '{{ route("show_task_page", ":id") }}';
                       url = url.replace(':id', data.task_id );
                        window.open(url, '_blank');
                    }
                },
                onAddClick: function(dt, rowId) {
                    alert("Empty space clicked - add an item!");
                },
                onRender: function() {
                     $(".gantt .leftPanel .spacer").html("<div class='gantt_project_name'><i class='fas fa-cubes'></i>  {{ $rec->name }}</div>");
               
                }
            });

            

        });
    </script>
@endsection

@endif