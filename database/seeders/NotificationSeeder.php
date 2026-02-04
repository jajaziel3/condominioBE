<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed some example notifications
        Notification::create([
            'category' => 'pago',
            'title' => 'Pago Pendiente',
            'body' => 'Tienes una cuota pendiente que vence mañana',
            'data' => ['amount' => 1200]
        ]);

        Notification::create([
            'category' => 'mantenimiento',
            'title' => 'Mantenimiento Programado',
            'body' => 'Corte de agua el miércoles de 9:00 a 12:00',
            'data' => ['area' => 'torre A']
        ]);
    }
}
