<?php
$lc_admin_id = ifsa_lc()->get_settings_lc_admin_id();
if (!$lc_admin_id) return; // User cannot access this page

$lc_members = ifsa_lc()->list_lc_members($lc_admin_id, IFSA_PENDING);

echo ifsa_utility()->insert_nonce("lc_admin_approval");

?>
<aside class="bp-feedback bp-messages ifsa-response-approve" style="visibility: hidden;">
    <span class="bp-icon" aria-hidden="true"></span>
    <p class="ifsa_approve_p"></p>
</aside>
<aside class="bp-feedback bp-messages ifsa-response-reject" style="visibility: hidden;">
    <span class="bp-icon" aria-hidden="true"></span>
    <p class="ifsa_reject_p"></p>
</aside>
<p>The list of all pending requests for the local committee will be listed out here.</p>
<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="ul_reason">
            <p>Please select reason:</p>
            <input type="radio" id="membership_expire" name="reason"
                   value="Your LC Membership has expired.">
            <label for="membership_expire">LC membership expired</label></br>
            <input type="radio" id="never_member" name="reason" value="You have never been an LC Member">
            <label for="You have never been an LC Member">The applicant user has never been an LC
                member</label></br>
            <input type="radio" id="other" name="reason" value="other">

            <label for="other">Other - open answer</label><br/>
            <input type="text" id="other_reason" maxlength="100" hidden="true"/>
            <a href="javascript:void(0)" row-id="" data-id="" id="final_reject"
               class="final_reject">Confirm</a>
            <div id="ifsa-loading-reject" style="display:none;">
                <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                     title="loading"/>
            </div>
        </div>

    </div>

</div>
<table id="table_id" class="display">
    <thead>
    <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Source</th>
        <th>Member Type</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($lc_members as $lc_member) {
        $the_user = get_user_by('id', $lc_member->user_id);
        $email = $the_user->user_email;
        $name = $the_user->first_name;
        $surname = $the_user->last_name;

        $renew_request = get_user_meta($lc_member->user_id, 'ifsa_renew_request', true);
        if ($renew_request == '1') {
            $renew_request = 'Renewal';
        } else {
            $renew_request = 'New';
        }
        ?>

        <tr>
            <td data-title="First Name"><?php esc_html_e($name, 'ifsa-member-verification'); ?></td>
            <td data-title="Last Name"> <?php esc_html_e($surname, 'ifsa-member-verification'); ?></td>
            <td data-title="Email"><?php esc_html_e($email, 'ifsa-member-verification'); ?></td>
            <td data-title="Source"><?php esc_html_e($lc_member->source, 'ifsa-member-verification'); ?></td>
            <td data-title="Member Type"> <?php esc_html_e($renew_request, 'ifsa-member-verification'); ?></td>

            <?php if ($lc_member->member_status == 0 && $lc_member->reason == '') { //TODO check this condition?>
                <td data-title="Action">
                    <div class="<?php esc_attr_e('cls-action-' . $lc_member->id); ?>">
                        <a
                            href="javascript:void(0)"
                            data-user-id="<?php esc_attr_e($lc_member->user_id); ?>"
                            class="cls-reject">Reject </a>
                        <a href="javascript:void(0)"
                           data-user-id="<?php esc_attr_e($lc_member->user_id); ?>"
                           class="cls-approve"> Approve</a></div>
                    <div id="<?php esc_attr_e('ifsa-loading-' . $lc_member->id ); ?>"
                         style="display:none;">
                        <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                             title="loading"/>
                    </div>
                </td>
            <?php } else if ($lc_member->member_status == 0 && $lc_member->reason != '') {
                ?>
                <td data-title="Action"> Rejected</td>
            <?php } else if ($lc_member->member_status == 1 && $lc_member->reason == '') {
                ?>
                <td data-title="Action"> Approved</td>
            <?php } ?>
        </tr>

    <?php } ?>

    </tbody>
</table>