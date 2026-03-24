<?php

namespace App\Services;

use App\Models\GodModeStyle;
use App\Models\Setting;

/**
 * ინვოისის ტექსტები God Mode-იდან (ფოლბექი: Setting / ნაგულისხმევი).
 */
class InvoiceDisplaySettings
{
    /** ცარიელი DB მნიშვნელობა → ნაგულისხმევი (getValue('' არ იძახებს default_value-ს სწორად). */
    private static function strOrDefault(string $key, string $default): string
    {
        $v = GodModeStyle::getValue($key);
        if ($v === null || trim($v) === '') {
            return $default;
        }

        return $v;
    }

    public static function headerTitle(): ?string
    {
        $v = trim((string) GodModeStyle::getValue('invoice_header_title', ''));
        return $v !== '' ? $v : null;
    }

    public static function companyName(): string
    {
        return GodModeStyle::getValue('invoice_company_name')
            ?: GodModeStyle::getValue('brand_company_name')
            ?: Setting::get('company_name', 'ONECAR LLC');
    }

    /** თუ God Mode-ში შევსებულია — ერთი ბლოკი; სხვა შემთხვევაში null (გამოიყენება Setting-ის ველები). */
    public static function companyExtraHtml(): ?string
    {
        $raw = GodModeStyle::getValue('invoice_company_extra');
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return nl2br(e($raw));
    }

    public static function companyAddress(): string
    {
        return Setting::get('company_address', 'Tbilisi, Georgia');
    }

    public static function companyPhone(): string
    {
        return Setting::get('company_phone', '+995 599 780 780');
    }

    public static function companyEmail(): string
    {
        return Setting::get('company_email', 'info@onecar.ge');
    }

    public static function companyLogo(): string
    {
        return GodModeStyle::getValue('brand_invoice_logo')
            ?: Setting::get('site_logo_dark', asset('favicon.ico'));
    }

    public static function labelBillTo(): string
    {
        return self::strOrDefault('invoice_label_bill_to', 'გადამხდელი / Bill To');
    }

    public static function labelPurpose(): string
    {
        return self::strOrDefault('invoice_label_purpose', 'დანიშნულება:');
    }

    public static function tableColDesc(): string
    {
        return self::strOrDefault('invoice_table_col_desc', 'აღწერა / Description');
    }

    public static function tableColAmount(): string
    {
        return self::strOrDefault('invoice_table_col_amount', 'თანხა / Amount ($)');
    }

    public static function tableTotal(): string
    {
        return self::strOrDefault('invoice_table_total', 'სულ / Total:');
    }

    public static function badgeText(): string
    {
        return self::strOrDefault('invoice_badge_text', 'გადასახდელი');
    }

    public static function footerText(): string
    {
        return self::strOrDefault('invoice_footer_text', 'გმადლობთ რომ სარგებლობთ ჩვენი მომსახურებით!');
    }

    /** პრეფიქსი; VIN ინვოისის კონტროლერში ემატება ავტომატურად. */
    public static function purposePrefix(string $type): string
    {
        if ($type === 'shipping') {
            return self::strOrDefault('invoice_purpose_shipping', 'ტრანსპორტირების გადასახადი');
        }

        return self::strOrDefault('invoice_purpose_vehicle', 'ავტომობილის გადასახადი');
    }

    public static function lineTitleVehicle(): string
    {
        return self::strOrDefault('invoice_line_vehicle', 'ავტომობილის ღირებულება / Vehicle Cost');
    }

    public static function lineTitleShipping(): string
    {
        return self::strOrDefault('invoice_line_shipping', 'ტრანსპორტირება / Shipping Cost');
    }

    public static function lineTitleAdditional(): string
    {
        return self::strOrDefault('invoice_line_additional', 'დამატებითი ხარჯები / Additional Fees');
    }

    public static function bankTitle(): string
    {
        return self::strOrDefault('invoice_bank_title', 'საბანკო რეკვიზიტები');
    }

    public static function bankName(): string
    {
        return GodModeStyle::getValue('invoice_bank_name')
            ?: Setting::get('bank_name', 'Bank of Georgia');
    }

    public static function bankRecipient(): string
    {
        return GodModeStyle::getValue('invoice_bank_recipient')
            ?: Setting::get('bank_recipient', 'ლუკა მურვანიძე');
    }

    public static function bankIban(): string
    {
        return GodModeStyle::getValue('invoice_bank_iban')
            ?: Setting::get('bank_iban', 'GE37BG0000000160921689');
    }

    public static function bankSwift(): string
    {
        return GodModeStyle::getValue('invoice_bank_swift')
            ?: Setting::get('bank_swift', 'BAGAGE22');
    }

    public static function bankLblBank(): string
    {
        return self::strOrDefault('invoice_bank_lbl_bank', 'ბანკი:');
    }

    public static function bankLblRecipient(): string
    {
        return self::strOrDefault('invoice_bank_lbl_recipient', 'მიმღები:');
    }

    public static function bankLblIban(): string
    {
        return self::strOrDefault('invoice_bank_lbl_iban', 'IBAN:');
    }

    public static function bankLblSwift(): string
    {
        return self::strOrDefault('invoice_bank_lbl_swift', 'SWIFT:');
    }

    /**
     * ცხრილის ხაზის აღწერა: სათაური + მოდელი/წელი + VIN მონაცემიდან.
     */
    public static function buildLineDescription(string $lineTitle, ?string $makeModel, $year, ?string $vin): string
    {
        $desc = $lineTitle;
        if ($makeModel) {
            $desc .= "\n" . $makeModel;
            if ($year) {
                $desc .= ' (' . $year . ')';
            }
        }
        if ($vin) {
            $desc .= "\nVIN: " . $vin;
        }

        return $desc;
    }

    public static function buildPaymentPurpose(string $type, ?string $vin): string
    {
        $prefix = self::purposePrefix($type);

        return $prefix . ($vin ? ' VIN: ' . $vin : '');
    }
}
