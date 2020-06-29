 @if(count($task_list) > 0)
   <div class="kanban">
      <div class="board">
         @foreach($task_list as $key=>$list)
         <div class="board-column">
            <div class="board-column-header" style="background-color: {{ $list['bg_color'] }}">
               <div style="color: #fff">{{ $list['name'] }} 
                  <span class="badge badge-light">{{ count($list['tasks']) }}</span></div>        
            </div>
            <div class="board-column-content-wrapper" data-status="{{ $key }}">
               <div class="board-column-content">
                  @if(count($list['tasks']) > 0)
                  @foreach($list['tasks'] as $task)
                  <div class="board-item" data-task="{{ $task->id }}" >
                     <div class="board-item-content">
                        <a style="font-size: 13px;" href="{{ route('show_task_page', $task->id ) }}">{{ $task->title }}</a>
                       <small style="font-size: 12px;" class="form-text text-muted">{{ $task->status->name }}</small>
                     </div>
                  </div>
                  @endforeach
                  @endif
               </div>
            </div>
         </div>
         @endforeach
      </div>
   </div>
   @endif



@section('innerChildPageJs')
    <script>

      
        $(function() {

            
          // Kanban
            
            var itemContainers = [].slice.call(document.querySelectorAll('.board-column-content'));
            var columnGrids = [];
            var boardGrid;

            // Define the column grids so we can drag those
            // items around.
            itemContainers.forEach(function (container) {

              // Instantiate column grid.
              var grid = new Muuri(container, {
                items: '.board-item',
                layoutDuration: 400,
                layoutEasing: 'ease',
                dragEnabled: true,
                dragSort: function () {
                  return columnGrids;
                },
                dragSortInterval: 0,
                dragContainer: document.body,
                dragReleaseDuration: 400,
                dragReleaseEasing: 'ease'
              })
              .on('dragStart', function (item) {
                // Let's set fixed widht/height to the dragged item
                // so that it does not stretch unwillingly when
                // it's appended to the document body for the
                // duration of the drag.
                item.getElement().style.width = item.getWidth() + 'px';
                item.getElement().style.height = item.getHeight() + 'px';
              })
              .on('dragReleaseEnd', function (item) {
                // Let's remove the fixed width/height from the
                // dragged item now that it is back in a grid
                // column and can freely adjust to it's
                // surroundings.

               
                // Just in case, let's refresh the dimensions of all items
                // in case dragging the item caused some other items to
                // be different size.
                columnGrids.forEach(function (grid) {
                  grid.refreshItems();
                });

                // Update Information
                var element = $(item.getElement());
                var task_id = element.data('task');
                var status_id = element.parent('div').parent('div').data('status');

                $.post( "{{ route("task_change_status_ajax") }}", { 
                    "_token": "{{ csrf_token() }}", 
                    status_id : status_id,
                    task_id : task_id,
                });


                
              })
              .on('layoutStart', function () {
                // Let's keep the board grid up to date with the
                // dimensions changes of column grids.
                boardGrid.refreshItems().layout();
              });

              // Add the column grid reference to the column grids
              // array, so we can access it later on.
              columnGrids.push(grid);

            });

            // Instantiate the board grid so we can drag those
            // columns around.
            boardGrid = new Muuri('.board', {
              layout: {
                horizontal: true,
              },
              layoutDuration: 400,
              layoutEasing: 'ease',
              dragEnabled: true,
              dragSortInterval: 0,
              dragStartPredicate: {
                handle: '.board-column-header'
              },
              dragReleaseDuration: 400,
              dragReleaseEasing: 'ease'
            });
            
            // End of Kanban

            


        });
    </script>
@endsection