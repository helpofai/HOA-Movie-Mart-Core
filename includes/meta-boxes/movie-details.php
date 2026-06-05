<?php
/**
 * Add Meta Boxes for Movie Details
 */

function helpofai_add_movie_meta_boxes() {
    add_meta_box(
        'movie_details',
        __( 'Movie Information & Downloads', 'helpofai' ),
        'helpofai_movie_details_callback',
        'movie',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'helpofai_add_movie_meta_boxes' );

/**
 * Enqueue Admin Assets for Movie Post Type
 */
function helpofai_movie_admin_assets( $hook ) {
    global $post;
    
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'movie' === get_post_type( $post ) ) {
        wp_enqueue_style( 'hoa-admin-movie-css', get_template_directory_uri() . '/assets/css/admin-movie.css', array(), '1.1' );
        wp_enqueue_script( 'hoa-admin-movie-js', get_template_directory_uri() . '/assets/js/admin-movie.js', array( 'jquery' ), '1.1', true );
        
        // Pass nonce to admin editor
        wp_localize_script( 'hoa-admin-movie-js', 'hoa_vars', array(
            'nonce' => wp_create_nonce( 'hoa_download_nonce' )
        ));
    }
}
add_action( 'admin_enqueue_scripts', 'helpofai_movie_admin_assets' );

/**
 * Render Meta Box
 */
function helpofai_movie_details_callback( $post ) {
    wp_nonce_field( 'helpofai_save_movie_details', 'helpofai_movie_details_nonce' );

    $imdb_rating = get_post_meta( $post->ID, '_movie_imdb_rating', true );
    $runtime     = get_post_meta( $post->ID, '_movie_runtime', true );
    $trailer_url = get_post_meta( $post->ID, '_movie_trailer_url', true );
    $language    = get_post_meta( $post->ID, '_movie_language', true );
    
    // Retrieve Download Links (Handle legacy and new JSON)
    $download_links = get_post_meta( $post->ID, '_movie_download_links_json', true );
    
    // Fallback for legacy data
    if ( empty( $download_links ) ) {
        $legacy_720 = get_post_meta( $post->ID, '_movie_download_link_720p', true );
        $legacy_1080 = get_post_meta( $post->ID, '_movie_download_link_1080p', true );
        $download_links = array();
        if ( $legacy_720 ) $download_links[] = array( 'label' => 'Download 720p', 'url' => $legacy_720, 'quality' => '720p' );
        if ( $legacy_1080 ) $download_links[] = array( 'label' => 'Download 1080p', 'url' => $legacy_1080, 'quality' => '1080p' );
    }
    ?>
    <div class="hoa-meta-wrapper">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <div class="hoa-form-row">
                    <label for="movie_imdb_rating"><?php _e( 'IMDb Rating', 'helpofai' ); ?></label>
                    <input type="text" id="movie_imdb_rating" name="movie_imdb_rating" value="<?php echo esc_attr( $imdb_rating ); ?>" class="widefat" placeholder="e.g. 8.5">
                </div>
                <div class="hoa-form-row">
                    <label for="movie_runtime"><?php _e( 'Runtime', 'helpofai' ); ?></label>
                    <input type="text" id="movie_runtime" name="movie_runtime" value="<?php echo esc_attr( $runtime ); ?>" class="widefat" placeholder="e.g. 120 min">
                </div>
            </div>
            <div>
                <div class="hoa-form-row">
                    <label for="movie_language"><?php _e( 'Language', 'helpofai' ); ?></label>
                    <input type="text" id="movie_language" name="movie_language" value="<?php echo esc_attr( $language ); ?>" class="widefat" placeholder="e.g. English, Spanish">
                </div>
                 <div class="hoa-form-row">
                    <label for="movie_trailer_url"><?php _e( 'Trailer URL (YouTube/Vimeo)', 'helpofai' ); ?></label>
                    <input type="url" id="movie_trailer_url" name="movie_trailer_url" value="<?php echo esc_url( $trailer_url ); ?>" class="widefat">
                </div>
            </div>
        </div>

        <hr>

        <div class="hoa-form-row" style="background: #fff5f5; padding: 15px; border: 1px solid #fed7d7; border-radius: 6px;">
            <label style="color: #c53030; font-weight: 700; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="movie_dmca_active" value="1" <?php checked( get_post_meta( $post->ID, '_movie_dmca_active', true ), '1' ); ?>>
                <?php _e( '⚠️ DMCA Takedown Active (Hide Download Links)', 'helpofai' ); ?>
            </label>
            <p class="description" style="margin-top: 5px;"><?php _e( 'If enabled, all download links will be hidden and replaced with a professional copyright notice. The page stays live for SEO.', 'helpofai' ); ?></p>
        </div>

        <hr>

        <h3><?php _e( 'Download Links', 'helpofai' ); ?></h3>
        <p class="description"><?php _e( 'Add multiple download sources below.', 'helpofai' ); ?></p>
        
        <div id="hoa-download-repeater" class="hoa-download-repeater">
            <?php 
            if ( ! empty( $download_links ) && is_array( $download_links ) ) :
                foreach ( $download_links as $index => $link ) : 
                    $label = isset( $link['label'] ) ? $link['label'] : '';
                    $encoded_url = isset( $link['url'] ) ? $link['url'] : '';
                    // Decrypt for Admin view
                    $url = function_exists('hoa_decrypt_url') ? hoa_decrypt_url($encoded_url) : $encoded_url;
                    $quality = isset( $link['quality'] ) ? $link['quality'] : '';
            ?>
                <div class="hoa-download-row">
                    <input type="text" name="download_links[<?php echo $index; ?>][label]" value="<?php echo esc_attr( $label ); ?>" placeholder="Server" style="flex:1">
                    <input type="text" name="download_links[<?php echo $index; ?>][quality]" value="<?php echo esc_attr( $quality ); ?>" placeholder="720p" style="width: 80px;">
                    <input type="text" name="download_links[<?php echo $index; ?>][size]" value="<?php echo isset($link['size']) ? esc_attr($link['size']) : ''; ?>" placeholder="1.2 GB" style="width: 80px;">
                    <input type="url" name="download_links[<?php echo $index; ?>][url]" value="<?php echo esc_url( $url ); ?>" placeholder="https://..." style="flex:2">
                    <button type="button" class="button hoa-row-remove"><span class="dashicons dashicons-trash" style="margin-top: 4px;"></span></button>
                </div>
            <?php 
                endforeach; 
            endif;
            ?>
        </div>
        <button type="button" id="hoa-add-download-row" class="button button-secondary" style="margin-top: 10px;"><?php _e( '+ Add Download Link', 'helpofai' ); ?></button>

        <hr style="margin: 25px 0;">
        <h3><?php _e( 'Social Actions', 'helpofai' ); ?></h3>
        <div class="hoa-social-actions">
            <button type="button" id="hoa-share-telegram" class="button button-primary" style="background: #0088cc; border-color: #0088cc;">
                <i class="dashicons dashicons-share-alt" style="margin-top:4px;"></i> <?php _e( '🚀 Share to Telegram', 'helpofai' ); ?>
            </button>
            <span id="hoa-telegram-status" style="margin-left: 10px; font-weight: 600;"></span>
        </div>
    </div>
    <?php
}

