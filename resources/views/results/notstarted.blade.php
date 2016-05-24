
<div class="alert alert-success" role="alert">

    <strong>Your request is pending!</strong>

</div>

<p>
    Your request has been queued and will start within the next 60secs.
    <br /><br />
    This page will reload auto-matically in: <span id="seconds">60</span> seconds.
</p>

<p>
    If you want to check back later / share your results, please record the following URL:
    <br><br>
    <a href="{{ URL::to( '/result' ) . '/' . $nonce }}">{{ URL::to( '/result' ) . '/' . $nonce }}</a>
</p>


<script>
(function countdown(remaining) {
    if(remaining <= 0)
        location.reload(true);
    document.getElementById('seconds').innerHTML = remaining;
    setTimeout(function(){ countdown(remaining - 1); }, 1000);
})(60); // 60 seconds
</script>
