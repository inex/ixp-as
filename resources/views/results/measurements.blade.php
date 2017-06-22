<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
@foreach ( $request->measurements as $m )

    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="heading-{{$m->id}}">
        <h4 class="panel-title">
            {{ $m->dnetwork->name}} (AS{{ $m->dnetwork->asn}})

            <a class="btn btn-default btn-xs collapsed pull-right" role="button" data-toggle="collapse"
               style="margin-left: 20px;"
               data-parent="#accordion" href="#collapse-{{$m->id}}" aria-expanded="false" aria-controls="collapse-{{$m->id}}">
                    <span class="glyphicon glyphicon glyphicon-menu-down" aria-hidden="true"></span>
            </a>

            @if (isset($m->state) && in_array( $m->state, [ 'Failed', 'No suitable probes' ] ) )

                <span class="label label-primary pull-right">TRACE FAILED</span>

            @elseif ( !$m->result )

                <span class="label label-default pull-right">PENDING</span>

            @else

                @if ( $m->result->routing == 'IXP_LAN_SYM' )
                    <span class="label label-success pull-right">SYMMETRIC</span>
                @elseif ( $m->result->routing == 'IXP_SYM' )
                    <span class="label label-success pull-right">SAME IXP</span>
                @elseif ( $m->result->routing == 'IXP_ASYM_OUT' )
                    <span class="label label-danger pull-right">ASYMMETRIC OUT</span>
                @elseif ( $m->result->routing == 'IXP_ASYM_IN' )
                    <span class="label label-danger pull-right">ASYMMETRIC IN</span>
                @elseif ( $m->result->routing == 'NON_IXP' )
                    <span class="label label-info pull-right">NOT {{$request->ixp->shortname}}</span>
                @else
                    <span class="label label-primary pull-right">WTF?</span>
                @endif


            @endif

        </h4>
      </div>
      <div id="collapse-{{$m->id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{$m->id}}">
        <div class="panel-body">


            <div class="row">
              <div class="col-md-12">
                  <p>
                      View Atlas JSON:
                      <span class="btn btn-primary btn-xs" id="atlas-json-requestout-{{$m->id}}" data-measid="{{$m->id}}">
                          Outbound Status
                      </span>
                      &nbsp;
                      <span class="btn btn-primary btn-xs" id="atlas-json-responseout-{{$m->id}}" data-measid="{{$m->id}}">
                          Outbound Response
                      </span>
                      &nbsp;
                      <span class="btn btn-primary btn-xs" id="atlas-json-requestin-{{$m->id}}" data-measid="{{$m->id}}">
                          Inbound Status
                      </span>
                      &nbsp;
                      <span class="btn btn-primary btn-xs" id="atlas-json-responsein-{{$m->id}}" data-measid="{{$m->id}}">
                          Inbound Response
                      </span>
                  </p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                  <h4>Trace Out: AS{{$request->snetwork->asn}} to AS{{ $m->dnetwork->asn}}<h4>
                  @if ( isset($m->result->path_out) )
                    @include('results.measurements-path', ['path' => $m->result->path_out, 'dir' => 'out'])
                  @endif
              </div>
              <div class="col-md-6">
                  <h4>Trace In: AS{{ $m->dnetwork->asn}} to AS{{$request->snetwork->asn}}<h4>
                  @if ( isset($m->result->path_in) )
                    @include('results.measurements-path', ['path' => $m->result->path_in, 'dir' => 'in'])
                  @endif
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                  <p>
                      <br><em>Hint: click on IP addresses for origin ASN, network details, etc.</em>
                  </p>
              </div>
          </div>
        </div>
      </div>
    </div>

@endforeach

</div>
