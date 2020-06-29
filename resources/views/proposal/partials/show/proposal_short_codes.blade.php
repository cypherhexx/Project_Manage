<ul class="list-group">
    
    @foreach($data['proposal_short_codes'] as $key=>$value)
    <li class="list-group-item">
        <b>{{ $value }}</b>
        <a href="#" v-on:click.prevent="shortCodeClicked('<?php echo $key; ?>')" class="float-md-right" ><?php echo $key; ?></a>
    </li>
    @endforeach

</ul>

