<?php task_modal(COMPONENT_TYPE_LEAD, $rec, TRUE); ?>
<?php echo task_table_html(); ?>

@section('innerPageJS')
 <?php task_table_js(COMPONENT_TYPE_LEAD, $rec->id); ?>
@endsection