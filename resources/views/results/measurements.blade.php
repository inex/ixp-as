<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
@foreach ( $request->measurements as $m )

    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="heading-{{$m->id}}">
        <h4 class="panel-title">
          <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{$m->id}}" aria-expanded="false" aria-controls="collapse-{{$m->id}}">
            {{ $m->dnetwork->name}} (AS{{ $m->dnetwork->asn}})

            @if ( !$m->result )
                <span class="label label-default">PENDING</span>

            @else

                @if ( $m->result->routing == 'IXP_SYM' )
                    <span class="label label-success">SYMMETRIC</span>
                @elseif ( $m->result->routing == 'IXP_ASYM_OUT' )
                    <span class="label label-danger">ASYMMETRIC OUT</span>
                @elseif ( $m->result->routing == 'IXP_ASYM_IN' )
                    <span class="label label-danger">ASYMMETRIC IN</span>
                @elseif ( $m->result->routing == 'NON_IXP' )
                    <span class="label label-info">NON IXP</span>
                @else
                    <span class="label label-primary">WTF?</span>
                @endif



            @endif

          </a>
        </h4>
      </div>
      <div id="collapse-{{$m->id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{$m->id}}">
        <div class="panel-body">
            <div class="row">
              <div class="col-md-6">
                  <h4>Trace Out: AS{{$request->snetwork->asn}} to AS{{ $m->dnetwork->asn}}<h4>

                  <ul>
                      @foreach ( $m->result->path_out as $hop )
                        <li> {{ $hop }} </li>
                      @endforeach
                  </ul>

              </div>
              <div class="col-md-6">
                  <h4>Trace In: AS{{ $m->dnetwork->asn}} to AS{{$request->snetwork->asn}}<h4>

                  <ul>
                      @foreach ( $m->result->path_in as $hop )
                        <li> {{ $hop }} </li>
                      @endforeach
                  </ul>

              </div>
            </div>
        </div>
      </div>
    </div>

@endforeach

</div>
