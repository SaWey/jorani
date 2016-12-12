<?php
/**
 * This view builds a monthly tabular calendar for a group of employees.
 * @copyright  Copyright (c) 2014-2016 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.3.0
 */
?>

<h2><?php echo lang('calendar_tabular_title');?> &nbsp;<?php echo $help;?></h2>

<div class="row-fluid">
    <div class="span4">
        <label for="txtEntity"><?php echo lang('calendar_organization_field_select_entity');?></label>
        <div class="input-append">
            <input type="text" id="txtEntity" name="txtEntity" value="<?php echo $department;?>" readonly />
            <button id="cmdSelectEntity" class="btn btn-primary"><?php echo lang('calendar_tabular_button_select_entity');?></button>
        </div>
        
        <label for="cboMonth"><?php echo lang('calendar_tabular_field_month');?></label>
        <select name="cboMonth" id="cboMonth">
            <?php for ($ii=1; $ii<13;$ii++) {
                if ($ii == $month) {
                    echo "<option val='" . $ii ."' selected>" . $ii ."</option>";
                } else {
                    echo "<option val='" . $ii ."'>" . $ii ."</option>";
                }
            }?>
        </select>
        
        <label for="cboYear"><?php echo lang('calendar_tabular_field_year');?></label>
        <select name="cboYear" id="cboYear">
            <?php 
            $len = date('Y', strtotime('+2 year'));
            for ($ii=date('Y', strtotime('-6 year')); $ii<$len;$ii++) {
                if ($ii == $year) {
                    echo "<option val='" . $ii ."' selected>" . $ii ."</option>";
                } else {
                    echo "<option val='" . $ii ."'>" . $ii ."</option>";
                }
            }?>
        </select>
        
    </div>
    <div class="span3">
        <label for="chkIncludeChildren">
            <input type="checkbox" value="" id="chkIncludeChildren" name="chkIncludeChildren"> <?php echo lang('calendar_tabular_check_include_subdept');?>
        </label>
    </div>
    <div class="span5">
        <div class="row-fluid">
            <div class="span12">
                <button id="cmdPrevious" class="btn btn-primary"><i class="icon-chevron-left icon-white"></i></button>
                <button id="cmdExecute" class="btn btn-primary"><i class="icon-file icon-white"></i>&nbsp;<?php echo lang('calendar_tabular_button_execute');?></button>
                <button id="cmdNext" class="btn btn-primary"><i class="icon-chevron-right icon-white"></i></button>
            </div>
        </div>
        <div class="row-fluid"><div class="span12">&nbsp;</div></div>
        <div class="row-fluid">
            <div class="span12">
                <button id="cmdExport" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp;<?php echo lang('calendar_tabular_button_export');?></button>
            </div>
        </div>

    </div>
</div>

<div class="row-fluid">
    <?php echo $legend; ?>
</div>

