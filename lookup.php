<?php
/**
 * Consume 'call' query parameter to search Cache for shelf ranges where call number can be found.
 * Redirect user to mapping frontend.
 */

require_once('dbcx.php');
require_once('Classes/Factories/Cache_Factory.php');

/** Gather */
$callNumber = $_GET['call'];
$building = $_GET['building'] ? $_GET['building'] : 'dummy';
$context = array(
  'db' => $db,
  'building' => $building,
);
$cache = Cache_Factory::create($context);
$urlBase = 'https://localhost/map';
$urlFallBack = 'https://localhost/fallback';

$norm = Regex_Call_Number_Parser::normalizeCallNumber($callNumber);
$denorm = Regex_Call_Number_Parser::denormalizeCallNumber($norm);

/** Operate */
$results = $cache->retrieveShelves($callNumber);

//header("Location: $urlFallBack");

if (count($results) > 0) {
  $shelf = intVal($results[0]['shelf']);
  $floor = $results[0]['floor'];
  $building = $results[0]['building_name'];
  $url = "$urlBase?call={$denorm}&floor={$floor}&shelf={$shelf}&building={$building}";
  $link = "<a target='_blank' href='$url'>link</a>";

  /** Redirect user to first result */
//  header("Location: $url");
}
//die();



/**
 * From here down is useful for local development
 * Comment out the location redirect header above
 */



/** Prepare for print */
foreach ($results as $result) {
  $startNorm = $result['start_norm'];
  $startDenorm = $result['start_denorm'];
  $endNorm = $result['end_norm'];
  $endDenorm = $result['end_denorm'];

  $table .= "
    <tr>
      <td>
        {$result['building_name']}
      </td>
      <td>
        {$result['floor']}
      </td>
      <td>
        {$result['shelf']}
      </td>
      <td>
        $startNorm
      </td>
      <td>
        $startDenorm
      </td>
      <td>
        $endNorm
      </td>
      <td>
        $endDenorm
      </td>
      <td>
        $link
      </td>
    </tr>
  ";
}

$header = '
  <tr>
    <th>
      Building
    </th>
    <th>
      floor
    </th>
    <th>
      shelf
    </th>
    <th>
      range_start_norm
    </th>
    <th>
      range_start
    </th>
    <th>
      range_end_norm
    </th>
    <th>
      range_end
    </th>
    <th>
      
    </th>
  </tr>
';
?>



<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

<div class="container">
  <div id="searching-for">Searching for: <?php echo $norm; ?></div>

  <table class="table table-bordered table-striped table-hover">
    <thead class="thead-dark">
      <?php print $header ?>
    </thead>
    <tbody>
      <?php print $table ?>
    </tbody>
  </table>
</div>