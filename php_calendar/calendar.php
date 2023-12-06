<?php

function generate_plot_infos($plotlist){
    $plot_dates = array();
    $plot_dates_and_time = array();
    $plot_durations = array();
    foreach($plotlist as $plot):
        $plot_date_string = substr(basename($plot), 0,8);
        $plot_date = date_create_from_format('Ymd', $plot_date_string);

        // --- With time --- //
        $plot_time_string = substr(basename($plot), 0,13);
        $plot_time = date_create_from_format('Ymd-Hi', $plot_time_string);

        // --- Array with durations --- //
        $plot_durations_string = substr(basename($plot), 14,5);
        if ($plot_durations_string[0] == '0'):
            $hours = $plot_durations_string[1];
        else:
            $hours = $plot_durations_string[0] . $plot_durations_string[1];
        endif;
        $minutes = $plot_durations_string[2] . $plot_durations_string[3];

        // --- Append to arrays --- #
        $plot_dates[]          = date_format($plot_date, 'Y-m-j');     // use this for marking available plots!!
        $plot_dates_and_time[] = date_format($plot_time, 'Y-m-j H:i'); // use this for marking available plots!!
        $plot_durations[]      = $hours . 'h' . $minutes;
    endforeach;

    $plot_infos = array($plot_dates, $plot_dates_and_time, $plot_durations);
    return $plot_infos;
}

// --- Timezone --- //
date_default_timezone_set('Europe/Paris');
//date_default_timezone_set('UTC');

// --- Lidar instrument --- //
// lidar = 0 -> CORAL
// lidar = 1 -> TELMA
// lidar = 2 -> HELIUM
// lidar = 3 -> OP
if (isset($_GET['lidar'])) {
    $lidar = $_GET['lidar'];
} else {
    $lidar = "coral";
}

if ($lidar == "telma")      {$bc="mediumturquoise";} // mediumturquoise, skyblue
elseif ($lidar == "helium") {$bc="darkgray";}
elseif ($lidar == "OP")     {$bc="mediumseagreen";}
else                        {$bc="coral";}

// --- Plot content --- //
// content = 0 -> tmp   (absolute temperature)
// content = 1 -> vebwf (vertical butterworth filter)
// content = 2 -> tempf (temporral filter)
// content = 3 -> aerob (aerosol backscatter)
// content = 4 -> era51 (era5 preview)
// content = 5 -> era52 
if (isset($_GET['content'])) {
    $content = $_GET['content'];
} else {
    $content = "tmp";
}

// --- Get list of plots for all lidars --- //
// Get list of files with .jpg extension in the directory and safe it in an array named $available_plots
chdir(__DIR__); // starts from dir of index.php
$dir_coral = "../plots/coral/" . $content . "/*.png";
$dir_telma = "../plots/telma/" . $content . "/*.png";

$plotlists = array();
$plots_coral = glob($dir_coral);
$plots_telma = glob($dir_telma);
natsort($plots_coral);
natsort($plots_telma);
$plotlists[] = $plots_coral;
$plotlists[] = $plots_telma;
// $n = sizeof($available_plots);

if     ($lidar == "telma"){$available_plots = $plots_telma;}
elseif ($lidar == "coral"){$available_plots = $plots_coral;}
else   {$available_plots = $plots_coral;}

// --- Get index for current plot --- //
if (isset($_GET['index'])) {
    $i_plot = intval($_GET['index']);
    if ($i_plot >= count($available_plots)) {
        $i_plot = count($available_plots)-1;
    }
} else {
    // last available plot - to change with len()
    $i_plot = count($available_plots)-1;
}

// ########################### //

// Home button 
$home_symbol = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-house-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8 3.293l6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                </svg>';

$back_arrow = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-up-left" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1H3.707l10.147 10.146a.5.5 0 0 1-.708.708L3 3.707V8.5a.5.5 0 0 1-1 0v-6z"/>
                </svg>';


// Nightly means plot & BWF
$toggle_off = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-toggle-off" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M11 4a4 4 0 0 1 0 8H8a4.992 4.992 0 0 0 2-4 4.992 4.992 0 0 0-2-4h3zm-6 8a4 4 0 1 1 0-8 4 4 0 0 1 0 8zM0 8a5 5 0 0 0 5 5h6a5 5 0 0 0 0-10H5a5 5 0 0 0-5 5z"/>
                </svg>';

$toggle_on = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-toggle-on" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M5 3a5 5 0 0 0 0 10h6a5 5 0 0 0 0-10H5zm6 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                </svg>';


// Nightly mean plot and button
if (isset($_GET['nm_state'])) {
    $nm_state = intval($_GET['nm_state']);
} else {
    $nm_state = 0;
}

