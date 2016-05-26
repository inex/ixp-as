
<ul>
  @foreach ( $path as $h )
    <li>
        @if (is_array($h))
            {{implode(',',$h)}}
        @else
            {{$h}}
        @endif
    </li>
  @endforeach
</ul>
