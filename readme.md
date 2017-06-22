# IXP Asymmetric Routing Detector

A tool to detect asymmetric routing over IXPs using bi-directional traceroutes from
[RIPE Atlas](https://atlas.ripe.net/) probes.

Live version: https://www.inex.ie/ard/

This is a project of the [RIPE Atlas Interface Hackaton](https://atlas.ripe.net/hackathon/Interface/) just prior
to [RIPE72](https://ripe72.ripe.net/) in Copenhagen, Denmark.

## Author(s):

* Barry O'Donovan, [INEX](https://www.inex.ie/), Dublin, Ireland - principal author and maintainer.

The team at the RIPE hackathon also included:

* Jacob Drabczyk, [Facebook](https://www.facebook.com/), Dublin, Ireland
* Drew Taylor, [Comcast](http://corporate.comcast.com/), Philadelphia, USA

## JSON Access

We've made the main elements accessible via JSON for API use:

1. JSON object of data used to create a request: ``/json``

2. Submit a request by GET to: ``/request/{ixp_id}/{network_id}/{protocol}/json``

3. Results: ``/result/{key}/json``


## Mode of Operation

The application is mostly async with the frontend requests just storing rows in the database and replying. The cron schedule below essentially calls the following artisan commands (and most can take a verbiosity parameter: ``-v``):

* ``ixps:update``: parse ``configs/ixps.php`` and add / update IXPs. Typically done once per day. **NB: does not remove IXPs.**
* ``atlas:update-probes``: iterates over all networks and updates probes (adds new, removes old, checks protocol support, etc.)
* ``atlas:create-measurements``: process new end user requests and queues up measurements in the database
* ``atlas:run-measurements``: iterates over the database and sends outstanding requests to RIPE Atlas
* ``atlas:update-measurements``: iterates over measurements sent to RIPE Atlas and looks for completed (failed, etc) results. When both paths of a traceroute are complete, this also emits an event for the ``Interpretor/Basic`` to make a routing decision and create the result object.
* ``atlas:complete-requests``: marks end user requests as complete if all measurements have completed.
* ``atlas:stop-all-measurements``: Sledge hammer to delete all outstanding RIPE Atlas measurements created with the configured Atlas key.

## Caveats

**This is the fruit of a hackaton! Expect bugs, non-defensive programming, databases definitely harmed.**

* RIPE Atlas allows a max of 10 measurements towards any one probe at a time. As such, larger requests will take longer to run (finger in the air guesstimate: 5mins per batch of 10).
* Any measurement that hasn't returned in 60mins is considered failed.
* Routing over two LANs at a single exchange is currently considered symmetric. It nearly is anyway :wink:



## Installation

This project uses the standard PHP stack and requires PHP >= 5.6, MySQL/MariaDB, composer.

1. Clone the repository:

   ```
   git clone https://github.com/inex/ixp-as.git /srv/ard
   ```

2. Install dependancies:

   ```
   cd /srv/ard
   composer install
   bower install
   ```

3. Create a database:

   ```
   mysql -u root
   CREATE DATABASE ard;
   GRANT ALL ON ard.* TO ard@localhost IDENTIFIED BY 'secret'
   ```

4. Copy the base config:

   ```
   cp .env.example .env
   ```

5. Create an application key (Laravel requires this):

   ```
   php artisan key:generate
   ```

6. Edit ``.env`` and set: the ``DB_`` parameters and add your RIPE Atlas API keys for creating and stopping measurements.

7. Create the database schema:

    ````
    php artisan doctrine:schema:create
    ````

8. Add IXPs to ``config/ixps.php``:

    ```
    cp config/ixps.php.dist config/ixps.php
    edit config/ixps.php
    ```

9. Add the following to cron:

   ```
   * * * * * www-data cd /srv/ard && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```

10. Test it locally:

   ```
   php artisan serve
   ```

## License

This application is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
