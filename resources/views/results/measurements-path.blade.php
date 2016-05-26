
<ul>
  @foreach ( $path as $h )
    <li>
        {{implode(',',$h)}}
    </li>
  @endforeach
</ul>
