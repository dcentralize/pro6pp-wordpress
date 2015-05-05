<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly

// Determine/Define the current (active) tab.
if (! empty($_GET['tab']) && array_key_exists($_GET['tab'], $tabs))
    $currentTab = urldecode($_GET['tab']);
else
    $currentTab = 'connection';

?>
<div class="wrap woocommerce">
  <form method="post" id="mainform" action="options.php"
    enctype="multipart/form-data">
    <div id="icon-woocommerce" class="icon32-woocommerce-settings icon32">
      <br />
    </div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
<?php
// Print the tab menu
foreach ($tabs as $page => $fields) :
    // Define the css class for active/inactive tab.
    $class = ($page === $currentTab) ? 'nav-tab nav-tab-active' : 'nav-tab';
    // Define the page link
    $url = esc_url(add_query_arg(
        'tab',
        $page,
        admin_url('admin.php?page=pro6pp_autocomplete')
    ));
    // Print the header
    printf('<a href="%s" class="%s">%s</a>', $url, $class, ucfirst($page));
endforeach;
?>
    </h2>
<?php
// Display message on save.
if (isset($_GET['settings-updated']) && ! empty($_GET['settings-updated']) && $currentTab == 'configuration') :
    ?>
    <div id="message" class="updated fade below-h2">
      <p>
        <strong>
        <?php
            echo __('Your settings have been saved.', 'pro6pp_autocomplete');
            ?>
        </strong>
      </p>
    </div>
<?php
endif;
// Print the security fields for that group.
settings_fields($tabs[$currentTab]['group']);
// Print the fields
do_settings_sections($tabs[$currentTab]['section']);

// Print the submit button.
submit_button();
?>
</form>
</div>