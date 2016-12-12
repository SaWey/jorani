<?php
/**
 * This view displays a yearly calendar of the leave taken by a user (can be displayed by HR or manager)
 * @copyright  Copyright (c) 2014-2016 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.4.3
 */
 
$isCurrentYear = (int)date('Y') === (int)$year;
$currentMonth = (int)date('m');
$currentDay = (int)date('d');
?>

<h2><?php echo lang('calendar_year_title');?>&nbsp;<span class="muted">(<?php echo $employee_name;?>)</span>&nbsp;<?php echo $help;?></h2>

<div class="row-fluid">
    <div class="span8">
        <?php echo $legend; ?>
    </div>
    <div class="span4">
        <div class="pull-right">
            <a href="<?php echo base_url();?>calendar/year/export/<?php echo $employee_id;?>/<?php echo ($year);?>" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;<?php echo lang('calendar_year_button_export');?></a>
            <a href="<?php echo base_url();?>calendar/year/<?php echo $employee_id;?>/<?php echo ($year - 1);?>" class="btn btn-primary"><i class="icon-chevron-left icon-white"></i></a>
            <?php echo $year;?>
            <a href="<?php echo base_url();?>calendar/year/<?php echo $employee_id;?>/<?php echo ($year + 1);?>" class="btn btn-primary"><i class="icon-chevron-right icon-white"></i></a>
        </div>
    </div>
</div>

<div class="row-fluid"><div class="span12">&nbsp;</div></div>

<div class="row-fluid">
    <div class="span12">
<table class="table table-bordered">
    <thead>
        <tr>
            <td>&nbsp;</td>
            <?php for ($ii = 1; $ii <=31; $ii++) {
                    echo '<td'.($ii === $currentDay ?' class="currentday-bg"':'').'>' . $ii . '</td>';
                }?>
        </tr>
    </thead>
  <tbody>
  <?php 
  
  $monthNumber = 0;  
  foreach ($months as $month_name => $month) { 
    $monthNumber++;
    $isCurrentMonth = $currentMonth === $monthNumber;

  ?>
    <tr>
      <td <?php echo $isCurrentMonth ?' class="currentday-bg"':'';?>><?php echo $month_name; ?></td>
        <?php //Iterate so as to display all mornings
        $pad_day = 1;
        foreach ($month->days as $dayNumber => $day) {
            $isCurrentDay = $isCurrentYear && $isCurrentMonth && $currentDay === $dayNumber;
            $class = '';
            $color_divs = [];
            switch($day->display){
                case '0': $class="working"; break;
                case '4': $class="dayoff"; break;
                case '9': $class="error"; break;
                //case '1':
                //case '2':
                //case '3':
                //case '5':
                //case '6':
                default:
                    $class="";
                    break;
            }

            switch($day->am->status){
                case 1: $color_divs[] = 'amplanned';  break;  // Planned
                case 2: $color_divs[] = 'amrequested'; break;  // Requested
                case 3: $color_divs[] = 'amaccepted';  break;  // Accepted
                case 4: $color_divs[] = 'amrejected'; break;  // Rejected
                case 5: $color_divs[] = 'amdayoff';  break;  // Day off
                default: break;
            }
            switch($day->pm->status){
                case 1: $color_divs[] = 'pmplanned'; break;  // Planned
                case 2: $color_divs[] = 'pmrequested';  break;  // Requested
                case 3: $color_divs[] = 'pmaccepted'; break;  // Accepted
                case 4: $color_divs[] = 'pmrejected';  break;  // Rejected
                case 6: $color_divs[] = 'pmdayoff'; break;  // Day off
                default: break;
            }

            if($isCurrentDay){
                $class .= ' currentday-border';
            }

            if ($class == "error"){
                echo '<td><img src="'.  base_url() .'assets/images/date_error.png"></td>';
            } else {
                $type_color = '';
                $type_title = '';
                if(property_exists($day->am, 'type_id')){
                    $type_color = "<div class='type_color_legend am_type_{$day->am->type_id}'></div>";
                    $type_title = $day->am->type;
                }
                if(property_exists($day->pm, 'type_id')){
                    $type_color .= "<div class='type_color_legend pm_type_{$day->pm->type_id}'></div>";
                    if($type_title == ''){
                        $type_title = $day->pm->type;
                    }else if($type_title != $day->pm->type){
                        $type_title .= '; ' . $day->pm->type;
                    }
                }
                if($type_title == '' && $day->type != ''){
                    $type_title = $day->type;
                }
                $type_title =  ($type_title != '') ? 'title="'.$type_title.'"':'';

                $divs = '';
                foreach ($color_divs as $class_name){
                    $divs .= "<div class='status_color_legend {$class_name}'></div>";
                }
                $divs .= $type_color;
                $divs = ($divs == '')? '&nbsp;' : $divs;
                echo "<td {$type_title} class='tabday {$class}'>{$divs}</td>";
            }

        $pad_day++;
        } ?>
      <?php //Fill 
      if ($pad_day <= 31) echo '<td colspan="' . (32 - $pad_day) . '" style="background-color:#00FFFF;">&nbsp;</td>';
        ?>
    </tr>
  <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <?php for ($ii = 1; $ii <=31; $ii++) {
                    echo '<td>' . $ii . '</td>';
                }?>
        </tr>
  </tbody>
</table>
        
    </div>
</div>
