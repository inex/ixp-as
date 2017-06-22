<?php $counter = 0; ?>
<ul>
  @foreach ( $path['hops'] as $h )
    <li class="ip-address-li">
        @if (is_array($h))
            @foreach ($h as $i)
                @if ($i == "*")
                    {{$i}}
                @else
                    @if (in_array( $i, $path['ixpx'] ) )
                        <span class="label label-info">
                    @endif
                    <span id="ip-address-{{$m->id}}-{{$dir}}-{{$counter++}}">
                        {{$i}}
                    </span>
                    @if (in_array( $i, $path['ixpx'] ) )
                        </span>
                    @endif
                @endif
            @endforeach
        @else
            @if (in_array( $h, $path['ixpx'] ) )
                <span class="label label-info">
            @endif
            <span id="ip-address-{{$counter++}}">
                {{$h}}
            </span>
            @if (in_array( $h, $path['ixpx'] ) )
                </span>
            @endif
        @endif
    </li>
  @endforeach
</ul>
