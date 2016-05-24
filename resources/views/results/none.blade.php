
<div class="alert alert-danger" role="alert">

    <strong>Oh snap!</strong> No results :-(

</div>

<p>
    We gave your request an hour to run and we've gotten no results back.
    Hey, come on! This is a hackaton project!
</p>

<p>
    If you want us to look into this, contact us with this URL:
    <br><br>
    <a href="{{ URL::to( '/result' ) . '/' . $nonce }}">{{ URL::to( '/result' ) . '/' . $nonce }}</a>
</p>
