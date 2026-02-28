<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SmsTemplate;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'ადმინისტრატორი',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '593040503',
                'balance' => 0,
                'sms_enabled' => true,
                'approved' => true,
            ]
        );

        // Create SMS Templates
        $smsTemplates = [
            [
                'status_key' => 'purchased',
                'template_text' => 'გილოცავთ თქვენ შეიძინეთ ავტომობილი - [მანქანა] [წელი] VIN : [ვინ] ლოტის ნომრით [ლოტი] მადლობას გიხდით თანამშრომლობისთვის!',
                'description' => 'შეძენილია',
            ],
            [
                'status_key' => 'warehouse',
                'template_text' => '[მანქანა] [წელი] - [ვინ] მიყვანილია გამშვებ პორტთან მდებარე საწყობში და ემზადება ჩასატვირთად.',
                'description' => 'საწყობშია',
            ],
            [
                'status_key' => 'loaded',
                'template_text' => '[მანქანა] [წელი] - [ვინ] ჩატვირთულია კონტეინერში! კონტეინერის ნომერი: [კონტეინერი]',
                'description' => 'ჩატვირთულია',
            ],
            [
                'status_key' => 'on_way',
                'template_text' => '[მანქანა] [წელი] - [ვინ] თქვენი ავტომობილი გზაშია. ტრანსპორტირების პროცესს შეგიძლიათ თვალი ადევნოთ კონტეინერის ნომრით [კონტეინერი]',
                'description' => 'გზაშია',
            ],
            [
                'status_key' => 'poti',
                'template_text' => 'გილოცავთ! [მანქანა] [წელი] - [ვინ] გემი უკვე პორტშია! დაელოდეთ კონტეინერის გახსნას.',
                'description' => 'ფოთშია',
            ],
            [
                'status_key' => 'green',
                'template_text' => '[მანქანა] [წელი] - [ვინ] გადმოიტვირთა და შეგიძლიათ გაიყვანოთ, ტერმინალი - APM',
                'description' => 'მწვანეშია',
            ],
            [
                'status_key' => 'delivered',
                'template_text' => '[მანქანა] [წელი] - [ვინ] გაყვანილია ტერმინალიდან. მადლობა თანამშრომლობისთვის!',
                'description' => 'გაყვანილია',
            ],
        ];

        foreach ($smsTemplates as $template) {
            SmsTemplate::firstOrCreate(
                ['status_key' => $template['status_key']],
                $template
            );
        }

        // Create default settings
        $settings = [
            ['setting_key' => 'site_name', 'setting_value' => 'OneCar CRM', 'setting_group' => 'general'],
            ['setting_key' => 'maintenance_mode', 'setting_value' => '0', 'setting_group' => 'general'],
            ['setting_key' => 'registration_enabled', 'setting_value' => '0', 'setting_group' => 'general'],
            ['setting_key' => 'sms_api_key', 'setting_value' => '', 'setting_group' => 'sms'],
            ['setting_key' => 'sms_sender', 'setting_value' => 'OneCar', 'setting_group' => 'sms'],
            ['setting_key' => 'default_currency', 'setting_value' => 'USD', 'setting_group' => 'finance'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
