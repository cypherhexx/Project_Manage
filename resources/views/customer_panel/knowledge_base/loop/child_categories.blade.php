@if(count($children) > 0)
   <ul>
      @foreach($children as $child)
      <li>
         <i class="fas fa-folder"></i> <a href="{{ route('knowledge_base_category_customer_view', $child->slug) }}">
         {{ $child->name }} {{ $child->articles()->get()->count() }}</a>
      </li>
      @endforeach
   </ul>
@endif