<?php if (count($tabular) > 0) {?>
<table class="table table-bordered">
    <thead>
        <tr>
            <td>&nbsp;</td>
            <?php
                $start = $year . '-' . $month . '-' . '1';    //first date of selected month
                $lastDay = date("t", strtotime($start));    //last day of selected month
                $isCurrentMonth = date('Y-n') === $year . '-' . (int)$month;
                $currentDay = (int)date('d');
                for ($ii = 1; $ii <=$lastDay; $ii++) {
                    $class = '';
                    if($isCurrentMonth && $ii === $currentDay){
                        $class .= ' currentday-bg';
                    }
                    $dayNum = date("N", strtotime($year . '-' . $month . '-' . $ii));
                    switch ($dayNum)
                    {
                        case 1: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_monday_short') . '</b></td>'; break;
                        case 2: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_tuesday_short') . '</b></td>'; break;
                        case 3: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_wednesday_short') . '</b></td>'; break;
                        case 4: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_thursday_short') . '</b></td>'; break;
                        case 5: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_friday_short') . '</b></td>'; break;
                        case 6: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_saturday_short') . '</b></td>'; break;
                        case 7: echo '<td'.($class?' class="'.$class.'"':'').'><b>' . lang('calendar_sunday_short') . '</b></td>'; break;
                    }
                }?>
        </tr>
        <tr>
            <td><b><?php echo lang('calendar_tabular_thead_employee');?></b></td>
            <?php
                $start = $year . '-' . $month . '-' . '1';    //first date of selected month
                $lastDay = date("t", strtotime($start));    //last day of selected month
                for ($ii = 1; $ii <=$lastDay; $ii++) {
                    $class = '';
                    if($isCurrentMonth && $ii === $currentDay){
                        $class .= ' currentday-bg';
                    }
                    echo '<td'.($class?' class="'.$class.'"':'').'><b>' . $ii . '</b></td>';
                }?>
        </tr>
    </thead>
  <tbody>
  <?php
/*
 * This partial view builds a "linear" calendar (which is technically a line into an HTML table).
 * A linear calendar displays the leaves of an employee during a month. Each cell is a day.
 * This partial view is included into the monthly presence report and the tabular calendar.
 */
  $repeater = 0;

  
  foreach ($tabular as $employee) {
      $dayIterator = 0;
      ?>
    <tr>
      <td><?php echo $employee->name; ?></td>
      <?php foreach ($employee->days as $day) {
          $dayIterator++;
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
          
          // Current day class
          if($isCurrentMonth && $dayIterator === $currentDay){
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
         } ?>
          </tr>
    <?php      
    if (++$repeater>=10) {
        $repeater = 0;?>
        <tr>
            <td>&nbsp;</td>
            <?php
                $start = $year . '-' . $month . '-' . '1';    //first date of selected month
                $lastDay = date("t", strtotime($start));    //last day of selected month
                for ($ii = 1; $ii <=$lastDay; $ii++) {
                    $dayNum = date("N", strtotime($year . '-' . $month . '-' . $ii));
                    switch ($dayNum)
                    {
                        case 1: echo '<td><b>' . lang('calendar_monday_short') . '</b></td>'; break;
                        case 2: echo '<td><b>' . lang('calendar_tuesday_short') . '</b></td>'; break;
                        case 3: echo '<td><b>' . lang('calendar_wednesday_short') . '</b></td>'; break;
                        case 4: echo '<td><b>' . lang('calendar_thursday_short') . '</b></td>'; break;
                        case 5: echo '<td><b>' . lang('calendar_friday_short') . '</b></td>'; break;
                        case 6: echo '<td><b>' . lang('calendar_saturday_short') . '</b></td>'; break;
                        case 7: echo '<td><b>' . lang('calendar_sunday_short') . '</b></td>'; break;
                    }
                }?>
        </tr>
    <tr>
        <td><b><?php echo lang('calendar_tabular_thead_employee');?></b></td>
        <?php for ($ii = 1; $ii <=$lastDay; $ii++) echo '<td><b>' . $ii . '</b></td>';?>
    </tr>
    <?php }
    }?>
  </tbody>
</table>
<?php } ?>

<div id="frmSelectEntity" class="modal hide fade">
    <div class="modal-header">
        <a href="#" onclick="$('#frmSelectEntity').modal('hide');" class="close">&times;</a>
         <h3><?php echo lang('calendar_tabular_popup_entity_title');?></h3>
    </div>
    <div class="modal-body" id="frmSelectEntityBody">
        <img src="<?php echo base_url();?>assets/images/loading.gif">
    </div>
    <div class="modal-footer">
        <a href="#" onclick="select_entity();" class="btn"><?php echo lang('calendar_tabular_popup_entity_button_ok');?></a>
        <a href="#" onclick="$('#frmSelectEntity').modal('hide');" class="btn"><?php echo lang('calendar_tabular_popup_entity_button_cancel');?></a>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/modernizr.min.js"></script>
<script src="<?php echo base_url();?>assets/js/bootbox.min.js"></script>
<script type="text/javascript">
    var entity = -1; //Id of the selected entity
    var text; //Label of the selected entity
    var entity = <?php echo $entity;?>;
    var month = <?php echo $month;?>;
    var year = <?php echo $year;?>;
    var children = '<?php echo $children;?>';
    
    function select_entity() {
        entity = $('#organization').jstree('get_selected')[0];
        text = $('#organization').jstree().get_text(entity);
        $('#txtEntity').val(text);
        $("#frmSelectEntity").modal('hide');
    }
    
    function includeChildren() {
        if ($('#chkIncludeChildren').prop('checked') == true) {
            return 'true';
        } else {
            return 'false';
        }
    }
    
    //Execute the report
    //Target : execution in the page or export to Excel
    function executeReport(month, year, children, target) {
        if (entity != -1) {
            url = '<?php echo base_url();?>calendar/' + target + '/' + entity + '/' + month+ '/' + year+ '/' + children;
            document.location.href = url;
        }
    }
    
    $(document).ready(function() {
        //Select radio button depending on URL
        if (children == '1') {
            $("#chkIncludeChildren").prop("checked", true);
        } else {
            $("#chkIncludeChildren").prop("checked", false);
        }
        
        //Popup select entity
        $("#cmdSelectEntity").click(function() {
            $("#frmSelectEntity").modal('show');
            $("#frmSelectEntityBody").load('<?php echo base_url(); ?>organization/select');
        });

        //Execute the report
        $('#cmdExecute').click(function() {
            month = $('#cboMonth').val();
            year = $('#cboYear').val();
            children = includeChildren();
            executeReport(month, year, children, 'tabular');
        });

        //Export the report into Excel
        $("#cmdExport").click(function() {
            month = $('#cboMonth').val();
            year = $('#cboYear').val();
            children = includeChildren();
            executeReport(month, year, children, 'tabular/export');
        });

<?php $datePrev = date_create($year . '-' . $month . '-01');
$dateNext = clone $datePrev;
date_add($dateNext, date_interval_create_from_date_string('1 month'));
date_sub($datePrev, date_interval_create_from_date_string('1 month'));?>
        //Previous/Next
        $('#cmdPrevious').click(function() {
            month = <?php echo $datePrev->format('m'); ?>;
            year = <?php echo $datePrev->format('Y'); ?>;
            children = includeChildren();
            url = '<?php echo base_url();?>calendar/tabular/' + entity + '/' + month+ '/' + year+ '/' + children;
            document.location.href = url;
        });
        $('#cmdNext').click(function() {
            month = <?php echo $dateNext->format('m'); ?>;
            year = <?php echo $dateNext->format('Y'); ?>;
            children = includeChildren();
            url = '<?php echo base_url();?>calendar/tabular/' + entity + '/' + month+ '/' + year+ '/' + children;
            document.location.href = url;
        });
        
        //Load alert forms
        $("#frmSelectEntity").alert();
        //Prevent to load always the same content (refreshed each time)
        $('#frmSelectEntity').on('hidden', function() {
            $(this).removeData('modal');
        });
    });
</script>
