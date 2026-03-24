<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * God Mode: ინვოისის ტექსტები (ცარიელი = ფოლბექი Setting / ნაგულისხმევი კოდში).
     */
    public function up(): void
    {
        $now = now();
        $rows = [
            ['invoice_header_title', 'ინვოისის სათაური (ზედა ხაზი)', 'invoice', 'text', '', 'ცარიელი = არ ჩანს'],
            ['invoice_company_name', 'კომპანიის სახელი (ინვოისში)', 'invoice', 'text', '', 'ცარიელი = ბრენდინგი / პარამეტრები'],
            ['invoice_company_extra', 'კომპანიის სრული ტექსტი (მისამართი, ტელ, ელფოსტა)', 'invoice', 'textarea', '', 'თუ შევსებულია — ჩაანაცვლებს მისამართის 3 ხაზს'],
            ['invoice_label_bill_to', 'გადამხდელის ბლოკის სათაური', 'invoice', 'text', 'გადამხდელი / Bill To', null],
            ['invoice_label_purpose', 'დანიშნულების ჭდე', 'invoice', 'text', 'დანიშნულება:', null],
            ['invoice_purpose_vehicle', 'დანიშნულება — ავტო (პრეფიქსი; VIN ემატება ავტომატურად)', 'invoice', 'text', 'ავტომობილის გადასახადი', null],
            ['invoice_purpose_shipping', 'დანიშნულება — ტრანსპორტი (პრეფიქსი; VIN ავტომატურად)', 'invoice', 'text', 'ტრანსპორტირების გადასახადი', null],
            ['invoice_line_vehicle', 'ცხრილის ხაზი — ავტომობილის ღირებულება (სათაური)', 'invoice', 'text', 'ავტომობილის ღირებულება / Vehicle Cost', 'VIN ქვემოთ ინვოისის მონაცემიდან'],
            ['invoice_line_shipping', 'ცხრილის ხაზი — ტრანსპორტი', 'invoice', 'text', 'ტრანსპორტირება / Shipping Cost', null],
            ['invoice_line_additional', 'ცხრილის ხაზი — დამატებითი ხარჯები', 'invoice', 'text', 'დამატებითი ხარჯები / Additional Fees', null],
            ['invoice_table_col_desc', 'ცხრილი: სვეტი „აღწერა“', 'invoice', 'text', 'აღწერა / Description', null],
            ['invoice_table_col_amount', 'ცხრილი: სვეტი „თანხა“', 'invoice', 'text', 'თანხა / Amount ($)', null],
            ['invoice_table_total', 'ცხრილი: სულ', 'invoice', 'text', 'სულ / Total:', null],
            ['invoice_badge_text', 'სტატუსის ბეიჯი', 'invoice', 'text', 'გადასახდელი', null],
            ['invoice_footer_text', 'ქვედა ტექსტი', 'invoice', 'textarea', 'გმადლობთ რომ სარგებლობთ ჩვენი მომსახურებით!', null],
            ['invoice_bank_title', 'ბანკის ბლოკის სათაური', 'invoice', 'text', 'საბანკო რეკვიზიტები', null],
            ['invoice_bank_name', 'ბანკის სახელი', 'invoice', 'text', '', 'ცარიელი = პარამეტრები'],
            ['invoice_bank_recipient', 'მიმღები', 'invoice', 'text', '', null],
            ['invoice_bank_iban', 'IBAN', 'invoice', 'text', '', null],
            ['invoice_bank_swift', 'SWIFT', 'invoice', 'text', '', null],
            ['invoice_bank_lbl_bank', 'ჭდე: ბანკი', 'invoice', 'text', 'ბანკი:', null],
            ['invoice_bank_lbl_recipient', 'ჭდე: მიმღები', 'invoice', 'text', 'მიმღები:', null],
            ['invoice_bank_lbl_iban', 'ჭდე: IBAN', 'invoice', 'text', 'IBAN:', null],
            ['invoice_bank_lbl_swift', 'ჭდე: SWIFT', 'invoice', 'text', 'SWIFT:', null],
        ];

        $sort = 200;
        foreach ($rows as $row) {
            [$key, $name, $group, $type, $default, $desc] = $row;
            DB::table('god_mode_styles')->insertOrIgnore([
                'style_key' => $key,
                'style_name' => $name,
                'style_group' => $group,
                'style_type' => $type,
                'style_value' => $default,
                'default_value' => $default,
                'description' => $desc,
                'sort_order' => $sort++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $keys = [
            'invoice_header_title', 'invoice_company_name', 'invoice_company_extra',
            'invoice_label_bill_to', 'invoice_label_purpose', 'invoice_purpose_vehicle', 'invoice_purpose_shipping',
            'invoice_line_vehicle', 'invoice_line_shipping', 'invoice_line_additional',
            'invoice_table_col_desc', 'invoice_table_col_amount', 'invoice_table_total',
            'invoice_badge_text', 'invoice_footer_text',
            'invoice_bank_title', 'invoice_bank_name', 'invoice_bank_recipient', 'invoice_bank_iban', 'invoice_bank_swift',
            'invoice_bank_lbl_bank', 'invoice_bank_lbl_recipient', 'invoice_bank_lbl_iban', 'invoice_bank_lbl_swift',
        ];
        DB::table('god_mode_styles')->whereIn('style_key', $keys)->delete();
    }
};
