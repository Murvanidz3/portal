<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GodModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default Super Admin
        DB::table('super_admins')->insertOrIgnore([
            'username' => 'superadmin',
            'email' => 'superadmin@onecar.ge',
            'password' => Hash::make('SuperAdmin@2026!'),
            'full_name' => 'Super Administrator',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed default permissions
        $permissions = [
            // Transactions
            ['feature_key' => 'transactions.access', 'feature_name' => 'ტრანზაქციების გვერდის ნახვა', 'feature_group' => 'transactions', 'description' => 'წვდომა ტრანზაქციების გვერდზე', 'sort_order' => 1],
            ['feature_key' => 'transactions.create', 'feature_name' => 'ტრანზაქციის შექმნა', 'feature_group' => 'transactions', 'description' => 'ახალი ტრანზაქციის დამატება', 'sort_order' => 2],
            ['feature_key' => 'transactions.edit', 'feature_name' => 'ტრანზაქციის რედაქტირება', 'feature_group' => 'transactions', 'description' => 'არსებული ტრანზაქციის შეცვლა', 'sort_order' => 3],
            ['feature_key' => 'transactions.delete', 'feature_name' => 'ტრანზაქციის წაშლა', 'feature_group' => 'transactions', 'description' => 'ტრანზაქციის წაშლა', 'sort_order' => 4],

            // Invoices
            ['feature_key' => 'invoices.access', 'feature_name' => 'ინვოისების გვერდის ნახვა', 'feature_group' => 'invoices', 'description' => 'წვდომა ინვოისების გვერდზე', 'sort_order' => 10],
            ['feature_key' => 'invoices.create', 'feature_name' => 'ინვოისის შექმნა', 'feature_group' => 'invoices', 'description' => 'ახალი ინვოისის შექმნა', 'sort_order' => 11],
            ['feature_key' => 'invoices.edit', 'feature_name' => 'ინვოისის რედაქტირება', 'feature_group' => 'invoices', 'description' => 'ინვოისის შეცვლა', 'sort_order' => 12],

            // Users
            ['feature_key' => 'users.access', 'feature_name' => 'მომხმარებლების გვერდის ნახვა', 'feature_group' => 'users', 'description' => 'წვდომა მომხმარებლების მართვაზე', 'sort_order' => 20],
            ['feature_key' => 'users.create', 'feature_name' => 'მომხმარებლის შექმნა', 'feature_group' => 'users', 'description' => 'ახალი მომხმარებლის დამატება', 'sort_order' => 21],
            ['feature_key' => 'users.edit', 'feature_name' => 'მომხმარებლის რედაქტირება', 'feature_group' => 'users', 'description' => 'მომხმარებლის შეცვლა', 'sort_order' => 22],
            ['feature_key' => 'users.delete', 'feature_name' => 'მომხმარებლის წაშლა', 'feature_group' => 'users', 'description' => 'მომხმარებლის წაშლა', 'sort_order' => 23],

            // SMS
            ['feature_key' => 'sms.access', 'feature_name' => 'SMS მენეჯერის ნახვა', 'feature_group' => 'sms', 'description' => 'წვდომა SMS მენეჯერზე', 'sort_order' => 30],
            ['feature_key' => 'sms.send', 'feature_name' => 'SMS გაგზავნა', 'feature_group' => 'sms', 'description' => 'SMS შეტყობინების გაგზავნა', 'sort_order' => 31],

            // Calculator
            ['feature_key' => 'calculator.access', 'feature_name' => 'კალკულატორის ნახვა', 'feature_group' => 'calculator', 'description' => 'წვდომა კალკულატორზე', 'sort_order' => 40],

            // Settings
            ['feature_key' => 'settings.access', 'feature_name' => 'პარამეტრების ნახვა', 'feature_group' => 'settings', 'description' => 'წვდომა პარამეტრებზე', 'sort_order' => 50],
            ['feature_key' => 'settings.edit', 'feature_name' => 'პარამეტრების რედაქტირება', 'feature_group' => 'settings', 'description' => 'პარამეტრების შეცვლა', 'sort_order' => 51],

            // Finance
            ['feature_key' => 'finance.access', 'feature_name' => 'ფინანსების გვერდის ნახვა', 'feature_group' => 'finance', 'description' => 'წვდომა ფინანსებზე', 'sort_order' => 60],
            ['feature_key' => 'finance.edit', 'feature_name' => 'ფინანსური მონაცემების რედაქტირება', 'feature_group' => 'finance', 'description' => 'თანხების შეცვლა', 'sort_order' => 61],

            // Cars
            ['feature_key' => 'cars.access', 'feature_name' => 'მანქანების გვერდის ნახვა', 'feature_group' => 'cars', 'description' => 'წვდომა მანქანებზე', 'sort_order' => 70],
            ['feature_key' => 'cars.create', 'feature_name' => 'მანქანის დამატება', 'feature_group' => 'cars', 'description' => 'ახალი მანქანის შექმნა', 'sort_order' => 71],
            ['feature_key' => 'cars.edit', 'feature_name' => 'მანქანის რედაქტირება', 'feature_group' => 'cars', 'description' => 'მანქანის შეცვლა', 'sort_order' => 72],
            ['feature_key' => 'cars.delete', 'feature_name' => 'მანქანის წაშლა', 'feature_group' => 'cars', 'description' => 'მანქანის წაშლა', 'sort_order' => 73],

            // Wallet
            ['feature_key' => 'wallet.access', 'feature_name' => 'საფულის გვერდის ნახვა', 'feature_group' => 'wallet', 'description' => 'წვდომა საფულეზე', 'sort_order' => 80],
            ['feature_key' => 'wallet.transfer', 'feature_name' => 'თანხის გადარიცხვა', 'feature_group' => 'wallet', 'description' => 'თანხის ტრანსფერი', 'sort_order' => 81],

            // Notifications
            ['feature_key' => 'notifications.access', 'feature_name' => 'შეტყობინებების ნახვა', 'feature_group' => 'notifications', 'description' => 'წვდომა შეტყობინებებზე', 'sort_order' => 90],
            ['feature_key' => 'notifications.send', 'feature_name' => 'შეტყობინების გაგზავნა', 'feature_group' => 'notifications', 'description' => 'შეტყობინების გაგზავნა', 'sort_order' => 91],
        ];

        foreach ($permissions as $permission) {
            DB::table('god_mode_permissions')->insertOrIgnore(array_merge($permission, [
                'is_enabled_global' => true,
                'is_enabled_admin' => true,
                'is_enabled_dealer' => in_array($permission['feature_group'], ['cars', 'calculator', 'wallet', 'notifications']),
                'is_enabled_client' => in_array($permission['feature_group'], ['calculator', 'notifications']),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed default styles
        $styles = [
            // Branding
            ['style_key' => 'brand_header_logo', 'style_name' => 'Header-ის ლოგო', 'style_group' => 'branding', 'style_type' => 'image', 'default_value' => '/images/logo.png', 'sort_order' => 1],
            ['style_key' => 'brand_invoice_logo', 'style_name' => 'ინვოისის ლოგო', 'style_group' => 'branding', 'style_type' => 'image', 'default_value' => '/images/logo.png', 'sort_order' => 2],
            ['style_key' => 'brand_favicon', 'style_name' => 'Favicon', 'style_group' => 'branding', 'style_type' => 'image', 'default_value' => '/favicon.ico', 'sort_order' => 3],
            ['style_key' => 'brand_company_name', 'style_name' => 'კომპანიის სახელი', 'style_group' => 'branding', 'style_type' => 'text', 'default_value' => 'ONECAR', 'sort_order' => 4],

            // Primary Colors
            ['style_key' => 'color_primary', 'style_name' => 'პირველადი ფერი', 'style_group' => 'colors', 'style_type' => 'color', 'default_value' => '#3b82f6', 'sort_order' => 10],
            ['style_key' => 'color_secondary', 'style_name' => 'მეორეული ფერი', 'style_group' => 'colors', 'style_type' => 'color', 'default_value' => '#64748b', 'sort_order' => 11],
            ['style_key' => 'color_accent', 'style_name' => 'აქცენტის ფერი', 'style_group' => 'colors', 'style_type' => 'color', 'default_value' => '#8b5cf6', 'sort_order' => 12],

            // Button Colors
            ['style_key' => 'color_btn_primary_bg', 'style_name' => 'ღილაკის ფონი', 'style_group' => 'buttons', 'style_type' => 'color', 'default_value' => '#3b82f6', 'sort_order' => 20],
            ['style_key' => 'color_btn_primary_text', 'style_name' => 'ღილაკის ტექსტი', 'style_group' => 'buttons', 'style_type' => 'color', 'default_value' => '#ffffff', 'sort_order' => 21],
            ['style_key' => 'color_btn_danger_bg', 'style_name' => 'საშიში ღილაკის ფონი', 'style_group' => 'buttons', 'style_type' => 'color', 'default_value' => '#ef4444', 'sort_order' => 22],

            // Layout Colors
            ['style_key' => 'color_header_bg', 'style_name' => 'Header-ის ფონი', 'style_group' => 'layout', 'style_type' => 'color', 'default_value' => '#1e293b', 'sort_order' => 30],
            ['style_key' => 'color_sidebar_bg', 'style_name' => 'Sidebar-ის ფონი', 'style_group' => 'layout', 'style_type' => 'color', 'default_value' => '#0f172a', 'sort_order' => 31],
            ['style_key' => 'color_sidebar_active', 'style_name' => 'Sidebar აქტიური ელემენტი', 'style_group' => 'layout', 'style_type' => 'color', 'default_value' => '#3b82f6', 'sort_order' => 32],
            ['style_key' => 'color_table_header_bg', 'style_name' => 'ცხრილის Header ფონი', 'style_group' => 'layout', 'style_type' => 'color', 'default_value' => '#f1f5f9', 'sort_order' => 33],

            // Status Colors
            ['style_key' => 'color_success', 'style_name' => 'წარმატების ფერი', 'style_group' => 'status', 'style_type' => 'color', 'default_value' => '#22c55e', 'sort_order' => 40],
            ['style_key' => 'color_warning', 'style_name' => 'გაფრთხილების ფერი', 'style_group' => 'status', 'style_type' => 'color', 'default_value' => '#f59e0b', 'sort_order' => 41],
            ['style_key' => 'color_error', 'style_name' => 'შეცდომის ფერი', 'style_group' => 'status', 'style_type' => 'color', 'default_value' => '#ef4444', 'sort_order' => 42],
            ['style_key' => 'color_info', 'style_name' => 'ინფორმაციის ფერი', 'style_group' => 'status', 'style_type' => 'color', 'default_value' => '#0ea5e9', 'sort_order' => 43],
        ];

        foreach ($styles as $style) {
            DB::table('god_mode_styles')->insertOrIgnore(array_merge($style, [
                'style_value' => $style['default_value'],
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
