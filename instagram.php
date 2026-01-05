<?php
/**
 * Plugin Name: Insta Feed BAF
 * Description: Menampilkan 4 postingan Instagram terbaru
 * Version: 2.0
 * Author: SERENOVA
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class InstaFeedBAF {
    private $access_token = 'IGAAdF10MVN6BBZAFQ0T0RtSTV4LV9WUXBtWTBEQ2ROczJnajdTYkZAzSWpqU3JhSFIwWTR2TU9lQjlIYTlXd3JvV0o1blcyRGxaQUZAiY2txTjQtZADFWUGFwRnhBdTkwbG94S0ZAnd0FaNTJZAczRIajJiaXRMeVJUYzB0U2h0S3U1QQZDZD'; 
    private $username_display = '@bafadventure';
    private $instagram_url = 'https://www.instagram.com/bafadventure/'; // Link profil

    public function __construct() {
        add_shortcode('my_insta_feed', array($this, 'render_feed'));
    }

    private function get_instagram_data() {
        $cached_data = get_transient('my_insta_feed_data');
        if (false !== $cached_data) {
            return $cached_data;
        }

        $url = "https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url&access_token={$this->access_token}&limit=4";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) return false;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['data'])) {
            set_transient('my_insta_feed_data', $data['data'], 3600);
            return $data['data'];
        }
        return false;
    }

    public function render_feed() {
        $posts = $this->get_instagram_data();
        if (!$posts) return '';

        ob_start();
        ?>
        
        <section class="baf-insta-wrapper">
            
            <div class="baf-insta-header">
                <h4 class="baf-subtitle">FOLLOW US ON INSTAGRAM</h4>
                <a href="<?php echo esc_url($this->instagram_url); ?>" target="_blank" class="baf-handle">
                    <?php echo esc_html($this->username_display); ?>
                </a>
            </div>

            <div class="baf-insta-grid">
                <?php foreach ($posts as $post): ?>
                    <?php 
                        $image_src = ($post['media_type'] == 'VIDEO') ? $post['thumbnail_url'] : $post['media_url']; 
                        $caption = isset($post['caption']) ? wp_trim_words($post['caption'], 8, '...') : '';
                    ?>
                    
                    <div class="baf-insta-card">
                        <a href="<?php echo esc_url($post['permalink']); ?>" target="_blank" class="baf-link">
                            <div class="baf-img-box">
                                <img src="<?php echo esc_url($image_src); ?>" alt="Instagram Post">
                            </div>
                            
                            <div class="baf-overlay">
                                <div class="baf-overlay-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-instagram"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                    
                                    <?php if($caption): ?>
                                        <p class="baf-caption"><?php echo esc_html($caption); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="baf-insta-footer">
                <a href="<?php echo esc_url($this->instagram_url); ?>" target="_blank" class="baf-btn-follow">
                    Ikuti Kami di Instagram
                </a>
            </div>

        </section>

        <style>
            /* Wrapper */
            .baf-insta-wrapper {
                padding: 40px 0;
                font-family: inherit;
            }

            /* Header Styling */
            .baf-insta-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .baf-subtitle {
                color: #ff6600;
                font-size: 28px;
                text-transform: uppercase;
                letter-spacing: 2px;
                margin: 0;
                font-weight: 600;
            }
            .baf-handle {
                color: #999;
                text-decoration: none;
                font-size: 16px;
                transition: color 0.3s;
            }
            .baf-handle:hover {
                color: #ff9854;
            }

            /* Grid Layout */
            .baf-insta-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 15px;
            }

            /* Card Styling */
            .baf-insta-card {
                position: relative;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                aspect-ratio: 1/1;
                background: #f0f0f0;
            }

            .baf-link {
                display: block;
                width: 100%;
                height: 100%;
            }

            /* Image Styling */
            .baf-img-box {
                width: 100%;
                height: 100%;
            }
            .baf-img-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }

            .baf-insta-card:hover .baf-img-box img {
                transform: scale(1.1);
            }

            /* Overlay Styling */
            .baf-overlay {
                position: absolute;
                top: 0; 
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                opacity: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                transition: opacity 0.3s ease;
                padding: 15px;
            }

            /* Show Overlay on Hover */
            .baf-insta-card:hover .baf-overlay {
                opacity: 1;
            }

            .baf-overlay-content svg {
                color: #fff;
                width: 32px;
                height: 32px;
                margin-bottom: 10px;
            }

            .baf-caption {
                color: #fff;
                font-size: 13px;
                line-height: 1.4;
                margin: 0;
                font-weight: 400;
            }

            /* Footer / Button */
            .baf-insta-footer {
                text-align: center;
                margin-top: 30px;
            }
            .baf-btn-follow {
                display: inline-block;
                padding: 12px 30px;
                background-color: #ff6600;
                color: #fff;
                text-decoration: none;
                font-weight: 600;
                transition: background 0.3s;
                font-size: 14px;
            }
            .baf-btn-follow:hover {
                background-color: #ff9854;
                color: #fff;
            }

            /* Responsive */
            @media (max-width: 992px) {
                .baf-insta-grid { grid-template-columns: repeat(2, 1fr); }
            }
            @media (max-width: 480px) {
                .baf-insta-grid { grid-template-columns: repeat(1, 1fr); } /* 1 kolom di HP kecil */
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

new InstaFeedBAF();