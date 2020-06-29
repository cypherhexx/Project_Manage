<style type="text/css">
  .todos__title {
  margin-bottom: 1rem;
}
.todos__input {
  padding: 0.5rem 0.75rem;
  border: solid 2px #d9d9d9;
  width: 15rem;
  margin: 0.5rem 0 1rem;
}
.todos__input:focus {
  outline: none;
}
.todos__list {
  margin: 1rem 0;
  padding: 0;
}
.todos__item {
  position: relative;
  list-style: none;
  padding: 0.5rem;
  margin: 0 0.3rem 0.3rem 0;
  background: #fff;
 
  color: #999;
}
.todos__item:hover {
  cursor: pointer;
  color: #666;
}
.todos__delete {
  margin-right: 0.5rem;
  background: #ff6347;
  border: none;
  color: #fff;
  border-radius: 50%;
  height: 1rem;
  width: 1rem;
  display: inline-block;
  line-height: 1;
  font-size: 0.8rem;
  text-align: center;
  padding: 0;
}
.todos__delete:hover {
  background: #f92600;
}
.todos__completed {
  background: #f6feff;
  text-decoration: line-through;
  color: #09a;
}
.todos__completed:hover {
  color: #008290;
}
.todos__clear {
  font-size: 0.8rem;
  color: #09a;
}
.todos__empty {
  font-size: 0.9rem;
  color: #666;
}
/* ##### transitions ##### */
.fade-transition {
  transition: all 0.2s;
}
.fade-enter {
  opacity: 0;
}
.fade-leave {
  display: none;
}
</style>

<div class="todos" id="toDoApp" v-cloak>
   <h5 class="todos__title">@lang('form.to_do_list') <span v-show="itemsTodo.length"> - (@{{ itemsTodo.length }} @lang('form.pending')) </span></h5>
   <input class="form-control form-control-sm" type="text" v-on:keyup.enter="addTodo" v-model="newTodo" placeholder="@lang('form.type_and_press_enter')"/>
   <ul class="todos__list">
      <li class="todos__item" v-for="(todo,index) in todos" :class="{ 'todos__completed': todo.completed }" v-on:click="markTask(todo, index)" transition="fade"> 
         <button class="todos__delete" @click.stop="removeTodo(todo.id, index)">&times; </button>@{{ todo.text }}        
      </li>
   </ul>
   <p class="todos__empty" v-show="!todos.length">
      @lang('form.nothing_to_do_yet')
   </p>
   <a class="todos__clear" href="#" v-on:click.prevent="clearCompleted" v-show="itemsDone.length">@lang('form.clear_completed_tasks')</a>
</div>


@section('innerPageJs')
<script type="text/javascript">

  to_do_app_config = {
    csrf_token : "{{ csrf_token() }}",
    url_get_todo_list : "{{ route('get_todo_list') }}",
    url_post_todo_item : "{{ route('post_todo_item') }}",
    url_todo_item_change_status : "{{ route('todo_item_change_status') }}",
    url_delete_todo_item : "{{ route('delete_todo_item', ':id') }}",
    url_todo_destory_all_completed : "{{ route('todo_destory_all_completed') }}",
  };

  
  var toDoApp = new Vue({

  el: '#toDoApp',


  data: {

    newTodo: '',

    todos: []

    },


  created() {
   this.fetch();
  },
  // keeping track of done / todo items
  computed: {

    itemsDone: function itemsDone() {
      return this.todos.filter(function (todo) {return todo.completed;});
    },

    itemsTodo: function itemsTodo() {
      return this.todos.filter(function (todo) {return !todo.completed;});
    } },


  // Let's implement our functionality in methods
  methods: {

    fetch: function () {
      $scope = this;
      $.post( to_do_app_config.url_get_todo_list , { "_token": to_do_app_config.csrf_token })
        .done(function( data ) {
            $scope.todos = data;
        });

    },

    addTodo: function addTodo() {
 
      $scope = this;
      
      var postItem =  { 
        "_token": to_do_app_config.csrf_token, 
        text: this.newTodo        
      };

      
      
      if ($scope.newTodo.length) 
      {
        $.post( to_do_app_config.url_post_todo_item, postItem ).done(function( data ) {        
              $scope.todos = data;
              $scope.newTodo = '';
        });
      }
        
    },


    markTask : function(todo, index){
   
      $scope = this;
      var status = (!todo.completed == true) ? 1 : null;

      $.post( to_do_app_config.url_todo_item_change_status  , { "_token": to_do_app_config.csrf_token,   id: todo.id, completed : status })
        .done(function( data ) {            
           
            if(data)
            {
              $scope.todos[index].completed = (!todo.completed);
            }    
          

        });

    },

    removeTodo: function removeTodo(id, index) {  

      var url = to_do_app_config.url_delete_todo_item.replace(':id', id);

      $scope = this;
      $.post( url , { "_token": to_do_app_config.csrf_token, id: id })
        .done(function( data ) {            
            // remove todo item       
            $scope.todos.splice(index, 1);

        });

      
    },

    clearCompleted: function clearCompleted() { 

      $scope = this;     
      
      $.post( to_do_app_config.url_todo_destory_all_completed  , { "_token": to_do_app_config.csrf_token })
        .done(function( data ) {            
           
            if(data)
            {
              $scope.todos = data;
            }    
          

        });


    } 
  } });
</script>

@endsection