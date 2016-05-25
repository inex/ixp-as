<!DOCTYPE html>
<html>
    <head>
        <title>Asymmetric Routing Over an IXP</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('bower_components/bootstrap/dist/css/bootstrap.min.css' ) }}">
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/jumbonarrow.css' ) }}">
    </head>
    <body>

        <div class="container">
            <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li role="presentation"><a href="{{ URL::to('/') }}">Home</a></li>
                    <li role="presentation"><a href="{{ URL::to('/history') }}">History</a></li>
                    <li role="presentation"><a href="https://github.com/inex/ixp-as">GitHib</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">Asymmetric Routing Detector</h3>
        </div>


        @yield('content')


        <footer class="footer">
          <p>A Project of the <a href="https://atlas.ripe.net/hackathon/Interface/">RIPE Atlas Interface Hackaton</a>, at
              <a href="https://ripe72.ripe.net/">RIPE72</a>, Copenhagen, Denmark. May 2016.
              <a href="http://opensource.org/licenses/MIT">MIT License</a>.
          </p>
        </footer>

      </div> <!-- /container -->

        <script src="{{ URL::asset('bower_components/jquery/dist/jquery.min.js' ) }}"></script>
        <script src="{{ URL::asset('bower_components/bootstrap/dist/js/bootstrap.min.js' ) }}"></script>
        <script src="{{ URL::asset('bower_components/react/react.min.js' ) }}"></script>
        <script src="{{ URL::asset('bower_components/react/react-dom.min.js' ) }}"></script>

        @yield('scripts')

    </body>
</html>
