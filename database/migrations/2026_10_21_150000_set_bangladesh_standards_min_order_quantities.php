<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $products = DB::table('products')->get();

        foreach ($products as $product) {
            $name = mb_strtolower($product->name, 'UTF-8');
            $unit = mb_strtolower($product->unit ?? 'kg', 'UTF-8');
            $moq = 1;

            if (str_contains($name, 'ধান') || str_contains($name, 'paddy')) {
                // Bangladesh DAM wholesale paddy: 1 sack = 50 kg
                $moq = 50;
            } elseif (str_contains($name, 'চাল') || str_contains($name, 'rice')) {
                // Bangladesh standard milled rice bag: 1 bag = 25 kg or 50 kg
                $moq = 25;
            } elseif (str_contains($name, 'আলু') || str_contains($name, 'potato')) {
                // Bangladesh cold storage sack: 1 sack = 40 or 50 kg
                $moq = 40;
            } elseif (str_contains($name, 'পেঁয়াজ') || str_contains($name, 'পেঁয়াজ') || str_contains($name, 'onion')) {
                // Bangladesh wholesale mokam standard: 4 palla (20 kg) or 1 mon (40 kg)
                $moq = 20;
            } elseif (str_contains($name, 'সরিষা') || str_contains($name, 'mustard')) {
                // BADC seed sack / oil seed lot: 20 kg
                $moq = 20;
            } elseif (str_contains($name, 'আম') || str_contains($name, 'mango') || str_contains($name, 'পেয়ারা') || str_contains($name, 'guava')) {
                // Official fruit wholesale crate: 1 crate = 20 kg
                $moq = 20;
            } elseif (str_contains($name, 'পেঁপে') || str_contains($name, 'papaya')) {
                // Wholesale vegetable crate: 15 kg
                $moq = 15;
            } elseif (str_contains($name, 'বেগুন') || str_contains($name, 'brinjal')) {
                // Wholesale basket/crate: 10 kg
                $moq = 10;
            } elseif (str_contains($name, 'টমেটো') || str_contains($name, 'tomato')) {
                // Wholesale plastic crate: 10 kg
                $moq = 10;
            } elseif (str_contains($name, 'মরিচ') || str_contains($name, 'chilli')) {
                // Wholesale sack: 1 palla = 5 kg
                $moq = 5;
            } elseif ($unit === 'piece' || str_contains($name, 'লাউ')) {
                // Wholesale bundle: 5 pieces
                $moq = 5;
            } else {
                $moq = ($unit === 'piece') ? 5 : 10;
            }

            // Ensure MOQ does not exceed current stock if stock is low, but at least 1
            if ($product->stock > 0 && $moq > $product->stock) {
                $moq = (int) $product->stock;
            }

            DB::table('products')->where('id', $product->id)->update([
                'min_order_quantity' => $moq,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')->update(['min_order_quantity' => 1]);
    }
};
