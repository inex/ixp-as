<?php $counter = 0; ?>
<ul>
  @foreach ( $path as $h )
    <li>
        @if (is_array($h))
            @foreach ($h as $i)
                @if ($i == "*")
                    {{$i}}
                @else
                    <span id="ip-address-{{$m->id}}-{{$dir}}-{{$counter++}}">{{$i}}</span>
                @endif
            @endforeach
        @else
            <span id="ip-address-{{$counter++}}">{{$h}}</span>
        @endif
    </li>
  @endforeach
</ul>
