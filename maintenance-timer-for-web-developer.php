<?php
/*
Plugin Name: Maintenance Timer for Web Developer
Plugin URI: https://www.enzomele.it/
Description: Plugin to manage remaining maintenance days and notify users upon expiration.
Version: 2.0
Author: Enzo Mele
Author URI: https://enzomele.it
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action('plugins_loaded', 'custom_countdown_load_textdomain');
function custom_countdown_load_textdomain() {
    load_plugin_textdomain('maintenance-timer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('admin_menu', 'custom_countdown_menu');
function custom_countdown_menu()
{
    add_menu_page(
        __('Maintenance Countdown', 'maintenance-timer'),
        __('Maintenance Timer', 'maintenance-timer'),
        'manage_options',
        'countdown-settings',
        'custom_countdown_page'
    );
}

function custom_countdown_page()
{
    if (isset($_POST['countdown_expiry_date'])) {
        update_option('countdown_expiry_date', $_POST['countdown_expiry_date']);
        update_option('countdown_contact_name', $_POST['countdown_contact_name']);
        update_option('countdown_contact_link', $_POST['countdown_contact_link']);
    }

    $countdownExpiryDate = get_option('countdown_expiry_date', date('Y-m-d', strtotime('+365 days'))); // Default 365 days
    $countdownContactName = get_option('countdown_contact_name', 'Enzo Mele');
    $countdownContactLink = get_option('countdown_contact_link', 'https://enzomele.it');

?>
    <div class="wrap custom-countdown-settings">
        <h1 style="text-align: center;"><?php _e('Set Timer', 'maintenance-timer'); ?></h1>
        <style>
            .custom-countdown-settings {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
                border-radius: 8px;
            }

            #donate-button-container {
                text-align: center;
                margin-top: 20px;
            }

            #donate-button-container label {
                display: block;
                font-size: 18px;
                margin-bottom: 10px;
            }

            #donate-button {
                display: inline-block;
            }

            form {
                text-align: center;
            }

            form label,
            form input {
                display: block;
                margin-bottom: 10px;
                width: 80%;
                margin: 0 auto;
            }
			
input[type="submit"] {
        background-color: #003087; /* Colore blu simile a PayPal */
        color: #ffffff; /* Testo bianco */
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #001f5e; /* Cambia tonalità al passaggio del mouse */
    }

        </style>
      <form method="post">
            <label for="countdown_expiry_date" style="margin-bottom: 5px;"><?php _e('Select the date for maintenance expiration:', 'maintenance-timer'); ?></label>
            <input style="margin-top: 5px;" type="date" id="countdown_expiry_date" name="countdown_expiry_date" value="<?php echo esc_attr($countdownExpiryDate); ?>">

            <label for="countdown_contact_name" style="margin-bottom: 5px;"><?php _e('Contact Name for Renewal:', 'maintenance-timer'); ?></label>
            <input style="margin-top: 5px;" type="text" id="countdown_contact_name" name="countdown_contact_name" value="<?php echo esc_attr($countdownContactName); ?>">

            <label for="countdown_contact_link" style="margin-bottom: 5px;"><?php _e('Link for Renewal Contact:', 'maintenance-timer'); ?></label>
            <input style="margin-top: 5px;" type="text" id="countdown_contact_link" name="countdown_contact_link" value="<?php echo esc_attr($countdownContactLink); ?>">

            <input style="margin-top: 15px;" type="submit" value="<?php _e('Save', 'maintenance-timer'); ?>">
            <div id="donate-button-container">
                <label>❤️ Give me a coffee ☕</label>
                <div id="donate-button"></div>
                <script src="https://www.paypalobjects.com/donate/sdk/donate-sdk.js" charset="UTF-8"></script>
                <script>
                    PayPal.Donation.Button({
                        env: 'production',
                        hosted_button_id: 'CDQMKBGRHT3X4',
                        image: {
                            src: 'https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif',
                            alt: 'Donate with PayPal button',
                            title: 'PayPal - The safer, easier way to pay online!',
                        }
                    }).render('#donate-button');
                </script>
            </div>
        </form>
    </div>
<?php
}

