
<div class="alert alert-info" role="alert">

    <strong>Your request is running!</strong>

</div>

<p>
    Your measurements have been submitted to RIPE Atlas and are running.
    This normally takes at least five minutes.
    <br /><br />
    This page will reload auto-matically in: <span id="seconds">300</span> seconds.
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
})(300);
</script>
