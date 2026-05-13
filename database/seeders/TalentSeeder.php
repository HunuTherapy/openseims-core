<?php

namespace Database\Seeders;

use App\Models\Talent;
use Illuminate\Database\Seeder;

class TalentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // sort alphabetically by name
        foreach ([
            // alphabetical order
            ['name' => 'Acting',            'description' => 'Performs well in drama and acting'],
            ['name' => 'Baking',            'description' => 'Expert in baking goods and pastries'],
            ['name' => 'Basket Weaving',    'description' => 'Creates baskets with skill'],
            ['name' => 'Calligraphy',       'description' => 'Art of beautiful handwriting'],
            ['name' => 'Coding',            'description' => 'Programming and software development'],
            ['name' => 'Cooking',           'description' => 'Good at preparing various dishes'],
            ['name' => 'Dancing',           'description' => 'Talented in various dance forms'],
            ['name' => 'Drawing',           'description' => 'Skilled at drawing and sketching'],
            ['name' => 'Gardening',         'description' => 'Enjoys and excels in gardening'],
            ['name' => 'Jewelry Making',    'description' => 'Creates beautiful jewelry pieces'],
            ['name' => 'Knitting',          'description' => 'Skilled in knitting and crocheting'],
            ['name' => 'Leadership',        'description' => 'Shows leadership qualities'],
            ['name' => 'Metalworking',      'description' => 'Skilled in working with metals'],
            ['name' => 'Musical Instrument', 'description' => 'Plays a musical instrument well'],
            ['name' => 'Painting',          'description' => 'Creates beautiful paintings'],
            ['name' => 'Photography',       'description' => 'Skilled in capturing photographs'],
            ['name' => 'Pottery',           'description' => 'Creates pottery items with skill'],
            ['name' => 'Public Speaking',   'description' => 'Confident in public speaking'],
            ['name' => 'Sewing',            'description' => 'Expert in sewing and tailoring'],
            ['name' => 'Singing',           'description' => 'Ability to sing melodiously'],
            ['name' => 'Sports',            'description' => 'Excels in sports activities'],
            ['name' => 'Woodworking',       'description' => 'Creates items from wood'],
            ['name' => 'Writing',           'description' => 'Good at creative or academic writing'],
        ] as $talent) {
            Talent::query()->updateOrCreate(
                ['name' => $talent['name']],
                $talent,
            );
        }
    }
}
