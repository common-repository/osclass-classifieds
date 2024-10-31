<div class="osclass-welcome">
    Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.
</div>

<div class="osclass-actions">
    <div class="osclass-action"><a href="<?php echo osclass_oc_item_new_url(); ?>"><?php _e('Add new listing', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_search_url(); ?>"><?php _e('Browse ads', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_search_filter_url(); ?>"><?php _e('Search ads', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_categories_url(); ?>"><?php _e('View categories', 'osclass-classifieds'); ?></a></div>
    <?php if(osclass_oc_is_web_user_logged_in()) { ?>
    <div class="osclass-action"><a href="<?php echo osclass_oc_my_account_url(); ?>"><?php _e('My account', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_logout_url(); ?>"><?php _e('Logout', 'osclass-classifieds'); ?></a></div>
    <?php } else { ?>
    <div class="osclass-action"><a href="<?php echo osclass_oc_login_url(); ?>"><?php _e('Login', 'osclass-classifieds'); ?></a></div>
    <?php } ?>
</div>