if ($nm_state == 0) {
    $nm_div = 'hide_nm';
    $toggle_button = $toggle_off;
} else {
    $nm_div = 'flex-image';
    $toggle_button = $toggle_on;
}

// ########################### //

// --- Get date of last plot --- #
$last_plot = basename($available_plots[array_key_last($available_plots)]);
$last_date_string = substr($last_plot, 0,8);
$last_date = date_create_from_format('Ymd', $last_date_string);
$date_of_last = date_format($last_date, 'Y-m-j');

// --- Get date of plot --- #
$current_plot = basename($available_plots[($i_plot)]);
$current_date_string = substr($current_plot, 0,8);
$current_date = date_create_from_format('Ymd', $current_date_string);
$date_of_plot = date_format($current_date, 'Y-m-j');

// --- Generate arrays with plot infos (date, time, duration) --- #
$plot_infos = generate_plot_infos($plotlists[0]);
$plot_dates_coral          = $plot_infos[0];
$plot_dates_and_time_coral = $plot_infos[1];
$plot_durations_coral      = $plot_infos[2];
$plot_infos = generate_plot_infos($plotlists[1]);
$plot_dates_telma          = $plot_infos[0];
$plot_dates_and_time_telma = $plot_infos[1];
$plot_durations_telma      = $plot_infos[2];

// ------------------------------------------------------------
// update colors (blue for telma, red for coral? )
//echo $plot_dates_coral[1];

// Get prev & next month by checking arguments passed via url link
if (isset($_GET['ym'])) {
    $ym = $_GET['ym'];
} else {
    // Month of last plot
    $ym = date_format($last_date, 'Y-m');
}

// Check format - otherwise use current date
$timestamp = strtotime($ym . '-01');  // the first day of the month
if ($timestamp === false) {
    $ym = date('Y-m');
    $timestamp = strtotime($ym . '-01');
}
        
// Today (Format:2018-08-8)
$today = date('Y-m-j');

// Title (Format:August, 2018)
$title = date('F, Y', $timestamp);

// Create prev & next month link
$prev = date('Y-m', strtotime('-1 month', $timestamp));
$next = date('Y-m', strtotime('+1 month', $timestamp));

// Create prev & next year link
$prevYear = date('Y-m', strtotime('-1 year', $timestamp));
$nextYear = date('Y-m', strtotime('+1 year', $timestamp));

// Number of days in the month
$day_count = date('t', $timestamp);

// 1:Mon 2:Tue 3: Wed ... 7:Sun
$str = date('N', $timestamp);

// Array for calendar
$weeks = [];
$week  = '';

// Add empty cell(s)
$week .= str_repeat('<td></td>', $str - 1);