/**
 * Save Meta Box Data
 */
function helpofai_save_movie_details( $post_id ) {
    if ( ! isset( $_POST['helpofai_movie_details_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['helpofai_movie_details_nonce'], 'helpofai_save_movie_details' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save Simple Fields
    $fields = array(
        'movie_imdb_rating' => '_movie_imdb_rating',
        'movie_runtime'     => '_movie_runtime',
        'movie_trailer_url' => '_movie_trailer_url',
        'movie_language'    => '_movie_language',
        'movie_dmca_active' => '_movie_dmca_active',
    );

    foreach ( $fields as $key => $meta_key ) {
        if ( isset( $_POST[ $key ] ) ) {
            $value = ( $key === 'movie_dmca_active' ) ? '1' : sanitize_text_field( $_POST[ $key ] );
            update_post_meta( $post_id, $meta_key, $value );
        } else {
            if ( $key === 'movie_dmca_active' ) {
                delete_post_meta( $post_id, $meta_key );
            }
        }
    }

    // Save Repeater (Download Links)
    if ( isset( $_POST['download_links'] ) && is_array( $_POST['download_links'] ) ) {
        $links = array();
        foreach ( $_POST['download_links'] as $link ) {
            if ( ! empty( $link['url'] ) ) {
                $raw_url = esc_url_raw( $link['url'] );
                // Apply Advanced Masking if helper exists
                $masked_url = function_exists('hoa_encrypt_url') ? hoa_encrypt_url($raw_url) : $raw_url;
                
                $links[] = array(
                    'label'   => sanitize_text_field( $link['label'] ),
                    'quality' => sanitize_text_field( $link['quality'] ),
                    'size'    => sanitize_text_field( $link['size'] ),
                    'url'     => $masked_url
                );
            }
        }
        update_post_meta( $post_id, '_movie_download_links_json', $links );

        // Maintain Backwards Compatibility for Theme Template
        if ( isset( $links[0] ) ) update_post_meta( $post_id, '_movie_download_link_720p', $links[0]['url'] );
        if ( isset( $links[1] ) ) update_post_meta( $post_id, '_movie_download_link_1080p', $links[1]['url'] );
    } else {
        // If all rows removed, delete meta
        delete_post_meta( $post_id, '_movie_download_links_json' );
        delete_post_meta( $post_id, '_movie_download_link_720p' );
        delete_post_meta( $post_id, '_movie_download_link_1080p' );
    }
}
add_action( 'save_post', 'helpofai_save_movie_details' );