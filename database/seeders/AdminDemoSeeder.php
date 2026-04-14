<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@whatsapp.local'],
            [
                'name' => 'admin',
                'phone' => '01000000000',
                'country_code' => '20',
                'type' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $basicPlan = Plan::updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic',
                'price' => 9.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'monthly_price' => 9.99,
                'yearly_price' => 99.99,
                'max_instances' => 2,
                'max_messages' => 1000,
                'max_campaigns' => 5,
                'color' => 'secondary',
                'description' => 'Perfect for small businesses',
                'features' => [
                    '2 WhatsApp Instances',
                    '1,000 Messages/Month',
                    'Up to 5 Campaigns',
                    'Basic Analytics',
                    'Email Support',
                ],
                'is_active' => true,
            ]
        );

        $proPlan = Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price' => 29.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'monthly_price' => 29.99,
                'yearly_price' => 299.99,
                'max_instances' => 5,
                'max_messages' => 5000,
                'max_campaigns' => 20,
                'color' => 'success',
                'description' => 'For growing businesses',
                'features' => [
                    '5 WhatsApp Instances',
                    '5,000 Messages/Month',
                    'Up to 20 Campaigns',
                    'Advanced Analytics',
                    'Priority Support',
                    'API Access',
                ],
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'price' => 99.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'monthly_price' => 99.99,
                'yearly_price' => 999.99,
                'max_instances' => 10,
                'max_messages' => 20000,
                'max_campaigns' => 0,
                'color' => 'primary',
                'description' => 'For enterprise teams',
                'features' => [
                    '10 WhatsApp Instances',
                    '20,000 Messages/Month',
                    'Unlimited Campaigns',
                    'Advanced Analytics & Reports',
                    '24/7 Priority Support',
                    'Full API Access',
                    'Custom Integration',
                    'Dedicated Account Manager',
                ],
                'is_active' => true,
            ]
        );

        $customerA = Customer::updateOrCreate(
            ['phone' => '+20 100 123 4567'],
            [
                'user_id' => $admin->id,
                'name' => 'Ahmed Ali',
                'email' => 'ahmed@example.com',
                'country_code' => '20',
                'plan' => 'Pro',
                'status' => 'active',
                'expiry_date' => now()->addDays(20)->toDateString(),
                'max_instances' => 5,
                'billing_cycle' => 'monthly',
            ]
        );

        $customerB = Customer::updateOrCreate(
            ['phone' => '+20 101 234 5678'],
            [
                'name' => 'Mohamed Hassan',
                'email' => 'mohamed@example.com',
                'country_code' => '20',
                'plan' => 'Basic',
                'status' => 'active',
                'expiry_date' => now()->addDays(5)->toDateString(),
                'max_instances' => 1,
                'billing_cycle' => 'monthly',
            ]
        );

        $subA = Subscription::updateOrCreate(
            ['customer_id' => $customerA->id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(20)->toDateString(),
                'price' => 99,
            ]
        );

        $subB = Subscription::updateOrCreate(
            ['customer_id' => $customerB->id],
            [
                'plan_id' => $basicPlan->id,
                'status' => 'active',
                'start_date' => now()->subDays(25)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'price' => 49,
            ]
        );

        $paymentA = Payment::updateOrCreate(
            ['customer_id' => $customerA->id, 'amount' => 199.00],
            [
                'subscription_id' => $subA->id,
                'currency' => 'USD',
                'method' => 'bank_transfer',
                'status' => 'approved',
                'paid_at' => now()->subDays(4),
                'notes' => 'Seed payment',
            ]
        );

        Payment::updateOrCreate(
            ['customer_id' => $customerB->id, 'amount' => 49.00],
            [
                'subscription_id' => $subB->id,
                'currency' => 'USD',
                'method' => 'wallet',
                'status' => 'pending',
                'paid_at' => null,
                'notes' => 'Pending approval',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-0001'],
            [
                'customer_id' => $customerA->id,
                'subscription_id' => $subA->id,
                'payment_id' => $paymentA->id,
                'amount' => 199.00,
                'currency' => 'USD',
                'status' => 'paid',
                'issued_at' => now()->subDays(5)->toDateString(),
                'due_at' => now()->addDays(25)->toDateString(),
                'paid_at' => now()->subDays(4),
            ]
        );
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> f9389bb0657d89ba01c4cde0b6d312a02bd1a402
