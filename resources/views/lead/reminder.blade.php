<?php reminder_modal(); ?>
<?php echo reminder_table_html(); ?>

@section('innerPageJS')
 <?php reminder_table_js(Lead::class, $rec->id); ?>
@endsection