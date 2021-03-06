<?php
/**
 * Class and Function List:
 * Function list:
 * - lsdc_product_description_header()
 * - lsdc_product_description_tab()
 * - lsdc_force_redirect_member()
 * - lsdc_shipping_physical_control()
 * - lsdc_shipping_physical_services()
 * - lsdc_shipping_starter_calculation()
 * Classes list:
 */
/**
 * Set Description Tabs
 */
function lsdc_product_description_header()
{
    $lsdc_product_tab_public = array(
        'description' => __('Deskripsi', 'lsdcommerce')
    );
    $lsdc_product_tab_public = array_reverse($lsdc_product_tab_public);
    if (has_filter('lsdcommerce_product_tabs_header'))
    {
        $lsdc_product_tab_public = apply_filters('lsdcommerce_product_tabs_header', $lsdc_product_tab_public);
    }
    return array_reverse($lsdc_product_tab_public);
}

function lsdc_product_description_tab()
{
?>
        <div class="lsdc-nav-tab">
            <?php $count = 0;
    foreach (lsdc_product_description_header() as $key => $item): ?>
                <a data-target="<?php echo $key; ?>" data-toggle="tab" class="nav-link <?php echo ($count == 0) ? 'active' : ''; ?>"><?php echo $item; ?></a>
            <?php $count++;
    endforeach; ?>
        </div>

        <div class="lsdc-tab-content py-10 px-10">
            <div class="tab-pane show" data-tab="description">
                <?php the_content(); ?>
            </div>
            <?php do_action('lsdcommerce_single_tabs_content') ?>
        </div>
    <?php
}
add_action('lsdcommerce_single_tabs', 'lsdc_product_description_tab'); //Single Tabs

/**
 * Auto Redirect wp-admin to member
 * Restrict wp-admin
 */
function lsdc_force_redirect_member()
{
    if (!current_user_can('manage_options') && (!wp_doing_ajax()))
    {
        wp_safe_redirect(get_permalink(lsdc_admin_get('general_settings', 'member_area'))); // Replace this with the URL to redirect to.
        exit;
    }
}
add_action('admin_init', 'lsdc_force_redirect_member', 1);

/**
 * Set Price Functions
 */
add_action('lsdcommerce_listing_price_hook', 'lsdc_price_frontend');
add_action('lsdcommerce_single_price', 'lsdc_price_frontend');

/**
 * Function to add State, City and Address for Shipping
 *
 * This function will provide interface for getting shipping address,
 * you can trigger to load any available package shipping with javascript
 * The adress will be default same address like store its mean, local shipment
 * and if you change the city, the option package will be loaded.
 *
 * @package LSDCommerce
 * @subpackage Shipping
 *
 * @link https://docs.lsdplugins.com/en/docs/shipping-physical-target/
 * @since 1.0.0
 * @param action lsdcommerce_shipping_physical_control
 */
function lsdc_shipping_physical_control()
{ ?>
    <p class="lsdp-mb-5"><?php _e("Alamat Pengiriman", 'lsdcommerce'); ?></p>
    <?php
    $store_settings = get_option('lsdcommerce_store_settings');
    $country_selected = isset($store_settings['lsdc_store_country']) ? esc_attr($store_settings['lsdc_store_country']) : 'ID';
    $state_selected = isset($store_settings['lsdc_store_state']) ? esc_attr($store_settings['lsdc_store_state']) : 3;
    $city_selected = isset($store_settings['lsdc_store_city']) ? esc_attr($store_settings['lsdc_store_city']) : 455;
    $address_selected = isset($store_settings['lsdc_store_address']) ? esc_attr($store_settings['lsdc_store_address']) : '';
    $postalcode_selected = isset($store_settings['lsdc_store_postalcode']) ? esc_attr($store_settings['lsdc_store_postalcode']) : '';

    $currency_selected = isset($store_settings['lsdc_store_currency']) ? esc_attr($store_settings['lsdc_store_currency']) : 'IDR';

    if ($country_selected)
    {
        $states = json_decode(file_get_contents(LSDC_PATH . 'assets/cache/' . $country_selected . '-states.json'));
        $cities = json_decode(file_get_contents(LSDC_PATH . 'assets/cache/' . $country_selected . '-cities.json'));
    }
    else
    {
        $states = json_decode(file_get_contents(LSDC_PATH . 'assets/cache/ID-states.json'));
        $cities = json_decode(file_get_contents(LSDC_PATH . 'assets/cache/ID-cities.json'));
    }
?>
    <input type="text" id="country" value="ID" class="hidden">
    <div class="lsdp-row no-gutters">
        <div class="col-6">
            <div class="form-group">
                <select class="form-control custom-select swiper-no-swiping shipping-reset" id="states">  <!-- lsdcommerce-admin.js onChange trigger result Cities -->
                    <?php foreach ($states as $key => $state): ?>
                        <option value="<?php echo $state->province_id; ?>"  <?php echo ($state->province_id == $state_selected) ? 'selected' : ''; ?>><?php echo $state->province; ?></option>
                    <?php
    endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <select class="form-control custom-select swiper-no-swiping shipping-reset" id="cities">  
                <option value=""><?php _e("Pilih Kota", 'lsdcommerce'); ?></option>
                <?php foreach ($cities as $key => $city): ?>
                    <?php if ($city->province_id == $state_selected): ?>
                        <option value="<?php echo $city->city_id; ?>"><?php echo $city->type . ' ' . $city->city_name; ?></option>
                    <?php
        endif; ?>
                <?php
    endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <!-- <div class="form-group">
        <select name="" class="form-control custom-select">
            <option>Pasar Kemis</option>
        </select>
    </div> -->
    <div class="form-group">
        <textarea id="shipping_address" class="form-control swiper-no-swiping" placeholder="Alamat"></textarea>
    </div>
    <?php
}
add_action('lsdcommerce_shipping_physical_control', 'lsdc_shipping_physical_control');

