<?php

namespace Database\Seeders;

use App\Models\TaxCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxCodeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allData = $this->getSeedableJsonData();
        foreach ($allData as $data)
        {
            TaxCode::create($data);
        }

        $this->command->info('✅ Successfully seeded ' . count($allData) . ' agricultural & food tax codes');
    }


    protected function getSeedableJsonData()
    {
        return [
            // Live Animals - Chapter 01
            ['code' => '0101', 'type' => 'goods', 'description' => 'Live horses, asses, mules, and hinnies', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0102', 'type' => 'goods', 'description' => 'Live bovine animals', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0103', 'type' => 'goods', 'description' => 'Live swine', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0104', 'type' => 'goods', 'description' => 'Live sheep and goats', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0105', 'type' => 'goods', 'description' => 'Live poultry (chickens, ducks, turkeys, etc.)', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0106', 'type' => 'goods', 'description' => 'Other live animals', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Meat & Edible Meat Offal - Chapter 02
            ['code' => '0201', 'type' => 'goods', 'description' => 'Meat of bovine animals, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0202', 'type' => 'goods', 'description' => 'Meat of bovine animals, frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0203', 'type' => 'goods', 'description' => 'Meat of swine, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0204', 'type' => 'goods', 'description' => 'Meat of sheep or goats, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0205', 'type' => 'goods', 'description' => 'Meat of horses, asses, mules, or hinnies, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0206', 'type' => 'goods', 'description' => 'Edible offal of bovine animals, swine, sheep, goats, or horses, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0207', 'type' => 'goods', 'description' => 'Meat and edible offal of poultry, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0208', 'type' => 'goods', 'description' => 'Other meat and edible offal, fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0209', 'type' => 'goods', 'description' => 'Pig fat, free of lean meat, and poultry fat, not rendered or otherwise extracted, fresh, chilled, frozen, salted, in brine, dried or smoked', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0210', 'type' => 'goods', 'description' => 'Meat and edible meat offal, salted, in brine, dried or smoked; edible flours and meals of meat or meat offal', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Fish & Crustaceans - Chapter 03
            ['code' => '0301', 'type' => 'goods', 'description' => 'Fish, live', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0302', 'type' => 'goods', 'description' => 'Fish, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0303', 'type' => 'goods', 'description' => 'Fish, frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0304', 'type' => 'goods', 'description' => 'Fish fillets and other fish meat (whether or not minced), fresh, chilled, or frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0305', 'type' => 'goods', 'description' => 'Fish dried, salted, or in brine; smoked fish, and other fish prepared or preserved', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0306', 'type' => 'goods', 'description' => 'Crustaceans and molluscs, live, fresh, chilled, frozen, dried, salted, or in brine', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0307', 'type' => 'goods', 'description' => 'Molluscs, whether in shell or not, live, fresh, chilled, frozen, dried, salted, or in brine; smoked molluscs', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0308', 'type' => 'goods', 'description' => 'Aquatic invertebrates other than crustaceans and molluscs, live, fresh, chilled, frozen, dried, salted, or in brine; smoked aquatic invertebrates', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Dairy Produce - Chapter 04
            ['code' => '0401', 'type' => 'goods', 'description' => 'Milk and cream, not concentrated or sweetened', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0402', 'type' => 'goods', 'description' => 'Milk and cream, concentrated or sweetened', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0403', 'type' => 'goods', 'description' => 'Buttermilk, curdled milk, and cream, yogurt, and other fermented or acidified milk', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0404', 'type' => 'goods', 'description' => 'Whey, whether or not concentrated or containing added sugar or other sweetening matter', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0405', 'type' => 'goods', 'description' => 'Butter and other fats and oils derived from milk', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0406', 'type' => 'goods', 'description' => 'Cheese and curd', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0407', 'type' => 'goods', 'description' => 'Birds\' eggs, in shell, fresh, preserved, or cooked', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0408', 'type' => 'goods', 'description' => 'Birds\' eggs, not in shell, and egg yolks, fresh, preserved, or cooked', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0409', 'type' => 'goods', 'description' => 'Natural honey', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0410', 'type' => 'goods', 'description' => 'Edible products of animal origin, not elsewhere specified or included', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Products of Animal Origin - Chapter 05
            ['code' => '0501', 'type' => 'goods', 'description' => 'Human hair, unworked, whether or not washed or scoured', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0502', 'type' => 'goods', 'description' => 'Pigs\' bristles and hair; badger hair; hog hair; and other brush making hair; waste of such bristles and hair', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0503', 'type' => 'goods', 'description' => 'Animal products of the class Mammalia, not elsewhere specified or included', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0504', 'type' => 'goods', 'description' => 'Guts, bladders, and stomachs of animals (other than fish), whole and pieces; such products, uncleaned or not prepared for human consumption', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0505', 'type' => 'goods', 'description' => 'Skins and other parts of birds, with feathers or down, feathers and parts of feathers (whether or not with down), and parts of birds\' eggs, not elsewhere specified or included', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0506', 'type' => 'goods', 'description' => 'Bones and horn-cores, unworked, defatted, simply prepared (but not otherwise worked) or merely sliced or crushed; powder and waste of these products', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0507', 'type' => 'goods', 'description' => 'Ivory, tortoiseshell, whalebone, and whalebone hair; animal nails, claws, and hooves, unworked, defatted, simply prepared (but not otherwise worked) or merely sliced or crushed; powder and waste of these products', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0508', 'type' => 'goods', 'description' => 'Coral and similar materials, unworked or simply prepared; shells of molluscs, and crustaceans, and cuttle-bone, unworked or simply prepared', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0509', 'type' => 'goods', 'description' => 'Natural sponges of animal origin', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0510', 'type' => 'goods', 'description' => 'Ambergris, castoreum, civet, and musk; glandular secretions of animals, whether or not refined', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0511', 'type' => 'goods', 'description' => 'Animal products not elsewhere specified or included', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Live Trees & Plants - Chapter 06
            ['code' => '0601', 'type' => 'goods', 'description' => 'Bulbs, tubers, tuberous roots, corms, crowns, and rhizomes, dormant; chicory plants and roots', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0602', 'type' => 'goods', 'description' => 'Other live plants (including their roots), cuttings, and slips; mushroom spawn', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0603', 'type' => 'goods', 'description' => 'Cut flowers and flower buds of a kind suitable for bouquets or for ornamental purposes', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0604', 'type' => 'goods', 'description' => 'Foliage, branches, and other parts of plants, without flowers or flower buds, and grasses, mosses, and lichens, being goods of a kind suitable for bouquets or for ornamental purposes', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Edible Vegetables - Chapter 07
            ['code' => '0701', 'type' => 'goods', 'description' => 'Potatoes, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0702', 'type' => 'goods', 'description' => 'Tomatoes, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0703', 'type' => 'goods', 'description' => 'Onions, shallots, and garlic, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0704', 'type' => 'goods', 'description' => 'Cabbages, cauliflowers, and headed broccoli, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0705', 'type' => 'goods', 'description' => 'Lettuce (Lactuca sativa) and chicory (Cichorium spp.), fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0706', 'type' => 'goods', 'description' => 'Carrots and turnips, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0707', 'type' => 'goods', 'description' => 'Cucumbers and gherkins, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0708', 'type' => 'goods', 'description' => 'Leguminous vegetables, shelled or unshelled, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0709', 'type' => 'goods', 'description' => 'Other vegetables, fresh or chilled', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0710', 'type' => 'goods', 'description' => 'Vegetables (uncooked or cooked by steaming or boiling in water), frozen', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0711', 'type' => 'goods', 'description' => 'Vegetables provisionally preserved (for example, by sulphur dioxide gas, in brine, in sulphur water or in other preservative solutions), but unsuitable in that state for immediate consumption', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0712', 'type' => 'goods', 'description' => 'Dried vegetables, whole, cut, sliced, broken or in powder, but not further prepared', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0713', 'type' => 'goods', 'description' => 'Dried leguminous vegetables, shelled, whether or not skinned or split', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0714', 'type' => 'goods', 'description' => 'Manioc, arrowroot, salep, Jerusalem artichokes, sweet potatoes, and similar roots and tubers with high starch or inulin content, fresh, chilled, frozen or dried, whether or not sliced or in the form of pellets; sago pith', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],

            // Edible Fruits & Nuts - Chapter 08
            ['code' => '0801', 'type' => 'goods', 'description' => 'Coconuts, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0802', 'type' => 'goods', 'description' => 'Other nuts, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0803', 'type' => 'goods', 'description' => 'Bananas, including plantains, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0804', 'type' => 'goods', 'description' => 'Dates, figs, pineapples, avocados, guavas, mangoes, and mangosteens, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0805', 'type' => 'goods', 'description' => 'Citrus fruit, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0806', 'type' => 'goods', 'description' => 'Grapes, fresh or dried', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0807', 'type' => 'goods', 'description' => 'Melons (including watermelons) and papaws (papayas), fresh', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0808', 'type' => 'goods', 'description' => 'Apples, pears and quinces, fresh', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0809', 'type' => 'goods', 'description' => 'Apricots, cherries, peaches (including nectarines), plums and sloes, fresh', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0810', 'type' => 'goods', 'description' => 'Other fruit, fresh', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0811', 'type' => 'goods', 'description' => 'Fruit and nuts, uncooked or cooked by steaming or boiling in water, frozen, whether or not containing added sugar or other sweetening matter', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0812', 'type' => 'goods', 'description' => 'Fruit and nuts, provisionally preserved (for example, by sulphur dioxide gas, in brine, in sulphur water or in other preservative solutions), but unsuitable in that state for immediate consumption', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0813', 'type' => 'goods', 'description' => 'Fruit, dried, other than that of headings 0801 to 0806; mixtures of nuts or dried fruits of this chapter', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
            ['code' => '0814', 'type' => 'goods', 'description' => 'Peel of citrus fruit or melons (including watermelons), fresh, frozen, dried or provisionally preserved in brine, in sulphur water or in other preservative solutions', 'cgst_rate' => 2.50, 'sgst_rate' => 2.50, 'igst_rate' => 5.00, 'cess_rate' => 0.00, 'is_active' => true],
        ];
    }
}
