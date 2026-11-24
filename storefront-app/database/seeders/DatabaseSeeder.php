<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Repositories\AdminRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

# demo data: 1 admin, 1 customer, 13 products || run with: php artisan db:seed
# safe to re-run, skips anything that's already there
class DatabaseSeeder extends Seeder
{
    public function run(UserRepository $users, AdminRepository $admins, ProductRepository $products):void{
        if (!$admins->get_user_id_by_email('admin@store.test')) {
            $admins->insert_admin('admin@store.test', Hash::make('password123'), 'Store', 'Admin', 1001);
        }
        if (!$users->get_user_id_by_email('demo@store.test')) {
            $users->insert_user('demo@store.test', Hash::make('password123'), 'Demo', 'Customer');
        }

        if ($products->get_all_products()->isNotEmpty()) {
            return;
        }
        # [name, description, price in cents, stock, image, status]
        $catalog = [
            ['Mechanical Keyboard', 'Hot-swappable 75% keyboard with tactile brown switches, PBT keycaps and white backlight. USB-C, works on Windows and Mac.', 8999, 25, 'mechanical-keyboard', ProductStatus::ACTIVE],
            ['Wireless Mouse', 'Ergonomic 2.4GHz + Bluetooth mouse with a silent click, 4000 DPI sensor and up to 70 days of battery.', 2999, 40, 'wireless-mouse', ProductStatus::ACTIVE],
            ['USB-C Hub', '7-in-1 hub: 4K HDMI, 2x USB-A, USB-C PD 100W pass-through, SD and microSD readers.', 3499, 30, 'usb-c-hub', ProductStatus::ACTIVE],
            ['Noise-Cancelling Headphones', 'Over-ear Bluetooth headphones with active noise cancelling, 30 hour battery and a foldable case.', 14999, 15, 'noise-cancelling-headphones', ProductStatus::ACTIVE],
            ['27" 4K Monitor', '27 inch IPS 3840x2160 display, 99% sRGB, height-adjustable stand, USB-C with 65W charging.', 32999, 8, '27-inch-monitor', ProductStatus::ACTIVE],
            ['Aluminum Laptop Stand', 'Adjustable aluminum stand that raises your laptop to eye level. Fits 10-17 inch laptops and folds flat.', 3999, 35, 'laptop-stand', ProductStatus::ACTIVE],
            ['1080p Webcam', 'Full HD webcam with autofocus, dual noise-reducing mics and a privacy shutter.', 4999, 20, 'webcam', ProductStatus::ACTIVE],
            ['Felt Desk Mat', 'Large 90x40cm wool felt desk mat with a non-slip cork bottom. Charcoal gray.', 2499, 50, 'desk-mat', ProductStatus::ACTIVE],
            ['1TB Portable SSD', 'Pocket-sized NVMe SSD with up to 1050MB/s read speeds over USB-C 3.2. Drop resistant.', 9999, 18, 'portable-ssd', ProductStatus::ACTIVE],
            ['LED Desk Lamp', 'Dimmable LED lamp with 5 color temperatures, a USB charging port and a 1 hour auto-off timer.', 3299, 22, 'led-desk-lamp', ProductStatus::ACTIVE],
            ['Bluetooth Speaker', 'Waterproof portable speaker with 360 degree sound and 12 hours of playtime.', 5999, 0, 'bluetooth-speaker', ProductStatus::ACTIVE],
            ['USB Microphone', 'Cardioid condenser mic for streaming and calls, with a headphone jack and mute button.', 6999, 12, 'usb-microphone', ProductStatus::ACTIVE],
            ['Smart Stylus Pen', 'Pressure-sensitive stylus with palm rejection. Coming soon!', 4499, 0, 'smart-pen', ProductStatus::PLANNING],
        ];
        foreach ($catalog as [$name, $description, $price, $stock, $img, $status]) {
            $products->insert_product($name, $description, $price, $stock, '/images/products/' . $img . '.svg', $status);
        }
    }
}
