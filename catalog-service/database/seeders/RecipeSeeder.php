<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\RecipeItem;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $all = Ingredient::all();
        $byName = $all->keyBy(fn($i) => mb_strtolower(trim($i->name)));

        $need = function(string $name) use ($byName): Ingredient {
            $key = mb_strtolower(trim($name));
            $ing = $byName->get($key);
            if (!$ing) {
                throw new \RuntimeException("Nedostaje sastojak u bazi: '{$name}'. Pokreni IngredientsCatalogSeeder pre ovoga.");
            }
            return $ing;
        };
        $recipes = [
            [
                'name' => 'Domaća pileća supa',
                'description' => 'Piletina se kuva na tihoj vatri sa lukom, šargarepom i začinima dok se ne dobije bistar bujon. Pred kraj se dodaje peršun za svežinu.',
                'items' => [
                    'Piletina' => 1,
                    'Crni luk' => 1,
                    'Šargarepa' => 2,
                    'So' => 0.08,
                    'Biber' => 0.02,
                    'Peršun' => 0.1,
                ],
            ],
            [
                'name' => 'Pasulj prebranac',
                'description' => 'Pasulj se prvo skuva do pola, zatim se zapeče sa dosta luka, uljem i alevom paprikom. Krčka se dok se ne zgusne i dobije koricu.',
                'items' => [
                    'Pasulj (beli)' => 1,
                    'Crni luk' => 2,
                    'Ulje' => 1,
                    'Aleva paprika' => 0.1,
                    'Lovorov list' => 0.02,
                    'So' => 0.1,
                ],
            ],
            [
                'name' => 'Musaka sa krompirom',
                'description' => 'Krompir se slaže sa dinstanim mlevenim mesom i lukom. Preliva se smesom jaja i mleka i peče dok ne porumeni.',
                'items' => [
                    'Krompir' => 1,
                    'Mleveno meso' => 1,
                    'Crni luk' => 1,
                    'Jaja' => 3,
                    'Mleko' => 1,
                    'So' => 0.08,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Paprike punjene mesom i pirinčem',
                'description' => 'Paprike se pune mešavinom mesa, pirinča i luka. Kuvaju se u paradajz sosu dok paprika ne omekša, a fil se ne sjedini.',
                'items' => [
                    'Paprika' => 1,
                    'Mleveno meso' => 1,
                    'Pirinač' => 1,
                    'Crni luk' => 1,
                    'Paradajz pire' => 2.5,
                    'So' => 0.1,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Sarma',
                'description' => 'Listovi kupusa se pune mesom i pirinčem, pa se lagano krčkaju sa suvim mesom i lovorom. Ukus je najbolji sutradan.',
                'items' => [
                    'Kupus' => 1,
                    'Mleveno meso' => 1,
                    'Pirinač' => 1,
                    'Crni luk' => 1,
                    'Suvo meso' => 2.5,
                    'Lovorov list' => 0.02,
                    'So' => 0.08,
                ],
            ],
            [
                'name' => 'Đuveč sa piletinom',
                'description' => 'Piletina se kratko proprži, doda se povrće i pirinač, pa se sve zapeče da pirinač upije sokove. Jednostavno, a zasitno.',
                'items' => [
                    'Piletina' => 1,
                    'Pirinač' => 1,
                    'Paprika' => 1,
                    'Paradajz' => 1,
                    'Crni luk' => 1,
                    'Ulje' => 1,
                    'So' => 0.08,
                ],
            ],
            [
                'name' => 'Pasta u paradajz sosu',
                'description' => 'Luk i beli luk se izdinstaju, dodaje se paradajz pire i začini. Sos se sjedini pa se prelije preko testenine.',
                'items' => [
                    'Testenina' => 1,
                    'Crni luk' => 1,
                    'Beli luk' => 1,
                    'Paradajz pire' => 3,
                    'Ulje' => 1,
                    'So' => 0.06,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Krompir čorba',
                'description' => 'Krompir se kuva sa lukom i šargarepom, zatim se začini i po želji blago izgnječi da čorba bude gušća.',
                'items' => [
                    'Krompir' => 1,
                    'Crni luk' => 1,
                    'Šargarepa' => 2,
                    'Ulje' => 1,
                    'So' => 0.07,
                    'Biber' => 0.02,
                    'Peršun' => 0.1,
                ],
            ],
            [
                'name' => 'Pohovana piletina',
                'description' => 'Piletina se začini, uvalja u brašno, jaja i prezle, pa se prži do zlatne korice. Hrskavo spolja, sočno iznutra.',
                'items' => [
                    'Piletina' => 1,
                    'Brašno' => 1,
                    'Jaja' => 2,
                    'Prezle' => 2,
                    'Ulje' => 1,
                    'So' => 0.06,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Šopska salata',
                'description' => 'Paradajz i krastavac se seckaju, dodaje se luk, sir i prelije maslinovim uljem i sirćetom. Brzo i osvežavajuće.',
                'items' => [
                    'Paradajz' => 1,
                    'Krastavac' => 1,
                    'Crni luk' => 1,
                    'Sir (beli)' => 2.5,
                    'Maslinovo ulje' => 1,
                    'Sirće' => 0.3,
                    'So' => 0.04,
                ],
            ],
            [
                'name' => 'Pita sa sirom',
                'description' => 'Sir se pomeša sa jajima i jogurtom, pa se slaže i peče dok ne dobije rumenu koricu. Najbolje ide uz čašu jogurta.',
                'items' => [
                    'Sir (beli)' => 3,
                    'Jaja' => 3,
                    'Jogurt' => 1,
                    'Ulje' => 1,
                    'So' => 0.04,
                    'Brašno' => 1,
                ],
            ],
            [
                'name' => 'Proja sa sirom',
                'description' => 'Testo sa jajima i jogurtom se obogati sirom, pa se peče dok ne bude mekano i mirisno. Odlična kao prilog ili doručak.',
                'items' => [
                    'Brašno' => 1,
                    'Jaja' => 2,
                    'Jogurt' => 1,
                    'Sir (beli)' => 2,
                    'Ulje' => 1,
                    'So' => 0.04,
                ],
            ],
            [
                'name' => 'Krem pire krompir',
                'description' => 'Krompir se skuva, izgnječi sa maslacem i mlekom, pa začini. Kremasta tekstura i pun ukus.',
                'items' => [
                    'Krompir' => 1,
                    'Maslac' => 0.8,
                    'Mleko' => 1,
                    'So' => 0.05,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Gulaš od svinjetine',
                'description' => 'Svinjetina se dinsta sa lukom, alevom paprikom i lovorom. Krčka se dok meso ne omekša i sos ne postane gust.',
                'items' => [
                    'Svinjsko meso' => 1,
                    'Crni luk' => 2,
                    'Ulje' => 1,
                    'Aleva paprika' => 0.1,
                    'Lovorov list' => 0.02,
                    'So' => 0.08,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Pečeni krompir sa belim lukom',
                'description' => 'Krompir se iseče, začini i zapeče sa belim lukom. Spolja hrskav, iznutra mekan.',
                'items' => [
                    'Krompir' => 1,
                    'Beli luk' => 1,
                    'Ulje' => 1,
                    'So' => 0.05,
                    'Biber' => 0.02,
                    'Peršun' => 0.1,
                ],
            ],
            [
                'name' => 'Paradajz čorba',
                'description' => 'Paradajz i pire daju pun ukus, luk i beli luk osnovu, a peršun svežinu. Laka čorba kao predjelo.',
                'items' => [
                    'Paradajz' => 1,
                    'Paradajz pire' => 2.5,
                    'Crni luk' => 1,
                    'Beli luk' => 1,
                    'Ulje' => 1,
                    'So' => 0.06,
                    'Biber' => 0.02,
                ],
            ],
            [
                'name' => 'Salata od zelene salate',
                'description' => 'Zelena salata se začini maslinovim uljem i sirćetom, uz malo soli i bibera. Lagano i idealno uz ručak.',
                'items' => [
                    'Zelena salata' => 1,
                    'Maslinovo ulje' => 1,
                    'Sirće' => 0.2,
                    'So' => 0.03,
                    'Biber' => 0.01,
                ],
            ],
            [
                'name' => 'Omlet sa sirom',
                'description' => 'Umućena jaja se peku na maslacu, doda se sir i kratko zapeče. Brz doručak ili večera.',
                'items' => [
                    'Jaja' => 3,
                    'Sir (beli)' => 1.5,
                    'Maslac' => 0.3,
                    'So' => 0.03,
                    'Biber' => 0.01,
                    'Peršun' => 0.1,
                ],
            ],
            [
                'name' => 'Kremasta salata od krastavca',
                'description' => 'Krastavac se pomeša sa kiselom pavlakom i začinima. Osvežavajući prilog uz pohovano ili roštilj.',
                'items' => [
                    'Krastavac' => 1,
                    'Kisela pavlaka' => 2,
                    'So' => 0.03,
                    'Biber' => 0.01,
                    'Beli luk' => 1,
                ],
            ],
            [
                'name' => 'Palačinke (osnovne)',
                'description' => 'Smesa od mleka, jaja i brašna se peče u tankim slojevima. Po želji služe se slatke ili slane.',
                'items' => [
                    'Brašno' => 1,
                    'Mleko' => 1,
                    'Jaja' => 2,
                    'Ulje' => 1,
                    'So' => 0.02,
                    'Šećer' => 0.1,
                ],
            ],
            [
                'name' => 'Brzi kakao kolač',
                'description' => 'Jednostavan kolač: kakao i šećer daju ukus, maslac i mleko sočnost. Odličan uz kafu.',
                'items' => [
                    'Brašno' => 1,
                    'Šećer' => 0.1,
                    'Kakao' => 0.4,
                    'Jaja' => 2,
                    'Mleko' => 1,
                    'Maslac' => 0.8,
                ],
            ],
        ];

        foreach ($recipes as $r) {
            $recipe = Recipe::query()->where('name', $r['name'])->first();

            if (!$recipe) {
                $recipe = Recipe::create([
                    'name' => $r['name'],
                    'description' => $r['description'],
                ]);
            } else {
                $recipe->update(['description' => $r['description']]);
            }

            $recipeId = (string) $recipe->_id;
            RecipeItem::where('recipe_id', $recipeId)->delete();

            foreach ($r['items'] as $ingName => $qty) {
                $ing = $need($ingName);
                RecipeItem::create([
                    'recipe_id' => $recipeId,
                    'ingredient_id' => (string) $ing->_id,
                    'quantity' => (float) $qty,
                ]);
            }
        }
    }
}
