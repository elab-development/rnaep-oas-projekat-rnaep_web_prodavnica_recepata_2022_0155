<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $items = [
            ['name' => 'Crni luk', 'unit' => 'kom', 'price' => 20, 'category' => 'povrce', 'type' => 'lukovicasto', 'description' => 'Osnovni aromatični sastojak za kuvana i pečena jela.','photo_path' => '/images/ingredients/crni_luk.jpg'],
            ['name' => 'Beli luk', 'unit' => 'kom', 'price' => 30, 'category' => 'povrce', 'type' => 'lukovicasto', 'description' => 'Daje intenzivnu aromu; koristi se svež ili termički obrađen.','photo_path' => '/images/ingredients/beli_luk.jpg'],
            ['name' => 'Šargarepa', 'unit' => 'kom', 'price' => 25, 'category' => 'povrce', 'type' => 'korenasto', 'description' => 'Slatkasto povrće, odlično za supe, čorbe i priloge.','photo_path' => '/images/ingredients/sargarepa.jpg'],
            ['name' => 'Krompir', 'unit' => 'kg', 'price' => 140, 'category' => 'povrce', 'type' => 'krtole', 'description' => 'Univerzalan prilog i osnova za mnoga jela.','photo_path' => '/images/ingredients/krompir.jpg'],
            ['name' => 'Paradajz', 'unit' => 'kg', 'price' => 260, 'category' => 'povrce', 'type' => 'plodovito', 'description' => 'Svež paradajz za salate i sosove.','photo_path' => '/images/ingredients/paradajz.jpg'],
            ['name' => 'Paprika', 'unit' => 'kg', 'price' => 320, 'category' => 'povrce', 'type' => 'plodovito', 'description' => 'Koristi se sveža, punjena ili pečena; daje slatkast ukus.','photo_path' => '/images/ingredients/paprika.jpg'],
            ['name' => 'Kupus', 'unit' => 'kg', 'price' => 120, 'category' => 'povrce', 'type' => 'lisnato', 'description' => 'Za sarmu, podvarak i salate; dobar i svež i kiseo.','photo_path' => '/images/ingredients/kupus.jpg'],
            ['name' => 'Krastavac', 'unit' => 'kg', 'price' => 220, 'category' => 'povrce', 'type' => 'plodovito', 'description' => 'Osvežavajuće povrće za salate.','photo_path' => '/images/ingredients/krastavac.jpg'],
            ['name' => 'Zelena salata', 'unit' => 'kom', 'price' => 120, 'category' => 'povrce', 'type' => 'lisnato', 'description' => 'Lagana osnova za salate i priloge.','photo_path' => '/images/ingredients/zelena_salata.jpg'],

            ['name' => 'Pirinač', 'unit' => 'kg', 'price' => 220, 'category' => 'zitarice', 'type' => 'zrna', 'description' => 'Za đuveč, punjene paprike i priloge.','photo_path' => '/images/ingredients/pirinac.jpg'],
            ['name' => 'Pasulj (beli)', 'unit' => 'kg', 'price' => 380, 'category' => 'zitarice', 'type' => 'mahunarke', 'description' => 'Za prebranac i čorbasti pasulj.','photo_path' => '/images/ingredients/beli_pasulj.jpg'],
            ['name' => 'Testenina', 'unit' => 'kg', 'price' => 260, 'category' => 'testenina', 'type' => 'pasta', 'description' => 'Za brze obroke sa sosom.','photo_path' => '/images/ingredients/testenina.jpg'],
            ['name' => 'Brašno', 'unit' => 'kg', 'price' => 110, 'category' => 'zitarice', 'type' => 'brasno', 'description' => 'Osnova za testa, pohovanje i poslastice.','photo_path' => '/images/ingredients/brasno.jpg'],
            ['name' => 'Prezle', 'unit' => '100g', 'price' => 70, 'category' => 'zitarice', 'type' => 'prezle', 'description' => 'Za pohovanje i gratiniranje.','photo_path' => '/images/ingredients/prezle.jpg'],

            ['name' => 'Jaja', 'unit' => 'kom', 'price' => 25, 'category' => 'mlecno', 'type' => 'jaja', 'description' => 'Za omlete, pohovanje, testa i kolače.','photo_path' => '/images/ingredients/jaja.jpg'],
            ['name' => 'Mleko', 'unit' => 'l', 'price' => 160, 'category' => 'mlecno', 'type' => 'mleko', 'description' => 'Za soseve, testa i poslastice.','photo_path' => '/images/ingredients/mleko.jpg'],
            ['name' => 'Jogurt', 'unit' => 'l', 'price' => 190, 'category' => 'mlecno', 'type' => 'jogurt', 'description' => 'Odlično uz pite i proje; koristi se i u testu.','photo_path' => '/images/ingredients/jogurt.jpg'],
            ['name' => 'Kisela pavlaka', 'unit' => '100g', 'price' => 120, 'category' => 'mlecno', 'type' => 'pavlaka', 'description' => 'Daje kremast ukus jelima i salatama.','photo_path' => '/images/ingredients/kisela_pavlaka.jpg'],
            ['name' => 'Sir (beli)', 'unit' => '100g', 'price' => 160, 'category' => 'mlecno', 'type' => 'sir', 'description' => 'Za pite, salate i doručak.','photo_path' => '/images/ingredients/beli_sir.jpg'],
            ['name' => 'Maslac', 'unit' => '100g', 'price' => 220, 'category' => 'mlecno', 'type' => 'maslac', 'description' => 'Za pire, pečenje i poslastice.','photo_path' => '/images/ingredients/maslacjpg.jpg'],

            ['name' => 'Piletina', 'unit' => 'kg', 'price' => 520, 'category' => 'meso', 'type' => 'piletina', 'description' => 'Za supe, pečenje i paprikaše.','photo_path' => '/images/ingredients/piletina.jpg'],
            ['name' => 'Svinjsko meso', 'unit' => 'kg', 'price' => 820, 'category' => 'meso', 'type' => 'svinjetina', 'description' => 'Za gulaš, pečenje i podvarak.','photo_path' => '/images/ingredients/svinjsko_meso.jpg'],
            ['name' => 'Mleveno meso', 'unit' => 'kg', 'price' => 950, 'category' => 'meso', 'type' => 'mleveno', 'description' => 'Za musaku, ćufte, punjene paprike i sarmu.','photo_path' => '/images/ingredients/mleveno_meso.jpg'],
            ['name' => 'Suvo meso', 'unit' => '100g', 'price' => 280, 'category' => 'meso', 'type' => 'dimljeno', 'description' => 'Daje dimljenu aromu jelima poput sarme i pasulja.','photo_path' => '/images/ingredients/suvo_meso.jpg'],

            ['name' => 'So', 'unit' => '100g', 'price' => 30, 'category' => 'zacin', 'type' => 'so', 'description' => 'Osnovni začin.','photo_path' => '/images/ingredients/so.jpg'],
            ['name' => 'Biber', 'unit' => '100g', 'price' => 35, 'category' => 'zacin', 'type' => 'biber', 'description' => 'Začinjavanje po ukusu.','photo_path' => '/images/ingredients/biber.jpg'],
            ['name' => 'Aleva paprika', 'unit' => '100g', 'price' => 15, 'category' => 'zacin', 'type' => 'paprika', 'description' => 'Daje boju i blag ukus; može biti slatka ili ljuta.','photo_path' => '/images/ingredients/aleva_paprika.jpg'],
            ['name' => 'Lovorov list', 'unit' => '100g', 'price' => 25, 'category' => 'zacin', 'type' => 'lovor', 'description' => 'Aroma za kuvana jela i variva.','photo_path' => '/images/ingredients/lovorov_list.jpg'],
            ['name' => 'Peršun', 'unit' => '100g', 'price' => 20, 'category' => 'zacin', 'type' => 'biljni', 'description' => 'Svež biljni začin za završnicu.','photo_path' => '/images/ingredients/persun.jpg'],
            ['name' => 'Ulje', 'unit' => 'l', 'price' => 210, 'category' => 'ostalo', 'type' => 'ulje', 'description' => 'Za prženje i kuvanje.','photo_path' => '/images/ingredients/ulje.jpg'],
            ['name' => 'Maslinovo ulje', 'unit' => 'l', 'price' => 1200, 'category' => 'ostalo', 'type' => 'ulje', 'description' => 'Za salate i lagano kuvanje.','photo_path' => '/images/ingredients/maslinovo_ulje.jpg'],
            ['name' => 'Sirće', 'unit' => 'l', 'price' => 120, 'category' => 'ostalo', 'type' => 'sirce', 'description' => 'Za salate i kiseljenje.','photo_path' => '/images/ingredients/sirce.jpg'],
            ['name' => 'Paradajz pire', 'unit' => '100g', 'price' => 50, 'category' => 'ostalo', 'type' => 'pire', 'description' => 'Osnova za sosove i kuvana jela.','photo_path' => '/images/ingredients/pire.jpg'],

            ['name' => 'Šećer', 'unit' => 'kg', 'price' => 140, 'category' => 'ostalo', 'type' => 'secer', 'description' => 'Za poslastice i kolače.','photo_path' => '/images/ingredients/secer.jpg'],
            ['name' => 'Kakao', 'unit' => '100g', 'price' => 90, 'category' => 'ostalo', 'type' => 'kakao', 'description' => 'Za kolače i tople napitke.','photo_path' => '/images/ingredients/kakao.jpg'],
        ];

        foreach ($items as $i) {
            Ingredient::query()->updateOrCreate(
                ['name' => $i['name']],
                [
                    'unit' => $i['unit'],
                    'price' => $i['price'],
                    'category' => $i['category'] ?? null,
                    'type' => $i['type'] ?? null,
                    'description' => $i['description'] ?? null,
                    'photo_path' => $i['photo_path'] ?? null,
                    'stock_quantity' => $i['stock_quantity'] ?? 100,
                ]
            );
        }
    }
}