for ($day = 1; $day <= $day_count; $day++, $str++) {

    $date = $ym . '-' . $day;

    // Start cell with class for background color
    if ($today == $date):
        $week .= '<td class="today">';
    elseif ($date_of_last == $date):
        $week .= '<td class="date_of_last">';
    elseif ($date_of_plot == $date):
        $week .= '<td class="date_of_plot">';
    else:
        $week .= '<td class="normal_day">';
    endif;

    // --- Add number of day to cell --- //
    $week .= $day;

    // --- Add event if plot_date --- //
    if (in_array($date, $plot_dates_coral)) {
        $keys = array_keys($plot_dates_coral, $date);
        foreach( $keys as $i):
            $week .= '<br/> <a href="?ym=' . $ym . '&index=' . $i . '&nm_state=' . $nm_state . '&content=' . $content . '&lidar=coral' . '" class="btn btn-secondary btn-sm" style="background-color:coral; border-color:coral">' . substr($plot_dates_and_time_coral[$i],-5) . ' (' . $plot_durations_coral[$i] . ')</a>';
        endforeach;
    }
    if (in_array($date, $plot_dates_telma)) {
        $keys = array_keys($plot_dates_telma, $date);
        foreach( $keys as $i):
            $week .= '<br/> <a href="?ym=' . $ym . '&index=' . $i . '&nm_state=' . $nm_state . '&content=' . $content . '&lidar=telma' . '" class="btn btn-secondary btn-sm" style="background-color:mediumturquoise; border-color:mediumturquoise">' . substr($plot_dates_and_time_telma[$i],-5) . ' (' . $plot_durations_telma[$i] . ')</a>';
        endforeach;
    }

    // Finish cell
    $week .= '</td>';

    // Sunday OR last day of the month
    if ($str % 7 == 0 || $day == $day_count) {

        // last day of the month
        if ($day == $day_count && $str % 7 != 0) {
            // Add empty cell(s)
            $week .= str_repeat('<td></td>', 7 - $str % 7);
        }

        $weeks[] = '<tr>' . $week . '</tr>';

        $week = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CORAL Calendar</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">-->
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!--<?php echo $plot_durations[$i_plot]; ?>-->
    <div class='<?php echo $nm_div ?>' ><img src='../images/nightly_means.png'/></div>
    <div class="row">
        <div class="col-xl-7 col-lg-8 col-md-12"> 
            <div class="container">
                <ul class="list-inline">
                    <li class="list-inline-item"><a href="?ym=<?= $prevYear; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=<?= $content; ?>&lidar=<?= $lidar; ?>" class="btn btn-dark">&lt; year</a></li>
                    <li class="list-inline-item"><a href="?ym=<?= $prev; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=<?= $content; ?>&lidar=<?= $lidar; ?>" class="btn btn-dark">&lt; month</a></li>
                    <li class="list-inline-item"><span class="title"><?= $title; ?></span></li>
                    <li class="list-inline-item"><a href="?ym=<?= $next; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=<?= $content; ?>&lidar=<?= $lidar; ?>" class="btn btn-dark">month &gt;</a></li>
                    <li class="list-inline-item"><a href="?ym=<?= $nextYear; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=<?= $content; ?>&lidar=<?= $lidar; ?>" class="btn btn-dark">year &gt;</a></li>
                </ul>
                <ul class="list-inline">
                    <span class="text-left"><a href="../index.php" class="btn btn-dark"><?php echo $back_arrow; echo $home_symbol; ?></a></span>
                    <span class="text-left"><a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?php if ($nm_state==0) {echo '1';} else {echo '0';} ?>&content=<?= $content; ?>" class="btn btn-dark">Overview   <?php echo $toggle_button ?></a></span>
                    <span class="list-inline"><button type="button" class="btn btn-secondary" style="width: 25%; font-weight: bold; opacity: 1; background-color: <?= $bc; ?>; border-color: <?= $bc; ?>" disabled><?= strtoupper($lidar); ?></button></span>
                    <span class="text-right"><a href="calendar.php?nm_state=<?= $nm_state; ?>&content=<?= $content; ?>&lidar=<?= $lidar; ?>" class="btn btn-dark">Latest Measurement</a></span>
                </ul>
                <p>
                <div class="btn-group btn-group-toggle">
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=tmp&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="tmp") {echo 'active';} else {echo '';} ?>">
                        Temperature
                        </a>
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=vebwf&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="vebwf") {echo 'active';} else {echo '';} ?>">
                        T' (vertical BWF)
                        </a>
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=tempf&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="tempf") {echo 'active';} else {echo '';} ?>">
                        T' (temporal mean)
                        </a>
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=aeros&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="aeros") {echo 'active';} else {echo '';} ?>">
                        Backscatter
                        </a> 
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=era51&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="era51") {echo 'active';} else {echo '';} ?>">
                        ERA5 (preview)
                        </a>
                        <a href="?ym=<?= $ym; ?>&index=<?= $i_plot; ?>&nm_state=<?= $nm_state; ?>&content=era52&lidar=<?= $lidar; ?>" class="btn btn-dark <?php if ($content=="era52") {echo 'active';} else {echo '';} ?>">
                        ERA5 (profiles)
                        </a>
                </div>
                </p>
                <span class="badge" style="background-color: coral; border-color: coral; color: white">CORAL</span>
                <span class="badge" style="background-color: mediumturquoise; border-color: mediumturquoise; color: white">TELMA</span>
                <span class="badge" style="background-color: LightSlateGray; border-color: LightSlateGray; color: white">HELIUM</span>
                <span class="badge" style="background-color: mediumseagreen; border-color: mediumseagreen; color: white">OP-LIDAR</span>
                <p>


                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>M</th>
                            <th>T</th>
                            <th>W</th>
                            <th>T</th>
                            <th>F</th>
                            <th>S</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($weeks as $week) {
                                echo $week;
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xl-5 col-lg-4 col-md-12">
            <div class="flex-image">
                <?php 
                #echo "<a href='plot.php?index=" . $i_plot . "&content=" . $content . "'><img src='" . $available_plots[$i_plot] . "' class='img-fluid' /><img src='" . $available_plots_aerosol[$i_plot] ."' class='img-fluid' /></a>";
                echo "<a href='plot.php?index=" . $i_plot . "&content=" . $content . "'><img src='" . $available_plots[$i_plot] . "' class='img-fluid' /></a>";
                ?>
            </div>
        </div>
    </div>

</body>
</html>
