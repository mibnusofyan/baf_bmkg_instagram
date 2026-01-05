<?php
/**
 * Plugin Name:       BAF Info Cuaca
 * Description:       Menampilkan data cuaca BMKG untuk Karangsalam Lor (33.02.22.2009) via shortcode [cuaca_baf].
 * Version:           3.0
 * Author:            SERENOVA
 */

if (!defined('ABSPATH')) {
    exit; // Mencegah akses file langsung
}

// Mendaftarkan shortcode [cuaca_baf]
function baf_register_mockup_weather_shortcode()
{
    add_shortcode('cuaca_baf', 'baf_fetch_mockup_weather_display');
}
add_action('init', 'baf_register_mockup_weather_shortcode');

// Bypass page cache untuk halaman yang mengandung shortcode cuaca
function baf_bypass_cache_for_weather()
{
    global $post;
    
    // Cek apakah halaman mengandung shortcode [cuaca_baf]
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'cuaca_baf')) {
        // Mencegah page caching
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        // Header untuk mencegah browser cache
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
add_action('template_redirect', 'baf_bypass_cache_for_weather', 1);

// Enqueue CSS untuk widget cuaca
function baf_enqueue_weather_styles()
{
    wp_enqueue_style('baf-weather-style', false);
    wp_add_inline_style('baf-weather-style', '
        .baf-weather-container-mockup {
            width: 150px; 
            padding: 10px;
            position: relative;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .baf-weather-container-mockup .weather-block {
            line-height: 1.1;
        }

        .baf-weather-container-mockup .label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-align: left;
        }

        .baf-weather-container-mockup .temp .label,
        .baf-weather-container-mockup .humid .label,
        .baf-weather-container-mockup .status .label {
            color: #FFFFFF;
        }

        .baf-weather-container-mockup .value {
            display: block;
            font-size: 64px;
            font-weight: 700;
            color: #FFFFFF;
            text-align: left;
        }

        .baf-weather-container-mockup.error {
            background: #a00;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
        }
    ');
}
add_action('wp_enqueue_scripts', 'baf_enqueue_weather_styles');

// mengambil, mem-cache, dan menampilkan data
function baf_fetch_mockup_weather_display()
{

    $transient_name = 'baf_weather_cache_adm4';
    $cached_data = get_transient($transient_name);
    $output_html = '';

    // Logika pengambilan data dari API atau Cache
    if (false === $cached_data) {
        $api_url = 'https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=33.02.22.2009';
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return '<div class="baf-weather-container-mockup error">Gagal koneksi ke BMKG.</div>';
        }

        $weather_data_json = wp_remote_retrieve_body($response);
        set_transient($transient_name, $weather_data_json, 300); // Simpan 5 menit
        $data_to_process = $weather_data_json;
    } else {
        $data_to_process = $cached_data;
    }

    // Memproses data JSON
    $data = json_decode($data_to_process, true);

    if (empty($data) || !isset($data['data'][0]['cuaca'][0][0])) {
        delete_transient($transient_name);
        return '<div class="baf-weather-container-mockup error">Data cuaca tidak valid.</div>';
    }

    // Membuat HTML output SESUAI DESAIN MOCKUP
    try {
        $prakiraan_terkini = $data['data'][0]['cuaca'][0][0];
        $temp_param = $prakiraan_terkini['t'];
        $humid_param = $prakiraan_terkini['hu'];
        $weather_param = isset($prakiraan_terkini['weather_desc']) ? $prakiraan_terkini['weather_desc'] : 
                         (isset($prakiraan_terkini['weather']) ? $prakiraan_terkini['weather'] : 'N/A');

        $output_html = "
            <div class='baf-weather-container-mockup'>
                <div class='weather-block temp'>
                    <span class='label'>SUHU</span>
                    <span class='value'>{$temp_param}&deg;</span>
                </div>
                <div class='weather-block humid'>
                    <span class='label'>KELEMBAPAN</span>
                    <span class='value'>{$humid_param}%</span>
                </div>
                <div class='weather-block status'>
                    <span class='label'>CUACA</span>
                    <span class='value'>{$weather_param}</span>
                </div>
            </div>
        ";
    } catch (Exception $e) {
        $output_html = "<div class='baf-weather-container-mockup error'>Gagal proses data.</div>";
    }

    return $output_html;
}
?>