/**
 * Function to get any packages available based on location
 * When : Checkout load on First
 *
 * This function will provide available shipping packages
 * will load any enable shipping method, and calc cost based on location
 * and display option to user.
 * Algorithm
 * - Load Every Shipping Method with Status Enable
 * - Calculate Every Shipping Cost based on Local Location
 * - Sort by Cheaper
 * - Display to User
 *
 * @package LSDCommerce
 * @subpackage Shipping
 *
 * @link https://docs.lsdplugins.com/en/docs/shipping-physical-target/
 * @since 1.0.0
 * @param action lsdcommerce_shipping_physical_control
 */
function lsdc_shipping_physical_services()
{
    $base = lsdc_get_store('city');
    $target = lsdc_get_store('city');

    global $lsdcommerce_shippings;
    $shipping_physical_results = array();
    if (isset($lsdcommerce_shippings))
    {
        foreach ($lsdcommerce_shippings as $key => $class)
        {
            $object = new $class;
            if ($object->type == 'physical')
            {
                if ($object->get_status() == 'on')
                {
                    echo $object->shipping_list($shipping_data);
                }
            }
        }
    }

    lsdc_array_sort_bykey($shipping_option, "cost");
?>

    <?php $index = 0;
    foreach ($shipping_option as $key => $item): ?>
        <div class="col-auto col-6 swiper-no-swiping">
            <div class="lsdp-form-group">
                <div class="item-radio">
                    <input type="radio" name="physical_courier" id="<?php echo $key; ?>" <?php echo $index == 0 ? 'checked' : ''; ?>>
                    <label for="<?php echo $key; ?>">
                        <img src="<?php echo $item['logo']; ?>" alt="<?php echo $item['label']; ?>">
                        <h6><?php echo $item['label']; ?></h6>
                        <p><?php echo $item['cost'] == 0 ? __('Gratis', 'lsdcommerce') : lsdc_currency_format(true, $item['cost']); ?></p>
                    </label>
                </div>
            </div>
        </div>
    <?php $index++;
    endforeach; ?>

<?php
}
// add_action( 'lsdcommerce_shipping_physical_services', 'lsdc_shipping_physical_services' );
// Load Unique Code by Seesion id in Browser, if Change the Uniqe Code will be change to
function lsdc_shipping_starter_calculation($extras)
{
    if (isset($extras['extras']['shipping']['physical']))
    {
        $physical = $extras['extras']['shipping']['physical'];
        $city = $physical['city'];
        $service = $physical['service'];
        $state = $physical['state'];

        // Automatic Get Weight in LSDCommerce Order Proccessing
        if (isset($extras['extras']['shipping']['weights']))
        {
            $weights = $extras['extras']['shipping']['weights'];
        }
        $extras['extras']['shipping']['weights'] = $weights;

        $detail = array( // Digital Courrier ID
            'destination' => $city,
            'weight' => $weights,
            'service' => $service
            //
            
        );

        $clean = lsdc_shipping_rajaongkir_starter_calc($detail);
        $extras = array_merge($extras, $clean);
    }
    return $extras;
}
add_filter('lsdcommerce_payment_extras', 'lsdc_shipping_starter_calculation');
?>