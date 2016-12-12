<?php 
/**
 * This partial view is included into views when we want to display a legend for the colors.
 */
?>
<script type="text/javascript">
    $(function () {
        //Popup generate a QR Code for mobile access
        $("#cmdToggleLegend").click(function() {
            $('#status_legend, #type_legend').toggle();
            if($('#status_legend').is(":visible")){
                $.each($('#calendar').fullCalendar('clientEvents'), function( index, event ) {
                    event.color = event.status_color;
                });
            }else{
                $.each($('#calendar').fullCalendar('clientEvents'), function( index, event ) {
                    event.color = event.type_color;
                });
            }
            $('#calendar').fullCalendar( 'rerenderEvents' );
        });

    });
</script>
<div id="status_legend">
    <div class="span2"><span class="label"><?php echo lang('Planned');?></span></div>
    <div class="span2"><span class="label label-success"><?php echo lang('Accepted');?></span></div>
    <div class="span2"><span class="label label-warning"><?php echo lang('Requested');?></span></div>
    <div class="span2"><span class="label label-important" style="background-color: #ff0000;"><?php echo lang('Rejected');?></span></div>
</div>
<div id="type_legend" style="display:none;">
<?php
foreach($types as $type){
 ?>   <div class="span1"><span class="label label" style="background-color: <?php echo $type['color']; ?>"><?php echo $type['name']; ?></span></div>
<?php
}
?>
</div>


