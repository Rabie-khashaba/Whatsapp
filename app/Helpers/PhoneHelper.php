<?php

namespace App\Helpers;

class PhoneHelper
{
    public static function normalizeEgyptPhone(string $phone): string
    {
        // شيل أي حاجة مش رقم
        $phone = preg_replace('/\D/', '', $phone);

        // لو بيبدأ بـ 01 → شيل الصفر
        if (str_starts_with($phone, '01')) {
            $phone = substr($phone, 1);
        }

        // لو مش مضاف كود الدولة
        if (!str_starts_with($phone, '20')) {
            $phone = '20' . $phone;
        }

        return $phone;
    }
}