add_action('admin_notices', 'display_countdown_notice');
function display_countdown_notice()
{
    $countdownExpiryDate = get_option('countdown_expiry_date', date('Y-m-d', strtotime('+365 days')));
    $countdownContactName = get_option('countdown_contact_name', 'Enzo Mele');
    $countdownContactLink = get_option('countdown_contact_link', 'https://enzomele.it');

    $expiryDate = new DateTime($countdownExpiryDate);
    $daysRemaining = $expiryDate->diff(new DateTime("now"))->format("%a");

    if ($expiryDate < new DateTime("now")) {
        echo '<div class="notice notice-error is-dismissible"><p><strong> ☹️ ' . esc_html__('MAINTENANCE ALERT:', 'maintenance-timer') . '</strong> ' . esc_html__('Contact', 'maintenance-timer') . ' <a href="' . esc_url($countdownContactLink) . '">' . esc_html($countdownContactName) . '</a> ' . esc_html__('to renew maintenance for', 'maintenance-timer') . ' ' . esc_html(get_bloginfo('name')) . '. </p> <p><strong>➡️ ' . esc_html__('Why is it essential to keep the site updated?', 'maintenance-timer') . '</strong><br>' .
        esc_html__('Updating WordPress, plugins, and the theme is crucial to', 'maintenance-timer') . ' <strong>' . esc_html__('ensure site security and proper functioning.', 'maintenance-timer') . '</strong><br>' .
        '<strong>' . esc_html__('Security', 'maintenance-timer') . '</strong> - ' . esc_html__('New versions resolve security issues and vulnerabilities (95% of malware-compromised sites are not updated!).', 'maintenance-timer') . '<br>' .
        '<strong>' . esc_html__('Functionality', 'maintenance-timer') . '</strong> - ' . esc_html__('Updates bring new features and support for the latest hosting and external services.', 'maintenance-timer') . '</p></div>';
    } else {
        echo '<div class="notice notice-success is-dismissible"><p><strong>😀 ' . esc_html__('Maintenance Ongoing:', 'maintenance-timer') . '</strong> ' . esc_html($daysRemaining) . ' ' . esc_html__('days remaining for maintenance on', 'maintenance-timer') . ' ' . esc_html(get_bloginfo('name')) . '. <br>➡️ ' . esc_html__('Contact', 'maintenance-timer') . ' <a href="' . esc_url($countdownContactLink) . '">' . esc_html($countdownContactName) . '</a> ' . esc_html__('for assistance or to renew maintenance.', 'maintenance-timer') . '</p></div>';
    }
}

add_action('wp_dashboard_setup', 'custom_countdown_dashboard_widget');
function custom_countdown_dashboard_widget()
{
    wp_add_dashboard_widget('custom_countdown_widget', __('Maintenance Countdown', 'maintenance-timer'), 'custom_countdown_dashboard_content');
}

function custom_countdown_dashboard_content()
{
    $countdownExpiryDate = get_option('countdown_expiry_date', date('Y-m-d', strtotime('+365 days')));
    $countdownContactName = get_option('countdown_contact_name', 'Enzo Mele');
    $countdownContactLink = get_option('countdown_contact_link', 'https://enzomele.it');

    $expiryDate = new DateTime($countdownExpiryDate);
    $difference = $expiryDate->getTimestamp() - (new DateTime())->getTimestamp();

    if ($difference <= 0) {
        echo '<p class="countdown_expired">➡️ ' . esc_html__('Contact', 'maintenance-timer') . ' <a href="' . esc_url($countdownContactLink) . '">' . esc_html($countdownContactName) . '</a> ' . esc_html__('to renew maintenance for', 'maintenance-timer') . ' ' . esc_html(get_bloginfo('name')) . '.</p>';
    } else {
        $days = floor($difference / (60 * 60 * 24));
        $hours = floor(($difference % (60 * 60 * 24)) / (60 * 60));
        $minutes = floor(($difference % (60 * 60)) / 60);
        $seconds = $difference % 60;

        echo '<p>' . esc_html__('Time remaining:', 'maintenance-timer') . ' ' . esc_html($days) . ' ' . esc_html__('days,', 'maintenance-timer') . ' ' . esc_html($hours) . ' ' . esc_html__('hours,', 'maintenance-timer') . ' ' . esc_html($minutes) . ' ' . esc_html__('minutes,', 'maintenance-timer') . ' ' . esc_html($seconds) . ' ' . esc_html__('seconds', 'maintenance-timer') . '</p>';
    }
}

register_uninstall_hook(__FILE__, 'custom_countdown_uninstall');
function custom_countdown_uninstall()
{
    delete_option('countdown_expiry_date');
    delete_option('countdown_contact_name');
    delete_option('countdown_contact_link');
    // Add any other necessary clean-up operations
}
