<?php

/**
 * This file is use to declare variables for the
 * Email regisration page of Member Verification
 * Plugin
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin/partials
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @link       #
 * @since      1.0.0
 */
?>


<?php 

$step_setting = array('media_buttons' => false,'quicktags'=>false, 'textarea_rows' => 6);
$step1 =  !empty ( get_option( 'ifsa_step_1_description' ) ) ? get_option( 'ifsa_step_1_description' ) : '';
$step2 =  !empty ( get_option( 'ifsa_step_2_description' ) ) ? get_option( 'ifsa_step_2_description' ) : '';
$step3 =  !empty ( get_option( 'ifsa_step_3_description' ) ) ? get_option( 'ifsa_step_3_description' ) : '';

$after_30 =  !empty ( get_option( 'after_30' ) ) ? get_option( 'after_30' ) : '30';
$after_21 =  !empty ( get_option( 'after_21' ) ) ? get_option( 'after_21' ) : '21';
$after_15 =  !empty ( get_option( 'after_15' ) ) ? get_option( 'after_15' ) : '15';
$before_30  =  !empty ( get_option( 'before_30' ) ) ? get_option( 'before_30' ) : '30';
$next_yr_valid  =  !empty ( get_option( 'next_yr_valid' ) ) ? get_option( 'next_yr_valid' ) : '60';

?>

<table class="form-table" role="presentation">
	<tbody>
	<tr>
		<th scope="row">Expiration Date:</th>
		<td>		<input type="text" id="ifsa_general_setting_date_field" name="ifsa_general_setting_date_field"
	       value="<?php echo esc_attr( get_option( 'ifsa_general_setting_date_field' ), 'Ifsa_Member_Verification' ) ?>"/>
 </td>
	</tr>
	<tr>
		<th scope="row">Registration Step 1 Description:</th>
		<td>
		<textarea id="ifsa_step_1_description" name="ifsa_step_1_description" rows="4" cols="50"> <?php echo trim($step1);?></textarea>
	</td>
	</tr>
	<tr>
		<th scope="row">Registration Step 2 Description:</th>
		
		<td>
		<textarea id="ifsa_step_2_description" name="ifsa_step_2_description" rows="4" cols="50"> <?php echo trim($step2);?></textarea>
		</td>
		</tr>
	<tr>
		<th scope="row">Registration Step 3 Description:</th>
		<td>
		<textarea id="ifsa_step_3_description" name="ifsa_step_3_description" rows="4" cols="50"> <?php echo trim($step3);?></textarea>
		</td>
	</tr>

	<tr>
		<th scope="row">Time when registration is valid for next year</th>
		<td>
		<input type="number" id="next_yr_valid" name="next_yr_valid" value="<?php echo $next_yr_valid;?>"/>
		</td>
	</tr>

	<tr>
		<th scope="row">Start to ability to renew account</th>
		<td>
		<input type="number" id="before_30" name="before_30" value="<?php echo $before_30;?>"/>
		</td>
	</tr>
	<tr>
		<th scope="row">1st reminder</th>
		<td>
		<input type="number" id="after_15" name="after_15" value="<?php echo $after_15;?>"/>
		</td>
	</tr>
	<tr>
		<th scope="row">2nd reminder</th>
		<td>
		<input type="number" id="after_21" name="after_21" value="<?php echo $after_21;?>"/>
		</td>
	</tr>
	<tr>
		<th scope="row">Final reminder and account disabled</th>
		<td>
		<input type="number" id="after_30" name="after_30" value="<?php echo $after_30;?>"/>
		</td>
	</tr>
	</tbody>
</table>
