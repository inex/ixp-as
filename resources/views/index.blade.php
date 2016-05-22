@extends('layouts.master')

@section('content')


<div class="jumbotron">
    <h3>Asymmetric Routing Over IXPs</h3>
    <p class="lead">
        A tool to detect asymmetric routing over IXPs using bi-directional traceroutes from
        <a href="https://atlas.ripe.net/">RIPE Atlas</a> probes.
    </p>
</div>

<p>
    This tool will identify networks with asymmetric routing over an IXP. The two qualifying criteria are:
</p>
<ul>
    <li> the IXP must have a publically accessible <a href="https://github.com/euro-ix/json-schemas">JSON export</a>; </li>
    <li> the IXP member / customer must have at least one public RIPE Atlas probe within their directly connected ASN (on a per protocol basis) </li>
</ul>

<h3>Create a Detection Request</h3>
<br>
<h4>Step 1: Choose an IXP</h4>

<select id="ixp_select" name="ixp">
    <option value=""></option>
</select>

<div id="div_protocol" style="display: none">
    <br>
    <h4>Step 2: Choose a protocol</h4>

    <select id="protocol_select" name="protocol">
        <option value=""></option>
        <option value="4">IPv4</option>
        <option value="6">IPv6</option>
    </select>
</div>

<div id="div_network" style="display: none">
    <br>
    <h4>Step 3: Choose a member/customer</h4>

    <select id="network_select" name="network">
        <option value=""></option>
    </select>
</div>

<div id="div_submit" style="display: none">
    <br>
    <h4>Step 4: Submit!</h4>

    <form method="post" action="{{ URL::to('/request') }}">
        <input type="hidden" id="form_network"  name="network_id" value="" />
        <input type="hidden" id="form_protocol" name="protocol" value="" />
        <input type="submit" name="submit" value="Submit" />
    </form>

</div>


<br><br><br>
@endsection

@section('scripts')
<script>
    var ixps = {!! $ixps !!};
</script>

<script src="{{ URL::asset('js/request.js' ) }}"></script>
@endsection
