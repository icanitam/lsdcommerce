<?php 
/**
 * Appeareance Settings
 * Font, Background, Theme
 */
$settings = get_option('lsdc_appearance_settings');
?>

<div class="entry columns col-gapless">
  <div class="column col-9">

    <section id="appearance" class="form-horizontal" style="padding: .4rem 10px;">
      <form>

        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="fontlist"><?php _e( 'Font', 'lsdcommerce' ); ?></label>
          </div>
          <div class="col-4 col-sm-12" style="padding-bottom:10px;">
            <select class="form-select" id="fontlist" name="lsdc_fontlist">
              <option>Poppins</option>
            </select>
            <div id="selectedfont" class="hidden"><?php esc_attr_e( ( empty($settings['lsdc_fontlist']) ) ? 'Poppins' : $settings['lsdc_fontlist'] ); ?></div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="bg-color"><?php _e( 'Background Color', 'lsdcommerce' ); ?></label>
          </div>
          <div class="col-9 col-sm-12" style="line-height:0;">
            <input type="text" name="lsdc_bgtheme_color" value="<?php esc_attr_e( $settings['lsdc_bgtheme_color'] ); ?>" class="lsdc-color-picker"> 
            <div class="color-picker" style="display: inline-block;z-index:999;"></div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="theme-color"><?php _e( 'Theme Color', 'lsdcommerce' ); ?></label>
          </div>
          <div class="col-9 col-sm-12" style="line-height:0;">
            <input type="text" name="lsdc_theme_color" value="<?php esc_attr_e( $settings['lsdc_theme_color'] ); ?>" class="lsdc-color-picker"> 
            <div class="color-picker" style="display: inline-block;z-index:999;"></div>
          </div>
        </div>

        <ul class="general-menu">
        <?php 
          foreach( LSDCommerce_Admin::lsdc_appearance_switch_option() as $key => $menu) :
            if( isset($settings[$key] ) ) : // if Option Exist ?>
              <li>
                <label class="form-switch">
                  <input name="<?php esc_attr_e( $key ); ?>" id="<?php esc_attr_e( $key ); ?>" type="checkbox" <?php echo ( $settings[$key] == 'on' ) ? 'checked="checked"' : ''; ?>>
                  <i class="form-icon"></i><?php esc_attr_e( $menu[0] ); ?>
                </label>
              </li>
            <?php else: ?>
              <li>
                <small style="float:right;"><?php esc_attr_e( $menu[1] ); ?></small>
                <label class="form-switch">
                  <input name="<?php esc_attr_e( $key ); ?>" id="<?php esc_attr_e( $key ); ?>" type="checkbox">
                  <i class="form-icon"></i><?php esc_attr_e( $menu[0] ); ?>
                </label>
                
              </li>
            <?php endif;
          endforeach;
        ?>
        </ul>

        <br>
      </form>
      <button class="btn btn-primary" id="lsdc_admin_appearance_save" style="width:120px"><?php _e( 'Save', 'lsdcommerce' ); ?></button> <!-- lsdconation-admin.js on Click Saving -->
    </section>

  </div>
</div>

<div class="column col-3">
    <!-- <h6>Shortcode <a class="btn btn-primary btn-sm float-right" target="_blank" href="https://docs.lsdplugins.com/" ><?php //_e( 'Learn Shortcode', 'lsdcommerce' ); ?></a></h6> -->
    <?php do_action( 'lsdc_shortcode_hook' ); ?>
</div>

<script>
  if( localStorage.getItem("lsdc_font_cache") == null || localStorage.getItem("lsdc_font_cache") == '' ){
    jQuery.getJSON("https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyCoDdOKhPem_sbA-bDgJ_-4cVhJyekWk-U", function(fonts){
      var lsdc_font_cache = {};
      for (var i = 0; i < fonts.items.length; i++) {   
        lsdc_font_cache[fonts.items[i].family] = fonts.items[i].files.regular;
      }   
      localStorage.setItem("lsdc_font_cache", JSON.stringify(lsdc_font_cache)); 
    });
  }else{
    var lsdc_font_cache = JSON.parse(localStorage.getItem("lsdc_font_cache"));
    var selectedfont = jQuery('#selectedfont').text();
    jQuery.each(lsdc_font_cache, function(index, value) {
      jQuery('#fontlist')
         .remove("option")
         .append( jQuery( ( index == selectedfont ) ? "<option selected></option>" : "<option></option>" )
         .attr("value", index)
         .attr("style", "font-family:"+ index + "; font-size: 16px")
         .text(index));
    });  
  }
</script>