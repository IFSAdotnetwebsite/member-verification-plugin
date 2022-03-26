<?php
$lc_admin_id = ifsa_lc()->get_settings_lc_admin_id();
if(!$lc_admin_id) return; // User cannot access this page

$lc_members = ifsa_lc()->list_lc_members($lc_admin_id, IFSA_VERIFIED);
    ?>

    <h2>Active Member List</h2>
    <p>You can see list of all members who are currently active in your Local Committee.</p>
    <aside class="bp-feedback bp-messages ifsa-response-reject" style="visibility: hidden;">
        <span class="bp-icon" aria-hidden="true"></span>
        <p class="ifsa_reject_p"></p>
    </aside>
    <!-- The Modal -->
    <div id="myModal_remove" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <span class="close-r">&times;</span>
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
                <a href="javascript:void(0)" row-id="" data-id="" id="final_remove"
                   class="final_remove">Confirm</a>
                <div id="ifsa-loading-remove" style="display:none;">
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
            <th>Status</th>
            <th>Member Type</th>
            <th>Verification date</th>
            <th>Profile Link</th>
            <th>Action</th>

        </tr>
        </thead>
        <tbody>
        <?php
        if($lc_members){
        foreach ($lc_members as $lc_member) {

            $the_user = get_user_by('id', $lc_member->user_id);
            $email = $the_user->user_email;
            $Name = $the_user->first_name;
            $Surname = $the_user->last_name;
            $profileURL = bp_core_get_user_domain($lc_member->user_id);
            $renew_request = get_user_meta($lc_member->user_id, 'ifsa_renew_request', true);
            if ($renew_request == '1') {
                $renew_request = 'Renewal';
            } else {
                $renew_request = 'New';
            }
            ?>

            <tr>
                <td data-title="First Name"><?php  esc_html_e($Name, 'Ifsa_Member_Verification'); ?></td>
                <td data-title="Last Name"><?php  esc_html_e($Surname, 'Ifsa_Member_Verification'); ?></td>
                <td data-title="Email"><?php  esc_html_e($email, 'Ifsa_Member_Verification'); ?></td>
                <td data-title="Source"><?php  esc_html_e($lc_member->source, 'Ifsa_Member_Verification'); ?></td>
                <td data-title="Status"> Approved</td>
                <td data-title="Member Type"> <?php  esc_html_e($renew_request, 'Ifsa_Member_Verification'); ?></td>

                <td data-title="Verification date"><?php
                    $lc_member->action_date = date('F j, Y', strtotime($lc_member->action_date));
                    esc_html_e(date('F j, Y', strtotime($lc_member->action_date)), 'Ifsa_Member_Verification'); ?></td>
                <td data-title="Profile Link"><a href="<?php  esc_url($profileURL); ?>">Profile</a></td>
                <td data-title="Action">
                    <div
                            class="<?php  esc_attr('cls-action-remove' . $lc_member->id, 'Ifsa_Member_Verification') . ''; ?>">
                        <a href="javascript:void(0)"
                           row-id="<?php  esc_html_e($lc_member->id, 'Ifsa_Member_Verification'); ?>"
                           data-id="<?php  esc_html_e($lc_member->user_id, 'Ifsa_Member_Verification'); ?>"
                           class="cls-remove">Remove </a>

                    </div>
                    <div
                            id="<?php  esc_attr('ifsa-loading-' . $lc_member->id . '', 'Ifsa_Member_Verification'); ?>"
                            style="display:none;">
                        <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                             title="loading"/>
                    </div>
                </td>
            </tr>

        <?php } ?>

        <?php } ?>

        </tbody>
    </table>
